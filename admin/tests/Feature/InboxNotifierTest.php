<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\InboxNotifier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class InboxNotifierTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'testing',
            'database.connections.testing' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''],
            // A real "smtp" mailer name with an in-memory transport, so nothing leaves the machine.
            'mail.default' => 'smtp',
            'mail.mailers.smtp' => ['transport' => 'array', 'password' => 'set'],
            'mail.from' => ['address' => 'site@example.com', 'name' => 'Site'],
        ]);
        DB::purge('testing');
        DB::statement("attach database ':memory:' as public");
        Schema::create('users', function ($t) {
            $t->id(); $t->string('name'); $t->string('email'); $t->string('password'); $t->string('role'); $t->rememberToken(); $t->timestamps();
        });
        Schema::create('app_settings', function ($t) {
            $t->string('key')->primary(); $t->text('value'); $t->timestamp('updated_at')->nullable();
        });
        DB::statement('create table public.contact_submissions (id integer primary key, created_at datetime, name text, email text, phone text, service text, message text, read_at datetime, starred boolean, notified_at datetime)');
        DB::statement('create table public.whitepaper_requests (id integer primary key, created_at datetime, whitepaper_slug text, name text, email text, company text, job_title text, handled_at datetime, notified_at datetime)');
        DB::statement('create table public.whitepapers (slug text primary key, title text)');
        User::create(['name' => 'Owner', 'email' => 'owner@example.com', 'password' => 'secret-pass', 'role' => 'administrator']);
    }

    private function sent(): array
    {
        return Mail::mailer('smtp')->getSymfonyTransport()->messages()->all();
    }

    public function test_each_new_message_is_emailed_once(): void
    {
        DB::table('public.contact_submissions')->insert([
            ['id' => 1, 'created_at' => now(), 'name' => "Jane\r\nBcc: x@evil.test", 'email' => 'jane@example.com', 'message' => 'Hello', 'notified_at' => null],
            ['id' => 2, 'created_at' => now(), 'name' => 'Old', 'email' => 'old@example.com', 'message' => 'Already sent', 'notified_at' => now()],
        ]);
        DB::table('public.whitepaper_requests')->insert(['id' => 1, 'created_at' => now(), 'whitepaper_slug' => 'cloud', 'name' => 'Sam', 'email' => 'sam@example.com', 'notified_at' => null]);

        $this->assertSame(2, InboxNotifier::run());
        $this->assertSame(0, InboxNotifier::run());

        $messages = $this->sent();
        $this->assertCount(2, $messages);
        $email = $messages[0]->getOriginalMessage();
        $this->assertSame('owner@example.com', $email->getTo()[0]->getAddress());
        $this->assertSame('jane@example.com', $email->getReplyTo()[0]->getAddress());
        $this->assertSame('New website message from Jane Bcc: x@evil.test', $email->getSubject());
        $this->assertEmpty($email->getBcc());
        $this->assertSame(0, DB::table('public.contact_submissions')->whereNull('notified_at')->count());
    }

    public function test_saved_recipients_replace_the_administrators(): void
    {
        \App\Models\AppSetting::put('mail', ['notify_to' => 'me@gmail.com, not-an-email, team@example.com']);
        $this->assertSame(['me@gmail.com', 'team@example.com'], InboxNotifier::recipients());
    }

    public function test_nothing_is_marked_sent_when_email_is_not_set_up(): void
    {
        config(['mail.mailers.smtp.password' => null]);
        DB::table('public.contact_submissions')->insert(['id' => 1, 'created_at' => now(), 'name' => 'Jane', 'email' => 'jane@example.com', 'message' => 'Hi']);

        $this->assertSame(0, InboxNotifier::run());
        $this->assertSame(1, DB::table('public.contact_submissions')->whereNull('notified_at')->count());
    }

    public function test_the_hook_is_public_but_takes_no_input(): void
    {
        $this->postJson('/hooks/inbox', ['anything' => 'ignored'])->assertOk()->assertJson(['sent' => 0]);
    }
}
