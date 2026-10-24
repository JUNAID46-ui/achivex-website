<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Backup;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

// Runs on a throwaway local PostgreSQL with the production table layout (tests/fixtures/schema.sql).
// Set TEST_PG_HOST (and optionally TEST_PG_PORT) to run it; it is skipped otherwise.
class BackupTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (! getenv('TEST_PG_HOST')) {
            $this->markTestSkipped('Set TEST_PG_HOST to run the backup tests on PostgreSQL.');
        }
        $base = ['driver' => 'pgsql', 'host' => getenv('TEST_PG_HOST'), 'port' => getenv('TEST_PG_PORT') ?: 5432, 'username' => 'postgres', 'password' => '', 'charset' => 'utf8', 'prefix' => '', 'search_path' => 'admin,public', 'sslmode' => 'disable'];
        config(['database.connections.setup' => $base + ['database' => 'postgres']]);
        DB::connection('setup')->statement('drop database if exists achivex_backup_test with (force)');
        DB::connection('setup')->statement('create database achivex_backup_test');
        DB::purge('setup');
        config(['database.connections.pgsql' => $base + ['database' => 'achivex_backup_test'], 'database.default' => 'pgsql', 'cache.default' => 'array']);
        DB::purge('pgsql');
        DB::unprepared(file_get_contents(base_path('tests/fixtures/schema.sql')));
        foreach (['0001_01_01_000000_create_users_table', '0001_01_01_000001_create_cache_table', '0001_01_01_000002_create_jobs_table', '0001_01_01_000003_create_site_content_tables', '2026_10_19_000001_create_admin_tables', '2026_10_20_000001_create_pages_and_settings', '2026_10_21_000001_add_inbox_notifications', '2026_10_22_000001_add_two_factor_to_users'] as $m) {
            DB::table('migrations')->insert(['migration' => $m, 'batch' => 1]);
        }
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        $this->seedSite();
    }

    private function seedSite(): void
    {
        DB::table('public.blog_posts')->insert([
            ['slug' => 'first', 'title' => 'First post', 'category' => 'News', 'excerpt' => 'x', 'intro_keyword' => 'k', 'intro_text' => 't', 'sections' => json_encode([['h' => 'Intro', 'p' => ['a', 'b']]]), 'published' => 'true', 'featured' => 'false'],
            ['slug' => 'second', 'title' => 'Second post', 'category' => 'News', 'excerpt' => 'x', 'intro_keyword' => 'k', 'intro_text' => 't', 'sections' => '[]', 'published' => 'false', 'featured' => 'true'],
        ]);
        DB::table('public.case_studies')->insert(['slug' => 'shop', 'title' => 'Shop', 'client_type' => 'Retail', 'industry' => 'Retail', 'services' => '{digital,"cloud ops"}', 'summary' => 's', 'overview' => 'o']);
        DB::table('public.hero_stats')->insert([['value' => '20', 'label' => 'Services'], ['value' => '7', 'label' => 'Areas']]);
        DB::table('public.contact_submissions')->insert(['name' => 'Jane', 'email' => 'jane@example.com', 'message' => 'Hello']);
        DB::table('admin.media')->insert(['path' => '2026/10/a.png', 'filename' => 'a.png', 'disk' => 'supabase']);
        DB::table('admin.users')->insert(['name' => 'Owner', 'email' => 'owner@example.com', 'password' => bcrypt('secret-pass'), 'role' => 'administrator']);
    }

    private function export(array $groups = ['inbox']): array
    {
        $out = fopen('php://memory', 'w+');
        Backup::write($out, Backup::tables($groups));
        rewind($out);

        return json_decode(stream_get_contents($out), true, flags: JSON_THROW_ON_ERROR);
    }

    public function test_backup_holds_content_and_never_admin_accounts(): void
    {
        $data = $this->export();

        $this->assertSame('achivex-backup', $data['format']);
        $this->assertCount(2, $data['tables']['public.blog_posts']);
        $this->assertCount(1, $data['tables']['public.contact_submissions']);
        $this->assertArrayNotHasKey('admin.users', $data['tables']);
        $this->assertArrayNotHasKey('public.page_views', $data['tables']);
        $this->assertArrayHasKey('public.page_views', $this->export(['inbox', 'analytics'])['tables']);
    }

    public function test_merge_restore_brings_back_deleted_and_changed_rows_and_keeps_new_ones(): void
    {
        $data = $this->export();
        DB::table('public.blog_posts')->where('slug', 'first')->delete();
        DB::table('public.blog_posts')->where('slug', 'second')->update(['title' => 'Changed']);
        DB::table('public.blog_posts')->insert(['slug' => 'new', 'title' => 'New', 'category' => 'News', 'excerpt' => 'x', 'intro_keyword' => 'k', 'intro_text' => 't']);
        DB::table('public.hero_stats')->delete();

        Backup::restore(Backup::parse($data), replace: false);

        $posts = DB::table('public.blog_posts')->orderBy('slug')->get()->keyBy('slug');
        $this->assertSame(['first', 'new', 'second'], $posts->keys()->all());
        $this->assertSame('Second post', $posts['second']->title);
        $this->assertTrue($posts['first']->published);
        $this->assertFalse($posts['second']->published);
        $this->assertSame([['h' => 'Intro', 'p' => ['a', 'b']]], json_decode($posts['first']->sections, true));
        $this->assertSame('{digital,"cloud ops"}', DB::table('public.case_studies')->value('services'));
        $this->assertSame([1, 2], DB::table('public.hero_stats')->orderBy('id')->pluck('id')->map(fn ($i) => (int) $i)->all());

        // The id counter moves past restored rows, so new rows do not collide.
        DB::table('public.hero_stats')->insert(['value' => '1', 'label' => 'New']);
        $this->assertSame(3, (int) DB::table('public.hero_stats')->max('id'));
    }

    public function test_replace_restore_removes_rows_added_after_the_backup(): void
    {
        $data = $this->export();
        DB::table('public.blog_posts')->insert(['slug' => 'new', 'title' => 'New', 'category' => 'News', 'excerpt' => 'x', 'intro_keyword' => 'k', 'intro_text' => 't']);
        DB::table('public.contact_submissions')->insert(['name' => 'Later', 'email' => 'later@example.com', 'message' => 'Hi']);

        Backup::restore(Backup::parse($data), replace: true);

        $this->assertSame(['first', 'second'], DB::table('public.blog_posts')->orderBy('slug')->pluck('slug')->all());
        $this->assertSame(['Jane'], DB::table('public.contact_submissions')->pluck('name')->all());
        $this->assertSame(1, DB::table('admin.users')->count());
    }

    public function test_a_failed_restore_changes_nothing(): void
    {
        $data = $this->export();
        $data['tables']['public.blog_posts'][1]['title'] = null; // breaks a NOT NULL rule
        DB::table('public.blog_posts')->where('slug', 'first')->update(['title' => 'Edited']);

        try {
            Backup::restore(Backup::parse($data), replace: true);
            $this->fail('The restore should have failed.');
        } catch (\Illuminate\Database\QueryException) {
        }

        $this->assertSame('Edited', DB::table('public.blog_posts')->where('slug', 'first')->value('title'));
        $this->assertSame(2, DB::table('public.blog_posts')->count());
    }

    public function test_crafted_files_are_rejected_or_neutralised(): void
    {
        foreach ([null, ['format' => 'other', 'tables' => []], ['format' => 'achivex-backup', 'tables' => ['admin.users' => []]], ['format' => 'achivex-backup', 'tables' => ['public.blog_posts' => ['not a row']]]] as $bad) {
            try {
                Backup::parse($bad);
                $this->fail('Accepted a bad file.');
            } catch (ValidationException) {
                $this->assertTrue(true);
            }
        }

        $data = $this->export();
        $data['tables']['public.blog_posts'][0]['title"; drop table admin.users; --'] = 'x';
        Backup::restore(Backup::parse($data), replace: false);
        $this->assertSame(1, DB::table('admin.users')->count());
    }

    public function test_only_administrators_can_use_backups(): void
    {
        $admin = User::where('role', 'administrator')->first();
        $editor = User::create(['name' => 'Editor', 'email' => 'editor@example.com', 'password' => 'secret-pass', 'role' => 'editor']);

        $this->actingAs($editor)->get('/admin/backup')->assertForbidden();
        $this->actingAs($editor)->get('/admin/backup/download')->assertForbidden();
        $this->actingAs($editor)->get('/admin/cache')->assertForbidden();

        $download = $this->actingAs($admin)->get('/admin/backup/download?inbox=1');
        $download->assertOk();
        $json = $download->streamedContent();
        $this->assertSame('achivex-backup', json_decode($json, true)['format']);

        DB::table('public.blog_posts')->delete();
        $file = UploadedFile::fake()->createWithContent('backup.json', $json);
        $this->actingAs($admin)->post('/admin/backup/restore', ['file' => $file, 'mode' => 'merge'])->assertSessionHasErrors('confirm_text');
        $this->assertSame(0, DB::table('public.blog_posts')->count());
        $this->actingAs($admin)->post('/admin/backup/restore', ['file' => $file, 'mode' => 'merge', 'confirm_text' => 'RESTORE'])->assertSessionHasNoErrors();
        $this->assertSame(2, DB::table('public.blog_posts')->count());

        $this->actingAs($admin)->get('/admin/backup')->assertOk()->assertSee('Restore from a file');
        $this->actingAs($admin)->get('/admin/cache')->assertOk()->assertSee('Clear all cache');
    }
}
