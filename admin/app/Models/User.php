<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    public const ROLES = [
        'administrator' => 'Administrator',
        'editor' => 'Editor',
        'author' => 'Author',
    ];

    protected $fillable = ['name', 'email', 'password', 'role', 'avatar', 'last_login_at'];
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'administrator';
    }

    // Area based permissions. Administrators can do everything.
    public function canAccess(string $area): bool
    {
        $map = [
            'administrator' => ['*'],
            'editor' => ['dashboard', 'content', 'builder', 'media', 'seo', 'inbox', 'analytics', 'profile'],
            'author' => ['dashboard', 'posts', 'media', 'profile'],
        ];
        $allowed = $map[$this->role] ?? ['dashboard', 'profile'];

        return in_array('*', $allowed) || in_array($area, $allowed) || ($area === 'posts' && in_array('content', $allowed));
    }

    public function initials(): string
    {
        return collect(explode(' ', $this->name))->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->take(2)->implode('');
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new \App\Notifications\ResetAdminPassword($token));
    }
}
