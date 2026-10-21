<?php

namespace App\Models\Content;

use Illuminate\Database\Eloquent\Model;

class BlogPost extends Model
{
    use Publishable;

    protected $table = 'public.blog_posts';
    protected $primaryKey = 'slug';
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false;
    protected $guarded = [];
    protected $casts = ['sections' => 'array', 'featured' => 'boolean', 'published' => 'boolean', 'published_on' => 'date', 'publish_at' => 'datetime'];
}
