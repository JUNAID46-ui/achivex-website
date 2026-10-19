<?php

namespace App\Admin\Resources;

use App\Admin\Resource;
use App\Models\Content\Whitepaper;
use Illuminate\Database\Eloquent\Model;

class WhitepaperResource extends Resource
{
    public string $slug = 'whitepapers';
    public string $model = Whitepaper::class;
    public string $label = 'Whitepapers and eBooks';
    public string $singular = 'Whitepaper';
    public array $search = ['title', 'description'];
    public ?string $orderBy = 'sort';
    public bool $hasPublished = true;
    public bool $seo = true;

    public function fields(): array
    {
        return [
            ['name' => 'title', 'label' => 'Title', 'type' => 'text', 'rules' => 'required|max:200', 'big' => true],
            ['name' => 'slug', 'label' => 'URL slug', 'type' => 'slug', 'from' => 'title', 'rules' => 'required|max:200|alpha_dash', 'prefix' => '/insights/whitepapers/', 'createOnly' => true],
            ['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'rows' => 2, 'rules' => 'required|max:400'],
            ['name' => 'inside', 'label' => 'What is inside', 'type' => 'lines', 'help' => 'One point per line.'],
            ['name' => 'file_url', 'label' => 'Download file (PDF)', 'type' => 'image', 'help' => 'Upload the PDF in the media library or paste a link. People receive this after filling in the form.', 'accept' => 'file'],
            ['name' => 'published', 'label' => 'Published', 'type' => 'toggle', 'side' => true],
            ['name' => 'type', 'label' => 'Type', 'type' => 'select', 'options' => ['Whitepaper' => 'Whitepaper', 'eBook' => 'eBook', 'Guide' => 'Guide', 'Checklist' => 'Checklist'], 'side' => true],
            ['name' => 'sort', 'label' => 'Order', 'type' => 'number', 'side' => true],
            ['name' => 'cover', 'label' => 'Cover image', 'type' => 'image', 'side' => true],
        ];
    }

    public function columns(): array
    {
        return [
            ['name' => 'cover', 'label' => '', 'type' => 'image'],
            ['name' => 'title', 'label' => 'Title', 'type' => 'title'],
            ['name' => 'type', 'label' => 'Type', 'type' => 'text'],
            ['name' => 'requests_count', 'label' => 'Requests', 'type' => 'text'],
            ['name' => 'file_url', 'label' => 'File', 'type' => 'bool'],
            ['name' => 'published', 'label' => 'Status', 'type' => 'status'],
        ];
    }

    public function query()
    {
        return parent::query()->withCount('requests');
    }

    public function defaults(): array
    {
        return ['published' => true, 'type' => 'Whitepaper', 'sort' => 0, 'inside' => []];
    }

    public function sitePath(Model $record): ?string
    {
        return "/insights/whitepapers/{$record->slug}/";
    }

    public function seoContent(Model $record): array
    {
        return ['text' => $record->description.' '.implode(' ', (array) $record->inside), 'headings' => ['What is inside'], 'first' => $record->description];
    }
}
