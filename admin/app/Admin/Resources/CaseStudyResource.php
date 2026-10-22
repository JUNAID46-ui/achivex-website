<?php

namespace App\Admin\Resources;

use App\Admin\Resource;
use App\Models\Content\CaseStudy;
use App\Services\SiteRoutes;
use Illuminate\Database\Eloquent\Model;

class CaseStudyResource extends Resource
{
    public string $slug = 'case-studies';
    public string $model = CaseStudy::class;
    public string $label = 'Case Studies';
    public string $singular = 'Case Study';
    public array $search = ['title', 'client_type', 'summary'];
    public ?string $orderBy = 'sort';
    public bool $hasPublished = true;
    public bool $seo = true;

    public function fields(): array
    {
        return [
            ['name' => 'title', 'label' => 'Title', 'type' => 'text', 'rules' => 'required|max:200', 'big' => true],
            ['name' => 'slug', 'label' => 'URL slug', 'type' => 'slug', 'from' => 'title', 'rules' => 'required|max:200|alpha_dash'.\App\Support\Permalinks::slugRule('caseStudies'), 'prefix' => \App\Support\Permalinks::base('caseStudies')],
            ['name' => 'summary', 'label' => 'Summary', 'type' => 'textarea', 'rows' => 2, 'rules' => 'required|max:400'],
            ['name' => 'overview', 'label' => 'The situation', 'type' => 'textarea', 'rows' => 3, 'rules' => 'required'],
            ['name' => 'challenge', 'label' => 'The challenge', 'type' => 'paragraphs', 'help' => 'Leave a blank line between paragraphs.'],
            ['name' => 'solution', 'label' => 'Our approach', 'type' => 'lines', 'help' => 'One step per line.'],
            ['name' => 'results', 'label' => 'How we measure success', 'type' => 'lines', 'help' => 'One measure per line. Add real figures only with client permission.'],
            ['name' => 'published', 'label' => 'Published', 'type' => 'toggle', 'side' => true],
            ['name' => 'client_type', 'label' => 'Client type', 'type' => 'text', 'rules' => 'required|max:120', 'side' => true],
            ['name' => 'industry', 'label' => 'Industry', 'type' => 'select', 'options' => SiteRoutes::industryOptions(), 'rules' => 'required', 'side' => true],
            ['name' => 'sort', 'label' => 'Order', 'type' => 'number', 'side' => true],
            ['name' => 'image', 'label' => 'Image', 'type' => 'image', 'side' => true],
            ['name' => 'services', 'label' => 'Services used', 'type' => 'tags', 'options' => SiteRoutes::serviceOptions()],
        ];
    }

    public function columns(): array
    {
        return [
            ['name' => 'image', 'label' => '', 'type' => 'image'],
            ['name' => 'title', 'label' => 'Title', 'type' => 'title'],
            ['name' => 'client_type', 'label' => 'Client type', 'type' => 'text'],
            ['name' => 'published', 'label' => 'Status', 'type' => 'status'],
            ['name' => 'seo', 'label' => 'SEO', 'type' => 'seo'],
            ['name' => 'sort', 'label' => 'Order', 'type' => 'text'],
        ];
    }

    public function defaults(): array
    {
        return ['published' => true, 'sort' => 0, 'services' => [], 'challenge' => [], 'solution' => [], 'results' => []];
    }

    public function sitePath(Model $record): ?string
    {
        return \App\Support\Permalinks::base('caseStudies').$record->slug.'/';
    }

    public function seoContent(Model $record): array
    {
        $text = implode(' ', array_merge([$record->summary, $record->overview], (array) $record->challenge, (array) $record->solution, (array) $record->results));

        return ['text' => $text, 'headings' => ['The situation', 'The challenge', 'Our approach'], 'first' => $record->overview];
    }
}
