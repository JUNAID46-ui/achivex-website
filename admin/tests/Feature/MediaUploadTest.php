<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MediaUploadTest extends TestCase
{
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'testing',
            'database.connections.testing' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''],
            'services.supabase.url' => 'https://project.supabase.co',
            'services.supabase.secret_key' => 'sb_secret_test',
            'admin.max_upload_mb' => 2048,
        ]);
        DB::purge('testing');
        Schema::create('users', function ($t) {
            $t->id(); $t->string('name'); $t->string('email'); $t->string('password'); $t->string('role'); $t->rememberToken(); $t->timestamps();
        });
        Schema::create('app_settings', function ($t) {
            $t->string('key')->primary(); $t->text('value'); $t->timestamp('updated_at')->nullable();
        });
        Schema::create('media', function ($t) {
            $t->id(); $t->string('disk'); $t->string('path'); $t->string('public_url')->nullable(); $t->string('filename'); $t->string('mime')->nullable();
            $t->bigInteger('size')->default(0); $t->integer('width')->nullable(); $t->integer('height')->nullable(); $t->string('alt')->nullable();
            $t->bigInteger('uploaded_by')->nullable(); $t->timestamps();
        });
        Schema::create('activity_logs', function ($t) {
            $t->id(); $t->bigInteger('user_id')->nullable(); $t->string('action'); $t->string('subject_type')->nullable(); $t->string('subject_label')->nullable();
            $t->string('description')->nullable(); $t->string('ip')->nullable(); $t->timestamp('created_at')->nullable();
        });
        $this->admin = User::create(['name' => 'Owner', 'email' => 'owner@example.com', 'password' => 'secret-pass', 'role' => 'administrator']);
    }

    public function test_a_large_photo_goes_straight_to_storage_and_lands_in_the_library(): void
    {
        Http::fake([
            'project.supabase.co/storage/v1/object/upload/sign/*' => Http::response(['url' => '/object/upload/sign/media/x?token=abc']),
            'project.supabase.co/storage/v1/object/public/*' => Http::response('', 200, ['Content-Length' => '734003200', 'Content-Type' => 'image/jpeg']),
        ]);

        $sign = $this->actingAs($this->admin)->postJson('/admin/media/sign', ['filename' => 'Team Photo.JPG', 'size' => 734003200])->assertOk();
        $this->assertStringStartsWith('https://project.supabase.co/storage/v1/object/upload/sign/media/x?token=', $sign['url']);
        $this->assertMatchesRegularExpression('#^\d{4}/\d{2}/team-photo-[a-z0-9]{6}\.jpg$#', $sign['path']);
        $this->assertSame('image/jpeg', $sign['mime']);

        $this->actingAs($this->admin)->postJson('/admin/media/complete', ['path' => $sign['path'], 'filename' => 'Team Photo.JPG', 'width' => 4000, 'height' => 3000])->assertOk();
        $media = Media::first();
        $this->assertSame('supabase', $media->disk);
        $this->assertSame(734003200, (int) $media->size);
        $this->assertSame('https://project.supabase.co/storage/v1/object/public/media/'.$sign['path'], $media->public_url);

        // The same upload cannot be registered twice.
        $this->actingAs($this->admin)->postJson('/admin/media/complete', ['path' => $sign['path'], 'filename' => 'again.jpg'])->assertStatus(422);
    }

    public function test_limits_and_types_are_enforced(): void
    {
        Http::fake();
        $this->actingAs($this->admin)->postJson('/admin/media/sign', ['filename' => 'huge.mp4', 'size' => 2049 * 1048576])->assertStatus(422);
        $this->actingAs($this->admin)->postJson('/admin/media/sign', ['filename' => 'page.html', 'size' => 100])->assertStatus(422);
        $this->actingAs($this->admin)->postJson('/admin/media/sign', ['filename' => 'run.php', 'size' => 100])->assertStatus(422);
        Http::assertNothingSent();

        // A path the admin never handed out is refused, even if it exists in storage.
        $this->actingAs($this->admin)->postJson('/admin/media/complete', ['path' => '2026/10/other.jpg', 'filename' => 'other.jpg'])->assertStatus(422);
        $this->assertSame(0, Media::count());
    }

    public function test_a_file_whose_content_type_does_not_match_is_removed(): void
    {
        Http::fake([
            'project.supabase.co/storage/v1/object/upload/sign/*' => Http::response(['url' => '/object/upload/sign/media/x?token=abc']),
            'project.supabase.co/storage/v1/object/public/*' => Http::response('', 200, ['Content-Length' => '10', 'Content-Type' => 'text/html']),
            'project.supabase.co/storage/v1/object/media' => Http::response([]),
        ]);
        $sign = $this->actingAs($this->admin)->postJson('/admin/media/sign', ['filename' => 'logo.png', 'size' => 10])->assertOk();
        $this->actingAs($this->admin)->postJson('/admin/media/complete', ['path' => $sign['path'], 'filename' => 'logo.png'])->assertStatus(422);
        $this->assertSame(0, Media::count());
        Http::assertSent(fn ($r) => $r->method() === 'DELETE');
    }

    public function test_mail_settings_refuse_a_local_server(): void
    {
        $this->actingAs($this->admin)->post('/admin/settings/mail', ['host' => '127.0.0.1', 'port' => 2525, 'username' => 'me@gmail.com', 'from_name' => 'AchiveX'])
            ->assertSessionHasErrors('host');
        $this->actingAs($this->admin)->post('/admin/settings/mail', ['host' => 'smtp.gmail.com', 'port' => 587, 'username' => 'me@gmail.com', 'password' => 'abcd efgh', 'from_name' => 'AchiveX'])
            ->assertSessionHasNoErrors();
        $this->assertSame('me@gmail.com', \App\Models\AppSetting::group('mail')['from_address']);
    }
}
