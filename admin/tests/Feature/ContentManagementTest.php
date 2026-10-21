<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Content\BlogPost;
use App\Models\Content\Milestone;
use App\Models\ContentMeta;
use App\Models\ContentRevision;
use App\Services\ContentHealth;
use App\Admin\Registry;
use App\Services\SiteLinks;
use Illuminate\Support\Facades\Http;
use Tests\PostgresTestCase;

class ContentManagementTest extends PostgresTestCase
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
            'category' => 'Cloud', 'author' => 'Team', 'workflow_status' => 'draft',
        ], $overrides);
    }

    public function test_drafts_are_not_published_and_record_who_created_them(): void
    {
        $editor = $this->makeUser('editor');
        $this->actingAs($editor)->post('/admin/r/blog-posts', $this->blog())->assertRedirect();
        $post = BlogPost::find('cloud-costs');
        $this->assertFalse($post->published);
        $meta = ContentMeta::where('resource', 'blog-posts')->where('record_key', 'cloud-costs')->first();
        $this->assertSame('draft', $meta->status);
        $this->assertSame('Editor User', $meta->created_by_name);
        $this->assertSame('cloud-costs', ActivityLog::where('action', 'created')->first()->subject_id);
    }

    public function test_every_update_keeps_a_revision_that_can_be_restored(): void
    {
        $editor = $this->makeUser('editor');
        $this->actingAs($editor)->post('/admin/r/blog-posts', $this->blog());
        $this->actingAs($editor)->put('/admin/r/blog-posts/cloud-costs', $this->blog(['title' => 'Second title']));
        $this->assertSame('Second title', BlogPost::find('cloud-costs')->title);
        $rev = ContentRevision::where('record_key', 'cloud-costs')->first();
        $this->assertSame('Cloud costs explained', $rev->data['title']);

        $this->actingAs($editor)->get("/admin/r/blog-posts/cloud-costs/revisions?compare={$rev->id}")->assertOk()->assertSee('Second title');
        $this->actingAs($editor)->post("/admin/r/blog-posts/cloud-costs/revisions/{$rev->id}", [])->assertSessionHasErrors('confirm');
        $this->actingAs($editor)->post("/admin/r/blog-posts/cloud-costs/revisions/{$rev->id}", ['confirm' => '1'])->assertRedirect();
        $this->assertSame('Cloud costs explained', BlogPost::find('cloud-costs')->title);
        $this->assertSame(2, ContentRevision::where('record_key', 'cloud-costs')->count());
    }

    public function test_scheduled_posts_need_a_future_time_and_stay_off_the_live_list(): void
    {
        $editor = $this->makeUser('editor');
        $this->actingAs($editor)->post('/admin/r/blog-posts', $this->blog(['workflow_status' => 'scheduled', 'publish_at' => now()->subHour()->format('Y-m-d\TH:i')]))
            ->assertSessionHasErrors('publish_at');
        $this->actingAs($editor)->post('/admin/r/blog-posts', $this->blog(['workflow_status' => 'scheduled', 'publish_at' => now()->addDay()->format('Y-m-d\TH:i')]))
            ->assertRedirect();
        $post = BlogPost::find('cloud-costs');
        $this->assertTrue($post->published);
        $this->assertTrue($post->publish_at->isFuture());
        $this->assertSame(0, BlogPost::live()->count());
        $this->assertDatabaseHas('notifications', ['type' => 'content_scheduled']);
    }

    public function test_authors_cannot_publish_but_can_send_for_review(): void
    {
        $author = $this->makeUser('author');
        $editor = $this->makeUser('editor');
        $this->actingAs($author)->post('/admin/r/blog-posts', $this->blog(['workflow_status' => 'published']))->assertSessionHasErrors('workflow_status');
        $this->assertNull(BlogPost::find('cloud-costs'));
        $this->actingAs($author)->post('/admin/r/blog-posts', $this->blog(['workflow_status' => 'review']))->assertRedirect();
        $this->assertFalse(BlogPost::find('cloud-costs')->published);
        $this->assertDatabaseHas('notifications', ['type' => 'content_review', 'user_id' => $editor->id]);
        $this->assertDatabaseMissing('notifications', ['type' => 'content_review', 'user_id' => $author->id]);
        $this->actingAs($author)->post('/admin/r/blog-posts/bulk', ['ids' => ['cloud-costs'], 'action' => 'publish'])->assertSessionHasErrors('action');
        $this->assertFalse(BlogPost::find('cloud-costs')->published);
    }

    public function test_status_filter_counts_each_state(): void
    {
        $editor = $this->makeUser('editor');
        $this->actingAs($editor)->post('/admin/r/blog-posts', $this->blog(['workflow_status' => 'review']));
        $this->actingAs($editor)->post('/admin/r/blog-posts', $this->blog(['slug' => 'live-one', 'workflow_status' => 'published']));
        $this->actingAs($editor)->get('/admin/r/blog-posts?status=review')->assertOk()->assertSee('Cloud costs explained')->assertDontSee('live-one');
        $this->actingAs($editor)->get('/admin/r/blog-posts?status=published')->assertOk();
    }

    public function test_bulk_delete_keeps_copies_and_deleted_items_come_back_as_drafts(): void
    {
        $editor = $this->makeUser('editor');
        $this->actingAs($editor)->post('/admin/r/blog-posts', $this->blog(['workflow_status' => 'published']));
        $this->actingAs($editor)->post('/admin/r/blog-posts/bulk', ['ids' => ['cloud-costs'], 'action' => 'delete'])->assertRedirect();
        $this->assertNull(BlogPost::find('cloud-costs'));
        $this->assertSame(1, ActivityLog::where('action', 'deleted')->where('subject_id', 'cloud-costs')->count());

        $rev = ContentRevision::where('action', 'delete')->first();
        $this->actingAs($editor)->get('/admin/r/blog-posts/deleted')->assertOk()->assertSee('Cloud costs explained');
        $this->actingAs($editor)->post("/admin/r/blog-posts/deleted/{$rev->id}", ['confirm' => '1'])->assertRedirect();
        $restored = BlogPost::find('cloud-costs');
        $this->assertNotNull($restored);
        $this->assertFalse($restored->published);
    }

    public function test_deleted_items_with_numeric_ids_restore_with_a_new_id(): void
    {
        $editor = $this->makeUser('editor');
        $m = Milestone::create(['year' => '2024', 'title' => 'Founded', 'sort' => 1]);
        $this->actingAs($editor)->delete("/admin/r/milestones/{$m->id}")->assertRedirect();
        $rev = ContentRevision::where('resource', 'milestones')->where('action', 'delete')->first();
        $this->actingAs($editor)->post("/admin/r/milestones/deleted/{$rev->id}", ['confirm' => '1'])->assertRedirect();
        $this->assertSame('Founded', Milestone::first()->title);
    }

    public function test_user_accounts_never_get_revisions(): void
    {
        $admin = $this->makeUser();
        $editor = $this->makeUser('editor');
        $this->actingAs($admin)->delete("/admin/r/users/{$editor->id}")->assertRedirect();
        $this->assertSame(0, ContentRevision::count());
        $this->actingAs($admin)->get('/admin/r/users/deleted')->assertNotFound();
    }

    public function test_preview_is_private_and_not_indexable(): void
    {
        $editor = $this->makeUser('editor');
        $this->actingAs($editor)->post('/admin/r/blog-posts', $this->blog());
        $this->actingAs($editor)->get('/admin/r/blog-posts/cloud-costs/preview')->assertOk()->assertSee('Cloud costs explained')->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        auth()->logout();
        $this->get('/admin/r/blog-posts/cloud-costs/preview')->assertRedirect(route('admin.login'));
    }

    public function test_link_checker_finds_broken_internal_links(): void
    {
        $this->assertSame('/contact-us/', SiteLinks::normalize('/contact-us?x=1#form'));
        $this->assertNull(SiteLinks::normalize('https://example.com/x'));
        $this->assertNull(SiteLinks::normalize('/images/a.webp'));
        $this->assertNull(SiteLinks::normalize('//evil.com/x'));
        $checked = SiteLinks::check(['text' => 'See <a href="/contact-us/">us</a> and [old](/no-such-page) and <a href="https://www.achivex.com/services/">services</a>']);
        $this->assertSame('ok', $checked['/contact-us/']);
        $this->assertSame('broken', $checked['/no-such-page/']);
        $this->assertSame('ok', $checked['/services/']);
    }

    public function test_custom_page_changes_keep_a_copy_that_can_be_restored(): void
    {
        $editor = $this->makeUser('editor');
        \App\Models\Content\Page::create(['path' => '/offer/', 'kind' => 'custom', 'title' => 'Offer', 'published' => true, 'content' => ['description' => 'First']]);
        $this->actingAs($editor)->post('/admin/pages/update', ['path' => '/offer/', 'title' => 'Offer 2', 'slug' => 'offer', 'description' => 'Second', 'published' => '1'])->assertRedirect();
        $this->assertSame('Second', \App\Models\Content\Page::find('/offer/')->content['description']);
        $rev = ContentRevision::where('resource', 'pages')->first();
        $this->actingAs($editor)->post("/admin/pages/revisions/{$rev->id}", ['confirm' => '1'])->assertRedirect();
        $this->assertSame('First', \App\Models\Content\Page::find('/offer/')->content['description']);
        $this->assertSame('Offer', \App\Models\Content\Page::find('/offer/')->title);
    }

    public function test_health_warns_about_missing_image_and_description(): void
    {
        $post = BlogPost::create(['slug' => 'x', 'title' => 'X', 'category' => 'Data', 'excerpt' => '', 'intro_keyword' => 'k', 'intro_text' => ' t', 'published' => true]);
        $labels = collect(ContentHealth::check(Registry::get('blog-posts'), $post))->pluck('label');
        $this->assertContains('No featured image', $labels);
        $this->assertContains('No meta description', $labels);
    }
}
