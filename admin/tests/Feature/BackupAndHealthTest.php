<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\AppSetting;
use App\Models\BackupRun;
use App\Models\Content\BlogPost;
use App\Models\ErrorLog;
use App\Services\Backup\BackupManager;
use App\Services\Health\DailyTasks;
use App\Services\Health\HealthCheck;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Tests\PostgresTestCase;

class BackupAndHealthTest extends PostgresTestCase
{
    // In-memory stand-in for the Supabase Storage API.
    private array $objects = [];
    private bool $bucket = false;
    private bool $bucketPublic = false;
    private bool $corrupt = false;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.supabase.url' => 'https://supa.test', 'services.supabase.secret_key' => 'sb_secret_test']);
        Http::fake(function (HttpRequest $r) {
            $url = $r->url();
            $path = parse_url($url, PHP_URL_PATH);
            if (str_ends_with($path, '/storage/v1/bucket/backups') && $r->method() === 'GET') {
                return $this->bucket ? Http::response(['id' => 'backups', 'public' => $this->bucketPublic]) : Http::response(['message' => 'not found'], 400);
            }
            if (str_ends_with($path, '/storage/v1/bucket') && $r->method() === 'POST') {
                $this->bucket = true;
                $this->bucketPublic = (bool) ($r->data()['public'] ?? true);

                return Http::response(['name' => 'backups']);
            }
            if (preg_match('#/storage/v1/object/backups/(.+)$#', $path, $m)) {
                if ($r->method() === 'POST') {
                    $this->objects[$m[1]] = $r->body();

                    return Http::response(['Key' => 'backups/'.$m[1]]);
                }
                if ($r->method() === 'GET') {
                    return isset($this->objects[$m[1]]) ? Http::response($this->corrupt ? $this->objects[$m[1]].' ' : $this->objects[$m[1]]) : Http::response('', 404);
                }
            }
            if (str_ends_with($path, '/storage/v1/object/backups') && $r->method() === 'DELETE') {
                foreach ($r->data()['prefixes'] ?? [] as $p) {
                    unset($this->objects[$p]);
                }

                return Http::response([]);
            }
            if (preg_match('#/storage/v1/object/sign/backups/(.+)$#', $path, $m)) {
                return Http::response(['signedURL' => '/object/sign/backups/'.$m[1].'?token=t']);
            }

            return Http::response('', 404);
        });
        BlogPost::create(['slug' => 'kept', 'title' => 'Kept', 'category' => 'Data', 'excerpt' => 'e', 'intro_keyword' => 'k', 'intro_text' => ' t', 'published' => true]);
    }

    public function test_backups_are_refused_until_private_storage_is_set_up(): void
    {
        $admin = $this->makeUser();
        $this->actingAs($admin);
        $run = BackupManager::create('manual', []);
        $this->assertSame('failed', $run->status);
        $this->assertDatabaseHas('notifications', ['type' => 'backup_failed', 'user_id' => $admin->id]);

        $this->bucket = true;
        $this->bucketPublic = true;
        $this->actingAs($admin)->post('/admin/backup/setup')->assertSessionHasErrors('storage');
        $this->assertSame('failed', BackupManager::create('manual', [])->status, 'A public bucket is never used');
    }

    public function test_a_backup_is_verified_by_reading_it_back(): void
    {
        $admin = $this->makeUser();
        $this->actingAs($admin)->post('/admin/backup/setup')->assertRedirect();
        $this->assertFalse($this->bucketPublic);
        $this->actingAs($admin)->post('/admin/backup/runs', ['inbox' => '1'])->assertSessionHasNoErrors();
        $run = BackupRun::first();
        $this->assertSame('verified', $run->status);
        $this->assertSame(64, strlen($run->sha256));
        $this->assertSame(1, $run->row_counts['public.blog_posts']);
        $this->assertSame(hash('sha256', $this->objects[$run->path]), $run->sha256);

        $this->corrupt = true;
        $this->assertFalse(BackupManager::verify($run->fresh()));
        $this->assertSame('failed', $run->fresh()->status);
        $this->assertStringContainsString('checksum', $run->fresh()->error);
    }

    public function test_restore_shows_a_preview_needs_typed_confirmation_and_saves_a_safety_copy(): void
    {
        $admin = $this->makeUser();
        $this->actingAs($admin)->post('/admin/backup/setup');
        $this->actingAs($admin)->post('/admin/backup/runs');
        $run = BackupRun::first();
        BlogPost::create(['slug' => 'later', 'title' => 'Later', 'category' => 'Data', 'excerpt' => 'e', 'intro_keyword' => 'k', 'intro_text' => ' t', 'published' => true]);
        BlogPost::find('kept')->update(['title' => 'Changed']);

        $this->actingAs($admin)->get("/admin/backup/runs/{$run->id}/preview?mode=replace")->assertOk()->assertSee('public.blog_posts');
        $this->actingAs($admin)->post("/admin/backup/runs/{$run->id}/restore", ['mode' => 'replace', 'confirm_text' => 'yes'])->assertSessionHasErrors('confirm_text');
        $this->assertSame('Changed', BlogPost::find('kept')->title);

        $this->actingAs($admin)->post("/admin/backup/runs/{$run->id}/restore", ['mode' => 'replace', 'confirm_text' => 'RESTORE'])->assertRedirect(route('admin.backup'));
        $this->assertSame('Kept', BlogPost::find('kept')->title);
        $this->assertNull(BlogPost::find('later'));
        $safety = BackupRun::where('trigger', 'pre-restore')->first();
        $this->assertSame('verified', $safety->status);
        $this->assertStringContainsString('"later"', $this->objects[$safety->path]);
        $this->assertSame(1, ActivityLog::where('action', 'restored')->where('status', 'success')->count());
    }

    public function test_retention_deletes_old_files_but_keeps_history(): void
    {
        $admin = $this->makeUser();
        $this->actingAs($admin)->post('/admin/backup/setup');
        AppSetting::put('backups', ['keep' => 2]);
        foreach (range(1, 3) as $i) {
            BackupManager::create('manual', []);
        }
        $this->assertSame(3, BackupRun::count());
        $this->assertSame(1, BackupRun::where('status', 'deleted')->count());
        $this->assertCount(2, $this->objects);
    }

    public function test_only_administrators_reach_backups_and_health(): void
    {
        $editor = $this->makeUser('editor');
        foreach (['/admin/backup', '/admin/health'] as $url) {
            $this->actingAs($editor)->get($url)->assertForbidden();
        }
        $this->actingAs($editor)->post('/admin/backup/runs')->assertForbidden();
    }

    public function test_health_reports_measurements_and_unknowns(): void
    {
        $admin = $this->makeUser();
        $checks = collect(HealthCheck::run())->keyBy('key');
        $this->assertSame('ok', $checks['database']['status']);
        $this->assertSame('unknown', $checks['website']['status'], 'Live checks are not faked as passing');
        $this->assertSame('unknown', $checks['daily']['status']);
        $this->assertSame('warning', $checks['backups']['status']);
        $this->actingAs($admin)->get('/admin/health')->assertOk()->assertSee('Database connection');
    }

    public function test_storage_threshold_raises_one_notification(): void
    {
        $this->makeUser();
        AppSetting::put('health', ['storage_mb' => 50, 'warn' => 80, 'critical' => 95]);
        \App\Models\Media::create(['disk' => 'supabase', 'path' => 'x.png', 'filename' => 'x.png', 'size' => 48 * 1048576]);
        $checks = HealthCheck::run();
        $this->assertSame('critical', collect($checks)->firstWhere('key', 'storage')['status']);
        HealthCheck::notify($checks);
        HealthCheck::notify($checks);
        $this->assertSame(1, \App\Models\AdminNotification::where('type', 'health_storage')->count());
    }

    public function test_errors_are_captured_without_secrets(): void
    {
        ErrorLog::capture(new \RuntimeException('Login failed with password=hunter2 (Connection: pgsql, SQL: select * from users where email = a@b.c)'));
        $row = ErrorLog::first();
        $this->assertStringNotContainsString('hunter2', $row->message);
        $this->assertStringNotContainsString('a@b.c', $row->message);
        $this->assertStringStartsWith('tests/', $row->location);
    }

    public function test_daily_task_runs_once_and_records_each_step(): void
    {
        $this->makeUser();
        $this->bucket = true;
        AppSetting::put('backups', ['scheduled' => true, 'keep' => 5]);
        $this->get('/hooks/daily')->assertOk()->assertJsonPath('backup.ok', true);
        $this->assertSame('scheduled', BackupRun::first()->trigger);
        $this->get('/hooks/daily')->assertOk()->assertJsonPath('skipped', 'Ran less than 20 hours ago');
        $this->assertSame(1, BackupRun::count());

        config(['services.cron_secret' => 'abc']);
        $this->get('/hooks/daily')->assertUnauthorized();
        $this->get('/hooks/daily', ['Authorization' => 'Bearer abc'])->assertOk();
    }
}
