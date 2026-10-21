<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AppSetting;
use App\Models\BackupRun;
use App\Services\Backup;
use App\Services\Backup\BackupManager;
use App\Services\Backup\PrivateStorage;
use App\Services\Notifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

// Administrators only (area:tools). Backup files live in a private bucket; downloads use one minute links.
class BackupController extends Controller
{
    public function index()
    {
        return view('admin.backup.index', [
            'counts' => collect(Backup::GROUPS)->map(fn ($tables) => collect($tables)->sum(fn ($t) => DB::table($t)->count())),
            'storage' => PrivateStorage::status(),
            'settings' => BackupManager::settings(),
            'runs' => BackupRun::with('user')->latest('id')->limit(30)->get(),
            'restores' => ActivityLog::where('action', 'restored')->where('module', 'Backup')->latest('id')->limit(5)->get(),
            'maxUploadMb' => 4,
        ]);
    }

    public function setup()
    {
        $status = PrivateStorage::status();
        if ($status['exists'] && $status['public']) {
            return back()->withErrors(['storage' => 'A bucket named "backups" exists and is PUBLIC. Make it private in Supabase (Storage, backups, Edit bucket) before storing backups.']);
        }
        try {
            PrivateStorage::createBucket();
        } catch (\Throwable $e) {
            return back()->withErrors(['storage' => $e->getMessage()]);
        }
        ActivityLog::record('settings', 'Backup', 'Private backup storage', 'Created the private "backups" bucket', ['module' => 'Backup']);

        return back()->with('status', 'Private backup storage is ready.');
    }

    public function store(Request $request)
    {
        $groups = array_keys(array_filter(['inbox' => $request->boolean('inbox'), 'analytics' => $request->boolean('analytics')]));
        $run = BackupManager::create('manual', $groups);

        return $run->status === 'verified'
            ? back()->with('status', 'Backup #'.$run->id.' saved and verified ('.\App\Services\Health\HealthCheck::mb((int) $run->size).').')
            : back()->withErrors(['backup' => 'The backup failed: '.$run->error]);
    }

    public function saveSettings(Request $request)
    {
        $data = $request->validate(['keep' => 'required|integer|min:1|max:100']);
        AppSetting::put('backups', ['keep' => (int) $data['keep'], 'scheduled' => $request->boolean('scheduled'), 'inbox' => $request->boolean('inbox'), 'analytics' => $request->boolean('analytics')]);
        ActivityLog::record('settings', 'Backup', 'Backup settings', 'Keep '.$data['keep'].', daily '.($request->boolean('scheduled') ? 'on' : 'off'), ['module' => 'Backup']);

        return back()->with('status', 'Backup settings saved.');
    }

    public function verify(BackupRun $run)
    {
        abort_unless($run->path && ! $run->deleted_at, 404);
        $ok = BackupManager::verify($run);
        ActivityLog::record('backup', 'Backup', "Backup #{$run->id}", $ok ? 'Verified again' : 'Verification failed', ['module' => 'Backup', 'id' => $run->id, 'status' => $ok ? 'success' : 'failure']);

        return $ok ? back()->with('status', "Backup #{$run->id} checked: the file matches its checksum and rows.") : back()->withErrors(['backup' => "Backup #{$run->id} failed the check: ".$run->fresh()->error]);
    }

    public function downloadStored(BackupRun $run)
    {
        abort_unless($run->path && ! $run->deleted_at && in_array($run->status, ['verified', 'completed'], true), 404);
        try {
            $url = PrivateStorage::signDownload($run->path);
        } catch (\Throwable $e) {
            return back()->withErrors(['backup' => $e->getMessage()]);
        }
        ActivityLog::record('exported', 'Backup', "Backup #{$run->id}", 'Downloaded a stored backup', ['module' => 'Backup', 'id' => $run->id]);

        return redirect()->away($url);
    }

    public function destroy(Request $request, BackupRun $run)
    {
        $request->validate(['confirm' => 'accepted']);
        abort_if($run->deleted_at, 404);
        try {
            BackupManager::remove($run, 'Deleted by '.$request->user()->name);
        } catch (\Throwable $e) {
            return back()->withErrors(['backup' => $e->getMessage()]);
        }
        ActivityLog::record('deleted', 'Backup', "Backup #{$run->id}", 'Deleted a stored backup file', ['module' => 'Backup', 'id' => $run->id]);

        return back()->with('status', "Backup #{$run->id} deleted from storage.");
    }

    // Direct download of a fresh backup without storing it.
    public function download(Request $request)
    {
        $groups = array_keys(array_filter(['inbox' => $request->boolean('inbox'), 'analytics' => $request->boolean('analytics')]));
        $tables = Backup::tables($groups);
        ActivityLog::record('exported', 'Backup', 'Download', 'Content'.($groups ? ', '.implode(', ', $groups) : ''), ['module' => 'Backup']);

        return response()->streamDownload(function () use ($tables) {
            $out = fopen('php://output', 'w');
            Backup::write($out, $tables);
            fclose($out);
        }, 'achivex-backup-'.now()->format('Y-m-d-His').'.json', ['Content-Type' => 'application/json']);
    }

    // Step 1 of restoring a file from this computer: a one time link to put it in the private bucket.
    public function uploadSign(Request $request)
    {
        $request->validate(['name' => 'required|string|max:200', 'size' => 'required|integer|min:2|max:'.(500 * 1048576)]);
        abort_unless(str_ends_with(strtolower($request->input('name')), '.json'), 422, 'Choose a .json backup file.');
        if (! PrivateStorage::ready()) {
            return response()->json(['message' => 'Set up private backup storage first.'], 422);
        }
        $path = 'uploads/'.now()->format('Y/m/').Str::lower(Str::random(16)).'.json';
        $run = BackupRun::create(['trigger' => 'upload', 'scope' => [], 'status' => 'running', 'path' => $path, 'user_id' => auth()->id(), 'created_at' => now(),
            'error' => 'Uploaded file: '.mb_substr($request->input('name'), 0, 150)]);

        return response()->json(['url' => PrivateStorage::signUpload($path), 'complete' => route('admin.backup.upload.complete', $run->id)]);
    }

    // Step 2: read the uploaded file back, check it is a valid backup, and go to the preview.
    public function uploadComplete(BackupRun $run)
    {
        abort_unless($run->trigger === 'upload' && $run->status === 'running' && $run->user_id === auth()->id(), 404);
        try {
            $contents = PrivateStorage::get($run->path);
            $tables = Backup::parse(json_decode($contents, true));
            $run->update(['status' => 'verified', 'verified_at' => now(), 'size' => strlen($contents), 'sha256' => hash('sha256', $contents),
                'row_counts' => collect($tables)->map(fn ($r) => count($r))->all(), 'scope' => array_keys(array_filter(['content' => true]))]);
        } catch (\Throwable $e) {
            $message = $e instanceof \Illuminate\Validation\ValidationException ? collect($e->errors())->flatten()->first() : $e->getMessage();
            $run->update(['status' => 'failed', 'error' => mb_substr((string) $message, 0, 500)]);
            rescue(fn () => PrivateStorage::delete($run->path), null, false);

            return response()->json(['message' => $message], 422);
        }

        return response()->json(['preview' => route('admin.backup.preview', ['run' => $run->id])]);
    }

    public function preview(Request $request, BackupRun $run)
    {
        abort_unless($run->path && ! $run->deleted_at && $run->status === 'verified', 404);
        $replace = $request->input('mode') === 'replace';
        try {
            $tables = Backup::parse(json_decode(PrivateStorage::get($run->path), true));
        } catch (\Throwable $e) {
            return redirect()->route('admin.backup')->withErrors(['backup' => 'The backup could not be read: '.($e instanceof \Illuminate\Validation\ValidationException ? collect($e->errors())->flatten()->first() : $e->getMessage())]);
        }

        return view('admin.backup.preview', ['run' => $run, 'replace' => $replace, 'rows' => BackupManager::preview($tables, $replace), 'safety' => PrivateStorage::ready()]);
    }

    public function restore(Request $request, BackupRun $run)
    {
        $request->validate(['mode' => 'required|in:merge,replace', 'confirm_text' => 'required|in:RESTORE'], ['confirm_text.in' => 'Type RESTORE in capital letters to confirm.', 'confirm_text.required' => 'Type RESTORE to confirm.']);
        abort_unless($run->path && ! $run->deleted_at && $run->status === 'verified', 404);
        $replace = $request->input('mode') === 'replace';

        // A fresh copy of the current data is stored first, so the restore can be undone.
        $safety = BackupManager::create('pre-restore', ['inbox']);
        if ($safety->status !== 'verified') {
            return back()->withErrors(['confirm_text' => 'The safety backup of the current data failed, so nothing was restored: '.$safety->error]);
        }
        try {
            $counts = BackupManager::restore($run, $replace);
        } catch (\Throwable $e) {
            report($e);
            ActivityLog::failure('restored', 'Backup', "Backup #{$run->id}", 'Restore stopped; nothing was changed', ['module' => 'Backup', 'id' => $run->id]);
            Notifier::send('restore_failed', 'critical', "Restore of backup #{$run->id} failed", 'Nothing was changed. The file may be from a different database layout.', 'Backup', route('admin.backup'), 'tools');

            return redirect()->route('admin.backup')->withErrors(['backup' => 'The restore was stopped and nothing was changed. '.($e instanceof \Illuminate\Validation\ValidationException ? collect($e->errors())->flatten()->first() : 'The file may be from a different database layout.')]);
        }
        $rows = array_sum($counts);
        ActivityLog::record('restored', 'Backup', "Backup #{$run->id}", ($replace ? 'Replace' : 'Merge').", {$rows} rows in ".count($counts)." tables. Safety backup #{$safety->id}.", ['module' => 'Backup', 'id' => $run->id]);
        Notifier::send('backup_restored', 'warning', "Backup #{$run->id} was restored", ($replace ? 'Replace' : 'Merge')." by {$request->user()->name}. The data before it is in backup #{$safety->id}.", 'Backup', route('admin.backup'), 'tools');

        return redirect()->route('admin.backup')->with('status', "Backup restored: {$rows} rows in ".count($counts)." tables. The data from just before is saved as backup #{$safety->id}.");
    }

    // Small files when private storage is not set up: the old one step restore, kept as a fallback.
    public function restoreUpload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:4096|extensions:json',
            'mode' => 'required|in:merge,replace',
            'confirm_text' => 'required|in:RESTORE',
        ], ['confirm_text.in' => 'Type RESTORE in capital letters to confirm.', 'confirm_text.required' => 'Type RESTORE to confirm.']);

        $data = json_decode((string) file_get_contents($request->file('file')->getRealPath()), true);
        $tables = Backup::parse(is_array($data) ? $data : null);
        $replace = $request->input('mode') === 'replace';
        try {
            $counts = Backup::restore($tables, $replace);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            ActivityLog::failure('restored', 'Backup', 'Uploaded file', 'Restore stopped; nothing was changed', ['module' => 'Backup']);

            return back()->withErrors(['file' => 'The restore was stopped and nothing was changed. The file may be from a different database layout.']);
        }
        Cache::flush();
        $rows = array_sum($counts);
        ActivityLog::record('restored', 'Backup', 'Uploaded file', ($replace ? 'Replace' : 'Merge').", {$rows} rows from ".$request->file('file')->getClientOriginalName(), ['module' => 'Backup']);

        return back()->with('status', "Backup restored: {$rows} rows in ".count($counts).' tables.');
    }
}
