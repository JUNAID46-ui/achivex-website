<?php

namespace App\Admin\Resources;

use App\Admin\Resource;
use App\Models\Content\Milestone;

class MilestoneResource extends Resource
{
    public string $slug = 'milestones';
    public string $model = Milestone::class;
    public string $label = 'Milestones';
    public string $singular = 'Milestone';
    public ?string $orderBy = 'sort';
    public string $description = 'Timeline on the Our Accomplishments page.';

    public function fields(): array
    {
        return [
            ['name' => 'title', 'label' => 'Title', 'type' => 'text', 'rules' => 'required|max:200', 'big' => true],
            ['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'rows' => 3],
            ['name' => 'year', 'label' => 'Year', 'type' => 'text', 'rules' => 'required|max:20', 'side' => true],
            ['name' => 'sort', 'label' => 'Order', 'type' => 'number', 'side' => true],
        ];
    }

    public function columns(): array
    {
        return [
            ['name' => 'year', 'label' => 'Year', 'type' => 'text'],
            ['name' => 'title', 'label' => 'Title', 'type' => 'title'],
            ['name' => 'sort', 'label' => 'Order', 'type' => 'text'],
        ];
    }

    public function defaults(): array
    {
        return ['sort' => 0, 'year' => date('Y')];
    }
}
