<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\AdminNotification;
use App\Services\Notifier;
use Illuminate\Support\Facades\DB;
use Tests\PostgresTestCase;

class NotificationsAndActivityTest extends PostgresTestCase
{
    public function test_activity_records_role_module_status_and_safe_context(): void
    {
        $admin = $this->makeUser();
        $this->actingAs($admin);
        ActivityLog::record('updated', 'Blog Post', 'Hello', null, ['id' => 'hello', 'context' => ['field' => 'title', 'password' => 'x', 'api_key' => 'y', 'nested' => ['token' => 'z', 'ok' => 1]]]);
        $row = ActivityLog::latest('id')->first();
        $this->assertSame('administrator', $row->user_role);
        $this->assertSame('Administrator User', $row->user_name);
        $this->assertSame('hello', $row->subject_id);
        $this->assertSame('success', $row->status);
        $this->assertSame(['field' => 'title', 'nested' => ['ok' => 1]], $row->context);
    }

    public function test_activity_rows_cannot_be_changed_by_the_app_or_the_database(): void
    {
        ActivityLog::record('login', 'User', 'x');
        $row = ActivityLog::first();
        try {
            $row->update(['action' => 'changed']);
            $this->fail('Update should be blocked');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException) {
        }
        $this->expectException(\Illuminate\Database\QueryException::class);
        DB::table('activity_logs')->where('id', $row->id)->delete();
    }

    public function test_deleting_a_user_keeps_their_activity_with_the_name(): void
    {
        $user = $this->makeUser('editor');
        $this->actingAs($user);
        ActivityLog::record('updated', 'Page', 'Home');
        auth()->logout();
        $user->delete();
        $row = ActivityLog::first();
        $this->assertNull($row->user_id);
        $this->assertSame('Editor User', $row->user_name);
    }

    public function test_notifications_go_only_to_users_who_can_open_the_area(): void
    {
        $admin = $this->makeUser();
        $editor = $this->makeUser('editor');
        $this->assertSame(1, Notifier::send('backup_failed', 'critical', 'Backup failed', null, 'Backup', '/admin/backup', 'tools'));
        $this->assertSame(1, AdminNotification::where('user_id', $admin->id)->count());
        $this->assertSame(0, AdminNotification::where('user_id', $editor->id)->count());
        $this->assertSame(2, Notifier::send('seo', 'info', 'Audit done', null, 'SEO', '/admin/seo', 'seo'));
    }

    public function test_repeated_checks_do_not_flood_notifications(): void
    {
        $this->makeUser();
        Notifier::send('storage', 'warning', 'Storage 85% full', dedupe: 'storage-85');
        Notifier::send('storage', 'warning', 'Storage 85% full', dedupe: 'storage-85');
        $this->assertSame(1, AdminNotification::count());
    }

    public function test_users_cannot_see_or_change_other_users_notifications(): void
    {
        $admin = $this->makeUser();
        $other = $this->makeUser('administrator', 'other@example.com');
        Notifier::send('x', 'info', 'Hello');
        $mine = AdminNotification::where('user_id', $admin->id)->first();
        $theirs = AdminNotification::where('user_id', $other->id)->first();

        $this->actingAs($admin)->get('/admin/notifications')->assertOk()->assertSee('Hello');
        $this->actingAs($admin)->post("/admin/notifications/{$theirs->id}", ['action' => 'archive'])->assertNotFound();
        $this->actingAs($admin)->get("/admin/notifications/{$theirs->id}/open")->assertNotFound();
        $this->actingAs($admin)->post("/admin/notifications/{$mine->id}", ['action' => 'archive'])->assertRedirect();
        $this->assertNotNull($mine->fresh()->archived_at);
        $this->assertNull($theirs->fresh()->archived_at);
    }

    public function test_notification_links_never_leave_the_admin(): void
    {
        $admin = $this->makeUser();
        $n = AdminNotification::create(['user_id' => $admin->id, 'type' => 'x', 'title' => 'x', 'url' => '//evil.example/']);
        $this->actingAs($admin)->get("/admin/notifications/{$n->id}/open")->assertRedirect(route('admin.notifications'));
    }
}
