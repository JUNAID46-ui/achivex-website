<?php

namespace App\Services;

use App\Models\ContentRevision;
use Illuminate\Database\Eloquent\Model;

// Keeps the last copies of each content record so earlier versions and deleted items can be restored.
class Revisions
{
    public const KEEP = 50;

    public static function snapshot(string $resource, Model $record, string $action): ContentRevision
    {
        $rev = ContentRevision::create([
            'resource' => $resource,
            'record_key' => (string) $record->getKey(),
            'action' => $action,
            'data' => $record->attributesToArray(),
            'user_id' => auth()->id(),
            'user_name' => auth()->user()?->name,
        ]);

        // Older copies beyond the limit are removed; the record itself is never touched.
        $old = ContentRevision::where('resource', $resource)->where('record_key', (string) $record->getKey())
            ->orderByDesc('id')->skip(self::KEEP)->take(1000)->pluck('id');
        if ($old->isNotEmpty()) {
            ContentRevision::whereIn('id', $old)->delete();
        }

        return $rev;
    }

    public static function for(string $resource, string $key)
    {
        return ContentRevision::where('resource', $resource)->where('record_key', $key)->latest('id');
    }

    // Deleted records of a resource whose key is not in use again.
    public static function deleted(string $resource, string $modelClass)
    {
        $revs = ContentRevision::where('resource', $resource)->where('action', 'delete')->latest('id')->limit(50)->get();
        $keyName = (new $modelClass)->getKeyName();
        $existing = $modelClass::whereIn($keyName, $revs->pluck('record_key'))->pluck($keyName)->map(fn ($k) => (string) $k)->all();

        return $revs->reject(fn ($r) => in_array($r->record_key, $existing, true))->unique('record_key')->values();
    }

    // Field by field differences between a revision and the current record, for the compare view.
    public static function diff(array $old, array $current): array
    {
        $changes = [];
        foreach (array_unique(array_merge(array_keys($old), array_keys($current))) as $k) {
            $a = $old[$k] ?? null;
            $b = $current[$k] ?? null;
            if (json_encode($a) !== json_encode($b)) {
                $changes[$k] = ['then' => $a, 'now' => $b];
            }
        }

        return $changes;
    }
}
