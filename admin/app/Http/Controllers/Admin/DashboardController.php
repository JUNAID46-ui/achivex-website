<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Content\BlogPost;
use App\Models\Content\CaseStudy;
use App\Models\Content\ContactSubmission;
use App\Models\Content\JobOpening;
use App\Models\Content\NewsPost;
use App\Models\Content\NotFoundLog;
use App\Models\Content\SiteSetting;
use App\Models\Content\TeamMember;
use App\Models\Content\Whitepaper;
use App\Models\Content\WhitepaperRequest;
use App\Models\Media;
use App\Services\Analytics;
use App\Services\SeoIndex;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function index()
    {
        $analytics = new Analytics(30);
        $seoRows = Cache::remember('seo-index', 600, fn () => SeoIndex::all());
        $seoAvg = $seoRows ? (int) round(collect($seoRows)->avg('score')) : 0;

        // All "At a glance" counts in a single query instead of six.
        $c = (array) DB::selectOne("select
            (select count(*) from public.blog_posts where published) as posts,
            (select count(*) from public.case_studies where published) as cases,
            (select count(*) from public.news_posts where published) as news,
            (select count(*) from public.whitepapers where published) as papers,
            (select count(*) from public.job_openings where published) as jobs,
            (select count(*) from media) as media");
        $glance = [
            ['label' => 'Blog posts', 'count' => $c['posts'], 'route' => route('admin.resources.index', 'blog-posts')],
            ['label' => 'Case studies', 'count' => $c['cases'], 'route' => route('admin.resources.index', 'case-studies')],
            ['label' => 'News posts', 'count' => $c['news'], 'route' => route('admin.resources.index', 'news')],
            ['label' => 'Whitepapers', 'count' => $c['papers'], 'route' => route('admin.resources.index', 'whitepapers')],
            ['label' => 'Open jobs', 'count' => $c['jobs'], 'route' => route('admin.resources.index', 'jobs')],
            ['label' => 'Media files', 'count' => $c['media'], 'route' => route('admin.media.index')],
        ];

        // Kept for 30 seconds only; the Live card below refreshes itself every 15 seconds.
        $traffic = Cache::remember('dashboard-traffic', 30, fn () => [
            'summary' => $analytics->summary(),
            'daily' => $analytics->daily(),
            'topPages' => $analytics->topPages(6),
            'devices' => $analytics->devices(),
        ]);

        return view('admin.dashboard', $traffic + [
            'live' => $this->liveStats(),
            'glance' => $glance,
            'unread' => ContactSubmission::whereNull('read_at')->count(),
            'requests30' => WhitepaperRequest::where('created_at', '>=', now()->subDays(30))->count(),
            'messages' => ContactSubmission::orderByDesc('created_at')->limit(5)->get(),
            'activity' => ActivityLog::with('user')->latest('created_at')->limit(8)->get(),
            'seoAvg' => $seoAvg,
            'seoCounts' => collect($seoRows)->groupBy(fn ($r) => \App\Services\SeoAnalyzer::grade($r['score']))->map->count(),
            'health' => $this->health($seoRows),
        ]);
    }

    // Real time figures for the dashboard, read straight from the page_views table (no cache).
    public function live()
    {
        return response()->json($this->liveStats())->header('Cache-Control', 'no-store');
    }

    private function liveStats(): array
    {
        $since = now()->subMinutes(5);
        $views = \App\Models\Content\PageView::query();
        $recent = (clone $views)->where('created_at', '>=', $since);
        $today = (clone $views)->where('created_at', '>=', now()->startOfDay());
        $month = (clone $views)->where('created_at', '>=', now()->subDays(30));

        return [
            'online' => (int) (clone $recent)->distinct('visitor')->count('visitor'),
            'viewsNow' => (int) (clone $recent)->count(),
            'pages' => (clone $recent)->selectRaw('path, count(*) as views')->groupBy('path')->orderByDesc('views')->limit(5)->get()
                ->map(fn ($r) => ['path' => $r->path, 'views' => (int) $r->views])->all(),
            'todayViews' => (int) (clone $today)->count(),
            'todayVisitors' => (int) (clone $today)->distinct('visitor')->count('visitor'),
            'monthViews' => (int) (clone $month)->count(),
            'monthVisitors' => (int) (clone $month)->distinct('visitor')->count('visitor'),
            'latest' => (clone $views)->latest('created_at')->limit(6)->get(['path', 'device', 'created_at'])
                ->map(fn ($r) => ['path' => $r->path, 'device' => $r->device ?: 'unknown', 'ago' => $r->created_at->diffForHumans(null, true).' ago'])->all(),
            'at' => now()->format('H:i:s'),
        ];
    }

    // WordPress style "Site Health" checks with links to fix each item.
    private function health(array $seoRows): array
    {
        $seo = SiteSetting::group('seo');
        $general = SiteSetting::group('general');
        $checks = [];
        $add = function ($ok, $label, $url) use (&$checks) {
            $checks[] = compact('ok', 'label', 'url');
        };

        $missingDesc = collect($seoRows)->filter(fn ($r) => mb_strlen($r['description']) < 50)->count();
        $noKeyword = collect($seoRows)->filter(fn ($r) => ! $r['keyword'])->count();
        $notFound = NotFoundLog::where('created_at', '>=', now()->subDays(7))->count();

        $add(! empty($seo['googleVerification']), ! empty($seo['googleVerification']) ? 'Google Search Console verification code added' : 'Add your Google Search Console verification code', route('admin.seo.webmaster'));
        $add(! empty($seo['ogImage']), ! empty($seo['ogImage']) ? 'Default social share image set' : 'Set a default social share image', route('admin.seo.appearance'));
        $add($missingDesc === 0, $missingDesc ? "{$missingDesc} pages have a very short meta description" : 'Every page has a meta description', route('admin.seo.pages'));
        $add($noKeyword === 0, $noKeyword ? "{$noKeyword} pages have no focus keyword" : 'Every page has a focus keyword', route('admin.seo.pages'));
        $add($notFound === 0, $notFound ? "{$notFound} visits hit missing pages in the last 7 days" : 'No missing pages visited in the last 7 days', route('admin.seo.notfound'));
        $team = TeamMember::where('published', true)->exists();
        $add($team, $team ? 'Leadership team profiles published' : 'Add leadership team profiles', route('admin.resources.index', 'team'));
        $contact = ! empty($general['phone']) && ! empty($general['email']);
        $add($contact, $contact ? 'Contact phone and email set' : 'Add a contact phone and email', route('admin.settings'));
        $noFile = Whitepaper::whereNull('file_url')->where('published', true)->count();
        $add($noFile === 0, $noFile ? "{$noFile} published whitepapers have no download file" : 'Every published whitepaper has a download file', route('admin.resources.index', 'whitepapers'));

        return $checks;
    }

    // Creates a draft blog post from the dashboard "Quick Draft" box.
    public function quickDraft(Request $request)
    {
        $data = $request->validate(['title' => 'required|max:200', 'content' => 'nullable|max:5000']);
        $slug = Str::slug($data['title']);
        $base = $slug;
        $i = 2;
        while (BlogPost::find($slug)) {
            $slug = $base.'-'.$i++;
        }
        $post = BlogPost::create([
            'slug' => $slug, 'title' => $data['title'], 'category' => 'Strategy', 'published_on' => now()->toDateString(),
            'author' => auth()->user()->name, 'published' => false, 'featured' => false,
            'excerpt' => Str::limit($data['content'] ?? $data['title'], 180), 'intro_keyword' => $data['title'],
            'intro_text' => '', 'sections' => $data['content'] ? [['id' => 'draft', 'heading' => 'Draft', 'paragraphs' => [$data['content']]]] : [],
        ]);
        ActivityLog::record('created', 'Blog Post', $post->title, 'Quick draft');

        return redirect()->route('admin.resources.edit', ['blog-posts', $post->slug])->with('status', 'Draft saved. Finish it here.');
    }
}
