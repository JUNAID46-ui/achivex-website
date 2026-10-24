<?php

namespace App\Http\Controllers\Admin;

use App\Admin\FieldValues;
use App\Admin\Registry;
use App\Admin\Resource;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Content\SeoMeta;
use App\Models\ContentRevision;
use App\Services\ContentHealth;
use App\Services\ContentWorkflow;
use App\Services\Notifier;
use App\Services\Revisions;
use App\Services\SiteLinks;
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
        if ($resource->hasPublished && isset(ContentWorkflow::STATES[$status])) {
            $this->whereState($query, $resource, $status);
        }

        $records = $query->paginate(20)->withQueryString();
        $counts = null;
        if ($resource->hasPublished) {
            $counts = ['all' => ($resource->model)::count()];
            foreach (array_keys(ContentWorkflow::STATES) as $state) {
                $counts[$state] = $this->whereState(($resource->model)::query(), $resource, $state)->count();
            }
        }
        $metas = ContentWorkflow::metaFor($resource->slug, $records);
        $seoMetas = $resource->seo ? SeoMeta::whereIn('path', $records->map(fn ($r) => $resource->sitePath($r)))->get()->keyBy('path') : collect();
        $health = [];
        $states = [];
        foreach ($records as $r) {
            $health[$r->getKey()] = ContentHealth::check($resource, $r, $resource->seo ? $seoMetas->get($resource->sitePath($r)) : null);
            $states[$r->getKey()] = $resource->hasPublished ? ContentWorkflow::status($r, $metas->get((string) $r->getKey())) : null;
        }

        $scores = [];
        if ($resource->seo) {
            $meta = SeoMeta::whereIn('path', $records->map(fn ($r) => $resource->sitePath($r)))->get()->keyBy('path');
            foreach ($records as $r) {
                $scores[$r->getKey()] = $this->analysis($resource, $r, $meta->get($resource->sitePath($r)))['score'];
            }
        }

        return view('admin.resources.index', compact('resource', 'records', 'counts', 'scores', 'health', 'states', 'metas') + [
            'deleted' => Revisions::deleted($resource->slug, $resource->model)->count(),
            'canPublish' => ContentWorkflow::canPublish(auth()->user()),
        ]);
    }

    public function create(string $slug)
    {
        $resource = $this->resource($slug);
        $record = $resource->newRecord();

        return view('admin.resources.form', ['resource' => $resource, 'record' => $record, 'seoMeta' => null, 'analysis' => null,
            'state' => 'draft', 'meta' => null, 'health' => [], 'revisions' => collect(), 'canPublish' => ContentWorkflow::canPublish(auth()->user())]);
    }

    public function store(Request $request, string $slug)
    {
        $resource = $this->resource($slug);
        $record = $resource->newRecord();
        $state = $this->save($request, $resource, $record);
        ActivityLog::record('created', $resource->singular, $resource->titleOf($record), $state ? 'Status: '.ContentWorkflow::STATES[$state] : null, ['module' => $resource->label, 'id' => $record->getKey()]);
        $this->announce($resource, $record, $state, null);

        return redirect()->route('admin.resources.edit', [$slug, $record->getKey()])->with('status', "{$resource->singular} created.");
    }

    public function edit(string $slug, string $id)
    {
        $resource = $this->resource($slug);
        $record = ($resource->model)::findOrFail($id);
        $seoMeta = $resource->seo ? SeoMeta::find($resource->sitePath($record)) : null;
        $analysis = $resource->seo ? $this->analysis($resource, $record, $seoMeta) : null;

        $meta = ContentWorkflow::meta($resource->slug, $record);

        return view('admin.resources.form', compact('resource', 'record', 'seoMeta', 'analysis', 'meta') + [
            'state' => $resource->hasPublished ? ContentWorkflow::status($record, $meta) : null,
            'health' => ContentHealth::check($resource, $record, $seoMeta),
            'revisions' => Revisions::for($slug, (string) $record->getKey())->limit(10)->get(),
            'canPublish' => ContentWorkflow::canPublish(auth()->user()),
        ]);
    }

    public function update(Request $request, string $slug, string $id)
    {
        $resource = $this->resource($slug);
        $record = ($resource->model)::findOrFail($id);
        $before = $resource->hasPublished ? ContentWorkflow::status($record, ContentWorkflow::meta($slug, $record)) : null;
        $state = $this->save($request, $resource, $record);
        $changed = $state && $state !== $before ? 'Status: '.ContentWorkflow::STATES[$before].' to '.ContentWorkflow::STATES[$state] : null;
        ActivityLog::record($state === 'published' && $before !== 'published' ? 'published' : 'updated', $resource->singular, $resource->titleOf($record), $changed, ['module' => $resource->label, 'id' => $record->getKey()]);
        $this->announce($resource, $record, $state, $before);

        return redirect()->route('admin.resources.edit', [$slug, $record->getKey()])->with('status', "{$resource->singular} saved.");
    }

    public function destroy(string $slug, string $id)
    {
        $resource = $this->resource($slug);
        $record = ($resource->model)::findOrFail($id);
        if ($reason = $resource->deleteBlocker($record)) {
            return back()->withErrors(['delete' => $reason]);
        }
        $title = $resource->titleOf($record);
        DB::transaction(function () use ($resource, $record, $slug) {
            if ($slug !== 'users') {
                Revisions::snapshot($slug, $record, 'delete');
            }
            if ($resource->seo) {
                SeoMeta::where('path', $resource->sitePath($record))->delete();
            }
            $record->delete();
        });
        $this->forgetCaches();
        ActivityLog::record('deleted', $resource->singular, $title, $slug !== 'users' ? 'A copy was kept and can be restored.' : null, ['module' => $resource->label, 'id' => $id]);

        return redirect()->route('admin.resources.index', $slug)->with('status', "{$resource->singular} deleted.");
    }

    public function bulk(Request $request, string $slug)
    {
        $resource = $this->resource($slug);
        $ids = array_slice(array_filter((array) $request->input('ids')), 0, 200);
        $action = $request->input('action');
        $allowed = $resource->hasPublished ? ['delete', 'publish', 'draft', 'review', 'archive'] : ['delete'];
        if (! $ids || ! in_array($action, $allowed, true)) {
            return back()->with('status', 'Choose at least one item and an action.');
        }
        if ($action === 'publish' && ! ContentWorkflow::canPublish($request->user())) {
            return back()->withErrors(['action' => 'Only editors and administrators can publish.']);
        }
        $records = ($resource->model)::whereIn($resource->newRecord()->getKeyName(), $ids)->get();
        DB::transaction(function () use (&$records, $resource, $slug, $action) {
            foreach ($records as $i => $record) {
                $title = $resource->titleOf($record);
                if ($action === 'delete') {
                    // Checked one by one, so a bulk delete can never remove the last administrator.
                    if ($resource->deleteBlocker($record)) {
                        $records->forget($i);

                        continue;
                    }
                    if ($slug !== 'users') {
                        Revisions::snapshot($slug, $record, 'delete');
                    }
                    if ($resource->seo) {
                        SeoMeta::where('path', $resource->sitePath($record))->delete();
                    }
                    $record->delete();
                    ActivityLog::record('deleted', $resource->singular, $title, 'Bulk delete', ['module' => $resource->label, 'id' => $record->getKey()]);

                    continue;
                }
                $state = ['publish' => 'published', 'draft' => 'draft', 'review' => 'review', 'archive' => 'archived'][$action];
                Revisions::snapshot($slug, $record, 'update');
                ContentWorkflow::apply($record, $state, null, auth()->user());
                $record->save();
                ContentWorkflow::touch($resource, $record, $state, false);
                ActivityLog::record($state === 'published' ? 'published' : 'updated', $resource->singular, $title, 'Bulk: '.ContentWorkflow::STATES[$state], ['module' => $resource->label, 'id' => $record->getKey()]);
            }
        });
        $this->forgetCaches();
        $count = $records->count();

        return back()->with('status', "{$count} ".\Illuminate\Support\Str::plural('item', $count).' updated.');
    }

    public function revisions(string $slug, string $id)
    {
        $resource = $this->resource($slug);
        $record = ($resource->model)::findOrFail($id);
        $revisions = Revisions::for($slug, (string) $record->getKey())->get();
        $compare = request('compare') ? $revisions->firstWhere('id', (int) request('compare')) : null;

        return view('admin.resources.revisions', [
            'resource' => $resource, 'record' => $record, 'revisions' => $revisions, 'compare' => $compare,
            'diff' => $compare ? Revisions::diff($compare->data, $record->attributesToArray()) : [],
        ]);
    }

    // Puts an earlier copy back. The current version is saved as a revision first, so this can be undone.
    public function restore(Request $request, string $slug, string $id, int $revision)
    {
        $resource = $this->resource($slug);
        abort_if($slug === 'users', 404);
        $request->validate(['confirm' => 'accepted'], ['confirm.accepted' => 'Tick the box to confirm the restore.']);
        $record = ($resource->model)::findOrFail($id);
        $rev = ContentRevision::where('resource', $slug)->where('record_key', (string) $record->getKey())->findOrFail($revision);
        if (($rev->data['published'] ?? false) && ! ContentWorkflow::canPublish($request->user()) && ! $record->published) {
            return back()->withErrors(['confirm' => 'This copy was published. Only editors and administrators can restore it.']);
        }
        DB::transaction(function () use ($slug, $record, $rev, $resource) {
            Revisions::snapshot($slug, $record, 'update');
            $record->forceFill(collect($rev->data)->except($record->getKeyName())->all())->save();
            ContentWorkflow::touch($resource, $record, null, false);
        });
        $this->forgetCaches();
        ActivityLog::record('restored', $resource->singular, $resource->titleOf($record), 'Restored the copy from '.$rev->created_at?->format('Y-m-d H:i'), ['module' => $resource->label, 'id' => $record->getKey(), 'context' => ['revision' => $rev->id]]);

        return redirect()->route('admin.resources.edit', [$slug, $record->getKey()])->with('status', 'Earlier version restored. The version it replaced is in the revision history.');
    }

    public function deleted(string $slug)
    {
        $resource = $this->resource($slug);
        abort_if($slug === 'users', 404);

        return view('admin.resources.deleted', ['resource' => $resource, 'items' => Revisions::deleted($slug, $resource->model)]);
    }

    public function restoreDeleted(Request $request, string $slug, int $revision)
    {
        $resource = $this->resource($slug);
        abort_if($slug === 'users', 404);
        $request->validate(['confirm' => 'accepted'], ['confirm.accepted' => 'Tick the box to confirm the restore.']);
        $rev = ContentRevision::where('resource', $slug)->where('action', 'delete')->findOrFail($revision);
        $record = $resource->newRecord();
        $keyName = $record->getKeyName();
        if ($record->getIncrementing()) {
            $data = collect($rev->data)->except($keyName)->all();
        } else {
            abort_if(($resource->model)::whereKey($rev->record_key)->exists(), 422, 'An item with this address exists again, so the deleted copy cannot be restored.');
            $data = $rev->data;
        }
        // Restored items come back as drafts so nothing goes live by surprise.
        if ($resource->hasPublished) {
            $data['published'] = false;
            $data['publish_at'] = null;
        }
        DB::transaction(function () use ($record, $data, $resource, $rev) {
            $record->forceFill(collect($data)->except(['publish_at'])->all() + ($resource->hasPublished ? ['publish_at' => null] : []))->save();
            ContentRevision::where('resource', $resource->slug)->where('record_key', $rev->record_key)->update(['record_key' => (string) $record->getKey()]);
            ContentWorkflow::touch($resource, $record, $resource->hasPublished ? 'draft' : null, true);
        });
        $this->forgetCaches();
        ActivityLog::record('restored', $resource->singular, $resource->titleOf($record), 'Restored a deleted item as a draft', ['module' => $resource->label, 'id' => $record->getKey()]);

        return redirect()->route('admin.resources.edit', [$slug, $record->getKey()])->with('status', 'Deleted item restored'.($resource->hasPublished ? ' as a draft.' : '.'));
    }

    // Admin side preview, so drafts can be read before they go live.
    public function preview(string $slug, string $id)
    {
        $resource = $this->resource($slug);
        $record = ($resource->model)::findOrFail($id);
        $meta = ContentWorkflow::meta($slug, $record);

        return response()->view('admin.resources.preview', [
            'resource' => $resource, 'record' => $record, 'state' => $resource->hasPublished ? ContentWorkflow::status($record, $meta) : null,
        ])->header('X-Robots-Tag', 'noindex, nofollow');
    }

    private function whereState($query, $resource, string $state)
    {
        $key = $resource->newRecord()->getKeyName();
        $saved = fn ($q, array $states) => $q->whereRaw("cast({$key} as text) in (select record_key from admin.content_meta where resource = ? and status in (".implode(',', array_fill(0, count($states), '?')).'))', array_merge([$resource->slug], $states));

        return match ($state) {
            'published' => $query->live(),
            'scheduled' => $query->where('published', true)->where('publish_at', '>', now()),
            'review', 'archived' => $saved($query->where('published', false), [$state]),
            'draft' => $query->where('published', false)->whereRaw("cast({$key} as text) not in (select record_key from admin.content_meta where resource = ? and status in ('review','archived'))", [$resource->slug]),
        };
    }

    // In-app notices for scheduled items and items sent for review.
    private function announce(Resource $resource, Model $record, ?string $state, ?string $before): void
    {
        if (! $state || $state === $before) {
            return;
        }
        $url = route('admin.resources.edit', [$resource->slug, $record->getKey()]);
        if ($state === 'scheduled') {
            Notifier::send('content_scheduled', 'info', "{$resource->singular} scheduled: ".$resource->titleOf($record), 'Goes live on '.$record->publish_at?->format('M j, Y H:i').' UTC.', $resource->label, $url, $resource->area);
        } elseif ($state === 'review') {
            Notifier::send('content_review', 'info', "{$resource->singular} ready for review: ".$resource->titleOf($record), 'Sent for review by '.auth()->user()?->name.'.', $resource->label, $url, 'content');
        }
    }

    private function forgetCaches(): void
    {
        Cache::forget('seo-index');
        SiteLinks::forget();
    }

    // Returns the workflow state that was saved, or null for content without a published switch.
    private function save(Request $request, Resource $resource, Model $record): ?string
    {
        $data = [];
        $rules = [];
        foreach ($resource->allFields() as $field) {
            if (($field['createOnly'] ?? false) && $record->exists) {
                continue;
            }
            if ($resource->hasPublished && $field['name'] === 'published') {
                continue; // set by the workflow below
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

        $created = ! $record->exists;
        $current = $resource->hasPublished && ! $created ? ContentWorkflow::status($record, ContentWorkflow::meta($resource->slug, $record)) : null;
        $state = null;
        if ($resource->hasPublished) {
            $request->validate(['workflow_status' => 'required|in:'.implode(',', array_keys(ContentWorkflow::STATES)), 'publish_at' => 'nullable|date']);
            $state = $request->input('workflow_status');
        }

        DB::transaction(function () use ($resource, $record, $data, $request, $created, $current, $state) {
            $oldPath = $record->exists && $resource->seo ? $resource->sitePath($record) : null;
            $oldKey = $created ? null : (string) $record->getKey();
            if (! $created && $resource->slug !== 'users') {
                Revisions::snapshot($resource->slug, $record, 'update');
            }
            $record->fill($data);
            if ($state) {
                ContentWorkflow::apply($record, $state, $request->input('publish_at'), $request->user(), $current);
            }
            $record->save();
            if ($resource->slug !== 'users') {
                ContentWorkflow::touch($resource, $record, $state, $created, $oldKey);
            }
            if ($resource->seo) {
                $this->saveSeo($request, $resource->sitePath($record->fresh()), $oldPath);
            }
        });
        $this->forgetCaches();

        return $state;
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
            'schema' => \App\Support\SchemaJson::parse($request->input('seo_schema'), 'seo_schema'),
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
