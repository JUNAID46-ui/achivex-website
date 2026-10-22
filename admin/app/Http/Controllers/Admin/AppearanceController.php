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
        $bases = Permalinks::all();

        return view('admin.settings.permalinks', [
            'bases' => $bases, 'defaults' => Permalinks::DEFAULTS, 'labels' => Permalinks::LABELS,
            'site' => rtrim((string) config('admin.site_url'), '/'),
            'counts' => [
                'blogs' => \App\Models\Content\BlogPost::count(), 'caseStudies' => \App\Models\Content\CaseStudy::count(),
                'newsroom' => \App\Models\Content\NewsPost::count(), 'whitepapers' => \App\Models\Content\Whitepaper::count(),
            ],
        ]);
    }

    public function savePermalinks(Request $request)
    {
        $input = [];
        foreach (Permalinks::DEFAULTS as $type => $default) {
            $raw = strtolower(trim((string) $request->input("bases.{$type}", $default)));
            $raw = '/'.trim(preg_replace('#/+#', '/', $raw), '/').'/';
            $input[$type] = $raw;
        }
        $this->validateBases($input);
        $old = Permalinks::all();
        $changes = array_filter($input, fn ($new, $type) => $new !== $old[$type], ARRAY_FILTER_USE_BOTH);
        if (! $changes) {
            return back()->with('status', 'Nothing changed.');
        }

        $moved = 0;
        DB::transaction(function () use ($input, $old, $changes, &$moved) {
            foreach ($changes as $type => $new) {
                $from = $old[$type];
                // SEO titles and descriptions follow their pages to the new address.
                $rows = SeoMeta::where('path', 'like', addcslashes($from, '%_').'%')->get();
                foreach ($rows as $row) {
                    $target = $new.substr($row->path, strlen($from));
                    if (SeoMeta::where('path', $target)->exists()) {
                        throw ValidationException::withMessages(["bases.{$type}" => "SEO settings already exist for {$target}. Remove them first under SEO, Pages and Posts."]);
                    }
                    SeoMeta::where('path', $row->path)->update(['path' => $target]);
                    $moved++;
                }
                // Redirects that pointed into the old prefix now point straight at the new one.
                Redirect::where('to_path', 'like', addcslashes($from, '%_').'%')->get()->each(function ($r) use ($from, $new) {
                    $r->update(['to_path' => $new.substr($r->to_path, strlen($from))]);
                });
            }
            SiteSetting::put('permalinks', $input);
        });
        Cache::forget('seo-index');
        SiteLinks::forget();
        $summary = collect($changes)->map(fn ($new, $type) => Permalinks::LABELS[$type].': '.$old[$type].' to '.$new)->implode('; ');
        ActivityLog::record('settings', 'Permalinks', 'Content addresses', $summary, ['module' => 'Permalinks', 'context' => ['moved_seo_rows' => $moved]]);

        return back()->with('status', 'Permalinks saved. Old addresses send visitors to the new ones. Publish the website again so its sitemap.xml copy updates.');
    }

    private function validateBases(array $bases): void
    {
        $errors = [];
        $custom = Page::where('kind', 'custom')->pluck('path')->map(fn ($p) => SiteLinks::normalize($p))->all();
        foreach ($bases as $type => $base) {
            $key = "bases.{$type}";
            if (! preg_match(Permalinks::PATTERN, $base)) {
                $errors[$key] = 'Use lowercase letters, numbers and dashes, for example /blog/ or /resources/articles/.';
            } elseif (in_array($base, Permalinks::RESERVED, true) || collect(Permalinks::RESERVED)->contains(fn ($r) => $r !== '/insights/' && str_starts_with($base, $r))) {
                $errors[$key] = "{$base} is used by a fixed page of the website.";
            } elseif (in_array($base, $custom, true)) {
                $errors[$key] = "A custom page already uses {$base}.";
            }
            foreach ($bases as $other => $otherBase) {
                if ($other !== $type && ! isset($errors[$key]) && (str_starts_with($base, $otherBase) || str_starts_with($otherBase, $base))) {
                    $errors[$key] = 'Two content types cannot share or nest their addresses ('.Permalinks::LABELS[$other].' uses '.$otherBase.').';
                }
            }
        }
        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
    }
}
