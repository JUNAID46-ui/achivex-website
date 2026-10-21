<?php

namespace App\Services;

use App\Admin\Resource;
use App\Models\Content\Page;
use App\Models\Content\Redirect;
use App\Models\Content\SeoMeta;
use Illuminate\Database\Eloquent\Model;

// Completeness warnings for one content record. Each warning: level (warning|info), label, fix.
class ContentHealth
{
    public static function check(Resource $resource, Model $record, ?SeoMeta $meta = null, bool $withLinks = true): array
    {
        $w = [];
        $image = collect($resource->allFields())->first(fn ($f) => $f['type'] === 'image' && ($f['side'] ?? false) && ($f['accept'] ?? null) !== 'file');
        if ($image && blank($record->{$image['name']})) {
            $w[] = ['level' => 'warning', 'label' => 'No '.strtolower($image['label']), 'fix' => 'Add an image so the item does not show an empty placeholder on the website and in shares.'];
        }

        if ($resource->seo) {
            $desc = $meta?->description ?: ($record->excerpt ?? $record->summary ?? $record->description ?? '');
            if (blank($desc)) {
                $w[] = ['level' => 'warning', 'label' => 'No meta description', 'fix' => 'Write a meta description of 120 to 160 characters in the SEO box.'];
            } elseif (! $meta?->description) {
                $w[] = ['level' => 'info', 'label' => 'Meta description uses the summary', 'fix' => 'Optional: write a dedicated meta description in the SEO box.'];
            }
            if (! $meta?->title) {
                $w[] = ['level' => 'info', 'label' => 'SEO title uses the item title', 'fix' => 'Optional: set an SEO title in the SEO box if the title is long or lacks the keyword.'];
            }
            if ($meta?->noindex && ($record->published ?? false)) {
                $w[] = ['level' => 'warning', 'label' => 'Published but hidden from search engines', 'fix' => 'Turn off "Hide from search engines" in the SEO box if this should appear in Google.'];
            }
        }

        if ($path = $record->exists ? $resource->sitePath($record) : null) {
            $norm = SiteLinks::normalize($path);
            if (Redirect::where('active', true)->get(['from_path'])->contains(fn ($r) => SiteLinks::normalize($r->from_path) === $norm)) {
                $w[] = ['level' => 'warning', 'label' => 'An active redirect uses this address', 'fix' => "Visitors to {$path} may be sent elsewhere. Remove or change the redirect under SEO, Redirects."];
            }
            if (Page::where('kind', 'custom')->get(['path'])->contains(fn ($p) => SiteLinks::normalize($p->path) === $norm)) {
                $w[] = ['level' => 'warning', 'label' => 'A custom page has the same address', 'fix' => 'Change the URL slug so the two do not clash.'];
            }
        }

        if ($withLinks) {
            foreach (SiteLinks::check($record->attributesToArray()) as $link => $state) {
                if ($state === 'broken') {
                    $w[] = ['level' => 'warning', 'label' => "Broken internal link: {$link}", 'fix' => 'No live page has this address. Fix the link or add a redirect.'];
                } elseif ($state === 'redirect') {
                    $w[] = ['level' => 'info', 'label' => "Link goes through a redirect: {$link}", 'fix' => 'Link straight to the final address.'];
                }
            }
        }

        return $w;
    }
}
