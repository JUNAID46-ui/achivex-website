<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

// Fixed website pages (services, industries, about pages) exported by the React site.
// Tries the live site's /site-routes.json first, then the copy bundled with the admin.
class SiteRoutes
{
    public static function data(): array
    {
        return Cache::remember('site-routes', 600, function () {
            try {
                $res = Http::timeout(2)->get(rtrim(config('admin.site_url'), '/').'/site-routes.json');
                if ($res->ok() && is_array($res->json('pages'))) {
                    return $res->json();
                }
            } catch (\Throwable) {
                // Fall back to the bundled copy below.
            }

            // The bundled copy describes the main website only; another selected website gets an
            // empty list until its own /site-routes.json can be read.
            return \App\Support\Sites::active()
                ? ['pages' => [], 'services' => [], 'industries' => [], 'blogCategories' => []]
                : json_decode(file_get_contents(resource_path('site-routes.json')), true);
        });
    }

    public static function pages(): array
    {
        return static::data()['pages'] ?? [];
    }

    public static function serviceOptions(): array
    {
        return collect(static::data()['services'] ?? [])->pluck('name', 'slug')->all();
    }

    public static function industryOptions(): array
    {
        return collect(static::data()['industries'] ?? [])->pluck('name', 'slug')->all();
    }

    // Category names for blog posts: the list saved under Blog Posts, Categories, plus any name a post
    // still uses so an older post keeps its value when edited.
    public static function blogCategories(): array
    {
        $saved = \App\Models\Content\SiteSetting::group('blog')['categories'] ?? [];
        $cats = $saved ?: (static::data()['blogCategories'] ?? ['Data', 'Cloud', 'Security', 'Strategy', 'Operations']);
        $cats = array_values(array_unique(array_merge($cats, static::blogCategoriesInUse())));

        return array_combine($cats, $cats);
    }

    public static function blogCategoriesInUse(): array
    {
        try {
            return \App\Models\Content\BlogPost::query()->whereNotNull('category')->distinct()->orderBy('category')->pluck('category')->all();
        } catch (\Throwable) {
            return [];
        }
    }
}
