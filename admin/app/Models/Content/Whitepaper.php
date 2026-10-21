<?php

namespace App\Models\Content;

use Illuminate\Database\Eloquent\Model;

class Whitepaper extends Model
{
    use Publishable;

    protected $table = 'public.whitepapers';
    protected $primaryKey = 'slug';
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false;
    protected $guarded = [];
    protected $casts = ['inside' => 'array', 'published' => 'boolean', 'publish_at' => 'datetime'];

    public function requests()
    {
        return $this->hasMany(WhitepaperRequest::class, 'whitepaper_slug', 'slug');
    }
}
