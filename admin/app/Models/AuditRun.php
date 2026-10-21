<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// One SEO, AI visibility or security audit, with its findings.
class AuditRun extends Model
{
    public $timestamps = false;
    protected $guarded = [];
    protected $casts = ['summary' => 'array', 'findings' => 'array', 'started_at' => 'datetime', 'finished_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
