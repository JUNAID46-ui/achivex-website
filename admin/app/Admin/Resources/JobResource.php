<?php

namespace App\Admin\Resources;

use App\Admin\Resource;
use App\Models\Content\JobOpening;

class JobResource extends Resource
{
    public string $slug = 'jobs';
    public string $model = JobOpening::class;
    public string $label = 'Careers';
    public string $singular = 'Job Opening';
    public ?string $orderBy = 'sort';
    public bool $hasPublished = true;
    public string $description = 'Open positions listed on the Careers page. When none are published, the page invites general applications.';

    public function fields(): array
    {
        return [
            ['name' => 'title', 'label' => 'Position title', 'type' => 'text', 'rules' => 'required|max:200', 'big' => true],
            ['name' => 'team', 'label' => 'Team', 'type' => 'text', 'rules' => 'nullable|max:120'],
            ['name' => 'location', 'label' => 'Location', 'type' => 'text', 'rules' => 'nullable|max:120', 'placeholder' => 'Remote, or a city'],
            ['name' => 'employment_type', 'label' => 'Employment type', 'type' => 'select', 'options' => ['Full time' => 'Full time', 'Part time' => 'Part time', 'Contract' => 'Contract', 'Internship' => 'Internship']],
            ['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'rows' => 6],
            ['name' => 'published', 'label' => 'Published', 'type' => 'toggle', 'side' => true],
            ['name' => 'sort', 'label' => 'Order', 'type' => 'number', 'side' => true],
        ];
    }

    public function columns(): array
    {
        return [
            ['name' => 'title', 'label' => 'Position', 'type' => 'title'],
            ['name' => 'team', 'label' => 'Team', 'type' => 'text'],
            ['name' => 'location', 'label' => 'Location', 'type' => 'text'],
            ['name' => 'employment_type', 'label' => 'Type', 'type' => 'text'],
            ['name' => 'published', 'label' => 'Status', 'type' => 'status'],
        ];
    }

    public function defaults(): array
    {
        return ['published' => true, 'sort' => 0, 'employment_type' => 'Full time'];
    }
}
