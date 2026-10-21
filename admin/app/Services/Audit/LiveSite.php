<?php

namespace App\Services\Audit;

use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;

// Fetches a few URLs of the live website at once and reads what a crawler that does not run
// JavaScript would see. Every result says whether the fetch worked, so a failed fetch is never
// reported as a passed check.
class LiveSite
{
    public static function base(): string
    {
        return rtrim((string) config('admin.site_url'), '/');
    }

    // [path => ['ok' => bool, 'status' => int|null, 'body' => string, 'type' => string, 'error' => string|null]]
    public static function fetch(array $paths, int $timeout = 5): array
    {
        $base = static::base();
        $paths = array_values(array_unique($paths));
        try {
            $responses = Http::pool(fn (Pool $pool) => array_map(
                fn ($p) => $pool->as($p)->withHeaders(['User-Agent' => 'AchiveX-Admin-Audit/1.0'])->timeout($timeout)->withoutRedirecting()->get($base.$p),
                $paths
            ));
        } catch (\Throwable $e) {
            return array_fill_keys($paths, ['ok' => false, 'status' => null, 'body' => '', 'type' => '', 'error' => class_basename($e)]);
        }
        $out = [];
        foreach ($paths as $p) {
            $r = $responses[$p] ?? null;
            if ($r instanceof \Illuminate\Http\Client\Response) {
                $out[$p] = ['ok' => true, 'status' => $r->status(), 'body' => mb_substr($r->body(), 0, 2_000_000), 'type' => (string) $r->header('Content-Type'), 'error' => null, 'location' => $r->header('Location')];
            } else {
                $out[$p] = ['ok' => false, 'status' => null, 'body' => '', 'type' => '', 'error' => $r instanceof \Throwable ? class_basename($r) : 'No response'];
            }
        }

        return $out;
    }

    // What the raw HTML (before any JavaScript) contains.
    public static function html(string $html): array
    {
        $get = fn (string $re) => preg_match($re, $html, $m) ? html_entity_decode(trim($m[1]), ENT_QUOTES) : null;
        $body = preg_replace('#<(script|style|noscript|template)\b[^>]*>.*?</\1>#is', ' ', $html);
        $body = preg_match('#<body\b[^>]*>(.*)</body>#is', $body, $m) ? $m[1] : $body;
        $text = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($body), ENT_QUOTES)));
        preg_match_all('#<script[^>]+type=["\']application/ld\+json["\'][^>]*>(.*?)</script>#is', $html, $ld);
        preg_match_all('#<img\b(?![^>]*\balt=)[^>]*>#i', $html, $noAlt);
        preg_match_all('#<h1\b#i', $html, $h1);

        return [
            'title' => $get('#<title[^>]*>(.*?)</title>#is'),
            'description' => $get('#<meta[^>]+name=["\']description["\'][^>]+content=["\']([^"\']*)#i') ?? $get('#<meta[^>]+content=["\']([^"\']*)["\'][^>]+name=["\']description["\']#i'),
            'canonical' => $get('#<link[^>]+rel=["\']canonical["\'][^>]+href=["\']([^"\']*)#i'),
            'robots' => $get('#<meta[^>]+name=["\']robots["\'][^>]+content=["\']([^"\']*)#i'),
            'jsonld' => count($ld[1]),
            'jsonld_valid' => collect($ld[1])->every(fn ($j) => json_decode(trim($j)) !== null),
            'words' => $text === '' ? 0 : count(preg_split('/\s+/', $text)),
            'h1' => count($h1[0]),
            'img_without_alt' => count($noAlt[0]),
        ];
    }
}
