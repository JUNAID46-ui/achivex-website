<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\TwoFactor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Tests\TestCase;

// Two-step login and password rules. Runs on an in-memory SQLite database, never on Supabase.
class TwoFactorTest extends TestCase
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
            $t->text('two_factor_secret')->nullable();
            $t->text('two_factor_recovery_codes')->nullable();
            $t->timestamp('two_factor_confirmed_at')->nullable();
            $t->rememberToken();
            $t->timestamps();
        });
        $this->createActivityLogs();
    }

    private function user(bool $twoFactor): User
    {
        $user = User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'correct-horse-9', 'role' => 'administrator']);
        if ($twoFactor) {
            $user->forceFill([
                'two_factor_secret' => 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ',
                'two_factor_recovery_codes' => ['abcde-fghij'],
                'two_factor_confirmed_at' => now(),
            ])->save();
        }

        return $user;
    }

    public function test_codes_match_the_rfc_6238_test_vector(): void
    {
        // Secret "12345678901234567890" at 59 seconds gives 94287082; apps show the last 6 digits.
        $this->assertSame('287082', TwoFactor::code('GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ', 1));
        $this->assertSame(32, strlen(TwoFactor::newSecret()));
    }

    public function test_a_code_works_only_once(): void
    {
        $secret = TwoFactor::newSecret();
        $code = TwoFactor::code($secret, intdiv(time(), 30));
        $this->assertTrue(TwoFactor::verify($secret, $code, 1));
        $this->assertFalse(TwoFactor::verify($secret, $code, 1));
        $this->assertFalse(TwoFactor::verify($secret, '000000x', 2));
    }

    public function test_password_alone_does_not_sign_in_when_two_step_is_on(): void
    {
        $this->user(true);
        $this->post('/admin/login', ['email' => 'admin@example.com', 'password' => 'correct-horse-9'])
            ->assertRedirect(route('admin.two-factor'));
        $this->assertGuest();

        $this->post('/admin/two-factor', ['code' => '000000'])->assertSessionHasErrors('code');
        $this->assertGuest();

        $code = TwoFactor::code('GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ', intdiv(time(), 30));
        $this->post('/admin/two-factor', ['code' => $code])->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();
    }

    public function test_a_recovery_code_signs_in_once(): void
    {
        $user = $this->user(true);
        $this->post('/admin/login', ['email' => 'admin@example.com', 'password' => 'correct-horse-9']);
        $this->post('/admin/two-factor', ['code' => 'ABCDE-FGHIJ'])->assertRedirect(route('admin.dashboard'));
        $this->assertSame([], $user->fresh()->two_factor_recovery_codes);
    }

    public function test_the_code_page_needs_the_password_step_first(): void
    {
        $this->user(true);
        $this->get('/admin/two-factor')->assertRedirect(route('admin.login'));
        $this->post('/admin/two-factor', ['code' => '123456'])->assertRedirect(route('admin.login'));
        $this->assertGuest();
    }

    public function test_users_without_two_step_sign_in_with_the_password(): void
    {
        $this->user(false);
        $this->post('/admin/login', ['email' => 'admin@example.com', 'password' => 'correct-horse-9'])
            ->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();
    }

    public function test_secret_is_stored_encrypted(): void
    {
        $this->user(true);
        $raw = DB::table('users')->value('two_factor_secret');
        $this->assertStringNotContainsString('GEZDGNBV', $raw);
    }

    public function test_weak_passwords_are_rejected(): void
    {
        // The breach check is left out here so the test does not call the internet.
        Password::defaults(fn () => Password::min(10)->letters()->numbers());
        $this->assertTrue(Validator::make(['p' => 'short1'], ['p' => Password::defaults()])->fails());
        $this->assertTrue(Validator::make(['p' => 'onlyletterslong'], ['p' => Password::defaults()])->fails());
        $this->assertFalse(Validator::make(['p' => 'letters-and-123'], ['p' => Password::defaults()])->fails());
    }

    public function test_setup_shows_a_qr_code(): void
    {
        $svg = TwoFactor::qrSvg(TwoFactor::newSecret(), 'admin@example.com');
        $this->assertStringContainsString('<svg', $svg);
    }
}
