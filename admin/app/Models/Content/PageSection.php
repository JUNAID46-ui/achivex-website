<?php

namespace App\Models\Content;

use Illuminate\Database\Eloquent\Model;

class PageSection extends Model
{
    protected $table = 'public.page_sections';

    public $timestamps = false;
    protected $guarded = [];
    protected $casts = ['data' => 'array', 'visible' => 'boolean', 'updated_at' => 'datetime'];
}
