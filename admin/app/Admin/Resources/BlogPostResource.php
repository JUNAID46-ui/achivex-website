<?php

namespace App\Admin\Resources;

use App\Admin\Resource;
use App\Models\Content\BlogPost;
use App\Services\SiteRoutes;
use Illuminate\Database\Eloquent\Model;

class BlogPostResource extends Resource
{
    public string $slug = 'blog-posts';
    public string $model = BlogPost::class;
    public string $label = 'Blog Posts';
    public string $singular = 'Blog Post';
    public string $area = 'posts';
    public array $search = ['title', 'excerpt', 'category'];
    public ?string $orderBy = 'published_on';
    public string $orderDir = 'desc';
    public bool $hasPublished = true;
    public bool $seo = true;
    public string $description = 'Articles shown on the Blogs page. The first paragraph starts with the bold keyword.';

    public function fields(): array
    {
        return [
            ['name' => 'title', 'label' => 'Title', 'type' => 'text', 'rules' => 'required|max:200', 'big' => true],
            ['name' => 'slug', 'label' => 'URL slug', 'type' => 'slug', 'from' => 'title', 'rules' => 'required|max:200|alpha_dash', 'prefix' => '/insights/blogs/'],
            ['name' => 'excerpt', 'label' => 'Excerpt', 'type' => 'textarea', 'rows' => 2, 'rules' => 'required|max:400', 'help' => 'Shown on blog cards and in search results.'],
            ['name' => 'intro_keyword', 'label' => 'Opening keyword (shown in bold)', 'type' => 'text', 'rules' => 'required|max:120'],
            ['name' => 'intro_text', 'label' => 'Rest of the first paragraph', 'type' => 'textarea', 'rows' => 3, 'rules' => 'required', 'help' => 'Starts right after the bold keyword. Begin with a space, for example " is the process of..."'],
            ['name' => 'sections', 'label' => 'Article sections', 'type' => 'sections', 'help' => 'Each section becomes a heading in the article and an entry in its table of contents.'],
            ['name' => 'published', 'label' => 'Published', 'type' => 'toggle', 'side' => true],
            ['name' => 'featured', 'label' => 'Featured on the Blogs page', 'type' => 'toggle', 'side' => true],
            ['name' => 'published_on', 'label' => 'Publish date', 'type' => 'date', 'rules' => 'required|date', 'side' => true],
            ['name' => 'category', 'label' => 'Category', 'type' => 'select', 'options' => SiteRoutes::blogCategories(), 'rules' => 'required', 'side' => true],
            ['name' => 'author', 'label' => 'Author', 'type' => 'text', 'rules' => 'required|max:120', 'side' => true],
            ['name' => 'image', 'label' => 'Featured image', 'type' => 'image', 'side' => true],
        ];
    }

    public function columns(): array
    {
        return [
            ['name' => 'image', 'label' => '', 'type' => 'image'],
            ['name' => 'title', 'label' => 'Title', 'type' => 'title'],
            ['name' => 'category', 'label' => 'Category', 'type' => 'text'],
            ['name' => 'author', 'label' => 'Author', 'type' => 'text'],
            ['name' => 'published', 'label' => 'Status', 'type' => 'status'],
            ['name' => 'seo', 'label' => 'SEO', 'type' => 'seo'],
            ['name' => 'published_on', 'label' => 'Date', 'type' => 'date'],
        ];
    }

    public function defaults(): array
    {
        return ['published' => true, 'published_on' => now()->toDateString(), 'author' => 'AchiveX Editorial Team', 'category' => 'Data', 'sections' => []];
    }

    public function sitePath(Model $record): ?string
    {
        return "/insights/blogs/{$record->slug}/";
    }

    public function seoContent(Model $record): array
    {
        $text = $record->intro_keyword.$record->intro_text;
        $headings = [];
        foreach ((array) $record->sections as $s) {
            $headings[] = $s['heading'] ?? '';
            $text .= ' '.implode(' ', $s['paragraphs'] ?? []).' '.implode(' ', $s['list'] ?? []);
        }

        return ['text' => $text, 'headings' => $headings, 'first' => $record->intro_keyword.$record->intro_text];
    }
}
