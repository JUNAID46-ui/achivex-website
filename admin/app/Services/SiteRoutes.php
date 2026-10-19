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

            return json_decode(file_get_contents(resource_path('site-routes.json')), true);
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

    public static function blogCategories(): array
    {
        $cats = static::data()['blogCategories'] ?? ['Data', 'Cloud', 'Security', 'Strategy', 'Operations'];

        return array_combine($cats, $cats);
    }
}
