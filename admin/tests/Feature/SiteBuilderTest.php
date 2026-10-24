<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\Content\BlogPost;
use App\Services\SiteBuilder;
use App\Services\SiteRebuild;
use Illuminate\Support\Facades\Http;
use Tests\PostgresTestCase;

class SiteBuilderTest extends PostgresTestCase
{
    private string $dir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dir = sys_get_temp_dir().'/builder-'.uniqid();
        mkdir("{$this->dir}/src", 0777, true);
        mkdir("{$this->dir}/live");
        file_put_contents("{$this->dir}/src/package.json", '{}');
        file_put_contents("{$this->dir}/src/package-lock.json", '{}');
        // Stands in for npm: "ci" makes node_modules, "run build" makes out/ (or fails when FAIL exists).
        file_put_contents("{$this->dir}/npm", "#!/bin/sh\nif [ \"\$1\" = ci ]; then mkdir -p node_modules; exit 0; fi\n"
            ."if [ -f FAIL ]; then echo 'Type error in page' >&2; exit 1; fi\n"
            ."mkdir -p out/_next out/new-post && echo home > out/index.html && echo missing > out/404.html && echo js > out/_next/app.js && echo post > out/new-post/index.html\n");
        chmod("{$this->dir}/npm", 0755);
        file_put_contents("{$this->dir}/live/404.html", '<html><head><meta charSet="utf-8"/><title>Page Not Found | AchiveX LLC</title></head></html>');
        config(['services.site_build' => 'server', 'services.site_source_path' => "{$this->dir}/src", 'services.site_public_path' => "{$this->dir}/live", 'services.site_npm' => "{$this->dir}/npm", 'services.site_deploy_hook' => null]);
    }

    protected function tearDown(): void
    {
        exec('rm -rf '.escapeshellarg($this->dir));
        parent::tearDown();
    }

    public function test_a_content_change_asks_for_a_build_that_the_cron_job_runs_here(): void
    {
        Http::fake();
        BlogPost::create(['title' => 'New post', 'slug' => 'new-post', 'excerpt' => 'x', 'intro_keyword' => 'K', 'intro_text' => ' t', 'category' => 'Data', 'author' => 'A', 'published_on' => now()->toDateString(), 'sections' => [], 'faqs' => []]);
        SiteRebuild::flush();
        $this->assertTrue(AppSetting::group('site_build')['requested']);
        // Only the admin's usual read of the page list; no build service is called.
        Http::assertNotSent(fn ($r) => ! str_ends_with($r->url(), 'site-routes.json'));

        // A file the previous build made that is gone now is removed; other files are kept.
        file_put_contents("{$this->dir}/live/.build-files.json", json_encode(['old-page/index.html']));
        mkdir("{$this->dir}/live/old-page");
        file_put_contents("{$this->dir}/live/old-page/index.html", 'old');
        file_put_contents("{$this->dir}/live/uploaded.txt", 'mine');

        $this->assertStringContainsString('Built and copied', SiteBuilder::runIfRequested());
        $this->assertSame("post\n", file_get_contents("{$this->dir}/live/new-post/index.html"));
        $this->assertFileExists("{$this->dir}/live/_next/app.js");
        $this->assertFileDoesNotExist("{$this->dir}/live/old-page/index.html");
        $this->assertFileExists("{$this->dir}/live/uploaded.txt");
        $state = AppSetting::group('site_build');
        $this->assertFalse($state['requested']);
        $this->assertTrue($state['last_ok']);
        $this->assertSame('Nothing to build.', SiteBuilder::runIfRequested());
    }

    public function test_a_failed_build_leaves_the_live_website_as_it_was(): void
    {
        file_put_contents("{$this->dir}/live/index.html", 'live home');
        touch("{$this->dir}/src/FAIL");
        SiteBuilder::request('Test');
        $this->assertStringContainsString('Type error in page', SiteBuilder::runIfRequested());
        $this->assertSame('live home', file_get_contents("{$this->dir}/live/index.html"));
        $this->assertFalse(AppSetting::group('site_build')['last_ok']);
    }
}
