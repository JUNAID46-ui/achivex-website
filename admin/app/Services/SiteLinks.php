<?php

namespace App\Services;

use App\Models\Content\Page;
use App\Models\Content\Redirect;
use Illuminate\Support\Facades\Cache;

// Internal links found in saved content, checked against the pages the website actually has.
class SiteLinks
{
    // Every live page path of the website, plus listing pages that have no SEO row.
    public static function known(): array
    {
        return Cache::remember('site-known-paths', 600, function () {
            $paths = collect(SeoIndex::all())->pluck('path');
            $paths = $paths->merge(collect(SiteRoutes::pages())->pluck('path')->map(fn ($x) => \App\Support\Permalinks::map($x)));
            $paths = $paths->merge(Page::live()->pluck('path'));

            return $paths->map(fn ($p) => static::normalize($p))->filter()->unique()->values()->all();
        });
    }

    public static function redirects(): array
    {
        return Cache::remember('site-redirect-map', 600, fn () => Redirect::where('active', true)->get(['from_path', 'to_path'])
            ->mapWithKeys(fn ($r) => [static::normalize($r->from_path) => $r->to_path])->all());
    }

    public static function forget(): void
    {
        Cache::forget('site-known-paths');
        Cache::forget('site-redirect-map');
    }

    // "/about-us" and "/about-us/?x=1#team" both become "/about-us/". Null for files and non site links.
    public static function normalize(?string $href): ?string
    {
        $href = trim((string) $href);
        $site = parse_url((string) config('admin.site_url'), PHP_URL_HOST);
        $public = parse_url((string) config('admin.site_public_url'), PHP_URL_HOST);
        if (preg_match('#^https?://#i', $href)) {
            $host = parse_url($href, PHP_URL_HOST);
            if (! in_array($host, array_filter([$site, $public, $public ? preg_replace('/^www\./', '', $public) : null]), true)) {
                return null;
            }
            $href = (string) (parse_url($href, PHP_URL_PATH) ?: '/');
        }
        if (! str_starts_with($href, '/') || str_starts_with($href, '//')) {
            return null;
        }
        $path = strtok($href, '?#') ?: '/';
        if (preg_match('/\.[a-z0-9]{2,5}$/i', $path)) {
            return null; // files such as /images/x.webp or /brand/logo.png
        }

        return rtrim($path, '/').'/';
    }

    // All internal link targets inside a value (strings, arrays, HTML or plain paths).
    public static function extract($value): array
    {
        $found = [];
        $walk = function ($v) use (&$walk, &$found) {
            if (is_array($v)) {
                foreach ($v as $item) {
                    $walk($item);
                }

                return;
            }
            if (! is_string($v) || $v === '') {
                return;
            }
            if (preg_match_all('/href\s*=\s*["\']([^"\']+)["\']/i', $v, $m)) {
                array_push($found, ...$m[1]);
            }
            if (preg_match_all('/\]\((\/[^)\s]*)\)/', $v, $m)) {
                array_push($found, ...$m[1]); // markdown style [text](/path)
            }
            if (preg_match('#^(/[^\s]*|https?://\S+)$#', trim($v))) {
                $found[] = trim($v); // a field that holds just a link
            }
        };
        $walk($value);

        return collect($found)->map(fn ($h) => static::normalize($h))->filter()->unique()->values()->all();
    }

    // [path => 'ok' | 'redirect' | 'broken'] for every internal link in the value.
    public static function check($value): array
    {
        $known = array_flip(static::known());
        $redirects = static::redirects();
        $out = [];
        foreach (static::extract($value) as $path) {
            $out[$path] = isset($known[$path]) ? 'ok' : (isset($redirects[$path]) || \App\Support\Permalinks::fromOldBase($path) ? 'redirect' : 'broken');
        }

        return $out;
    }
}
