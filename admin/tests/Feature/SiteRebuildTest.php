<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\Content\BlogPost;
use App\Models\Content\ContactSubmission;
use App\Services\SiteRebuild;
use Illuminate\Support\Facades\Http;
use Tests\PostgresTestCase;

class SiteRebuildTest extends PostgresTestCase
{
    private function makePost(string $slug): void
    {
        BlogPost::create(['title' => 'T '.$slug, 'slug' => $slug, 'excerpt' => 'x', 'intro_keyword' => 'K', 'intro_text' => ' t', 'category' => 'Data', 'author' => 'A', 'published_on' => now()->toDateString(), 'sections' => [], 'faqs' => []]);
    }

    public function test_content_change_starts_one_build_and_waits_between_builds(): void
    {
        config(['services.site_deploy_hook' => 'https://hooks.example/deploy']);
        Http::fake(['hooks.example/*' => Http::response(['job' => 'x'], 201), '*' => Http::response('', 404)]);

        $this->makePost('one');
        $this->makePost('two');
        SiteRebuild::flush();
        Http::assertSentCount(1);
        $this->assertFalse(AppSetting::group('site_build')['pending']);

        // A change inside the gap waits, then goes out once the gap has passed.
        $this->makePost('three');
        SiteRebuild::flush();
        Http::assertSentCount(1);
        $this->assertTrue(AppSetting::group('site_build')['pending']);

        AppSetting::put('site_build', ['last_at' => now()->subMinutes(5)->toIso8601String()]);
        SiteRebuild::flush();
        Http::assertSentCount(2);
    }

    public function test_visitor_activity_and_missing_hook_do_nothing(): void
    {
        Http::fake();
        config(['services.site_deploy_hook' => 'https://hooks.example/deploy']);
        ContactSubmission::create(['name' => 'A', 'email' => 'a@example.com', 'message' => 'hi']);
        SiteRebuild::flush();
        Http::assertNothingSent();

        config(['services.site_deploy_hook' => '']);
        $this->makePost('four');
        SiteRebuild::flush();
        Http::assertNothingSent();
    }

    public function test_button_starts_a_build(): void
    {
        config(['services.site_deploy_hook' => 'https://hooks.example/deploy']);
        Http::fake(['hooks.example/*' => Http::response('', 201), '*' => Http::response('', 404)]);
        $this->actingAs($this->makeUser('administrator'))->post('/admin/site/rebuild')->assertRedirect()->assertSessionHas('status');
        Http::assertSent(fn ($r) => $r->url() === 'https://hooks.example/deploy');
    }
}
