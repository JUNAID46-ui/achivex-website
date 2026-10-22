<?php

namespace App\Support;

use App\Models\Content\SiteSetting;

// Address prefixes of the website's content types (Settings, Permalinks). Mirrors website/src/lib/permalinks.js.
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

    // Fixed pages of the website that a prefix must not take over.
    public const RESERVED = ['/about-us/', '/services/', '/industries/', '/contact-us/', '/careers/', '/privacy-policy/', '/terms-of-service/', '/insights/'];

    public const PATTERN = '#^/[a-z0-9-]+(/[a-z0-9-]+)*/$#';

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

    // A path written with the default prefix, under the saved prefix.
    public static function map(string $path): string
    {
        $bases = static::all();
        foreach (self::DEFAULTS as $type => $default) {
            if (str_starts_with($path, $default)) {
                return $bases[$type].substr($path, strlen($default));
            }
        }

        return $path;
    }

    // For a link still using an old default prefix: its new address, or null.
    public static function fromOldBase(string $path): ?string
    {
        $bases = static::all();
        foreach (self::DEFAULTS as $type => $default) {
            if ($bases[$type] !== $default && str_starts_with($path, $default)) {
                return $bases[$type].substr($path, strlen($default));
            }
        }

        return null;
    }
}
