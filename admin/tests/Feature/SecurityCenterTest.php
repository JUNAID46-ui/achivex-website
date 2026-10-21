<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\AuditRun;
use App\Services\Security\SecretScanner;
use App\Services\Security\SecurityScan;
use Illuminate\Support\Facades\Http;
use Tests\PostgresTestCase;

class SecurityCenterTest extends PostgresTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::fake([
            'api.osv.dev/v1/querybatch' => Http::response(['results' => [['vulns' => [['id' => 'GHSA-test-0001']]]]]),
            'api.osv.dev/v1/vulns/*' => Http::response(['summary' => 'Example advisory']),
            '*' => Http::response('', 404),
        ]);
    }

    public function test_failed_sign_ins_are_recorded_without_the_password(): void
    {
        $this->makeUser();
        $this->post('/admin/login', ['email' => 'Administrator@example.com', 'password' => 'wrong-password-123'])->assertSessionHasErrors('email');
        $row = ActivityLog::where('action', 'login_failed')->first();
        $this->assertSame('failure', $row->status);
        $this->assertSame('administrator@example.com', $row->subject_label);
        $this->assertStringNotContainsString('wrong-password-123', json_encode($row->toArray()));
        $this->post('/admin/login', ['email' => 'administrator@example.com', 'password' => 'correct-horse-9'])->assertRedirect();
        $this->assertSame(1, ActivityLog::where('action', 'login')->count());
    }

    public function test_repeated_failures_are_flagged(): void
    {
        $this->makeUser();
        for ($i = 0; $i < 6; $i++) {
            ActivityLog::failure('login_failed', 'User', 'administrator@example.com', 'Wrong email or password');
        }
        $checks = collect(SecurityScan::authPatterns())->pluck('check');
        $this->assertContains('Repeated failed sign ins for one account', $checks);
    }

    public function test_role_changes_are_logged_as_sensitive(): void
    {
        $admin = $this->makeUser();
        $editor = $this->makeUser('editor');
        $this->actingAs($admin);
        $editor->update(['role' => 'author']);
        $row = ActivityLog::where('action', 'role_changed')->first();
        $this->assertSame('Role changed from editor to author', $row->description);
        $this->assertSame((string) $editor->id, $row->subject_id);
    }

    public function test_every_route_is_public_on_purpose_or_protected(): void
    {
        $this->assertSame([], SecurityScan::routes());
    }

    public function test_secret_scanner_masks_what_it_finds(): void
    {
        $dir = sys_get_temp_dir().'/scan-'.uniqid();
        mkdir("{$dir}/config", 0777, true);
        $fake = 'sb_secret_'.str_repeat('a1B2', 8);
        file_put_contents("{$dir}/config/x.php", "<?php return ['k' => '{$fake}'];");
        file_put_contents("{$dir}/config/.env", "SECRET={$fake}");
        $hits = SecretScanner::scan($dir);
        $this->assertCount(1, $hits);
        $this->assertSame('config/x.php', $hits[0]['file']);
        $this->assertStringNotContainsString($fake, $hits[0]['masked']);
        $this->assertStringStartsWith('sb_s', $hits[0]['masked']);
        $this->assertSame([], SecretScanner::scan(base_path()), 'The admin code itself contains no credentials');
    }

    public function test_security_center_is_for_administrators_only_and_stores_runs(): void
    {
        $admin = $this->makeUser();
        $editor = $this->makeUser('editor');
        $this->actingAs($editor)->get('/admin/security')->assertForbidden();
        $this->actingAs($editor)->post('/admin/security/scan')->assertForbidden();
        $this->actingAs($admin)->post('/admin/security/scan')->assertRedirect();
        $run = AuditRun::where('type', 'security')->first();
        $this->assertSame('completed', $run->status);
        $checks = collect($run->findings)->pluck('check');
        $this->assertContains('Two-step login is off', $checks);
        $this->assertContains('Vulnerable package: '.json_decode(file_get_contents(base_path('composer.lock')), true)['packages'][0]['name'], $checks);
        $this->actingAs($admin)->get('/admin/security')->assertOk()->assertSee('Two-step login is off');
    }

    public function test_signing_a_user_out_everywhere_needs_the_admin_password(): void
    {
        $admin = $this->makeUser();
        $editor = $this->makeUser('editor');
        $token = $editor->remember_token;
        $this->actingAs($admin)->post("/admin/security/users/{$editor->id}/sessions", ['password' => 'nope'])->assertSessionHasErrors('password');
        $this->actingAs($admin)->post("/admin/security/users/{$editor->id}/sessions", ['password' => 'correct-horse-9'])->assertRedirect();
        $this->assertNotSame($token, $editor->fresh()->remember_token);
        $this->assertSame(1, ActivityLog::where('action', 'logout_all')->count());
    }
}
