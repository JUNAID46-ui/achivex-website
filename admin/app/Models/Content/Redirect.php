<?php

namespace App\Models\Content;

use Illuminate\Database\Eloquent\Model;

class Redirect extends Model
{
    protected $table = 'public.redirects';

    public $timestamps = false;
    protected $guarded = [];
    protected $casts = ['active' => 'boolean', 'created_at' => 'datetime'];
}
