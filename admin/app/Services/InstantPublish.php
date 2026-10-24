<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\Content\SiteSetting;

// Makes new pages real pages the moment they are saved, when the admin runs on the same server as the
// website (Hostinger). SITE_PUBLIC_PATH in .env is the website folder, for example
// /home/u123456789/domains/achivex.com/public_html.
//
// For every published page that has no file yet, the website's 404.html (which shows the right page
// for any address) is copied to that address with the page's own title, description and canonical
// link, and without noindex, so it answers with status 200 and search engines can index it at once.
// The next full build replaces these files with complete ones. sitemap.xml, robots.txt and llms.txt
// are written too, so they are always current.
class InstantPublish
{
    private const MARK = '<!--instant-publish-->';

    public static function root(): string
    {
        return rtrim((string) config('services.site_public_path', ''), '/');
    }

    public static function enabled(): bool
    {
        return static::root() !== '' && is_file(static::root().'/404.html');
    }

    // Writes the missing pages and removes pages written earlier that are no longer published.
    // Returns the number of pages written.
    public static function sync(): int
    {
        if (! static::enabled()) {
            return 0;
        }
        $root = static::root();
        $shell = (string) file_get_contents("{$root}/404.html");
        $written = AppSetting::group('site_instant')['paths'] ?? [];
        $live = [];
        $count = 0;

        foreach (SeoIndex::all() as $row) {
            $path = static::cleanPath($row['path']);
            if ($path === null) {
                continue;
            }
            $live[$path] = true;
            $file = "{$root}{$path}index.html";
            if (is_file($file)) {
                continue;
            }
            @mkdir(dirname($file), 0755, true);
            if (@file_put_contents($file, static::page($shell, $row)) !== false) {
                $written[$path] = true;
                $count++;
            }
        }

        // Unpublished or deleted: remove only the files written here, never files from a build.
        foreach (array_keys($written) as $path) {
            $file = "{$root}{$path}index.html";
            $ours = is_file($file) && str_contains((string) file_get_contents($file), self::MARK);
            if (isset($live[$path]) && $ours) {
                continue;
            }
            if ($ours) {
                @unlink($file);
                @rmdir(dirname($file));
            }
            unset($written[$path]);
        }
        AppSetting::put('site_instant', ['paths' => $written, 'synced_at' => now()->toIso8601String()]);

        static::writeSeoFiles($root);

        return $count;
    }

    // Accepts only plain site addresses such as /my-post/ or /insights/blogs/my-post/.
    private static function cleanPath(string $path): ?string
    {
        $path = '/'.trim($path, '/').'/';
        if ($path === '//' || ! preg_match('#^(/[a-z0-9][a-z0-9._-]*)+/$#i', $path) || str_contains($path, '..')) {
            return null;
        }

        return $path;
    }

    // The 404 page made into this page: title, description, canonical and robots changed in the HTML
    // and in the page data Next.js keeps in its inline scripts, then the script hashes recomputed.
    public static function page(string $shell, array $row): string
    {
        $base = rtrim((string) config('admin.site_public_url'), '/');
        $title = (string) $row['seo_title'];
        $robots = ! empty($row['noindex']) ? 'noindex, nofollow' : 'index, follow';
        $esc = fn (string $s) => htmlspecialchars($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        // A string as it appears inside the page data: JSON inside a JavaScript string.
        $data = fn (string $s) => str_replace('\\', '\\\\', substr(json_encode($s, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT), 1, -1));

        preg_match('#<title>(.*?)</title>#s', $shell, $m);
        $oldTitle = html_entity_decode($m[1] ?? 'Page Not Found | AchiveX LLC', ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $html = str_replace('<title>'.($m[1] ?? '').'</title>', '<title>'.$esc($title).'</title>'
            .'<meta name="description" content="'.$esc((string) $row['description']).'"/>'
            .'<link rel="canonical" href="'.$esc($base.$row['path']).'"/>', $shell);
        $html = str_replace('<meta name="robots" content="noindex"/>', '<meta name="robots" content="'.$robots.'"/>', $html);
        $html = str_replace('\"children\":\"'.$data($oldTitle).'\"', '\"children\":\"'.$data($title).'\"', $html);
        $html = str_replace('{\"name\":\"robots\",\"content\":\"noindex\"}', '{\"name\":\"robots\",\"content\":\"'.$robots.'\"}', $html);

        return static::rehash($html).self::MARK;
    }

    // Same rule as website/scripts/csp-hashes.mjs: allow only this page's own inline scripts.
    private static function rehash(string $html): string
    {
        preg_match_all('#<script(?![^>]*\bsrc=)([^>]*)>([\s\S]*?)</script>#', $html, $all, PREG_SET_ORDER);
        $hashes = [];
        foreach ($all as [, $attrs, $code]) {
            if (preg_match('#\btype="([^"]+)"#', $attrs, $t) && ! preg_match('#javascript|module#i', $t[1])) {
                continue;
            }
            $hashes["'sha256-".base64_encode(hash('sha256', $code, true))."'"] = true;
        }
        $policy = "script-src 'self' ".implode(' ', array_keys($hashes))."; object-src 'none'; base-uri 'self'";

        return preg_replace('#<meta http-equiv="Content-Security-Policy" content="[^"]*"/>#', '<meta http-equiv="Content-Security-Policy" content="'.$policy.'"/>', $html, 1);
    }

    private static function writeSeoFiles(string $root): void
    {
        try {
            @file_put_contents("{$root}/sitemap.xml", Sitemap::xml());
            @file_put_contents("{$root}/robots.txt", SiteSetting::group('robots')['content'] ?? "User-agent: *\nAllow: /\n");
            $llms = trim((string) (SiteSetting::group('llms')['content'] ?? ''));
            if ($llms !== '') {
                @file_put_contents("{$root}/llms.txt", $llms."\n");
            }
        } catch (\Throwable) {
            // The files from the last build stay in place.
        }
    }
}
