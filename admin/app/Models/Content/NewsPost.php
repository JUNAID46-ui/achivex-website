<?php

namespace App\Models\Content;

use Illuminate\Database\Eloquent\Model;

class NewsPost extends Model
{
    protected $table = 'public.news_posts';
    protected $primaryKey = 'slug';
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false;
    protected $guarded = [];
    protected $casts = ['body' => 'array', 'published' => 'boolean', 'published_on' => 'date'];
}
