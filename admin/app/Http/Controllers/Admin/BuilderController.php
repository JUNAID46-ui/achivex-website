<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Content\CaseStudy;
use App\Models\Content\HeroStat;
use App\Models\Content\PageSection;
use App\Services\PageCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

// Visual editor for page sections stored in page_sections. Works for the home page,
// pages created in the admin, and extra sections at the bottom of any fixed page.
class BuilderController extends Controller
{
    public function index(Request $request)
    {
        $path = $request->input('page', '/');
        $page = PageCatalog::find($path);
        abort_unless($page, 404, 'That page does not exist.');
        $schemas = config('builder');
        $sections = PageSection::where('page', PageCatalog::sectionKey($path))->orderBy('sort')->get()
            ->map(fn ($s) => $this->present($s, $schemas));

        $library = collect($schemas)->map(fn ($s, $type) => ['type' => $type, 'label' => $s['label'] ?? Str::headline($type), 'description' => $s['description'] ?? ''])->values();

        return view('admin.builder.index', [
            'page' => $page,
            'pages' => collect(PageCatalog::all())->sortBy(fn ($p) => [$p['kind'] === 'home' ? 0 : ($p['kind'] === 'custom' ? 1 : 2), $p['path']])->values(),
            'sections' => $sections,
            'library' => $library,
            'heroStats' => HeroStat::orderBy('sort')->get(['id', 'value', 'label']),
            'cases' => CaseStudy::orderBy('sort')->pluck('title', 'slug'),
            'siteUrl' => rtrim(config('admin.site_url'), '/'),
        ]);
    }

    private function present(PageSection $s, array $schemas): array
    {
        $type = $s->type ?: $s->key;

        return [
            'id' => $s->id, 'key' => $s->key, 'type' => $type, 'label' => $s->label, 'visible' => $s->visible,
            'data' => (object) ($s->data ?? []), 'updated_at' => $s->updated_at?->diffForHumans(),
            'schema' => $schemas[$type] ?? ['description' => '', 'fields' => []],
        ];
    }

    public function add(Request $request)
    {
        $data = $request->validate(['page' => 'required|max:500', 'type' => 'required|in:'.implode(',', array_keys(config('builder')))]);
        abort_unless(PageCatalog::find($data['page']), 404);
        $schema = config("builder.{$data['type']}");
        $pageKey = PageCatalog::sectionKey($data['page']);

        // Start from the home page version of this section if there is one, so it is not empty.
        $starter = PageSection::where('page', 'home')->where(fn ($q) => $q->where('type', $data['type'])->orWhere('key', $data['type']))->value('data');
        $section = PageSection::create([
            'page' => $pageKey,
            'key' => $data['type'].'-'.Str::lower(Str::random(6)),
            'type' => $data['type'],
            'label' => $schema['label'] ?? Str::headline($data['type']),
            'data' => $starter ?? ($schema['defaults'] ?? []),
            'sort' => (int) PageSection::where('page', $pageKey)->max('sort') + 1,
            'visible' => true,
            'updated_at' => now(),
        ]);
        ActivityLog::record('created', 'Page section', $section->label, 'Added to '.$data['page']);

        return response()->json(['ok' => true, 'message' => "{$section->label} added.", 'section' => $this->present($section->fresh(), config('builder'))]);
    }

    public function destroy(PageSection $section)
    {
        $section->delete();
        ActivityLog::record('deleted', 'Page section', $section->label, 'Removed from '.$section->page);

        return response()->json(['ok' => true, 'message' => "{$section->label} removed."]);
    }

    public function save(Request $request, PageSection $section)
    {
        $schema = config('builder.'.($section->type ?: $section->key).'.fields', []);
        $input = (array) $request->input('data', []);
        $data = $section->data ?? [];
        foreach ($schema as $field) {
            $data[$field['name']] = $this->clean($field, $input[$field['name']] ?? null);
        }
        $section->forceFill(['data' => $data, 'updated_at' => now()])->save();
        if ($request->filled('label')) {
            $section->forceFill(['label' => mb_substr($request->input('label'), 0, 80)])->save();
        }
        ActivityLog::record('updated', 'Page section', $section->label, 'Page builder: '.$section->page);

        return response()->json(['ok' => true, 'message' => "{$section->label} saved."]);
    }

    public function order(Request $request)
    {
        $ids = array_map('intval', (array) $request->input('ids'));
        DB::transaction(function () use ($ids) {
            foreach ($ids as $i => $id) {
                PageSection::where('id', $id)->update(['sort' => $i, 'updated_at' => now()]);
            }
        });
        ActivityLog::record('updated', 'Page section', 'Section order', 'Reordered sections');

        return response()->json(['ok' => true, 'message' => 'Section order saved.']);
    }

    public function toggle(PageSection $section)
    {
        $section->forceFill(['visible' => ! $section->visible, 'updated_at' => now()])->save();
        ActivityLog::record('updated', 'Page section', $section->label, $section->visible ? 'Shown' : 'Hidden');

        return response()->json(['ok' => true, 'visible' => $section->visible, 'message' => $section->label.($section->visible ? ' is now visible.' : ' is now hidden.')]);
    }

    public function heroStats(Request $request)
    {
        $rows = collect((array) $request->input('stats'))
            ->map(fn ($r) => ['value' => trim((string) ($r['value'] ?? '')), 'label' => trim((string) ($r['label'] ?? ''))])
            ->filter(fn ($r) => $r['value'] !== '' || $r['label'] !== '')->values()->take(3);
        DB::transaction(function () use ($rows) {
            HeroStat::query()->delete();
            foreach ($rows as $i => $r) {
                HeroStat::create($r + ['sort' => $i]);
            }
        });
        ActivityLog::record('updated', 'Page section', 'Hero figures');

        return response()->json(['ok' => true, 'message' => 'Hero figures saved.']);
    }

    private function clean(array $field, mixed $value): mixed
    {
        return match ($field['type']) {
            'lines' => is_array($value) ? array_values(array_filter(array_map('trim', $value), 'strlen'))
                : array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $value)), 'strlen')),
            'images' => array_values(array_filter(array_map(fn ($v) => trim((string) $v), (array) $value), 'strlen')),
            'repeater' => collect((array) $value)->map(function ($item) use ($field) {
                $row = [];
                foreach ($field['fields'] as $sub) {
                    $row[$sub['name']] = $this->clean($sub, $item[$sub['name']] ?? null);
                }

                return $row;
            })->filter(fn ($row) => collect($row)->filter(fn ($v) => $v !== '' && $v !== [])->isNotEmpty())->values()->all(),
            default => mb_substr(trim((string) $value), 0, 5000),
        };
    }
}
