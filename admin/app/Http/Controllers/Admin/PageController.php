<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Content\Page;
use App\Models\Content\PageSection;
use App\Models\Content\SeoMeta;
use App\Services\ContentSchema;
use App\Services\PageCatalog;
use App\Services\Revisions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

// The "Pages" section: every page of the website, its content, and pages made in the admin.
class PageController extends Controller
{
    public function index(Request $request)
    {
        $scores = collect(Cache::remember('seo-index', 600, fn () => \App\Services\SeoIndex::all()))->pluck('score', 'path');
        $rows = collect(PageCatalog::all());
        if ($t = $request->input('type')) {
            $rows = $rows->where('type', $t);
        }
        if ($q = mb_strtolower((string) $request->input('q'))) {
            $rows = $rows->filter(fn ($r) => str_contains(mb_strtolower($r['title'].' '.$r['path']), $q));
        }
        $rows = $rows->sortBy(fn ($r) => [$r['kind'] === 'home' ? 0 : ($r['kind'] === 'custom' ? 1 : 2), $r['path']])->values();

        return view('admin.pages.index', [
            'rows' => $rows,
            'scores' => $scores,
            'types' => collect(PageCatalog::all())->pluck('type')->unique()->values(),
            'siteUrl' => rtrim(config('admin.site_url'), '/'),
        ]);
    }

    // Page templates for new builder pages: the sections each one starts with.
    public const TEMPLATES = [
        'service' => ['Service page', 'Heading, overview with image, what is included, how it works, call to action and FAQs.', ['page_hero', 'image_text', 'cards', 'rich_text', 'cta', 'faq']],
        'landing' => ['Landing page', 'Heading, benefits, image with text, gallery, call to action and FAQs. Good for campaigns.', ['page_hero', 'cards', 'image_text', 'gallery', 'cta', 'faq']],
        'about' => ['About style page', 'Heading, story with image, text, values cards and a call to action.', ['page_hero', 'image_text', 'rich_text', 'cards', 'cta']],
        'basic' => ['Text page', 'Heading, a text block and a call to action. For simple information pages.', ['page_hero', 'rich_text', 'cta']],
        'image' => ['Heading, image and cards', 'Heading, image with text, a cards grid and a call to action.', ['page_hero', 'image_text', 'cards', 'cta']],
        'blank' => ['Blank page', 'Only the page heading. Add every section yourself.', ['page_hero']],
    ];

    public function create(Request $request)
    {
        $layout = (string) $request->query('template');
        if (! isset(self::TEMPLATES[$layout])) {
            return view('admin.pages.choose', ['templates' => self::TEMPLATES]);
        }

        return view('admin.pages.create', ['layout' => $layout, 'template' => self::TEMPLATES[$layout],
            'parents' => collect(PageCatalog::all())->where('kind', '!=', 'home')->pluck('title', 'path')]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|max:120',
            'slug' => ['required', 'max:120', 'regex:/^[a-z0-9-]+(\/[a-z0-9-]+)*$/'],
            'description' => 'nullable|max:300',
            'layout' => 'required|in:'.implode(',', array_keys(self::TEMPLATES)),
            'published' => 'nullable|boolean',
        ], ['slug.regex' => 'Use lowercase letters, numbers and dashes only, for example "our-pricing".'], ['slug' => 'page address']);
        $path = '/'.trim($data['slug'], '/').'/';
        if (PageCatalog::find($path)) {
            return back()->withInput()->withErrors(['slug' => "A page already uses {$path}. Choose another address."]);
        }
        if ($owner = \App\Support\Permalinks::contentAt($path)) {
            return back()->withInput()->withErrors(['slug' => "{$path} is the address of {$owner} (see Settings, Permalinks). Choose another address."]);
        }

        DB::transaction(function () use ($data, $path, $request) {
            Page::create([
                'path' => $path, 'kind' => 'custom', 'title' => $data['title'], 'published' => $request->boolean('published'),
                'content' => ['description' => $data['description'] ?? ''],
            ]);
            $starter = self::TEMPLATES[$data['layout']][2];
            foreach ($starter as $i => $type) {
                $schema = config("builder.{$type}");
                $sectionData = in_array($type, ['cta', 'faq'])
                    ? (PageSection::where('page', 'home')->where('key', $type)->value('data') ?? [])
                    : ($schema['defaults'] ?? []);
                if ($type === 'page_hero') {
                    $sectionData['heading'] = $data['title'];
                    $sectionData['text'] = $data['description'] ?? '';
                }
                PageSection::create([
                    'page' => $path, 'key' => $type.'-'.Str::lower(Str::random(6)), 'type' => $type,
                    'label' => $schema['label'] ?? Str::headline($type), 'data' => $sectionData, 'sort' => $i, 'visible' => true, 'updated_at' => now(),
                ]);
            }
        });
        Cache::forget('seo-index');
        ActivityLog::record('created', 'Page', $data['title'], $path);

        return redirect()->route('admin.builder', ['page' => $path])->with('status', 'Page created. Add and edit its sections here.');
    }

    public function edit(Request $request)
    {
        $page = PageCatalog::find((string) $request->input('path'));
        abort_unless($page, 404, 'That page does not exist.');
        if ($page['kind'] === 'home') {
            return redirect()->route('admin.builder', ['page' => '/']);
        }
        $saved = Page::find($page['path']);
        $viewData = ['page' => $page, 'saved' => $saved, 'siteUrl' => rtrim(config('admin.site_url'), '/'),
            'sectionCount' => PageSection::where('page', $page['path'])->count(),
            'revisions' => Revisions::for('pages', $page['path'])->where('action', 'update')->limit(10)->get()];

        if ($page['kind'] === 'custom') {
            return view('admin.pages.custom', $viewData);
        }
        $defaults = PageCatalog::defaults($page['path']);
        abort_unless($defaults, 404, 'This page has no editable content.');

        return view('admin.pages.edit', $viewData + [
            'fields' => [...ContentSchema::fields($defaults), \App\Support\DesignOptions::field()],
            'values' => array_replace($defaults, $saved?->content ?? []) + ['design' => []],
        ]);
    }

    public function update(Request $request)
    {
        $page = PageCatalog::find((string) $request->input('path'));
        abort_unless($page && $page['kind'] !== 'home', 404);

        if ($page['kind'] === 'custom') {
            return $this->updateCustom($request, $page);
        }
        $defaults = PageCatalog::defaults($page['path']);
        $input = json_decode((string) $request->input('content_json'), true);
        abort_unless(is_array($input), 422, 'The form could not be read. Reload the page and try again.');
        $content = ContentSchema::clean(ContentSchema::fields($defaults), $input, $defaults);
        if ($design = \App\Support\DesignOptions::clean($input['design'] ?? [])) {
            $content['design'] = $design;
        }

        if ($old = Page::find($page['path'])) {
            Revisions::snapshot('pages', $old, 'update');
        }
        Page::updateOrCreate(['path' => $page['path']], ['kind' => 'builtin', 'title' => $page['title'], 'published' => true, 'content' => $content]);
        Cache::forget('seo-index');
        ActivityLog::record('updated', 'Page', $page['title'], $page['path'], ['module' => 'Pages', 'id' => $page['path']]);

        return back()->with('status', 'Page saved. The website shows the new content on its next load.');
    }

    private function updateCustom(Request $request, array $page)
    {
        $data = $request->validate([
            'title' => 'required|max:120',
            'slug' => ['required', 'max:120', 'regex:/^[a-z0-9-]+(\/[a-z0-9-]+)*$/'],
            'description' => 'nullable|max:300',
        ], ['slug.regex' => 'Use lowercase letters, numbers and dashes only.'], ['slug' => 'page address']);
        $newPath = '/'.trim($data['slug'], '/').'/';
        if ($newPath !== $page['path'] && PageCatalog::find($newPath)) {
            return back()->withInput()->withErrors(['slug' => "A page already uses {$newPath}."]);
        }
        if ($newPath !== $page['path'] && ($owner = \App\Support\Permalinks::contentAt($newPath))) {
            return back()->withInput()->withErrors(['slug' => "{$newPath} is the address of {$owner} (see Settings, Permalinks)."]);
        }
        DB::transaction(function () use ($page, $newPath, $data, $request) {
            $row = Page::findOrFail($page['path']);
            Revisions::snapshot('pages', $row, 'update');
            if ($newPath !== $page['path']) {
                \App\Models\ContentRevision::where('resource', 'pages')->where('record_key', $page['path'])->update(['record_key' => $newPath]);
                Page::create(['path' => $newPath, 'kind' => 'custom', 'title' => $data['title'], 'published' => $request->boolean('published'), 'content' => ['description' => $data['description'] ?? '']]);
                PageSection::where('page', $page['path'])->update(['page' => $newPath]);
                SeoMeta::where('path', $page['path'])->update(['path' => $newPath]);
                $row->delete();
            } else {
                $row->update(['title' => $data['title'], 'published' => $request->boolean('published'), 'content' => array_merge($row->content ?? [], ['description' => $data['description'] ?? ''])]);
            }
        });
        Cache::forget('seo-index');
        ActivityLog::record('updated', 'Page', $data['title'], $newPath, ['module' => 'Pages', 'id' => $newPath]);

        return redirect()->route('admin.pages.edit', ['path' => $newPath])->with('status', 'Page settings saved.');
    }

    public function reset(Request $request)
    {
        $page = PageCatalog::find((string) $request->input('path'));
        abort_unless($page && $page['kind'] === 'builtin', 404);
        if ($old = Page::find($page['path'])) {
            Revisions::snapshot('pages', $old, 'update');
            $old->delete();
        }
        Cache::forget('seo-index');
        ActivityLog::record('updated', 'Page', $page['title'], 'Restored original content', ['module' => 'Pages', 'id' => $page['path']]);

        return back()->with('status', 'The original content of this page has been restored.');
    }

    // Puts an earlier saved copy of a page's content back. The current content is kept as a revision first.
    public function restoreRevision(Request $request, int $revision)
    {
        $request->validate(['confirm' => 'accepted'], ['confirm.accepted' => 'Tick the box to confirm the restore.']);
        $rev = \App\Models\ContentRevision::where('resource', 'pages')->where('action', 'update')->findOrFail($revision);
        $page = PageCatalog::find($rev->record_key);
        abort_unless($page && $page['kind'] !== 'home', 404);
        DB::transaction(function () use ($rev) {
            if ($current = Page::find($rev->record_key)) {
                Revisions::snapshot('pages', $current, 'update');
            }
            Page::updateOrCreate(['path' => $rev->record_key], collect($rev->data)->only(['kind', 'title', 'published', 'content'])->all());
        });
        Cache::forget('seo-index');
        ActivityLog::record('restored', 'Page', $page['title'], 'Restored the copy from '.$rev->created_at?->format('Y-m-d H:i'), ['module' => 'Pages', 'id' => $rev->record_key]);

        return redirect()->route('admin.pages.edit', ['path' => $rev->record_key])->with('status', 'Earlier version of the page restored.');
    }

    public function destroy(Request $request)
    {
        $page = PageCatalog::find((string) $request->input('path'));
        abort_unless($page && $page['kind'] === 'custom', 404);
        DB::transaction(function () use ($page) {
            if ($row = Page::find($page['path'])) {
                Revisions::snapshot('pages', $row, 'delete');
            }
            PageSection::where('page', $page['path'])->delete();
            SeoMeta::where('path', $page['path'])->delete();
            Page::where('path', $page['path'])->delete();
        });
        Cache::forget('seo-index');
        ActivityLog::record('deleted', 'Page', $page['title'], $page['path'], ['module' => 'Pages', 'id' => $page['path']]);

        return redirect()->route('admin.pages.index')->with('status', 'Page deleted.');
    }
}
