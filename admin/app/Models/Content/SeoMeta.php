<?php

namespace App\Models\Content;

use Illuminate\Database\Eloquent\Model;

class SeoMeta extends Model
{
    protected $table = 'public.seo_meta';
    protected $primaryKey = 'path';
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false;
    protected $guarded = [];
    protected $casts = ['noindex' => 'boolean', 'updated_at' => 'datetime'];
}
