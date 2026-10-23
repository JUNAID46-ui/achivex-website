<?php

namespace Tests\Feature;

use App\Models\Content\BlogPost;
use App\Models\Site;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\PostgresTestCase;

class SitesTest extends PostgresTestCase
{
    private const OTHER = 'achivex_feature_site_two';

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake(['*' => Http::response('', 404)]); // never call a live website from tests
        DB::connection('setup')->statement('drop database if exists '.self::OTHER.' with (force)');
        DB::connection('setup')->statement('create database '.self::OTHER);
    }

    private function siteForm(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Client Co', 'site_url' => 'https://client.example.com', 'public_url' => 'https://www.client.example.com',
            'db_host' => getenv('TEST_PG_HOST'), 'db_port' => getenv('TEST_PG_PORT') ?: 5432, 'db_database' => self::OTHER,
            'db_username' => 'postgres', 'db_password' => 'not-used-locally', 'db_sslmode' => 'disable',
        ], $overrides);
    }

    public function test_only_administrators_manage_sites(): void
    {
        $editor = $this->makeUser('editor');
        $this->actingAs($editor)->get('/admin/sites')->assertForbidden();
        $this->actingAs($editor)->post('/admin/sites', $this->siteForm())->assertForbidden();
    }

    public function test_a_website_is_added_prepared_and_managed_with_its_own_database(): void
    {
        $admin = $this->makeUser();
        $mainPosts = BlogPost::count();

        $this->actingAs($admin)->post('/admin/sites', $this->siteForm())->assertRedirect('/admin/sites');
        $site = Site::first();
        $this->assertSame('needs_setup', $site->status);
        $this->assertNotSame('not-used-locally', DB::table('sites')->value('db_password'), 'password is stored encrypted');

        // Not ready yet, so switching is refused.
        $this->actingAs($admin)->post("/admin/sites/{$site->id}/switch")->assertSessionHas('error');

        $this->actingAs($admin)->post("/admin/sites/{$site->id}/prepare")->assertSessionHas('status');
        $this->assertSame('ready', $site->fresh()->status);

        $this->actingAs($admin)->post("/admin/sites/{$site->id}/switch")->assertRedirect('/admin');
        $this->assertSame($site->id, session('site_id'));

        // Content written now goes to the other website's database.
        $this->actingAs($admin)->post('/admin/r/blog-posts', [
            'title' => 'Hello from site two', 'slug' => 'hello-two', 'excerpt' => 'e', 'intro_keyword' => 'k', 'intro_text' => ' t',
            'published_on' => '2026-10-01', 'category' => 'Data', 'author' => 'Team', 'workflow_status' => 'published',
        ])->assertRedirect();
        $this->actingAs($admin)->get('/admin')->assertOk()->assertSee('Client Co')->assertSee('www.client.example.com');

        config(['database.connections.other' => array_merge(config('database.connections.pgsql'), ['database' => self::OTHER])]);
        $this->assertSame(1, DB::connection('other')->table('public.blog_posts')->where('slug', 'hello-two')->count());
        $this->assertSame(1, DB::connection('other')->table('admin.users')->where('id', $admin->id)->count(), 'the user is mirrored for foreign keys');
        $this->assertSame($mainPosts, BlogPost::where('slug', '!=', 'x')->count() - 0);
        $this->assertNull(BlogPost::find('hello-two'), 'the main website is untouched');
        $this->assertSame(1, User::where('email', $admin->email)->count());

        // Back to the main website.
        $this->actingAs($admin)->post('/admin/sites/main')->assertRedirect('/admin');
        $this->assertNull(session('site_id'));
        DB::purge('other');
    }

    public function test_a_wrong_database_shows_a_clear_error(): void
    {
        $admin = $this->makeUser();
        $this->actingAs($admin)->post('/admin/sites', $this->siteForm(['db_database' => 'does_not_exist_here']))->assertRedirect();
        $site = Site::first();
        $this->assertSame('error', $site->status);
        $this->assertStringContainsString('Could not connect', $site->status_message);
        $this->actingAs($admin)->get('/admin/sites')->assertOk()->assertSee('Cannot connect');
    }
}
