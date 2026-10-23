<?php

namespace Tests\Feature;

use App\Admin\Registry;
use App\Models\Content\BlogPost;
use App\Models\Content\Redirect;
use App\Models\Content\SeoMeta;
use App\Models\Content\SiteSetting;
use App\Services\SiteLinks;
use App\Support\Permalinks;
use App\Support\Theme;
use Illuminate\Support\Facades\Http;
use Tests\PostgresTestCase;

class AppearanceTest extends PostgresTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::fake(['*' => Http::response('', 404)]);
    }

    private function theme(array $colors = [], array $fonts = []): array
    {
        return ['colors' => array_merge(Theme::defaults(), $colors), 'fonts' => array_merge(Theme::DEFAULT_FONTS, $fonts)];
    }

    public function test_only_administrators_change_colours_fonts_and_permalinks(): void
    {
        $editor = $this->makeUser('editor');
        $this->actingAs($editor)->get('/admin/settings/appearance')->assertForbidden();
        $this->actingAs($editor)->post('/admin/settings/permalinks', ['bases' => ['blogs' => '/blog/']])->assertForbidden();
        $admin = $this->makeUser();
        $this->actingAs($admin)->get('/admin/settings/appearance')->assertOk()->assertSee('Dark sections');
        $this->actingAs($admin)->get('/admin/settings/permalinks')->assertOk()->assertSee('/insights/blogs/');
    }

    public function test_colours_are_saved_and_unsafe_values_are_refused(): void
    {
        $admin = $this->makeUser();
        $this->actingAs($admin)->post('/admin/settings/appearance', $this->theme(['brand' => 'red; } body { display:none']))->assertSessionHasErrors('colors.brand');
        $this->actingAs($admin)->post('/admin/settings/appearance', $this->theme([], ['body' => 'Comic Sans</style>']))->assertSessionHasErrors('fonts.body');
        $this->actingAs($admin)->post('/admin/settings/appearance', $this->theme(['brand' => '#ff6b6b'], ['heading' => 'Playfair Display']))->assertSessionHasNoErrors();

        $saved = SiteSetting::group('theme');
        $this->assertSame(['brand' => '#FF6B6B'], $saved['colors'], 'Only changes from the defaults are stored');
        $this->assertSame(['heading' => 'Playfair Display'], $saved['fonts']);
        $this->assertSame('#FF6B6B', Theme::current()['colors']['brand']);

        $this->actingAs($admin)->post('/admin/settings/appearance/reset')->assertRedirect();
        $this->assertSame(Theme::defaults()['brand'], Theme::current()['colors']['brand']);
    }

    public function test_contrast_warnings(): void
    {
        $this->assertSame(21.0, Theme::contrast('#000000', '#FFFFFF'));
        $warn = Theme::warnings(array_merge(Theme::defaults(), ['mutedText' => '#222222']));
        $this->assertStringContainsString('Text on page heroes', $warn[0]);
        $this->assertSame([], Theme::warnings(Theme::defaults()));
    }

    public function test_invalid_permalinks_are_refused(): void
    {
        $admin = $this->makeUser();
        foreach ([
            ['blogs' => '/Blog Posts/'], ['blogs' => '/services/'], ['blogs' => '/about-us/blog/'], ['blogs' => '/insights/'],
            ['blogs' => '/news/', 'newsroom' => '/news/'], ['blogs' => '/news/', 'newsroom' => '/news/press/'],
        ] as $bases) {
            $this->actingAs($admin)->post('/admin/settings/permalinks', ['bases' => $bases + Permalinks::DEFAULTS])->assertSessionHasErrors();
        }
        $this->assertSame(Permalinks::DEFAULTS, Permalinks::all());
    }

    public function test_changing_a_prefix_moves_addresses_seo_and_redirects(): void
    {
        $admin = $this->makeUser();
        BlogPost::create(['slug' => 'cloud', 'title' => 'Cloud', 'category' => 'Cloud', 'excerpt' => 'e', 'intro_keyword' => 'k', 'intro_text' => ' t', 'published' => true]);
        SeoMeta::create(['path' => '/insights/blogs/cloud/', 'title' => 'Custom title']);
        SeoMeta::create(['path' => '/insights/blogs/', 'description' => 'Blog list']);
        Redirect::create(['from_path' => '/old-cloud/', 'to_path' => '/insights/blogs/cloud/', 'status_code' => 301, 'active' => true]);

        $this->actingAs($admin)->post('/admin/settings/permalinks', ['bases' => ['blogs' => 'blog'] + Permalinks::DEFAULTS])->assertSessionHasNoErrors();

        $this->assertSame('/blog/', Permalinks::base('blogs'));
        $this->assertSame('/blog/cloud/', Registry::get('blog-posts')->sitePath(BlogPost::find('cloud')));
        $this->assertSame('Custom title', SeoMeta::find('/blog/cloud/')?->title);
        $this->assertSame('Blog list', SeoMeta::find('/blog/')?->description);
        $this->assertNull(SeoMeta::find('/insights/blogs/cloud/'));
        $this->assertSame('/blog/cloud/', Redirect::first()->to_path);
        $this->assertSame('/blog/cloud/', Permalinks::fromOldBase('/insights/blogs/cloud/'));
        $this->assertSame('redirect', SiteLinks::check(['x' => '<a href="/insights/blogs/cloud/">old</a>'])['/insights/blogs/cloud/']);
        $this->assertSame('ok', SiteLinks::check(['x' => '<a href="/blog/cloud/">new</a>'])['/blog/cloud/']);
        $this->assertDatabaseHas('activity_logs', ['module' => 'Permalinks']);
    }

    public function test_blog_posts_and_services_can_live_at_the_root(): void
    {
        $admin = $this->makeUser();
        BlogPost::create(['slug' => 'cloud-tips', 'title' => 'Cloud', 'category' => 'Cloud', 'excerpt' => 'e', 'intro_keyword' => 'k', 'intro_text' => ' t', 'published' => true]);
        SeoMeta::create(['path' => '/insights/blogs/cloud-tips/', 'title' => 'Post title']);
        SeoMeta::create(['path' => '/services/web-and-software/web-development/', 'title' => 'Service title']);

        $this->actingAs($admin)->post('/admin/settings/permalinks', ['bases' => ['blogs' => '/'] + Permalinks::DEFAULTS, 'services' => 'root'])->assertSessionHasNoErrors();

        $this->assertSame('/cloud-tips/', Registry::get('blog-posts')->sitePath(BlogPost::find('cloud-tips')));
        $this->assertSame('/insights/blogs/', Permalinks::listPath('blogs'), 'The blog list keeps its address');
        $this->assertSame('/insights/blogs/', Permalinks::map('/insights/blogs/'));
        $this->assertSame('/web-development/', Permalinks::map('/services/web-and-software/web-development/'));
        $this->assertSame('/services/web-and-software/', Permalinks::map('/services/web-and-software/'), 'Service area pages keep their address');
        $this->assertSame('Post title', SeoMeta::find('/cloud-tips/')?->title);
        $this->assertSame('Service title', SeoMeta::find('/web-development/')?->title);
        $this->assertSame('/web-development/', Permalinks::fromOldBase('/services/web-and-software/web-development/'));
        $this->assertSame('/cloud-tips/', Permalinks::fromOldBase('/insights/blogs/cloud-tips/'));
        $this->assertNull(Permalinks::fromOldBase('/insights/blogs/'));
        $this->assertSame('ok', SiteLinks::check(['x' => '<a href="/web-development/">s</a>'])['/web-development/']);

        // A new post cannot take the address of a service or a fixed page.
        foreach (['web-development', 'contact-us'] as $taken) {
            $this->actingAs($admin)->post('/admin/r/blog-posts', ['title' => 'T', 'slug' => $taken, 'category' => 'Cloud', 'excerpt' => 'e', 'intro_keyword' => 'k', 'intro_text' => 't', 'published_on' => '2026-10-01'])
                ->assertSessionHasErrors('slug');
        }
        // And a custom page cannot take the address of a post.
        $this->actingAs($admin)->post('/admin/pages', ['title' => 'Landing', 'slug' => 'cloud-tips', 'layout' => 'blank'])->assertSessionHasErrors('slug');
    }

    public function test_root_addresses_that_would_clash_are_refused(): void
    {
        $admin = $this->makeUser();
        BlogPost::create(['slug' => 'web-development', 'title' => 'S', 'category' => 'Security', 'excerpt' => 'e', 'intro_keyword' => 'k', 'intro_text' => ' t', 'published' => true]);
        $this->actingAs($admin)->post('/admin/settings/permalinks', ['bases' => ['blogs' => '/'] + Permalinks::DEFAULTS, 'services' => 'root'])->assertSessionHasErrors('bases');
        $this->actingAs($admin)->post('/admin/settings/permalinks', ['bases' => Permalinks::DEFAULTS, 'services' => 'anything'])->assertSessionHasErrors('services');
        $this->assertSame('category', Permalinks::serviceMode());
        $this->assertSame(Permalinks::DEFAULTS, Permalinks::all());
    }
}
