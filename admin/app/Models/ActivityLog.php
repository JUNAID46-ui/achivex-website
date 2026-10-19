<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    public const UPDATED_AT = null;
    protected $guarded = [];
    protected $casts = ['created_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Records an admin action, shown on the dashboard and the Activity page.
    public static function record(string $action, ?string $type = null, ?string $label = null, ?string $description = null): void
    {
        static::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'subject_type' => $type,
            'subject_label' => $label ? mb_substr($label, 0, 250) : null,
            'description' => $description ? mb_substr($description, 0, 250) : null,
            'ip' => request()->ip(),
        ]);
    }
}
