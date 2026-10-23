<?php

namespace App\Services;

use App\Admin\Registry;
use App\Models\Content\BlogPost;
use App\Models\Content\CaseStudy;
use App\Models\Content\NewsPost;
use App\Models\Content\SeoMeta;
use App\Models\Content\SiteSetting;
use App\Models\Content\Whitepaper;

// Every URL of the website with its effective SEO values and score.
class SeoIndex
{
    public static function all(): array
    {
        $meta = SeoMeta::all()->keyBy('path');
        $seo = SiteSetting::group('seo');
        $template = $seo['titleTemplate'] ?? '%title% | AchiveX LLC';
        $rows = [];

        foreach (SiteRoutes::pages() as $p) {
            $rows[] = static::row(\App\Support\Permalinks::map($p['path']), $p['title'], $p['description'], $p['type'], $meta, $template, $seo, [
                'text' => $p['content'] ?? '', 'headings' => $p['headings'] ?? [], 'first' => '', 'defaultKeyword' => $p['focus'] ?? '',
            ]);
        }

        // Pages made in the admin, scored on the text of their sections.
        $sections = \App\Models\Content\PageSection::where('visible', true)->where('page', '!=', 'home')->get()->groupBy('page');
        foreach (\App\Models\Content\Page::where('kind', 'custom')->live()->get() as $page) {
            $parts = $sections->get($page->path, collect());
            $text = $parts->map(fn ($sec) => collect($sec->data ?? [])->flatten()->filter(fn ($v) => is_string($v) && ! str_starts_with($v, 'http') && ! str_starts_with($v, '/'))->implode(' '))->implode(' ');
            $headings = $parts->map(fn ($sec) => $sec->data['heading'] ?? null)->filter()->values()->all();
            $rows[] = static::row($page->path, $page->title, (string) ($page->content['description'] ?? ''), 'Custom page', $meta, $template, $seo, [
                'text' => $text, 'headings' => $headings, 'first' => '', 'defaultKeyword' => '',
            ]);
        }

        $dynamic = [
            'blog-posts' => [BlogPost::where('published', true)->get(), 'Blog post'],
            'case-studies' => [CaseStudy::where('published', true)->get(), 'Case study'],
            'news' => [NewsPost::where('published', true)->get(), 'News'],
            'whitepapers' => [Whitepaper::where('published', true)->get(), 'Whitepaper'],
        ];
        foreach ($dynamic as $slug => [$items, $type]) {
            $resource = Registry::get($slug);
            foreach ($items as $item) {
                $content = $resource->seoContent($item);
                $default = $slug === 'blog-posts' || $slug === 'news' ? $item->excerpt ?? $item->summary : ($item->summary ?? $item->description);
                $rows[] = static::row($resource->sitePath($item), $resource->titleOf($item), (string) $default, $type, $meta, $template, $seo, $content + [
                    'resource' => $slug, 'id' => $item->getKey(),
                ]);
            }
        }

        return $rows;
    }

    private static function row(string $path, string $title, string $description, string $type, $meta, string $template, array $seo, array $content): array
    {
        $m = $meta->get($path);
        $pageTitle = $m?->title ?: $title;
        $fullTitle = $path === '/' && ! $m?->title ? ($seo['homeTitle'] ?? $pageTitle) : str_replace('%title%', $pageTitle, $template);
        $desc = $m?->description ?: $description;
        $keyword = $m?->focus_keyword ?: ($content['defaultKeyword'] ?? '');
        $analysis = SeoAnalyzer::analyze([
            'title' => $fullTitle, 'description' => $desc, 'keyword' => $keyword, 'path' => $path,
            'text' => $content['text'] ?? '', 'headings' => $content['headings'] ?? [], 'first' => $content['first'] ?? '', 'noindex' => $m?->noindex,
            'raw' => $content['raw'] ?? null, 'paragraphs' => $content['paragraphs'] ?? null,
        ]);

        return [
            'path' => $path, 'type' => $type, 'title' => $title, 'seo_title' => $fullTitle, 'description' => $desc,
            'keyword' => $keyword, 'custom' => (bool) $m, 'noindex' => (bool) $m?->noindex, 'score' => $analysis['score'],
            'checks' => $analysis['checks'], 'resource' => $content['resource'] ?? null, 'id' => $content['id'] ?? null,
        ];
    }
}
