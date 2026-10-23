<?php

namespace Tests\Feature;

use App\Models\AdminNotification;
use App\Models\AppSetting;
use App\Models\BlockedIp;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Tests\PostgresTestCase;

class LoginGuardTest extends PostgresTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            // A real "smtp" mailer name with an in-memory transport, so nothing leaves the machine.
            'mail.default' => 'smtp',
            'mail.mailers.smtp' => ['transport' => 'array', 'password' => 'set'],
            'mail.from' => ['address' => 'site@example.com', 'name' => 'Site'],
        ]);
    }

    private function sent(): array
    {
        return Mail::mailer('smtp')->getSymfonyTransport()->messages()->all();
    }

    private function wrong(string $ip = '203.0.113.7', string $email = 'owner@example.com')
    {
        return $this->withServerVariables(['REMOTE_ADDR' => $ip, 'HTTP_USER_AGENT' => 'TestBrowser/1.0'])
            ->post('/admin/login', ['email' => $email, 'password' => 'wrong-password']);
    }

    public function test_three_failed_sign_ins_block_the_ip_record_it_and_email_the_owner(): void
    {
        $this->makeUser();
        AppSetting::put('mail', ['notify_to' => 'junaidmurtaza862@gmail.com']);

        $this->wrong()->assertSessionHasErrors('email');
        $this->wrong(email: 'guess@example.com')->assertSessionHasErrors('email');
        $this->wrong()->assertStatus(403)->assertSee('Access blocked');

        $block = BlockedIp::sole();
        $this->assertSame('203.0.113.7', $block->ip);
        $this->assertSame(3, $block->attempts);
        $this->assertEqualsCanonicalizing(['owner@example.com', 'guess@example.com'], $block->emails);
        $this->assertSame('TestBrowser/1.0', $block->user_agent);
        $this->assertTrue($block->isActive());
        $this->assertNotNull($block->notified_at);

        // Every admin page is closed to that address now, even with the right password.
        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.7'])->post('/admin/login', ['email' => 'administrator@example.com', 'password' => 'correct-horse-9'])->assertStatus(403);
        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.7'])->get('/admin/login')->assertStatus(403);
        // Other addresses are not affected.
        $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.2'])->get('/admin/login')->assertOk();

        $mails = $this->sent();
        $this->assertCount(1, $mails);
        $this->assertSame('junaidmurtaza862@gmail.com', $mails[0]->getOriginalMessage()->getTo()[0]->getAddress());
        $this->assertStringContainsString('203.0.113.7', $mails[0]->getOriginalMessage()->getSubject());
        $this->assertStringNotContainsString('wrong-password', $mails[0]->toString());
        $this->assertSame(1, AdminNotification::where('severity', 'critical')->count());
        $this->assertDatabaseHas('activity_logs', ['action' => 'ip_blocked', 'subject_label' => '203.0.113.7']);
    }

    public function test_an_administrator_can_unblock_or_keep_an_address_blocked(): void
    {
        $admin = $this->makeUser();
        foreach (range(1, 3) as $i) {
            $this->wrong('192.0.2.50');
        }
        $block = BlockedIp::sole();

        // The administrator works from another network.
        $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.9']);
        $this->actingAs($admin)->get('/admin/security')->assertOk()->assertSee('192.0.2.50')->assertSee('Blocked IP addresses');
        $this->actingAs($admin)->post("/admin/security/blocked/{$block->id}/keep")->assertRedirect();
        $this->assertNull($block->fresh()->expires_at);
        $this->actingAs($admin)->post("/admin/security/blocked/{$block->id}/unblock")->assertRedirect();
        $this->assertFalse($block->fresh()->isActive());

        auth()->logout();
        $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.50'])->get('/admin/login')->assertOk();
        // Failures from before the unblock do not count again: one more wrong try is not a new block.
        $this->wrong('192.0.2.50')->assertSessionHasErrors('email');
        $this->assertSame(1, BlockedIp::count());

        $editor = $this->makeUser('editor');
        $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.9']);
        $this->actingAs($editor)->post("/admin/security/blocked/{$block->id}/unblock")->assertForbidden();
    }

    public function test_the_signed_in_user_and_the_last_administrator_cannot_be_deleted_or_demoted(): void
    {
        $admin = $this->makeUser();
        $editor = $this->makeUser('editor');

        $this->actingAs($admin)->get('/admin/r/users')->assertOk();
        $this->actingAs($admin)->delete("/admin/r/users/{$admin->id}")->assertSessionHasErrors('delete');
        $this->actingAs($admin)->post('/admin/r/users/bulk', ['action' => 'delete', 'ids' => [$admin->id]]);
        $this->assertNotNull($admin->fresh());
        $this->actingAs($admin)->put("/admin/r/users/{$admin->id}", ['name' => $admin->name, 'email' => $admin->email, 'role' => 'editor'])->assertSessionHasErrors('role');
        $this->assertSame('administrator', $admin->fresh()->role);

        // With a second administrator, the first one can delete the other, but never the last one left.
        $other = $this->makeUser('administrator', 'second@example.com');
        $this->actingAs($admin)->delete("/admin/r/users/{$other->id}")->assertSessionHasNoErrors();
        $this->assertNull(User::find($other->id));
        $this->actingAs($admin)->delete("/admin/r/users/{$editor->id}")->assertSessionHasNoErrors();
        $this->assertNull(User::find($editor->id));
    }
}
