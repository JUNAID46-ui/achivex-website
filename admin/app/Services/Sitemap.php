<?php

namespace App\Services;

use App\Models\Content\BlogPost;
use App\Models\Content\NewsPost;

// Builds sitemap.xml from every indexable page of the website.
class Sitemap
{
    public static function urls(): array
    {
        $base = rtrim(config('admin.site_public_url'), '/');
        $dates = BlogPost::pluck('published_on', 'slug')->mapWithKeys(fn ($d, $s) => [\App\Support\Permalinks::base('blogs').$s.'/' => $d])
            ->merge(NewsPost::pluck('published_on', 'slug')->mapWithKeys(fn ($d, $s) => [\App\Support\Permalinks::base('newsroom').$s.'/' => $d]));

        return collect(SeoIndex::all())->reject(fn ($r) => $r['noindex'])->map(function ($r) use ($base, $dates) {
            $depth = substr_count(trim($r['path'], '/'), '/') + ($r['path'] === '/' ? -1 : 0);

            return [
                'loc' => $base.$r['path'],
                'path' => $r['path'],
                'type' => $r['type'],
                'lastmod' => isset($dates[$r['path']]) ? \Illuminate\Support\Carbon::parse($dates[$r['path']])->toDateString() : now()->toDateString(),
                'priority' => number_format(max(0.3, 1 - 0.2 * ($depth + 1)), 1),
            ];
        })->sortBy('path')->values()->all();
    }

    public static function xml(): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
        foreach (static::urls() as $u) {
            $xml .= '  <url><loc>'.e($u['loc']).'</loc><lastmod>'.$u['lastmod'].'</lastmod><priority>'.$u['priority'].'</priority></url>'."\n";
        }

        return $xml.'</urlset>'."\n";
    }
}
