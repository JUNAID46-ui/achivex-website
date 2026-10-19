<?php

namespace App\Admin\Resources;

use App\Admin\Resource;
use App\Models\Content\Redirect;
use Illuminate\Database\Eloquent\Model;

class RedirectResource extends Resource
{
    public string $slug = 'redirects';
    public string $model = Redirect::class;
    public string $label = 'Redirects';
    public string $singular = 'Redirect';
    public string $area = 'seo';
    public array $search = ['from_path', 'to_path'];
    public ?string $orderBy = 'id';
    public string $orderDir = 'desc';
    public string $titleField = 'from_path';
    public string $description = 'Send visitors from old or mistyped URLs to the right page. Use paths like /old-page/.';

    public function fields(): array
    {
        return [
            ['name' => 'from_path', 'label' => 'Source URL', 'type' => 'text', 'rules' => 'required|max:300|starts_with:/', 'placeholder' => '/old-page/', 'big' => true],
            ['name' => 'to_path', 'label' => 'Target URL', 'type' => 'text', 'rules' => 'required|max:500', 'placeholder' => '/new-page/ or https://...'],
            ['name' => 'status_code', 'label' => 'Redirect type', 'type' => 'select', 'options' => ['301' => '301 Moved permanently', '302' => '302 Found (temporary)', '307' => '307 Temporary redirect', '308' => '308 Permanent redirect'], 'side' => true],
            ['name' => 'active', 'label' => 'Active', 'type' => 'toggle', 'side' => true],
        ];
    }

    public function columns(): array
    {
        return [
            ['name' => 'from_path', 'label' => 'Source', 'type' => 'title'],
            ['name' => 'to_path', 'label' => 'Target', 'type' => 'text'],
            ['name' => 'status_code', 'label' => 'Type', 'type' => 'text'],
            ['name' => 'active', 'label' => 'Active', 'type' => 'bool'],
        ];
    }

    public function defaults(): array
    {
        return ['status_code' => 301, 'active' => true];
    }

    public function beforeSave(array $data, ?Model $record): array
    {
        $data['from_path'] = '/'.trim($data['from_path'], '/').'/';
        $data['status_code'] = (int) $data['status_code'];

        return $data;
    }
}
