<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use App\Support\Csv;
use Illuminate\Http\Request;

// Read only: there are deliberately no routes to edit or delete activity entries.
class ActivityController extends Controller
{
    private function filtered(Request $request)
    {
        return ActivityLog::with('user')
            ->when($request->input('user'), fn ($q, $u) => $q->where('user_id', $u))
            ->when($request->input('action'), fn ($q, $a) => $q->where('action', $a))
            ->when($request->input('module'), fn ($q, $m) => $q->where('module', $m))
            ->when(in_array($request->input('status'), ['success', 'failure'], true), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->boolean('sensitive'), fn ($q) => $q->whereIn('action', ActivityLog::SENSITIVE))
            ->when(trim((string) $request->input('q')), function ($q, $term) {
                $q->where(fn ($w) => $w->where('subject_label', 'ilike', "%{$term}%")->orWhere('description', 'ilike', "%{$term}%")->orWhere('subject_id', 'ilike', "%{$term}%"));
            })
            ->when($request->input('from'), fn ($q, $d) => $q->where('created_at', '>=', $d))
            ->when($request->input('to'), fn ($q, $d) => $q->where('created_at', '<', \Carbon\Carbon::parse($d)->addDay()));
    }

    public function index(Request $request)
    {
        $request->validate(['from' => 'nullable|date', 'to' => 'nullable|date', 'q' => 'nullable|max:100']);

        return view('admin.activity.index', [
            'logs' => $this->filtered($request)->latest('created_at')->latest('id')->paginate(40)->withQueryString(),
            'users' => User::orderBy('name')->pluck('name', 'id'),
            'actions' => ActivityLog::select('action')->distinct()->orderBy('action')->pluck('action'),
            'modules' => ActivityLog::whereNotNull('module')->select('module')->distinct()->orderBy('module')->pluck('module'),
        ]);
    }

    public function export(Request $request)
    {
        $request->validate(['from' => 'nullable|date', 'to' => 'nullable|date', 'q' => 'nullable|max:100']);
        $rows = $this->filtered($request)->latest('created_at')->limit(10000)->get();
        ActivityLog::record('exported', 'Activity Log', "{$rows->count()} entries", null, ['module' => 'Activity Log']);

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Time (UTC)', 'User', 'Role', 'Action', 'Status', 'Module', 'Item', 'Record', 'Details', 'IP']);
            foreach ($rows as $l) {
                fputcsv($out, array_map([Csv::class, 'cell'], [$l->created_at?->format('Y-m-d H:i:s'), $l->user?->name ?? $l->user_name ?? 'System', $l->user_role, $l->action, $l->status, $l->module, $l->subject_label, $l->subject_id, $l->description, $l->ip]));
            }
            fclose($out);
        }, 'activity-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }
}
