<?php

namespace App\Admin\Resources;

use App\Admin\Resource;
use App\Models\Content\NewsPost;
use Illuminate\Database\Eloquent\Model;

class NewsResource extends Resource
{
    public string $slug = 'news';
    public string $model = NewsPost::class;
    public string $label = 'Newsroom';
    public string $singular = 'News Post';
    public array $search = ['title', 'summary'];
    public ?string $orderBy = 'published_on';
    public string $orderDir = 'desc';
    public bool $hasPublished = true;
    public bool $seo = true;

    public function fields(): array
    {
        return [
            ['name' => 'title', 'label' => 'Headline', 'type' => 'text', 'rules' => 'required|max:200', 'big' => true],
            ['name' => 'slug', 'label' => 'URL slug', 'type' => 'slug', 'from' => 'title', 'rules' => 'required|max:200|alpha_dash', 'prefix' => '/insights/newsroom/'],
            ['name' => 'summary', 'label' => 'Summary', 'type' => 'textarea', 'rows' => 2, 'rules' => 'required|max:400'],
            ['name' => 'body', 'label' => 'Article', 'type' => 'paragraphs', 'rows' => 12, 'help' => 'Leave a blank line between paragraphs.'],
            ['name' => 'published', 'label' => 'Published', 'type' => 'toggle', 'side' => true],
            ['name' => 'published_on', 'label' => 'Date', 'type' => 'date', 'rules' => 'required|date', 'side' => true],
            ['name' => 'image', 'label' => 'Image', 'type' => 'image', 'side' => true],
        ];
    }

    public function columns(): array
    {
        return [
            ['name' => 'image', 'label' => '', 'type' => 'image'],
            ['name' => 'title', 'label' => 'Headline', 'type' => 'title'],
            ['name' => 'published', 'label' => 'Status', 'type' => 'status'],
            ['name' => 'seo', 'label' => 'SEO', 'type' => 'seo'],
            ['name' => 'published_on', 'label' => 'Date', 'type' => 'date'],
        ];
    }

    public function defaults(): array
    {
        return ['published' => true, 'published_on' => now()->toDateString(), 'body' => []];
    }

    public function sitePath(Model $record): ?string
    {
        return "/insights/newsroom/{$record->slug}/";
    }

    public function seoContent(Model $record): array
    {
        $body = (array) $record->body;

        return ['text' => $record->summary.' '.implode(' ', $body), 'headings' => [], 'first' => $body[0] ?? $record->summary];
    }
}
