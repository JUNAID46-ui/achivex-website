<?php

namespace App\Admin\Resources;

use App\Admin\Resource;
use App\Models\Content\TeamMember;

class TeamResource extends Resource
{
    public string $slug = 'team';
    public string $model = TeamMember::class;
    public string $label = 'Team';
    public string $singular = 'Team Member';
    public array $search = ['name', 'role'];
    public ?string $orderBy = 'sort';
    public bool $hasPublished = true;
    public string $titleField = 'name';
    public string $description = 'People shown on the Our Leadership page.';

    public function fields(): array
    {
        return [
            ['name' => 'name', 'label' => 'Full name', 'type' => 'text', 'rules' => 'required|max:120', 'big' => true],
            ['name' => 'role', 'label' => 'Role', 'type' => 'text', 'rules' => 'required|max:120'],
            ['name' => 'linkedin_url', 'label' => 'LinkedIn profile', 'type' => 'url', 'rules' => 'nullable|url|max:300'],
            ['name' => 'published', 'label' => 'Published', 'type' => 'toggle', 'side' => true],
            ['name' => 'sort', 'label' => 'Order', 'type' => 'number', 'side' => true],
            ['name' => 'photo_url', 'label' => 'Photo', 'type' => 'image', 'side' => true],
        ];
    }

    public function columns(): array
    {
        return [
            ['name' => 'photo_url', 'label' => '', 'type' => 'image'],
            ['name' => 'name', 'label' => 'Name', 'type' => 'title'],
            ['name' => 'role', 'label' => 'Role', 'type' => 'text'],
            ['name' => 'published', 'label' => 'Status', 'type' => 'status'],
            ['name' => 'sort', 'label' => 'Order', 'type' => 'text'],
        ];
    }

    public function defaults(): array
    {
        return ['published' => true, 'sort' => 0];
    }
}
