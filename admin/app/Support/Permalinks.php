<?php

namespace App\Support;

use App\Models\Content\BlogPost;
use App\Models\Content\CaseStudy;
use App\Models\Content\NewsPost;
use App\Models\Content\Page;
use App\Models\Content\SiteSetting;
use App\Models\Content\Whitepaper;
use App\Services\SiteRoutes;

// Web addresses of the website's content types and services (Settings, Permalinks).
// Mirrors website/src/lib/permalinks.js. A prefix of "/" puts items at the root of the site
// (/my-post/); the list page then keeps its default address.
class Permalinks
{
    public const DEFAULTS = [
        'blogs' => '/insights/blogs/',
        'caseStudies' => '/insights/case-studies/',
        'newsroom' => '/insights/newsroom/',
        'whitepapers' => '/insights/whitepapers/',
    ];

    public const LABELS = [
        'blogs' => 'Blog posts',
        'caseStudies' => 'Case studies',
        'newsroom' => 'Newsroom',
        'whitepapers' => 'Whitepapers and eBooks',
    ];

    public const MODELS = [
        'blogs' => BlogPost::class,
        'caseStudies' => CaseStudy::class,
        'newsroom' => NewsPost::class,
        'whitepapers' => Whitepaper::class,
    ];

    public const ITEM = ['blogs' => 'blog post', 'caseStudies' => 'case study', 'newsroom' => 'news post', 'whitepapers' => 'whitepaper'];

    public const SERVICE_MODES = [
        'category' => '/services/{category}/{service}/',
        'flat' => '/services/{service}/',
        'root' => '/{service}/',
    ];

    // Fixed pages of the website that a prefix must not take over.
    public const RESERVED = ['/about-us/', '/services/', '/industries/', '/contact-us/', '/careers/', '/privacy-policy/', '/terms-of-service/', '/insights/'];

    // First path segments that are not free for root level items (fixed pages and build folders).
    public const TOP_SEGMENTS = ['about-us', 'services', 'industries', 'contact-us', 'careers', 'privacy-policy', 'terms-of-service', 'insights', 'assets', 'brand', 'admin', 'api'];

    public const PATTERN = '#^/([a-z0-9-]+/)*$#';

    public static function all(): array
    {
        $saved = SiteSetting::group('permalinks');
        $out = [];
        foreach (self::DEFAULTS as $type => $default) {
            $v = $saved[$type] ?? null;
            $out[$type] = is_string($v) && preg_match(self::PATTERN, $v) ? $v : $default;
        }

        return $out;
    }

    public static function base(string $type): string
    {
        return static::all()[$type];
    }

    public static function serviceMode(): string
    {
        $v = SiteSetting::group('permalinks')['services'] ?? null;

        return isset(self::SERVICE_MODES[$v]) ? $v : 'category';
    }

    // Address of the list page (blog index and so on).
    public static function listPath(string $type, ?string $base = null): string
    {
        $base ??= static::base($type);

        return $base === '/' ? self::DEFAULTS[$type] : $base;
    }

    // Service slug => default address, as exported by the website.
    public static function serviceDefaults(): array
    {
        return collect(SiteRoutes::data()['services'] ?? [])->pluck('path', 'slug')->all();
    }

    public static function servicePath(string $slug, string $default, ?string $mode = null): string
    {
        return match ($mode ?? static::serviceMode()) {
            'root' => "/{$slug}/",
            'flat' => "/services/{$slug}/",
            default => $default,
        };
    }

    // A path written with the default address, under the saved permalinks.
    public static function map(string $path): string
    {
        $bases = static::all();
        foreach (self::DEFAULTS as $type => $default) {
            if ($path === $default) {
                return static::listPath($type, $bases[$type]);
            }
            if (str_starts_with($path, $default)) {
                return $bases[$type].substr($path, strlen($default));
            }
        }
        $slug = array_search($path, static::serviceDefaults(), true);

        return $slug === false ? $path : static::servicePath($slug, $path);
    }

    // For a link still using an old address format: its current address, or null.
    public static function fromOldBase(string $path): ?string
    {
        $bases = static::all();
        foreach (self::DEFAULTS as $type => $default) {
            if ($bases[$type] !== $default && $path !== $default && str_starts_with($path, $default)) {
                return $bases[$type].substr($path, strlen($default));
            }
        }
        // The website sends every non-root format of a service address on to the current one.
        foreach (static::serviceDefaults() as $slug => $default) {
            $now = static::servicePath($slug, $default);
            if ($path !== $now && in_array($path, [$default, "/services/{$slug}/"], true)) {
                return $now;
            }
        }

        return null;
    }

    // Who claims each one-segment address (/slug/) under the given bases and service mode, as
    // [slug, owner] pairs. Duplicates mean two things would share one address.
    public static function rootClaims(array $bases, string $serviceMode, ?string $except = null): array
    {
        $claims = array_map(fn ($s) => [$s, 'a fixed page of the website'], self::TOP_SEGMENTS);
        foreach (Page::where('kind', 'custom')->pluck('path') as $path) {
            $seg = trim((string) $path, '/');
            if ($seg !== '' && ! str_contains($seg, '/')) {
                $claims[] = [$seg, 'a custom page'];
            }
        }
        if ($serviceMode === 'root') {
            foreach (array_keys(static::serviceDefaults()) as $slug) {
                $claims[] = [$slug, 'a service'];
            }
        }
        foreach (self::MODELS as $type => $model) {
            if ($type !== $except && $bases[$type] === '/') {
                foreach ($model::pluck('slug') as $slug) {
                    $claims[] = [$slug, 'a '.self::ITEM[$type]];
                }
            }
        }

        return $claims;
    }

    // What other than a page already lives at a one-segment address such as /my-post/, or null.
    public static function contentAt(string $path): ?string
    {
        $seg = trim($path, '/');
        if ($seg === '' || str_contains($seg, '/')) {
            return null;
        }
        foreach (static::rootClaims(static::all(), static::serviceMode()) as [$slug, $owner]) {
            if ($slug === $seg && $owner !== 'a custom page' && $owner !== 'a fixed page of the website') {
                return $owner;
            }
        }

        return null;
    }

    // Extra validation rule for an item's slug when its type lives at the root of the site.
    public static function slugRule(string $type): string
    {
        if (static::base($type) !== '/') {
            return '';
        }
        $taken = array_unique(array_column(static::rootClaims(static::all(), static::serviceMode(), $type), 0));

        return '|not_in:'.implode(',', $taken);
    }
}
