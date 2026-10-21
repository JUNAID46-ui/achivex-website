<?php

namespace Tests\Feature;

use App\Admin\Resources\RedirectResource;
use App\Models\AppSetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

// Security regression tests. They run on an in-memory SQLite database, never on Supabase.
class SecurityTest extends TestCase
{
    use \Tests\SqliteTables;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'testing',
            'database.connections.testing' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''],
        ]);
        DB::purge('testing');
        Schema::create('users', function ($t) {
            $t->id();
            $t->string('name');
            $t->string('email')->unique();
            $t->string('password');
            $t->string('role')->default('editor');
            $t->string('avatar')->nullable();
            $t->timestamp('last_login_at')->nullable();
            $t->timestamp('email_verified_at')->nullable();
            $t->rememberToken();
            $t->timestamps();
        });
        Schema::create('sessions', function ($t) {
            $t->string('id')->primary();
            $t->foreignId('user_id')->nullable();
            $t->string('ip_address', 45)->nullable();
            $t->text('user_agent')->nullable();
            $t->longText('payload');
            $t->integer('last_activity');
        });
        $this->createActivityLogs();
        Schema::create('app_settings', function ($t) {
            $t->string('key')->primary();
            $t->text('value');
            $t->timestamp('updated_at')->nullable();
        });
    }

    public function test_guests_are_sent_to_login_from_protected_pages(): void
    {
        foreach (['/admin', '/admin/settings', '/admin/r/users', '/admin/inbox/messages', '/admin/inbox/messages/export', '/admin/media', '/admin/analytics/export'] as $url) {
            $this->get($url)->assertRedirect(route('admin.login'));
        }
        $this->post('/admin/settings/storage', ['secretKey' => 'x'])->assertRedirect(route('admin.login'));
    }

    public function test_authors_and_editors_cannot_open_admin_only_areas(): void
    {
        $author = User::create(['name' => 'Author', 'email' => 'author@example.com', 'password' => 'secret-pass', 'role' => 'author']);
        $editor = User::create(['name' => 'Editor', 'email' => 'editor@example.com', 'password' => 'secret-pass', 'role' => 'editor']);

        $this->actingAs($author)->get('/admin/settings')->assertForbidden();
        $this->actingAs($author)->get('/admin/r/users')->assertForbidden();
        $this->actingAs($author)->get('/admin/inbox/messages/export')->assertForbidden();
        $this->actingAs($editor)->get('/admin/r/users')->assertForbidden();
        $this->actingAs($editor)->post('/admin/r/users', ['name' => 'X', 'email' => 'x@example.com', 'password' => 'secret-pass', 'role' => 'administrator'])->assertForbidden();
        $this->actingAs($editor)->post('/admin/settings/storage', ['secretKey' => 'x'])->assertForbidden();
        $this->assertSame(0, User::where('email', 'x@example.com')->count());
    }

    public function test_admin_responses_carry_security_headers(): void
    {
        $response = $this->get('/admin/login');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_password_change_signs_out_other_sessions_and_remember_cookies(): void
    {
        $user = User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'old-password', 'role' => 'administrator']);
        $user->forceFill(['remember_token' => 'old-token'])->save();
        DB::table('sessions')->insert([
            ['id' => 'laptop', 'user_id' => $user->id, 'payload' => '', 'last_activity' => time()],
            ['id' => 'phone', 'user_id' => $user->id, 'payload' => '', 'last_activity' => time()],
            ['id' => 'someone-else', 'user_id' => 999, 'payload' => '', 'last_activity' => time()],
        ]);
        config(['session.driver' => 'database']);

        $user->forceFill(['password' => 'new-password'])->save();

        $this->assertSame(['someone-else'], DB::table('sessions')->pluck('id')->all());
        $this->assertNotSame('old-token', $user->fresh()->remember_token);
    }

    public function test_role_change_signs_the_user_out(): void
    {
        $user = User::create(['name' => 'Editor', 'email' => 'e@example.com', 'password' => 'secret-pass', 'role' => 'administrator']);
        DB::table('sessions')->insert(['id' => 'tab', 'user_id' => $user->id, 'payload' => '', 'last_activity' => time()]);
        config(['session.driver' => 'database']);

        $user->update(['role' => 'editor']);

        $this->assertSame(0, DB::table('sessions')->count());
    }

    public function test_secret_settings_are_encrypted_in_the_database(): void
    {
        AppSetting::put('mail', ['host' => 'smtp.example.com', 'password' => 'app-password-123']);
        AppSetting::put('storage', ['secretKey' => 'sb_secret_example']);

        $raw = DB::table('app_settings')->pluck('value', 'key');
        $this->assertStringNotContainsString('app-password-123', $raw['mail']);
        $this->assertStringNotContainsString('sb_secret_example', $raw['storage']);
        $this->assertStringContainsString('smtp.example.com', $raw['mail']);

        $this->assertSame('app-password-123', AppSetting::group('mail')['password']);
        $this->assertSame('sb_secret_example', AppSetting::group('storage')['secretKey']);
    }

    public function test_redirect_targets_must_be_site_paths_or_web_links(): void
    {
        $rules = collect((new RedirectResource)->fields())->firstWhere('name', 'to_path')['rules'];
        foreach (['/new-page/', 'https://example.com/a', 'http://example.com'] as $ok) {
            $this->assertTrue(Validator::make(['to_path' => $ok], ['to_path' => $rules])->passes(), $ok);
        }
        foreach (['javascript:alert(1)', 'data:text/html,x', '//evil.example', '/\\evil.example', 'evil.example'] as $bad) {
            $this->assertTrue(Validator::make(['to_path' => $bad], ['to_path' => $rules])->fails(), $bad);
        }
    }

    public function test_email_fields_reject_line_breaks(): void
    {
        $this->post('/admin/forgot-password', ['email' => "a@example.com\r\nBcc: x@example.com"])
            ->assertSessionHasErrors('email');
    }
}
