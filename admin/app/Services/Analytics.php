<?php

namespace App\Services;

use App\Models\Content\PageView;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

// Traffic figures from the page_views table, which the website fills on every page load.
class Analytics
{
    public function __construct(public int $days = 30)
    {
    }

    private function since(): Carbon
    {
        return now()->subDays($this->days - 1)->startOfDay();
    }

    public function summary(): array
    {
        $current = PageView::where('created_at', '>=', $this->since());
        $previousStart = $this->since()->copy()->subDays($this->days);
        $previous = PageView::whereBetween('created_at', [$previousStart, $this->since()]);

        $views = (clone $current)->count();
        $visitors = (clone $current)->distinct('visitor')->count('visitor');
        $prevViews = (clone $previous)->count();
        $prevVisitors = (clone $previous)->distinct('visitor')->count('visitor');

        return [
            'views' => $views,
            'visitors' => $visitors,
            'views_change' => $this->change($views, $prevViews),
            'visitors_change' => $this->change($visitors, $prevVisitors),
            'pages_per_visitor' => $visitors ? round($views / $visitors, 1) : 0,
        ];
    }

    private function change(int $now, int $before): ?int
    {
        return $before ? (int) round(($now - $before) * 100 / $before) : null;
    }

    public function daily(): array
    {
        $rows = PageView::where('created_at', '>=', $this->since())
            ->selectRaw("to_char(created_at, 'YYYY-MM-DD') as day, count(*) as views, count(distinct visitor) as visitors")
            ->groupBy('day')->pluck('views', 'day');
        $visitors = PageView::where('created_at', '>=', $this->since())
            ->selectRaw("to_char(created_at, 'YYYY-MM-DD') as day, count(distinct visitor) as visitors")
            ->groupBy('day')->pluck('visitors', 'day');

        $labels = $views = $uniques = [];
        for ($d = $this->since()->copy(); $d <= now(); $d->addDay()) {
            $key = $d->format('Y-m-d');
            $labels[] = $d->format('M j');
            $views[] = (int) ($rows[$key] ?? 0);
            $uniques[] = (int) ($visitors[$key] ?? 0);
        }

        return compact('labels', 'views', 'uniques');
    }

    public function topPages(int $limit = 10): array
    {
        return PageView::where('created_at', '>=', $this->since())
            ->select('path', DB::raw('count(*) as views'), DB::raw('count(distinct visitor) as visitors'))
            ->groupBy('path')->orderByDesc('views')->limit($limit)->get()->toArray();
    }

    public function referrers(int $limit = 10): array
    {
        $rows = PageView::where('created_at', '>=', $this->since())->whereNotNull('referrer')
            ->select('referrer')->get();
        $hosts = [];
        foreach ($rows as $r) {
            $host = parse_url($r->referrer, PHP_URL_HOST) ?: $r->referrer;
            $host = preg_replace('/^www\./', '', $host);
            $hosts[$host] = ($hosts[$host] ?? 0) + 1;
        }
        arsort($hosts);

        return array_slice($hosts, 0, $limit, true);
    }

    public function devices(): array
    {
        $rows = PageView::where('created_at', '>=', $this->since())
            ->select('device', DB::raw('count(*) as total'))->groupBy('device')->pluck('total', 'device');

        return ['Desktop' => (int) ($rows['desktop'] ?? 0), 'Mobile' => (int) ($rows['mobile'] ?? 0), 'Tablet' => (int) ($rows['tablet'] ?? 0)];
    }
}
