<?php

namespace App\Models\Content;

use Illuminate\Database\Eloquent\Model;

class PageView extends Model
{
    protected $table = 'public.page_views';

    public $timestamps = false;
    protected $guarded = [];
    protected $casts = ['created_at' => 'datetime'];
}
