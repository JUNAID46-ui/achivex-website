<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Content\NotFoundLog;
use App\Models\Content\Redirect;
use App\Models\Content\SeoMeta;
use App\Models\Content\SiteSetting;
use App\Services\SeoAnalyzer;
use App\Services\SeoIndex;
use App\Services\Sitemap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

// All in one SEO area: overview, per page settings, sitemap, robots, redirects, 404s, schema and verification.
class SeoController extends Controller
{
    private function index(): array
    {
        return Cache::remember('seo-index', 600, fn () => SeoIndex::all());
    }

    private function forget(): void
    {
        Cache::forget('seo-index');
    }

    public function overview()
    {
        $rows = collect($this->index());
        $issues = [];
        foreach ($rows as $r) {
            foreach ($r['checks'] as $c) {
                if ($c['status'] !== 'good') {
                    $issues[$c['group']] = ($issues[$c['group']] ?? 0) + 1;
                }
            }
        }
        arsort($issues);
        $titles = $rows->groupBy('seo_title')->filter(fn ($g) => $g->count() > 1);

        return view('admin.seo.overview', [
            'avg' => (int) round($rows->avg('score')),
            'counts' => $rows->groupBy(fn ($r) => SeoAnalyzer::grade($r['score']))->map->count(),
            'total' => $rows->count(),
            'issues' => array_slice($issues, 0, 8, true),
            'duplicateTitles' => $titles,
            'worst' => $rows->sortBy('score')->take(8)->values(),
            'noindex' => $rows->where('noindex', true)->count(),
            'notFound7' => NotFoundLog::where('created_at', '>=', now()->subDays(7))->count(),
            'redirects' => Redirect::where('active', true)->count(),
            'settings' => SiteSetting::group('seo'),
            'byType' => $rows->groupBy('type')->map(fn ($g) => ['count' => $g->count(), 'avg' => (int) round($g->avg('score'))]),
        ]);
    }

    public function pages(Request $request)
    {
        $rows = collect($this->index());
        if ($t = $request->input('type')) {
            $rows = $rows->where('type', $t);
        }
        if ($q = mb_strtolower((string) $request->input('q'))) {
            $rows = $rows->filter(fn ($r) => str_contains(mb_strtolower($r['path'].' '.$r['seo_title'].' '.$r['keyword']), $q));
        }
        if ($g = $request->input('grade')) {
            $rows = $rows->filter(fn ($r) => SeoAnalyzer::grade($r['score']) === $g);
        }
        $sort = $request->input('sort', 'score');
        $rows = $sort === 'path' ? $rows->sortBy('path') : $rows->sortBy('score');

        return view('admin.seo.pages', [
            'rows' => $rows->values(),
            'types' => collect($this->index())->pluck('type')->unique()->values(),
        ]);
    }

    public function editPage(Request $request)
    {
        $path = $request->input('path');
        $row = collect($this->index())->firstWhere('path', $path);
        abort_unless($row, 404);
        if ($row['resource']) {
            return redirect()->route('admin.resources.edit', [$row['resource'], $row['id']]);
        }
        $page = collect(\App\Services\SiteRoutes::pages())->firstWhere('path', $path);

        return view('admin.seo.page-edit', [
            'row' => $row, 'page' => $page, 'meta' => SeoMeta::find($path),
            'template' => SiteSetting::group('seo')['titleTemplate'] ?? '%title% | AchiveX LLC',
        ]);
    }

    public function savePage(Request $request)
    {
        $data = $request->validate([
            'path' => 'required|max:500',
            'title' => 'nullable|max:200',
            'description' => 'nullable|max:400',
            'focus_keyword' => 'nullable|max:120',
            'og_image' => 'nullable|max:500',
            'canonical' => 'nullable|url|max:500',
        ]);
        $data += ['title' => null, 'description' => null, 'focus_keyword' => null, 'og_image' => null, 'canonical' => null];
        SeoMeta::updateOrCreate(['path' => $data['path']], [
            'title' => $data['title'] ?: null, 'description' => $data['description'] ?: null,
            'focus_keyword' => $data['focus_keyword'] ?: null, 'og_image' => $data['og_image'] ?: null,
            'canonical' => $data['canonical'] ?: null, 'noindex' => $request->boolean('noindex'), 'updated_at' => now(),
        ]);
        $this->forget();
        ActivityLog::record('updated', 'SEO', $data['path']);

        return back()->with('status', 'SEO settings saved for '.$data['path']);
    }

    public function appearance()
    {
        return view('admin.seo.appearance', ['seo' => SiteSetting::group('seo')]);
    }

    public function saveAppearance(Request $request)
    {
        $data = $request->validate([
            'titleTemplate' => 'required|max:120',
            'homeTitle' => 'required|max:120',
            'defaultDescription' => 'required|max:300',
            'ogImage' => 'nullable|max:500',
            'twitterHandle' => 'nullable|max:60',
        ]);
        if (! str_contains($data['titleTemplate'], '%title%')) {
            return back()->withErrors(['titleTemplate' => 'The title template must contain %title%.'])->withInput();
        }
        SiteSetting::put('seo', $data);
        $this->forget();
        ActivityLog::record('updated', 'SEO', 'Search appearance');

        return back()->with('status', 'Search appearance saved.');
    }

    public function sitemap()
    {
        $urls = Sitemap::urls();

        return view('admin.seo.sitemap', ['urls' => $urls, 'publicUrl' => route('sitemap')]);
    }

    public function downloadSitemap()
    {
        return response(Sitemap::xml(), 200, [
            'Content-Type' => 'application/xml',
            'Content-Disposition' => 'attachment; filename="sitemap.xml"',
        ]);
    }

    public function robots()
    {
        return view('admin.seo.robots', ['content' => SiteSetting::group('robots')['content'] ?? '']);
    }

    public function saveRobots(Request $request)
    {
        $data = $request->validate(['content' => 'required|max:5000']);
        SiteSetting::put('robots', ['content' => str_replace("\r\n", "\n", $data['content'])]);
        ActivityLog::record('updated', 'SEO', 'robots.txt');

        return back()->with('status', 'robots.txt saved.');
    }

    public function notFound()
    {
        $rows = NotFoundLog::select('path', DB::raw('count(*) as hits'), DB::raw('max(created_at) as last_seen'), DB::raw('max(referrer) as referrer'))
            ->groupBy('path')->orderByDesc('hits')->paginate(30);
        $redirected = Redirect::pluck('to_path', 'from_path');

        return view('admin.seo.notfound', compact('rows', 'redirected'));
    }

    public function redirectNotFound(Request $request)
    {
        $data = $request->validate(['from_path' => 'required|max:300', 'to_path' => ['required', 'max:500', 'regex:#^(/(?![/\\\\])|https?://)#i']]);
        $from = '/'.trim($data['from_path'], '/').'/';
        Redirect::updateOrCreate(['from_path' => $from], ['to_path' => $data['to_path'], 'status_code' => 301, 'active' => true]);
        ActivityLog::record('created', 'Redirect', $from.' to '.$data['to_path']);

        return back()->with('status', "Redirect created from {$from}.");
    }

    public function clearNotFound()
    {
        NotFoundLog::query()->delete();
        ActivityLog::record('deleted', '404 log', 'All entries');

        return back()->with('status', '404 log cleared.');
    }

    public function schema()
    {
        return view('admin.seo.schema', ['schema' => SiteSetting::group('schema')]);
    }

    public function saveSchema(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|in:Organization,LocalBusiness,ProfessionalService,Corporation',
            'name' => 'required|max:120',
            'url' => 'nullable|url|max:300',
            'logo' => 'nullable|max:500',
            'phone' => 'nullable|max:50',
            'email' => 'nullable|email|max:200|not_regex:/[\r\n]/',
            'street' => 'nullable|max:200',
            'city' => 'nullable|max:120',
            'region' => 'nullable|max:60',
            'postalCode' => 'nullable|max:20',
            'country' => 'nullable|max:60',
            'openingHours' => 'nullable|max:200',
            'priceRange' => 'nullable|max:20',
        ]);
        SiteSetting::put('schema', $data);
        ActivityLog::record('updated', 'SEO', 'Local SEO and schema');

        return back()->with('status', 'Local SEO settings saved.');
    }

    public function webmaster()
    {
        return view('admin.seo.webmaster', ['seo' => SiteSetting::group('seo')]);
    }

    public function saveWebmaster(Request $request)
    {
        $data = $request->validate([
            'googleVerification' => 'nullable|max:200',
            'bingVerification' => 'nullable|max:200',
        ]);
        // Accept either the code or the full meta tag that Google and Bing show.
        foreach ($data as $k => $v) {
            if ($v && preg_match('/content=["\']([^"\']+)["\']/', $v, $m)) {
                $data[$k] = $m[1];
            }
        }
        SiteSetting::put('seo', $data);
        $this->forget();
        ActivityLog::record('updated', 'SEO', 'Webmaster verification');

        return back()->with('status', 'Verification codes saved.');
    }
}
