<?php

namespace App\Services;

use App\Admin\Resource;
use App\Models\ContentMeta;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

// Draft, review, scheduled, published and archived states on top of the website's "published" switch.
// The website only reads "published" (and "publish_at" through its read policy), so states that are
// not live always have published = false.
class ContentWorkflow
{
    public const STATES = [
        'draft' => 'Draft',
        'review' => 'In review',
        'scheduled' => 'Scheduled',
        'published' => 'Published',
        'archived' => 'Archived',
    ];

    // Editors and administrators publish; authors can save drafts and send them for review.
    public static function canPublish(?User $user): bool
    {
        return (bool) $user?->canAccess('content');
    }

    public static function status(Model $record, ?ContentMeta $meta): string
    {
        if ($record->published) {
            $at = $record->publish_at ?? null;

            return $at && Carbon::parse($at)->isFuture() ? 'scheduled' : 'published';
        }
        $saved = $meta?->status;

        return in_array($saved, ['draft', 'review', 'archived'], true) ? $saved : 'draft';
    }

    // Applies a requested state to the record's attributes (not saved yet).
    // Keeping the current state is always allowed, so an author can fix a typo in a live post.
    public static function apply(Model $record, string $state, ?string $publishAt, ?User $user, ?string $current = null): void
    {
        if (! isset(self::STATES[$state])) {
            throw ValidationException::withMessages(['workflow_status' => 'Choose a valid status.']);
        }
        if (in_array($state, ['published', 'scheduled'], true) && $state !== $current && ! static::canPublish($user)) {
            throw ValidationException::withMessages(['workflow_status' => 'Only editors and administrators can publish. Save it as a draft or send it for review.']);
        }
        if ($state === 'scheduled') {
            $at = $publishAt ? rescue(fn () => Carbon::parse($publishAt), null, false) : null;
            if (! $at || $at->isPast()) {
                throw ValidationException::withMessages(['publish_at' => 'Choose a future date and time for scheduled publishing.']);
            }
            $record->published = true;
            $record->publish_at = $at;

            return;
        }
        $record->published = $state === 'published';
        $record->publish_at = null;
    }

    public static function meta(string $resource, Model $record): ?ContentMeta
    {
        return ContentMeta::where('resource', $resource)->where('record_key', (string) $record->getKey())->first();
    }

    public static function metaFor(string $resource, $records)
    {
        $items = $records instanceof \Illuminate\Pagination\AbstractPaginator ? $records->getCollection() : collect($records);

        return ContentMeta::where('resource', $resource)->whereIn('record_key', $items->map(fn ($r) => (string) $r->getKey()))->get()->keyBy('record_key');
    }

    // Saves status and attribution after a record is created or updated.
    public static function touch(Resource $resource, Model $record, ?string $state, bool $created, ?string $oldKey = null): void
    {
        $user = auth()->user();
        $key = (string) $record->getKey();
        if ($oldKey !== null && $oldKey !== $key) {
            ContentMeta::where('resource', $resource->slug)->where('record_key', $oldKey)->update(['record_key' => $key]);
            \App\Models\ContentRevision::where('resource', $resource->slug)->where('record_key', $oldKey)->update(['record_key' => $key]);
        }
        $existing = ContentMeta::where('resource', $resource->slug)->where('record_key', $key)->exists();
        $values = ['updated_by' => $user?->id, 'updated_by_name' => $user?->name, 'updated_at' => now()];
        if ($state) {
            $values['status'] = $state;
        }
        if ($existing) {
            ContentMeta::where('resource', $resource->slug)->where('record_key', $key)->update($values);
        } else {
            DB::table('content_meta')->insert($values + [
                'resource' => $resource->slug, 'record_key' => $key, 'status' => $state ?? ($record->published ?? true ? 'published' : 'draft'),
                'created_by' => $created ? $user?->id : null, 'created_by_name' => $created ? $user?->name : null, 'created_at' => now(),
            ]);
        }
    }
}
