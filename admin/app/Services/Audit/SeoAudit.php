<?php

namespace App\Services\Audit;

use App\Models\Content\Redirect;
use App\Models\Content\SeoMeta;
use App\Models\Content\SiteSetting;
use App\Models\Media;
use App\Services\SeoIndex;
use App\Services\SiteLinks;
use App\Services\Sitemap;

// Technical SEO checks. Database and settings checks are always run; live checks read what the
// website actually serves and say so when the site could not be reached.
class SeoAudit
{
    public static function run(?array $live = null): array
    {
        $f = [];
        $rows = collect(SeoIndex::all());
        $known = array_flip(SiteLinks::known());
        $siteUrl = LiveSite::base();

        // Titles and descriptions
        foreach ($rows as $r) {
            if (blank($r['seo_title'])) {
                $f[] = Finding::make('Missing title tag', 'Titles', 'high', $r['path'], 'No SEO title and no page title.', 'Set an SEO title for this page.');
            } elseif (mb_strlen($r['seo_title']) > 60) {
                $f[] = Finding::make('Title longer than 60 characters', 'Titles', 'low', $r['path'], mb_strlen($r['seo_title']).' characters: "'.$r['seo_title'].'"', 'Shorten the SEO title so Google does not cut it off.');
            }
            if (blank($r['description'])) {
                $f[] = Finding::make('Missing meta description', 'Descriptions', 'medium', $r['path'], 'No meta description and no summary text to fall back on.', 'Write a 120 to 160 character meta description.');
            } elseif (mb_strlen($r['description']) > 160 || mb_strlen($r['description']) < 70) {
                $f[] = Finding::make('Meta description length', 'Descriptions', 'low', $r['path'], mb_strlen($r['description']).' characters.', 'Aim for 120 to 160 characters.');
            }
        }
        foreach ($rows->filter(fn ($r) => filled($r['seo_title']))->groupBy('seo_title')->filter(fn ($g) => $g->count() > 1) as $title => $group) {
            foreach ($group as $r) {
                $f[] = Finding::make('Duplicate title tag', 'Titles', 'medium', $r['path'], '"'.$title.'" is also used by '.$group->where('path', '!=', $r['path'])->pluck('path')->implode(', '), 'Give each page its own title.');
            }
        }
        foreach ($rows->filter(fn ($r) => filled($r['description']))->groupBy('description')->filter(fn ($g) => $g->count() > 1) as $group) {
            foreach ($group as $r) {
                $f[] = Finding::make('Duplicate meta description', 'Descriptions', 'low', $r['path'], 'Same description as '.$group->where('path', '!=', $r['path'])->pluck('path')->implode(', '), 'Write a description that is specific to this page.');
            }
        }

        // Broken internal links and links through redirects, from saved content
        $inbound = [];
        foreach (Corpus::items() as $item) {
            foreach (SiteLinks::check($item['data']) as $link => $state) {
                $inbound[$link][] = $item['path'];
                if ($state === 'broken') {
                    $f[] = Finding::make('Broken internal link', 'Links', 'high', $item['path'], "Links to {$link}, which is not a live page.", 'Fix the link in '.$item['label'].' or add a redirect for the old address.');
                } elseif ($state === 'redirect') {
                    $f[] = Finding::make('Internal link through a redirect', 'Links', 'low', $item['path'], "Links to {$link}, which redirects.", 'Link straight to the final address.');
                }
            }
        }

        // Redirect chains, loops, missing targets and redirects that hide live pages
        $map = Redirect::where('active', true)->get()->mapWithKeys(fn ($r) => [SiteLinks::normalize($r->from_path) ?? $r->from_path => $r->to_path])->all();
        foreach ($map as $from => $to) {
            $chain = [$from];
            $next = SiteLinks::normalize($to);
            $loop = false;
            while ($next && isset($map[$next]) && count($chain) < 10) {
                if (in_array($next, $chain, true)) {
                    $loop = true;
                    break;
                }
                $chain[] = $next;
                $next = SiteLinks::normalize($map[$next]);
            }
            if ($loop) {
                $f[] = Finding::make('Redirect loop', 'Redirects', 'critical', $from, 'Path: '.implode(' > ', $chain).' > '.$next, 'Change one of these redirects so the chain ends on a live page.');
            } elseif (count($chain) > 1) {
                $f[] = Finding::make('Redirect chain', 'Redirects', 'medium', $from, 'Path: '.implode(' > ', $chain).' > '.$next, "Point {$from} straight to the final address.");
            } elseif ($next && ! isset($known[$next])) {
                $f[] = Finding::make('Redirect to a missing page', 'Redirects', 'high', $from, "Goes to {$to}, which is not a live page.", 'Change the target to a live page.');
            }
            if (isset($known[$from])) {
                $f[] = Finding::make('Redirect on a live page address', 'Redirects', 'medium', $from, 'A live page has this address, and an active redirect also uses it.', 'Remove the redirect, or unpublish the page if the redirect is intended.');
            }
        }

        // Canonical and indexability conflicts saved in the admin
        foreach (SeoMeta::whereNotNull('canonical')->where('canonical', '!=', '')->get() as $m) {
            $target = SiteLinks::normalize($m->canonical);
            if (! $target && preg_match('#^https?://#', $m->canonical)) {
                $f[] = Finding::make('Canonical points to another site', 'Canonical', 'medium', $m->path, "Canonical: {$m->canonical}", 'Only point canonicals at another domain on purpose (syndicated content).');
            } elseif ($target && ! isset($known[$target])) {
                $f[] = Finding::make('Canonical points to a missing page', 'Canonical', 'high', $m->path, "Canonical: {$m->canonical}", 'Point the canonical at a live page or clear it.');
            }
            if ($m->noindex) {
                $f[] = Finding::make('Canonical on a noindex page', 'Canonical', 'low', $m->path, 'The page is hidden from search engines and also names a canonical.', 'Use one signal: either noindex or a canonical, not both.');
            }
        }
        foreach ($rows->where('noindex', true) as $r) {
            $f[] = Finding::make('Page hidden from search engines', 'Indexing', 'info', $r['path'], 'Marked noindex in the admin.', 'Check that this is intended.');
        }

        // Sitemap built by the admin
        $sitemap = collect(Sitemap::urls());
        $publicHost = parse_url((string) config('admin.site_public_url'), PHP_URL_HOST);
        foreach ($sitemap->pluck('path')->duplicates() as $dup) {
            $f[] = Finding::make('Duplicate sitemap URL', 'Sitemap', 'low', $dup, 'Listed more than once.', 'Each URL should appear once.');
        }
        foreach ($sitemap as $u) {
            if (! isset($known[SiteLinks::normalize($u['path'])])) {
                $f[] = Finding::make('Sitemap URL is not a live page', 'Sitemap', 'medium', $u['path'], $u['loc'], 'Remove it from the sitemap or publish the page.');
            }
            if (parse_url($u['loc'], PHP_URL_HOST) !== $publicHost) {
                $f[] = Finding::make('Sitemap URL on another host', 'Sitemap', 'medium', $u['path'], $u['loc'], 'All sitemap URLs must use the website domain.');
            }
        }

        // Robots.txt saved in the admin
        $robots = (string) (SiteSetting::group('robots')['content'] ?? '');
        $parsed = Robots::parse($robots);
        foreach ($sitemap as $u) {
            $c = Robots::check($parsed, 'Googlebot', $u['path']);
            if (! $c['allowed']) {
                $f[] = Finding::make('Sitemap page blocked by robots.txt', 'Indexing', 'high', $u['path'], "Blocked for Googlebot by \"{$c['rule']}\" in the robots.txt saved in the admin.", 'Remove the rule, or remove the page from the sitemap.');
            }
        }

        // Images used in content without alt text
        $text = json_encode(collect(Corpus::items())->pluck('data')->all());
        foreach (Media::where('mime', 'like', 'image/%')->where(fn ($q) => $q->whereNull('alt')->orWhere('alt', ''))->get() as $img) {
            $url = $img->public_url ?: $img->path;
            if ($url && str_contains((string) $text, str_replace('/', '\/', $url))) {
                $f[] = Finding::make('Image without alt text', 'Images', 'low', null, "{$img->filename} is used in website content and has no alt text in the media library.", 'Add alt text in Media. Note: the website may set its own alt text for some images.');
            }
        }

        // Important pages with few links from editable content
        foreach (['/services/', '/contact-us/', '/about-us/', '/insights/blogs/'] as $p) {
            if (count(array_unique($inbound[$p] ?? [])) < 2) {
                $f[] = Finding::make('Important page with few internal links', 'Links', 'info', $p, 'Linked from '.count(array_unique($inbound[$p] ?? [])).' items of editable content. Menus and fixed templates are not counted.', 'Link to this page from related posts and pages.', 'recommendation');
            }
        }

        return Finding::sort(array_merge($f, static::liveChecks($live, $robots, $sitemap->count())));
    }

    // Compares what the live site serves with what the admin manages.
    public static function liveChecks(?array $live, string $robots, int $sitemapCount): array
    {
        $f = [];
        $base = LiveSite::base();
        $live ??= LiveSite::fetch(['/', '/robots.txt', '/sitemap.xml']);

        $home = $live['/'] ?? null;
        if (! ($home['ok'] ?? false)) {
            $f[] = Finding::make('Website could not be reached', 'Live site', 'high', $base.'/', 'Request failed: '.($home['error'] ?? 'unknown').'. Live checks were skipped.', 'Check that the website is online, then run the audit again.');

            return $f;
        }
        if ($home['status'] !== 200) {
            $f[] = Finding::make('Home page did not return 200', 'Live site', 'critical', $base.'/', 'HTTP '.$home['status'], 'Fix the website so the home page loads.');
        } else {
            $h = LiveSite::html($home['body']);
            if (blank($h['title'])) {
                $f[] = Finding::make('No title tag in the raw HTML', 'Live site', 'medium', $base.'/', 'The HTML served before JavaScript runs has no <title>.', 'Add a default title to index.html.');
            }
            if (blank($h['description'])) {
                $f[] = Finding::make('No meta description in the raw HTML', 'Live site', 'low', $base.'/', 'The HTML served before JavaScript runs has no meta description.', 'Add a default meta description to index.html.');
            }
            if ($h['robots'] && str_contains(strtolower($h['robots']), 'noindex')) {
                $f[] = Finding::make('Live home page is noindex', 'Indexing', 'critical', $base.'/', 'meta robots: '.$h['robots'], 'Remove noindex from the home page.');
            }
        }

        $r = $live['/robots.txt'] ?? null;
        if (($r['ok'] ?? false) && $r['status'] === 200) {
            if (trim(str_replace("\r", '', $r['body'])) !== trim(str_replace("\r", '', $robots)) && trim($robots) !== '') {
                $f[] = Finding::make('Live robots.txt differs from the admin', 'Robots.txt', 'high', $base.'/robots.txt', 'The website serves a robots.txt that is not the one saved under SEO, Robots.txt.', 'Publish the website again so it takes robots.txt from the admin.');
            }
        } else {
            $f[] = Finding::make('robots.txt not available', 'Robots.txt', 'medium', $base.'/robots.txt', ($r['ok'] ?? false) ? 'HTTP '.$r['status'] : 'Request failed', 'Serve a robots.txt file.');
        }

        $s = $live['/sitemap.xml'] ?? null;
        if (($s['ok'] ?? false) && $s['status'] === 200 && str_contains($s['body'], '<urlset')) {
            $count = substr_count($s['body'], '<loc>');
            if ($count !== $sitemapCount) {
                $f[] = Finding::make('Live sitemap differs from the admin', 'Sitemap', 'medium', $base.'/sitemap.xml', "Live sitemap has {$count} URLs, the admin sitemap has {$sitemapCount}.", 'Publish the website again so it takes sitemap.xml from the admin.');
            }
        } else {
            $f[] = Finding::make('sitemap.xml not available', 'Sitemap', 'medium', $base.'/sitemap.xml', ($s['ok'] ?? false) ? 'HTTP '.$s['status'] : 'Request failed', 'Serve a sitemap.xml file.');
        }

        return $f;
    }
}
