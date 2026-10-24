<?php

namespace Tests\Feature;

use App\Models\Content\BlogPost;
use App\Models\Content\SiteSetting;
use App\Services\SeoAnalyzer;
use App\Services\SiteRoutes;
use Illuminate\Support\Facades\Http;
use Tests\PostgresTestCase;

class BlogCategoriesAndSeoTest extends PostgresTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::fake(['*' => Http::response('', 404)]);
    }

    public function test_editor_saves_categories_and_authors_cannot(): void
    {
        $this->actingAs($this->makeUser('author'))->get('/admin/blog-categories')->assertForbidden();

        $editor = $this->makeUser('editor');
        $this->actingAs($editor)->get('/admin/blog-categories')->assertOk()->assertSee('Blog categories');
        $this->actingAs($editor)->post('/admin/blog-categories', ['categories' => ['SEO', 'AI Automation', 'Web']])->assertRedirect();
        $this->assertSame(['SEO', 'AI Automation', 'Web'], SiteSetting::group('blog')['categories']);

        $this->actingAs($editor)->post('/admin/blog-categories', ['categories' => ['SEO', 'SEO']])->assertSessionHasErrors('categories.0');
    }

    public function test_categories_in_use_stay_selectable(): void
    {
        SiteSetting::put('blog', ['categories' => ['SEO']]);
        BlogPost::create(['title' => 'Old post', 'slug' => 'old-post', 'excerpt' => 'x', 'intro_keyword' => 'Old', 'intro_text' => ' post', 'category' => 'Cloud', 'author' => 'A', 'published_on' => now()->toDateString(), 'sections' => [], 'faqs' => []]);

        $this->assertSame(['SEO' => 'SEO', 'Cloud' => 'Cloud'], SiteRoutes::blogCategories());
    }

    public function test_link_and_paragraph_checks(): void
    {
        $long = str_repeat('word ', 130);
        $r = SeoAnalyzer::analyze(['title' => 'A title', 'keyword' => '', 'text' => 'some text', 'raw' => 'See [a](/x/) and [b](https://example.com).', 'paragraphs' => [$long]]);
        $labels = collect($r['checks'])->pluck('label');
        $this->assertTrue($labels->contains('1 internal link.'));
        $this->assertTrue($labels->contains('1 external link.'));
        $this->assertTrue($labels->contains('1 paragraph is over 120 words.'));

        // Pages without the extra inputs keep the old set of checks.
        $plain = SeoAnalyzer::analyze(['title' => 'A title', 'keyword' => '', 'text' => 'some text']);
        $this->assertFalse(collect($plain['checks'])->pluck('group')->contains('Few internal links'));
    }
}
