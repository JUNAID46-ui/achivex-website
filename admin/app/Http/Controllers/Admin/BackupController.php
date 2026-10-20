<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Services\Backup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class BackupController extends Controller
{
    public function index()
    {
        $counts = collect(Backup::GROUPS)->map(fn ($tables) => collect($tables)->sum(fn ($t) => DB::table($t)->count()));

        return view('admin.backup.index', [
            'counts' => $counts,
            'history' => ActivityLog::where('subject_type', 'Backup')->latest()->limit(8)->get(),
            'maxUploadMb' => 4,
        ]);
    }

    public function download(Request $request)
    {
        $groups = array_keys(array_filter(['inbox' => $request->boolean('inbox'), 'analytics' => $request->boolean('analytics')]));
        $tables = Backup::tables($groups);
        ActivityLog::record('exported', 'Backup', 'Download', 'Content'.($groups ? ', '.implode(', ', $groups) : ''));

        return response()->streamDownload(function () use ($tables) {
            $out = fopen('php://output', 'w');
            Backup::write($out, $tables);
            fclose($out);
        }, 'achivex-backup-'.now()->format('Y-m-d-His').'.json', ['Content-Type' => 'application/json']);
    }

    public function restore(Request $request)
    {
        // Vercel accepts uploads up to 4.5 MB, so the limit is kept just under that.
        $request->validate([
            'file' => 'required|file|max:4096|extensions:json',
            'mode' => 'required|in:merge,replace',
            'confirm' => 'accepted',
        ], ['confirm.accepted' => 'Tick the box to confirm the restore.']);

        $data = json_decode((string) file_get_contents($request->file('file')->getRealPath()), true);
        $tables = Backup::parse(is_array($data) ? $data : null);
        $replace = $request->input('mode') === 'replace';

        try {
            $counts = Backup::restore($tables, $replace);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors(['file' => 'The restore was stopped and nothing was changed. The file may be from a different database layout.']);
        }

        Cache::flush();
        $rows = array_sum($counts);
        ActivityLog::record('updated', 'Backup', 'Restore', ($replace ? 'Replace' : 'Merge').", {$rows} rows from ".$request->file('file')->getClientOriginalName());

        return back()->with('status', "Backup restored: {$rows} rows in ".count($counts).' tables.');
    }
}
