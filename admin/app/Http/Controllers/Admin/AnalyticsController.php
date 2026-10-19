<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Content\PageView;
use App\Services\Analytics;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $days = in_array((int) $request->input('days'), [7, 30, 90, 365]) ? (int) $request->input('days') : 30;
        $a = new Analytics($days);

        return view('admin.analytics.index', [
            'days' => $days,
            'summary' => $a->summary(),
            'daily' => $a->daily(),
            'topPages' => $a->topPages(20),
            'referrers' => $a->referrers(15),
            'devices' => $a->devices(),
        ]);
    }

    public function export(Request $request)
    {
        $days = (int) $request->input('days', 30);

        return response()->streamDownload(function () use ($days) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Date', 'Path', 'Referrer', 'Device']);
            PageView::where('created_at', '>=', now()->subDays($days))->orderBy('created_at')->chunk(1000, function ($rows) use ($out) {
                foreach ($rows as $r) {
                    fputcsv($out, [$r->created_at, $r->path, $r->referrer, $r->device]);
                }
            });
            fclose($out);
        }, 'page-views-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }
}
