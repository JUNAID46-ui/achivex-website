<?php

namespace App\Services;

use App\Models\Content\Page;

// One list of every page on the website: the fixed pages shipped with the site
// plus the pages created in the admin.
class PageCatalog
{
    public static function all(): array
    {
        $saved = Page::all()->keyBy('path');
        $rows = [];
        foreach (SiteRoutes::pages() as $p) {
            $row = $saved->get($p['path']);
            $rows[] = [
                'path' => $p['path'], 'title' => $p['title'], 'type' => $p['type'], 'kind' => $p['path'] === '/' ? 'home' : 'builtin',
                'published' => true, 'edited' => (bool) ($row && $row->content), 'updated_at' => $row?->updated_at,
                'description' => $p['description'] ?? '', 'editable' => ! empty($p['editable']),
            ];
        }
        foreach ($saved->where('kind', 'custom') as $row) {
            $rows[] = [
                'path' => $row->path, 'title' => $row->title, 'type' => 'Custom page', 'kind' => 'custom',
                'published' => $row->published, 'edited' => true, 'updated_at' => $row->updated_at,
                'description' => $row->content['description'] ?? '', 'editable' => true,
            ];
        }

        return $rows;
    }

    public static function find(string $path): ?array
    {
        return collect(static::all())->firstWhere('path', $path);
    }

    // Default content for a fixed page, as exported by the website.
    public static function defaults(string $path): array
    {
        return collect(SiteRoutes::pages())->firstWhere('path', $path)['editable'] ?? [];
    }

    // The value used for page_sections.page: "home" for the home page, the URL otherwise.
    public static function sectionKey(string $path): string
    {
        return $path === '/' ? 'home' : $path;
    }
}
