<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// In-app notification for one admin user (table "notifications").
class AdminNotification extends Model
{
    public const UPDATED_AT = null;
    public const SEVERITIES = ['info', 'warning', 'critical'];
    protected $table = 'notifications';
    protected $guarded = [];
    protected $casts = ['created_at' => 'datetime', 'read_at' => 'datetime', 'archived_at' => 'datetime'];

    public function scopeFor($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }
}
