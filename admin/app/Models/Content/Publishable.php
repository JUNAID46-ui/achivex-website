<?php

namespace App\Models\Content;

// Content the website shows: published, and either not scheduled or past its publish time.
trait Publishable
{
    public function scopeLive($query)
    {
        return $query->where('published', true)->where(fn ($q) => $q->whereNull('publish_at')->orWhere('publish_at', '<=', now()));
    }
}
