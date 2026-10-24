<?php

namespace Tests\Feature;

use App\Models\Content\BlogPost;
use App\Services\SiteRebuild;
use Illuminate\Support\Facades\Http;
use Tests\PostgresTestCase;

class InstantPublishTest extends PostgresTestCase
{
    private string $root;

    protected function setUp(): void
    {
        parent::setUp();
        $this->root = sys_get_temp_dir().'/instant-'.uniqid();
        mkdir($this->root);
        // A small stand-in for the website's 404.html, with the parts the admin changes.
        file_put_contents("{$this->root}/404.html", '<html><head><meta charSet="utf-8"/><meta http-equiv="Content-Security-Policy" content="script-src \'self\'"/>'
            .'<title>Page Not Found | AchiveX LLC</title><meta name="robots" content="noindex"/></head><body>'
            .'<script>self.__next_f.push([1,"[\"$\",\"title\",\"0\",{\"children\":\"Page Not Found | AchiveX LLC\"}],{\"name\":\"robots\",\"content\":\"noindex\"}"])</script></body></html>');
        mkdir("{$this->root}/built-page");
        file_put_contents("{$this->root}/built-page/index.html", 'from the build');
        config(['services.site_public_path' => $this->root, 'services.site_deploy_hook' => null]);
    }

    protected function tearDown(): void
    {
        exec('rm -rf '.escapeshellarg($this->root));
        parent::tearDown();
    }

    private function makePost(string $slug, string $title = 'New post'): BlogPost
    {
        return BlogPost::create(['title' => $title, 'slug' => $slug, 'excerpt' => 'About the post', 'intro_keyword' => 'K', 'intro_text' => ' t', 'category' => 'Data', 'author' => 'A', 'published_on' => now()->toDateString(), 'sections' => [], 'faqs' => []]);
    }

    public function test_a_saved_post_is_a_real_page_at_once_and_goes_away_when_unpublished(): void
    {
        $post = $this->makePost('fresh-post', 'Fresh "post"');
        SiteRebuild::flush();

        $file = "{$this->root}/insights/blogs/fresh-post/index.html";
        $this->assertFileExists($file);
        $html = file_get_contents($file);
        $this->assertStringContainsString('Fresh &quot;post&quot;', $html);
        $this->assertStringContainsString('<link rel="canonical" href="https://www.achivex.com/insights/blogs/fresh-post/"/>', $html);
        $this->assertStringContainsString('<meta name="robots" content="index, follow"/>', $html);
        $this->assertStringNotContainsString('Page Not Found', $html);
        $this->assertStringNotContainsString('noindex', $html);
        // The page data keeps valid escaping and the script hash matches the changed script.
        $this->assertStringContainsString('\"children\":\"Fresh \\\\u0022post\\\\u0022', $html);
        preg_match('#<script>(.*?)</script>#s', $html, $m);
        $this->assertStringContainsString("'sha256-".base64_encode(hash('sha256', $m[1], true))."'", $html);
        $this->assertFileExists("{$this->root}/sitemap.xml");
        // Files from the build are never touched.
        $this->assertSame('from the build', file_get_contents("{$this->root}/built-page/index.html"));

        $post->update(['published' => false]);
        SiteRebuild::flush();
        $this->assertFileDoesNotExist($file);
        $this->assertSame('from the build', file_get_contents("{$this->root}/built-page/index.html"));
    }

    public function test_publish_button_writes_pages_without_a_build_hook(): void
    {
        Http::fake();
        BlogPost::withoutEvents(fn () => $this->makePost('quiet-post'));
        $this->actingAs($this->makeUser('administrator'))->post('/admin/site/rebuild')->assertSessionHas('status', fn ($s) => str_contains($s, 'live now'));
        $this->assertFileExists("{$this->root}/insights/blogs/quiet-post/index.html");
    }

    public function test_github_dispatch_sends_token_and_event(): void
    {
        config(['services.site_deploy_hook' => 'https://api.github.com/repos/o/r/dispatches', 'services.site_deploy_token' => 'tok']);
        Http::fake(['api.github.com/*' => Http::response('', 204)]);
        $this->assertTrue(SiteRebuild::start('Test'));
        Http::assertSent(fn ($r) => $r->hasHeader('Authorization', 'Bearer tok') && $r['event_type'] === 'site-rebuild');
    }
}
