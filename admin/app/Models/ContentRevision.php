<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// A saved copy of a content record, taken before every change or delete.
class ContentRevision extends Model
{
    public const UPDATED_AT = null;
    protected $guarded = [];
    protected $casts = ['data' => 'array', 'created_at' => 'datetime'];
}
