<?php

namespace App\Admin\Resources;

use App\Admin\Resource;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class UserResource extends Resource
{
    public string $slug = 'users';
    public string $model = User::class;
    public string $label = 'Users';
    public string $singular = 'User';
    public string $area = 'users';
    public array $search = ['name', 'email'];
    public ?string $orderBy = 'name';
    public string $titleField = 'name';
    public string $description = 'Administrators can do everything. Editors manage content, media, SEO and the inbox. Authors write blog posts.';

    public function fields(): array
    {
        return [
            ['name' => 'name', 'label' => 'Full name', 'type' => 'text', 'rules' => 'required|max:120', 'big' => true],
            ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'rules' => 'required|email|max:200|not_regex:/[\r\n]/'],
            ['name' => 'password', 'label' => 'Password', 'type' => 'password', 'help' => 'At least 8 characters. Leave empty to keep the current password.'],
            ['name' => 'role', 'label' => 'Role', 'type' => 'select', 'options' => User::ROLES, 'rules' => 'required|in:'.implode(',', array_keys(User::ROLES)), 'side' => true],
        ];
    }

    public function columns(): array
    {
        return [
            ['name' => 'name', 'label' => 'Name', 'type' => 'title'],
            ['name' => 'email', 'label' => 'Email', 'type' => 'text'],
            ['name' => 'role', 'label' => 'Role', 'type' => 'text'],
            ['name' => 'last_login_at', 'label' => 'Last login', 'type' => 'datetime'],
        ];
    }

    public function defaults(): array
    {
        return ['role' => 'editor'];
    }

    public function beforeSave(array $data, ?Model $record): array
    {
        $exists = User::where('email', $data['email'])->when($record?->exists, fn ($q) => $q->where('id', '!=', $record->id))->exists();
        if ($exists) {
            throw \Illuminate\Validation\ValidationException::withMessages(['email' => 'Another user already has this email.']);
        }
        if (empty($data['password'])) {
            if (! $record?->exists) {
                throw \Illuminate\Validation\ValidationException::withMessages(['password' => 'Set a password for the new user.']);
            }
            unset($data['password']);
        } elseif (strlen($data['password']) < 8) {
            throw \Illuminate\Validation\ValidationException::withMessages(['password' => 'The password must be at least 8 characters.']);
        }

        return $data;
    }
}
