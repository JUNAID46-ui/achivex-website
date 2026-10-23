<?php

namespace Tests\Feature;

use App\Models\Content\BlogPost;
use App\Models\Content\PageSection;
use App\Models\Content\SeoMeta;
use Illuminate\Support\Facades\Http;
use Tests\PostgresTestCase;

class TemplatesAndSchemaTest extends PostgresTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::fake(['*' => Http::response('', 404)]); // never call the live website from tests
    }

    private function blog(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Cloud costs explained', 'slug' => 'cloud-costs', 'excerpt' => 'How to read a cloud bill.',
            'intro_keyword' => 'Cloud costs', 'intro_text' => ' are easy to misread.', 'published_on' => '2026-10-01',
            'category' => 'Cloud', 'author' => 'Team', 'workflow_status' => 'published',
        ], $overrides);
    }

    public function test_blog_faqs_and_header_design_are_saved(): void
    {
        $admin = $this->makeUser();
        $faqs = json_encode([['q' => 'How long?', 'a' => 'A few weeks.'], ['q' => '', 'a' => '']]);
        $this->actingAs($admin)->post('/admin/r/blog-posts', $this->blog(['faqs' => $faqs, 'hero_color' => '#0F2C9C', 'hero_image' => 'https://example.com/a.jpg']))->assertRedirect();
        $post = BlogPost::find('cloud-costs');
        $this->assertEquals([['q' => 'How long?', 'a' => 'A few weeks.']], $post->faqs);
        $this->assertSame('#0F2C9C', $post->hero_color);
        $this->assertSame('https://example.com/a.jpg', $post->hero_image);
        $this->actingAs($admin)->get('/admin/r/blog-posts/cloud-costs/edit')->assertOk()->assertSee('FAQs')->assertSee('Header background colour')->assertSee('Schema (JSON-LD)');
    }

    public function test_custom_schema_is_validated_and_saved_from_both_seo_forms(): void
    {
        $admin = $this->makeUser();
        $schema = json_encode(['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => []]);

        $this->actingAs($admin)->post('/admin/r/blog-posts', $this->blog(['seo_schema' => '{"@context": "https://schema.org"}']))->assertSessionHasErrors('seo_schema');
        $this->assertNull(BlogPost::find('cloud-costs'));
        $this->actingAs($admin)->post('/admin/r/blog-posts', $this->blog(['seo_schema' => $schema]))->assertSessionHasNoErrors();
        $path = SeoMeta::where('schema', '!=', null)->value('path');
        $this->assertSame('FAQPage', SeoMeta::find($path)->schema['@type']);

        $this->actingAs($admin)->post('/admin/seo/pages/edit', ['path' => '/contact-us/', 'schema' => 'not json'])->assertSessionHasErrors('schema');
        $this->actingAs($admin)->post('/admin/seo/pages/edit', ['path' => '/contact-us/', 'schema' => json_encode([['@type' => 'LocalBusiness', 'name' => 'AchiveX'], ['@type' => 'Event', 'name' => 'Talk']])])->assertSessionHasNoErrors();
        $this->assertSame(['LocalBusiness', 'Event'], array_column(SeoMeta::find('/contact-us/')->schema, '@type'));
        $this->actingAs($admin)->post('/admin/seo/pages/edit', ['path' => '/contact-us/', 'schema' => ''])->assertSessionHasNoErrors();
        $this->assertNull(SeoMeta::find('/contact-us/')->schema);
    }

    public function test_add_page_asks_for_a_type_then_builds_the_chosen_template(): void
    {
        $admin = $this->makeUser();
        $this->actingAs($admin)->get('/admin/pages/create')->assertOk()->assertSee('Service page')->assertSee('Blog post')->assertSee('/admin/r/blog-posts/create', false);
        $this->actingAs($admin)->get('/admin/pages/create?template=service')->assertOk()->assertSee('Template: Service page');

        $this->actingAs($admin)->post('/admin/pages', ['title' => 'Pricing', 'slug' => 'pricing', 'layout' => 'service', 'published' => '1'])->assertRedirect();
        $types = PageSection::where('page', '/pricing/')->orderBy('sort')->pluck('type')->all();
        $this->assertSame(['page_hero', 'image_text', 'cards', 'rich_text', 'cta', 'faq'], $types);
        $this->actingAs($admin)->post('/admin/pages', ['title' => 'Other', 'slug' => 'other', 'layout' => 'nope'])->assertSessionHasErrors('layout');
    }

    public function test_every_builder_section_has_design_options_and_unsafe_colours_are_dropped(): void
    {
        $admin = $this->makeUser();
        foreach (config('builder') as $type => $schema) {
            $this->assertSame('design', collect($schema['fields'])->last()['name'], "{$type} has no design options");
        }
        $section = PageSection::create(['page' => 'home', 'key' => 'intro', 'type' => 'intro', 'label' => 'Intro', 'data' => ['before' => 'Hi'], 'sort' => 1, 'visible' => true, 'updated_at' => now()]);
        $this->actingAs($admin)->postJson("/admin/builder/sections/{$section->id}", ['data' => [
            'before' => 'Hello', 'design' => ['bg' => '#0F2C9C', 'heading' => 'red;}body{', 'headingFont' => 'Poppins', 'spacing' => 'compact'],
        ]])->assertOk();
        $this->assertEquals(['bg' => '#0F2C9C', 'headingFont' => 'Poppins', 'spacing' => 'compact'], $section->fresh()->data['design']);
    }

    public function test_dashboard_live_stats_are_real_time(): void
    {
        $admin = $this->makeUser();
        \App\Models\Content\PageView::insert([
            ['path' => '/', 'device' => 'desktop', 'visitor' => 'a', 'created_at' => now()],
            ['path' => '/services/', 'device' => 'mobile', 'visitor' => 'b', 'created_at' => now()->subMinutes(2)],
            ['path' => '/', 'device' => 'desktop', 'visitor' => 'c', 'created_at' => now()->subHours(3)],
        ]);
        $this->actingAs($admin)->getJson('/admin/dashboard/live')->assertOk()
            ->assertJson(['online' => 2, 'viewsNow' => 2, 'monthViews' => 3, 'monthVisitors' => 3])
            ->assertJsonCount(2, 'pages');
        $this->actingAs($admin)->get('/admin')->assertOk()->assertSee('Live now');
    }

    public function test_fixed_pages_save_design_options_and_insights_text_is_editable(): void
    {
        $admin = $this->makeUser();
        $this->actingAs($admin)->get('/admin/pages/edit?path='.urlencode('/insights/'))->assertOk()->assertSee('Design (colours, fonts and spacing)', false);
        $content = ['heroTitle' => 'Our insights', 'design' => ['bg' => '#F5F6F8', 'heading' => 'javascript:x', 'align' => 'right', 'headingFont' => 'Comic Sans']];
        $this->actingAs($admin)->post('/admin/pages/update', ['path' => '/insights/', 'content_json' => json_encode($content)])->assertRedirect();
        $saved = \App\Models\Content\Page::find('/insights/')->content;
        $this->assertSame('Our insights', $saved['heroTitle']);
        $this->assertEquals(['bg' => '#F5F6F8', 'align' => 'right'], $saved['design']);
    }
}
