<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Content\Page;
use App\Models\Content\Redirect;
use App\Models\Content\SeoMeta;
use App\Models\Content\SiteSetting;
use App\Services\SiteLinks;
use App\Support\Permalinks;
use App\Support\Theme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

// Website colours, fonts and permalinks (administrators only, area:settings).
// The website reads these on every page load, so changes show without a new deploy.
class AppearanceController extends Controller
{
    public function theme()
    {
        $current = Theme::current();

        return view('admin.settings.theme', [
            'groups' => Theme::COLORS, 'colors' => $current['colors'], 'fonts' => $current['fonts'],
            'fontList' => Theme::FONTS, 'defaults' => Theme::defaults(), 'defaultFonts' => Theme::DEFAULT_FONTS,
            'warnings' => Theme::warnings($current['colors']), 'pairs' => Theme::PAIRS,
        ]);
    }

    public function saveTheme(Request $request)
    {
        $keys = array_keys(Theme::defaults());
        $rules = collect($keys)->mapWithKeys(fn ($k) => ["colors.{$k}" => ['required', 'regex:/^#[0-9a-fA-F]{6}$/']])->all();
        $rules['fonts.body'] = 'required|in:'.implode(',', Theme::FONTS);
        $rules['fonts.heading'] = 'required|in:'.implode(',', Theme::FONTS);
        $data = $request->validate($rules, ['regex' => 'Use a colour like #1A2B3C.']);

        $colors = collect($data['colors'])->only($keys)->map(fn ($v) => strtoupper($v))->all();
        // Keep only what differs from the defaults, so a later change of default still applies.
        $changed = array_diff_assoc($colors, Theme::defaults());
        SiteSetting::put('theme', ['colors' => $changed, 'fonts' => array_diff_assoc($data['fonts'], Theme::DEFAULT_FONTS)]);
        ActivityLog::record('settings', 'Appearance', 'Colors and fonts', count($changed).' colours changed, fonts '.$data['fonts']['heading'].' / '.$data['fonts']['body'], ['module' => 'Appearance']);
        $warnings = Theme::warnings($colors);

        return back()->with('status', 'Colours and fonts saved. The website shows them on its next page load.'.($warnings ? ' Check the contrast warnings.' : ''));
    }

    public function resetTheme()
    {
        SiteSetting::put('theme', ['colors' => [], 'fonts' => []]);
        ActivityLog::record('settings', 'Appearance', 'Colors and fonts', 'Reset to the brand defaults', ['module' => 'Appearance']);

        return back()->with('status', 'Colours and fonts reset to the brand defaults.');
    }

    public function permalinks()
    {
        return view('admin.settings.permalinks', [
            'bases' => Permalinks::all(), 'defaults' => Permalinks::DEFAULTS, 'labels' => Permalinks::LABELS,
            'serviceMode' => Permalinks::serviceMode(), 'serviceModes' => Permalinks::SERVICE_MODES,
            'site' => rtrim((string) config('admin.site_url'), '/'),
            'counts' => collect(Permalinks::MODELS)->map(fn ($model) => $model::count())->all() + ['services' => count(Permalinks::serviceDefaults())],
        ]);
    }

    public function savePermalinks(Request $request)
    {
        $input = [];
        foreach (Permalinks::DEFAULTS as $type => $default) {
            $raw = strtolower(trim((string) $request->input("bases.{$type}", $default)));
            $raw = trim(preg_replace('#/+#', '/', $raw), '/');
            $input[$type] = $raw === '' ? '/' : '/'.$raw.'/';
        }
        $mode = (string) $request->input('services', Permalinks::serviceMode());
        if (! isset(Permalinks::SERVICE_MODES[$mode])) {
            throw ValidationException::withMessages(['services' => 'Choose one of the listed service address formats.']);
        }
        $this->validateBases($input, $mode);

        $old = Permalinks::all();
        $oldMode = Permalinks::serviceMode();
        $changes = array_filter($input, fn ($new, $type) => $new !== $old[$type], ARRAY_FILTER_USE_BOTH);
        if (! $changes && $mode === $oldMode) {
            return back()->with('status', 'Nothing changed.');
        }

        // Every address that changes: list pages, each item, and each service.
        $moves = [];
        foreach ($changes as $type => $new) {
            $moves[Permalinks::listPath($type, $old[$type])] = Permalinks::listPath($type, $new);
            foreach (Permalinks::MODELS[$type]::pluck('slug') as $slug) {
                $moves[$old[$type].$slug.'/'] = $new.$slug.'/';
            }
        }
        if ($mode !== $oldMode) {
            foreach (Permalinks::serviceDefaults() as $slug => $default) {
                $moves[Permalinks::servicePath($slug, $default, $oldMode)] = Permalinks::servicePath($slug, $default, $mode);
            }
        }
        $moves = array_filter($moves, fn ($to, $from) => $to !== $from, ARRAY_FILTER_USE_BOTH);

        $moved = 0;
        DB::transaction(function () use ($input, $mode, $moves, &$moved) {
            // SEO titles and descriptions follow their pages to the new address.
            foreach (SeoMeta::whereIn('path', array_keys($moves))->get() as $row) {
                $target = $moves[$row->path];
                if (! isset($moves[$target]) && SeoMeta::where('path', $target)->exists()) {
                    throw ValidationException::withMessages(['bases' => "SEO settings already exist for {$target}. Remove them first under SEO, Pages and Posts."]);
                }
                SeoMeta::where('path', $row->path)->update(['path' => $target]);
                $moved++;
            }
            // Redirects that pointed at an old address now point straight at the new one.
            foreach (Redirect::whereIn('to_path', array_keys($moves))->get() as $r) {
                $r->update(['to_path' => $moves[$r->to_path]]);
            }
            SiteSetting::put('permalinks', $input + ['services' => $mode]);
        });
        Cache::forget('seo-index');
        SiteLinks::forget();
        $summary = collect($changes)->map(fn ($new, $type) => Permalinks::LABELS[$type].': '.$old[$type].' to '.$new)
            ->when($mode !== $oldMode, fn ($c) => $c->push('Services: '.Permalinks::SERVICE_MODES[$oldMode].' to '.Permalinks::SERVICE_MODES[$mode]))->implode('; ');
        ActivityLog::record('settings', 'Permalinks', 'Content addresses', $summary, ['module' => 'Permalinks', 'context' => ['moved_seo_rows' => $moved]]);

        return back()->with('status', 'Permalinks saved. Old addresses send visitors to the new ones. Publish the website again so its sitemap.xml copy updates.');
    }

    private function validateBases(array $bases, string $serviceMode): void
    {
        $errors = [];
        $custom = Page::where('kind', 'custom')->pluck('path')->map(fn ($p) => SiteLinks::normalize($p))->all();
        foreach ($bases as $type => $base) {
            $key = "bases.{$type}";
            if (! preg_match(Permalinks::PATTERN, $base)) {
                $errors[$key] = 'Use lowercase letters, numbers and dashes, for example /blog/, or / for the root of the site.';
            } elseif ($base === '/') {
                continue;
            } elseif (in_array($base, Permalinks::RESERVED, true) || collect(Permalinks::RESERVED)->contains(fn ($r) => $r !== '/insights/' && str_starts_with($base, $r))) {
                $errors[$key] = "{$base} is used by a fixed page of the website.";
            } elseif (in_array($base, $custom, true)) {
                $errors[$key] = "A custom page already uses {$base}.";
            }
            foreach ($bases as $other => $otherBase) {
                if ($other !== $type && $otherBase !== '/' && ! isset($errors[$key]) && (str_starts_with($base, $otherBase) || str_starts_with($otherBase, $base))) {
                    $errors[$key] = 'Two content types cannot share or nest their addresses ('.Permalinks::LABELS[$other].' uses '.$otherBase.').';
                }
            }
        }
        // Items at the root of the site share one set of addresses, so their slugs must not clash.
        if (! $errors) {
            $seen = [];
            foreach (Permalinks::rootClaims($bases, $serviceMode) as [$slug, $owner]) {
                if (isset($seen[$slug]) && $seen[$slug] !== $owner) {
                    $errors['bases'] = "/{$slug}/ would be the address of both {$seen[$slug]} and {$owner}. Change one of the slugs first, or keep a prefix.";
                    break;
                }
                $seen[$slug] ??= $owner;
            }
        }
        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
    }
}
