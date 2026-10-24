<?php

namespace App\Services\Audit;

use App\Admin\Registry;
use App\Models\Content\Page;
use App\Models\Content\PageSection;
use App\Services\SiteRoutes;

// Every piece of live website content the admin can read: path, text, headings and raw data.
class Corpus
{
    public static function items(): array
    {
        $items = [];
        foreach (['blog-posts', 'case-studies', 'news', 'whitepapers'] as $slug) {
            $resource = Registry::get($slug);
            foreach (($resource->model)::live()->get() as $r) {
                $c = $resource->seoContent($r);
                $items[] = ['path' => $resource->sitePath($r), 'label' => $resource->titleOf($r), 'type' => $resource->singular,
                    'text' => strip_tags((string) ($c['text'] ?? '')), 'headings' => array_values(array_filter($c['headings'] ?? [])),
                    'first' => strip_tags((string) ($c['first'] ?? '')), 'data' => $r->attributesToArray(), 'edit' => route('admin.resources.edit', [$slug, $r->getKey()])];
            }
        }

        $sections = PageSection::where('visible', true)->get()->groupBy('page');
        foreach (Page::live()->get() as $p) {
            $parts = $sections->get($p->path, collect());
            $items[] = ['path' => $p->path, 'label' => $p->title, 'type' => $p->kind === 'custom' ? 'Custom page' : 'Page',
                'text' => static::flatten([$p->content, $parts->pluck('data')->all()]), 'headings' => $parts->map(fn ($s) => $s->data['heading'] ?? null)->filter()->values()->all(),
                'first' => '', 'data' => ['content' => $p->content, 'sections' => $parts->pluck('data')->all()], 'edit' => route('admin.pages.edit', ['path' => $p->path])];
        }
        if ($home = $sections->get('home')) {
            $items[] = ['path' => '/', 'label' => 'Home', 'type' => 'Page', 'text' => static::flatten($home->pluck('data')->all()),
                'headings' => $home->map(fn ($s) => $s->data['heading'] ?? null)->filter()->values()->all(), 'first' => '', 'data' => $home->pluck('data')->all(), 'edit' => route('admin.builder')];
        }

        // Fixed pages with text bundled in the website (services). Read only here.
        foreach (SiteRoutes::pages() as $p) {
            if (! empty($p['content'])) {
                $items[] = ['path' => $p['path'], 'label' => $p['title'], 'type' => $p['type'] ?? 'Page', 'text' => (string) $p['content'],
                    'headings' => $p['headings'] ?? [], 'first' => '', 'data' => [], 'edit' => null];
            }
        }

        return $items;
    }

    public static function flatten($value): string
    {
        $out = [];
        array_walk_recursive($value, function ($v) use (&$out) {
            if (is_string($v) && ! preg_match('#^(https?://|/)\S*$#', $v)) {
                $out[] = strip_tags($v);
            }
        });

        return implode(' ', $out);
    }

    public static function words(string $text): int
    {
        $text = trim($text);

        return $text === '' ? 0 : count(preg_split('/\s+/', $text));
    }
}
