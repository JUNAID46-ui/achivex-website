<?php

namespace App\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

// Describes one editable content type: its model, form fields, list columns and SEO behaviour.
abstract class Resource
{
    public string $slug;
    public string $model;
    public string $label;
    public string $singular;
    public string $area = 'content';
    public array $search = ['title'];
    public ?string $orderBy = null;
    public string $orderDir = 'asc';
    public bool $hasPublished = false;
    public bool $seo = false;
    public string $titleField = 'title';
    public string $description = '';

    abstract public function fields(): array;

    abstract public function columns(): array;

    // Path of the record on the public website, for "View" links and SEO.
    public function sitePath(Model $record): ?string
    {
        return null;
    }

    // Plain text and headings used by the SEO analyzer.
    public function seoContent(Model $record): array
    {
        return ['text' => '', 'headings' => []];
    }

    public function defaults(): array
    {
        return [];
    }

    public function beforeSave(array $data, ?Model $record): array
    {
        return $data;
    }

    // Null when the record may be deleted, otherwise the reason it may not.
    public function deleteBlocker(Model $record): ?string
    {
        return null;
    }

    public function titleOf(Model $record): string
    {
        return (string) ($record->{$this->titleField} ?? '#'.$record->getKey());
    }

    public function query()
    {
        $q = ($this->model)::query();
        if ($this->orderBy) {
            $q->orderBy($this->orderBy, $this->orderDir);
        }

        return $q;
    }

    public function newRecord(): Model
    {
        return new ($this->model)($this->defaults());
    }

    public function allFields(): array
    {
        return collect($this->fields())->flatMap(fn ($f) => $f['type'] === 'group' ? $f['fields'] : [$f])->all();
    }

    public static function slugFrom(string $text): string
    {
        return Str::slug($text);
    }
}
