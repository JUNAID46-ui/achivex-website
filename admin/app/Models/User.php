<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use Notifiable;

    public const ROLES = [
        'administrator' => 'Administrator',
        'editor' => 'Editor',
        'author' => 'Author',
    ];

    protected $fillable = ['name', 'email', 'password', 'role', 'avatar', 'last_login_at'];
    protected $hidden = ['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted:array',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function hasTwoFactor(): bool
    {
        return $this->two_factor_confirmed_at !== null && (bool) $this->two_factor_secret;
    }

    // A new password or role signs the account out everywhere else: other browser sessions are
    // removed and "remember me" cookies stop working. The browser making the change stays signed in.
    protected static function booted(): void
    {
        static::updating(function (User $user) {
            if ($user->isDirty('password')) {
                $user->remember_token = Str::random(60);
            }
        });
        static::updated(function (User $user) {
            if ($user->wasChanged('role')) {
                ActivityLog::record('role_changed', 'User', $user->name, 'Role changed from '.($user->getOriginal('role') ?? '?').' to '.$user->role, ['module' => 'Users', 'id' => $user->id]);
            }
            if ($user->wasChanged(['password', 'role']) && config('session.driver') === 'database') {
                DB::table(config('session.table', 'sessions'))
                    ->where('user_id', $user->getKey())
                    ->when(session()->isStarted(), fn ($q) => $q->where('id', '!=', session()->getId()))
                    ->delete();
            }
        });
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
