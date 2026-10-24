<?php

namespace App\Models\Content;

use Illuminate\Database\Eloquent\Model;

// A row per edited fixed page (kind "builtin", content holds the changed text)
// or per page made in the admin (kind "custom", built from page_sections).
class Page extends Model
{
    use Publishable;

    protected $table = 'public.pages';
    protected $primaryKey = 'path';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = [];
    protected $casts = ['content' => 'array', 'published' => 'boolean', 'publish_at' => 'datetime'];
}
