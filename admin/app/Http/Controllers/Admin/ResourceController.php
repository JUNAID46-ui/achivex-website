<?php

namespace App\Http\Controllers\Admin;

use App\Admin\FieldValues;
use App\Admin\Registry;
use App\Admin\Resource;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Content\SeoMeta;
use App\Services\SeoAnalyzer;
use App\Services\SiteRoutes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

// One controller for every content type defined in App\Admin\Resources.
class ResourceController extends Controller
{
    private function resource(string $slug): Resource
    {
        $resource = Registry::get($slug);
        abort_unless(auth()->user()->canAccess($resource->area), 403, 'You do not have permission to manage '.$resource->label.'.');

        return $resource;
    }

    public function index(Request $request, string $slug)
    {
        $resource = $this->resource($slug);
        $query = $resource->query();

        if ($term = trim((string) $request->input('q'))) {
            $query->where(function ($q) use ($resource, $term) {
                foreach ($resource->search as $col) {
                    $q->orWhere($col, 'ilike', "%{$term}%");
                }
            });
        }
        $status = $request->input('status');
        if ($resource->hasPublished && in_array($status, ['published', 'draft'], true)) {
            $query->where('published', $status === 'published');
        }

        $records = $query->paginate(20)->withQueryString();
        $counts = $resource->hasPublished ? [
            'all' => ($resource->model)::count(),
            'published' => ($resource->model)::where('published', true)->count(),
            'draft' => ($resource->model)::where('published', false)->count(),
        ] : null;

        $scores = [];
        if ($resource->seo) {
            $meta = SeoMeta::whereIn('path', $records->map(fn ($r) => $resource->sitePath($r)))->get()->keyBy('path');
            foreach ($records as $r) {
                $scores[$r->getKey()] = $this->analysis($resource, $r, $meta->get($resource->sitePath($r)))['score'];
            }
        }

        return view('admin.resources.index', compact('resource', 'records', 'counts', 'scores'));
    }

    public function create(string $slug)
    {
        $resource = $this->resource($slug);
        $record = $resource->newRecord();

        return view('admin.resources.form', ['resource' => $resource, 'record' => $record, 'seoMeta' => null, 'analysis' => null]);
    }

    public function store(Request $request, string $slug)
    {
        $resource = $this->resource($slug);
        $record = $resource->newRecord();
        $this->save($request, $resource, $record);
        ActivityLog::record('created', $resource->singular, $resource->titleOf($record));

        return redirect()->route('admin.resources.edit', [$slug, $record->getKey()])->with('status', "{$resource->singular} created.");
    }

    public function edit(string $slug, string $id)
    {
        $resource = $this->resource($slug);
        $record = ($resource->model)::findOrFail($id);
        $seoMeta = $resource->seo ? SeoMeta::find($resource->sitePath($record)) : null;
        $analysis = $resource->seo ? $this->analysis($resource, $record, $seoMeta) : null;

        return view('admin.resources.form', compact('resource', 'record', 'seoMeta', 'analysis'));
    }

    public function update(Request $request, string $slug, string $id)
    {
        $resource = $this->resource($slug);
        $record = ($resource->model)::findOrFail($id);
        $this->save($request, $resource, $record);
        ActivityLog::record('updated', $resource->singular, $resource->titleOf($record));

        return redirect()->route('admin.resources.edit', [$slug, $record->getKey()])->with('status', "{$resource->singular} saved.");
    }

    public function destroy(string $slug, string $id)
    {
        $resource = $this->resource($slug);
        $record = ($resource->model)::findOrFail($id);
        abort_if($slug === 'users' && $record->getKey() === auth()->id(), 422, 'You cannot delete your own account.');
        $title = $resource->titleOf($record);
        if ($resource->seo) {
            SeoMeta::where('path', $resource->sitePath($record))->delete();
        }
        $record->delete();
        Cache::forget('seo-index');
        ActivityLog::record('deleted', $resource->singular, $title);

        return redirect()->route('admin.resources.index', $slug)->with('status', "{$resource->singular} deleted.");
    }

    public function bulk(Request $request, string $slug)
    {
        $resource = $this->resource($slug);
        $ids = array_filter((array) $request->input('ids'));
        $action = $request->input('action');
        if (! $ids || ! in_array($action, ['delete', 'publish', 'unpublish'], true)) {
            return back()->with('status', 'Choose at least one item and an action.');
        }
        $query = ($resource->model)::whereIn(($resource->newRecord())->getKeyName(), $ids);
        if ($slug === 'users') {
            $query->where('id', '!=', auth()->id());
        }
        $count = match ($action) {
            'delete' => $query->delete(),
            'publish' => $resource->hasPublished ? $query->update(['published' => true]) : 0,
            'unpublish' => $resource->hasPublished ? $query->update(['published' => false]) : 0,
        };
        Cache::forget('seo-index');
        ActivityLog::record($action === 'delete' ? 'deleted' : 'updated', $resource->label, "{$count} items", "Bulk {$action}");

        return back()->with('status', "{$count} items updated.");
    }

    private function save(Request $request, Resource $resource, Model $record): void
    {
        $data = [];
        $rules = [];
        foreach ($resource->allFields() as $field) {
            if (($field['createOnly'] ?? false) && $record->exists) {
                continue;
            }
            $data[$field['name']] = FieldValues::fromRequest($field, $request);
            $rules[$field['name']] = $field['rules'] ?? 'nullable';
        }

        // Unique key check for slug based content
        $keyName = $record->getKeyName();
        if (isset($data[$keyName]) && $keyName !== 'id') {
            // Table name without the schema prefix; the connection's search_path finds it.
            $table = \Illuminate\Support\Str::after($record->getTable(), 'public.');
            $rules[$keyName] .= '|unique:'.$table.','.$keyName.($record->exists ? ','.$record->getKey().','.$keyName : '');
        }

        Validator::make($data, $rules, [], collect($resource->allFields())->pluck('label', 'name')->all())->validate();
        $data = $resource->beforeSave($data, $record);

        DB::transaction(function () use ($resource, $record, $data, $request) {
            $oldPath = $record->exists && $resource->seo ? $resource->sitePath($record) : null;
            $record->fill($data)->save();
            if ($resource->seo) {
                $this->saveSeo($request, $resource->sitePath($record->fresh()), $oldPath);
            }
        });
        Cache::forget('seo-index');
    }

    private function saveSeo(Request $request, string $path, ?string $oldPath): void
    {
        if ($oldPath && $oldPath !== $path) {
            SeoMeta::where('path', $oldPath)->update(['path' => $path]);
        }
        $values = [
            'title' => $request->input('seo_title') ?: null,
            'description' => $request->input('seo_description') ?: null,
            'focus_keyword' => $request->input('seo_keyword') ?: null,
            'og_image' => $request->input('seo_og_image') ?: null,
            'noindex' => $request->boolean('seo_noindex'),
        ];
        if (array_filter($values) || SeoMeta::find($path)) {
            SeoMeta::updateOrCreate(['path' => $path], $values + ['updated_at' => now()]);
        }
    }

    private function analysis(Resource $resource, Model $record, ?SeoMeta $meta): array
    {
        $template = \App\Models\Content\SiteSetting::group('seo')['titleTemplate'] ?? '%title% | AchiveX LLC';
        $content = $resource->seoContent($record);
        $description = $record->excerpt ?? $record->summary ?? $record->description ?? '';

        return SeoAnalyzer::analyze([
            'title' => str_replace('%title%', $meta?->title ?: $resource->titleOf($record), $template),
            'description' => $meta?->description ?: $description,
            'keyword' => $meta?->focus_keyword,
            'path' => $resource->sitePath($record),
            'noindex' => $meta?->noindex,
        ] + $content);
    }
}
