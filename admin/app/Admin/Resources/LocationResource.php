<?php

namespace App\Admin\Resources;

use App\Admin\Resource;
use App\Models\Content\Location;

class LocationResource extends Resource
{
    public string $slug = 'locations';
    public string $model = Location::class;
    public string $label = 'Locations';
    public string $singular = 'Location';
    public array $search = ['city', 'country'];
    public ?string $orderBy = 'sort';
    public string $titleField = 'city';
    public string $description = 'Offices shown on the Our Global Presence page.';

    public function fields(): array
    {
        return [
            ['name' => 'city', 'label' => 'City or state', 'type' => 'text', 'rules' => 'required|max:120', 'big' => true],
            ['name' => 'country', 'label' => 'Country', 'type' => 'text', 'rules' => 'required|max:120'],
            ['name' => 'address', 'label' => 'Street address', 'type' => 'textarea', 'rows' => 2],
            ['name' => 'phone', 'label' => 'Phone', 'type' => 'text', 'rules' => 'nullable|max:50'],
            ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'rules' => 'nullable|email|max:200|not_regex:/[\r\n]/'],
            ['name' => 'type', 'label' => 'Type', 'type' => 'select', 'options' => ['Headquarters' => 'Headquarters', 'Office' => 'Office', 'Delivery center' => 'Delivery center'], 'side' => true],
            ['name' => 'is_hq', 'label' => 'Headquarters', 'type' => 'toggle', 'side' => true],
            ['name' => 'sort', 'label' => 'Order', 'type' => 'number', 'side' => true],
        ];
    }

    public function columns(): array
    {
        return [
            ['name' => 'city', 'label' => 'City', 'type' => 'title'],
            ['name' => 'country', 'label' => 'Country', 'type' => 'text'],
            ['name' => 'type', 'label' => 'Type', 'type' => 'text'],
            ['name' => 'is_hq', 'label' => 'HQ', 'type' => 'bool'],
        ];
    }

    public function defaults(): array
    {
        return ['sort' => 0, 'type' => 'Office', 'is_hq' => false];
    }
}
