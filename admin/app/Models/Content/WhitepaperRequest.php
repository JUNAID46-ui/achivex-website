<?php

namespace App\Models\Content;

use Illuminate\Database\Eloquent\Model;

class WhitepaperRequest extends Model
{
    protected $table = 'public.whitepaper_requests';

    public $timestamps = false;
    protected $guarded = [];
    protected $casts = ['created_at' => 'datetime', 'handled_at' => 'datetime'];

    public function whitepaper()
    {
        return $this->belongsTo(Whitepaper::class, 'whitepaper_slug', 'slug');
    }
}
