<?php

namespace Tests\Feature;

use App\Models\AuditRun;
use App\Models\Content\BlogPost;
use App\Models\Content\Redirect;
use App\Models\Content\SiteSetting;
use App\Services\Audit\AiVisibilityAudit;
use App\Services\Audit\Robots;
use App\Services\Audit\SeoAudit;
use Illuminate\Support\Facades\Http;
use Tests\PostgresTestCase;

class AuditTest extends PostgresTestCase
{
    private const SHELL = '<!doctype html><html><head><title>AchiveX</title><meta name="description" content="x"></head><body><div id="root"></div><script src="/assets/app.js"></script></body></html>';

    protected function setUp(): void
    {
        parent::setUp();
        config(['admin.site_url' => 'https://site.test', 'admin.site_public_url' => 'https://www.achivex.com']);
        Http::fake([
            'site.test/robots.txt' => Http::response("User-agent: *\nAllow: /\n\nUser-agent: GPTBot\nDisallow: /\n", 200, ['Content-Type' => 'text/plain']),
            'site.test/sitemap.xml' => Http::response('<urlset><url><loc>https://www.achivex.com/</loc></url></urlset>', 200),
            'site.test/llms.txt' => Http::response(self::SHELL, 200, ['Content-Type' => 'text/html']),
            'site.test/*' => Http::response(self::SHELL, 200, ['Content-Type' => 'text/html']),
            '*' => Http::response('', 404),
        ]);
    }

    public function test_robots_reader_follows_groups_and_longest_match(): void
    {
        $p = Robots::parse("User-agent: *\nDisallow: /private/\nAllow: /private/public/\n\nUser-agent: GPTBot\nUser-agent: CCBot\nDisallow: /\nSitemap: https://x.test/sitemap.xml");
        $this->assertTrue(Robots::check($p, 'Googlebot', '/')['allowed']);
        $this->assertFalse(Robots::check($p, 'Googlebot', '/private/x')['allowed']);
        $this->assertTrue(Robots::check($p, 'Googlebot', '/private/public/x')['allowed']);
        $this->assertFalse(Robots::check($p, 'GPTBot', '/')['allowed']);
        $this->assertFalse(Robots::check($p, 'CCBot', '/')['allowed']);
        $this->assertSame('Disallow: /', Robots::check($p, 'GPTBot', '/')['rule']);
        $this->assertSame(['https://x.test/sitemap.xml'], $p['sitemaps']);
        $this->assertTrue(Robots::check(Robots::parse("User-agent: *\nDisallow:"), 'x', '/')['allowed']);
    }

    public function test_seo_audit_finds_redirect_problems_and_broken_links(): void
    {
        Redirect::create(['from_path' => '/a/', 'to_path' => '/b/', 'status_code' => 301, 'active' => true]);
        Redirect::create(['from_path' => '/b/', 'to_path' => '/a/', 'status_code' => 301, 'active' => true]);
        Redirect::create(['from_path' => '/old/', 'to_path' => '/gone/', 'status_code' => 301, 'active' => true]);
        BlogPost::create(['slug' => 'p', 'title' => 'P', 'category' => 'Data', 'excerpt' => 'e', 'intro_keyword' => 'k', 'published' => true,
            'intro_text' => ' see <a href="/no-such-page/">this</a>.']);
        SiteSetting::put('robots', ['content' => "User-agent: *\nAllow: /\nSitemap: https://www.achivex.com/sitemap.xml"]);

        $f = collect(SeoAudit::run());
        $this->assertTrue($f->contains(fn ($x) => $x['check'] === 'Redirect loop' && $x['severity'] === 'critical'));
        $this->assertTrue($f->contains(fn ($x) => $x['check'] === 'Redirect to a missing page' && $x['url'] === '/old/'));
        $broken = $f->firstWhere('check', 'Broken internal link');
        $this->assertSame('/insights/blogs/p/', $broken['url']);
        $this->assertStringContainsString('/no-such-page/', $broken['evidence']);
        $this->assertSame('verified', $broken['kind']);
        $this->assertTrue($f->contains(fn ($x) => $x['check'] === 'Live robots.txt differs from the admin'));
    }

    public function test_ai_audit_reports_what_it_observes_and_nothing_else(): void
    {
        BlogPost::create(['slug' => 'p', 'title' => 'P', 'category' => 'Data', 'excerpt' => 'e', 'intro_keyword' => 'k', 'intro_text' => ' t', 'published' => true]);
        $r = AiVisibilityAudit::run();
        $f = collect($r['findings']);
        $this->assertFalse($r['access']['GPTBot']['allowed']);
        $this->assertTrue($r['access']['Googlebot']['allowed']);
        $this->assertTrue($f->contains(fn ($x) => $x['check'] === 'Page text is not in the HTML' && $x['kind'] === 'verified'));
        $this->assertTrue($f->contains(fn ($x) => $x['check'] === 'No llms.txt on the website'));
        // No invented metrics: nothing about citations, rankings or visits.
        $this->assertFalse($f->contains(fn ($x) => preg_match('/citation|ranking|visits/i', $x['check'])));
        // Heuristic advice is labelled as such.
        $this->assertTrue($f->where('category', 'Answer readiness')->every(fn ($x) => $x['kind'] === 'recommendation'));
    }

    public function test_unreachable_site_is_reported_not_passed(): void
    {
        Http::fake(['*' => fn () => throw new \Illuminate\Http\Client\ConnectionException('down')]);
        $f = collect(AiVisibilityAudit::run()['findings']);
        $this->assertTrue($f->contains(fn ($x) => $x['check'] === 'Website could not be reached'));
    }

    public function test_runs_are_stored_and_downloadable_by_seo_users_only(): void
    {
        $editor = $this->makeUser('editor');
        $author = $this->makeUser('author');
        $this->actingAs($author)->post('/admin/seo/audits/seo')->assertForbidden();
        $this->actingAs($editor)->post('/admin/seo/audits/seo')->assertRedirect();
        $run = AuditRun::first();
        $this->assertSame('completed', $run->status);
        $this->assertDatabaseHas('notifications', ['type' => 'seo_audit', 'user_id' => $editor->id]);

        $this->actingAs($editor)->get('/admin/seo/audit')->assertOk()->assertSeeText('Run #'.$run->id);
        $csv = $this->actingAs($editor)->get("/admin/seo/audits/{$run->id}/download/csv");
        $csv->assertOk();
        $this->assertStringContainsString('Severity,Kind,Category', $csv->streamedContent());
        $this->actingAs($editor)->get("/admin/seo/audits/{$run->id}/download/json")->assertOk()->assertJsonPath('run', $run->id);
        $this->actingAs($author)->get("/admin/seo/audits/{$run->id}/download/csv")->assertForbidden();
    }

    public function test_llms_txt_is_saved_and_served(): void
    {
        $this->get('/llms.txt')->assertNotFound();
        $editor = $this->makeUser('editor');
        $this->actingAs($editor)->get('/admin/seo/llms/draft')->assertOk()->assertJsonStructure(['content']);
        $this->actingAs($editor)->post('/admin/seo/llms', ['content' => "# AchiveX\n\n> Test"])->assertRedirect();
        $this->get('/llms.txt')->assertOk()->assertSee('# AchiveX', false)->assertHeader('Content-Type', 'text/plain; charset=utf-8');
    }
}
