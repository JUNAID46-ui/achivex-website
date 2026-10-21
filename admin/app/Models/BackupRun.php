<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// One stored backup file (or a failed attempt) in the private "backups" bucket.
class BackupRun extends Model
{
    public $timestamps = false;
    protected $guarded = [];
    protected $casts = ['scope' => 'array', 'row_counts' => 'array', 'created_at' => 'datetime', 'verified_at' => 'datetime', 'deleted_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
