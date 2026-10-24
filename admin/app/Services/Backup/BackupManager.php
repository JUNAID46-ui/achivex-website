<?php

namespace App\Services\Backup;

use App\Models\ActivityLog;
use App\Models\AppSetting;
use App\Models\BackupRun;
use App\Services\Backup;
use App\Services\Notifier;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

// Stored backups: create, verify, list, download, restore preview and retention.
// A backup counts as good only after the stored file was read back and its SHA-256 and rows matched.
class BackupManager
{
    public static function settings(): array
    {
        $s = AppSetting::group('backups');

        return [
            'keep' => max(1, min(100, (int) ($s['keep'] ?? 10))),
            'scheduled' => (bool) ($s['scheduled'] ?? false),
            'inbox' => (bool) ($s['inbox'] ?? true),
            'analytics' => (bool) ($s['analytics'] ?? false),
        ];
    }

    // Builds the backup file in memory. Returns [contents, row counts].
    public static function build(array $groups): array
    {
        $stream = fopen('php://temp', 'w+');
        Backup::write($stream, Backup::tables($groups));
        rewind($stream);
        $contents = stream_get_contents($stream);
        fclose($stream);
        $decoded = json_decode($contents, true);
        $counts = collect($decoded['tables'] ?? [])->map(fn ($rows) => count($rows))->all();

        return [$contents, $counts];
    }

    public static function create(string $trigger, array $groups): BackupRun
    {
        $groups = array_values(array_intersect($groups, ['inbox', 'analytics']));
        $run = BackupRun::create(['trigger' => $trigger, 'scope' => array_merge(['content'], $groups), 'status' => 'running', 'user_id' => auth()->id(), 'created_at' => now()]);
        try {
            if (! PrivateStorage::ready()) {
                throw new \RuntimeException('The private backups bucket is not set up (or is public). Set it up on the Backup page first.');
            }
            [$contents, $counts] = static::build($groups);
            $path = now()->format('Y/m/').'achivex-backup-'.now()->format('Y-m-d-His').'-'.Str::lower(Str::random(6)).'.json';
            PrivateStorage::put($path, $contents);
            $run->update(['path' => $path, 'size' => strlen($contents), 'sha256' => hash('sha256', $contents), 'row_counts' => $counts, 'status' => 'completed']);
            unset($contents);
            static::verify($run);
        } catch (\Throwable $e) {
            report($e);
            static::fail($run, $e->getMessage());
        }
        $run->refresh();
        ActivityLog::record('backup', 'Backup', "Backup #{$run->id}", ucfirst($trigger).' backup: '.$run->status, ['module' => 'Backup', 'id' => $run->id, 'status' => $run->status === 'verified' ? 'success' : 'failure']);
        if ($run->status === 'verified') {
            static::applyRetention();
        }

        return $run;
    }

    // Reads the stored file back and checks hash, format and row counts.
    public static function verify(BackupRun $run): bool
    {
        try {
            $contents = PrivateStorage::get($run->path);
            if (! hash_equals((string) $run->sha256, hash('sha256', $contents))) {
                throw new \RuntimeException('The stored file does not match its SHA-256 checksum.');
            }
            $tables = Backup::parse(json_decode($contents, true));
            $counts = collect($tables)->map(fn ($rows) => count($rows))->all();
            if ($counts != ($run->row_counts ?? [])) {
                throw new \RuntimeException('Row counts in the stored file differ from the ones recorded when it was made.');
            }
            $run->update(['status' => 'verified', 'verified_at' => now(), 'error' => null]);

            return true;
        } catch (\Throwable $e) {
            static::fail($run, $e instanceof \Illuminate\Validation\ValidationException ? collect($e->errors())->flatten()->first() : $e->getMessage());

            return false;
        }
    }

    private static function fail(BackupRun $run, string $message): void
    {
        $run->update(['status' => 'failed', 'error' => mb_substr($message, 0, 1000)]);
        Notifier::send('backup_failed', 'critical', 'Backup failed', mb_substr($message, 0, 300), 'Backup', route('admin.backup'), 'tools');
    }

    // Keeps the newest N verified backups; older files are deleted, their history rows stay.
    public static function applyRetention(): int
    {
        $keep = static::settings()['keep'];
        $old = BackupRun::where('status', 'verified')->whereNull('deleted_at')->whereNotNull('path')->where('trigger', '!=', 'upload')
            ->orderByDesc('id')->skip($keep)->take(500)->get();
        foreach ($old as $run) {
            static::remove($run, 'Removed by the retention setting (keep '.$keep.')');
        }

        return $old->count();
    }

    public static function remove(BackupRun $run, string $why): void
    {
        if ($run->path) {
            PrivateStorage::delete($run->path);
        }
        $run->update(['status' => 'deleted', 'deleted_at' => now(), 'error' => $why]);
    }

    // What a restore would do, table by table, before anything is changed.
    public static function preview(array $tables, bool $replace): array
    {
        $rows = [];
        foreach ($tables as $table => $records) {
            $current = DB::table($table)->count();
            $key = static::keyOf($table);
            $keys = collect($records)->pluck($key)->filter(fn ($k) => $k !== null)->map(fn ($k) => (string) $k)->all();
            $existing = $keys ? DB::table($table)->whereIn($key, $keys)->count() : 0;
            $rows[$table] = [
                'current' => $current,
                'in_backup' => count($records),
                'update' => $existing,
                'add' => count($records) - $existing,
                'remove' => $replace ? max(0, $current - $existing) : 0,
            ];
        }

        return $rows;
    }

    private static function keyOf(string $table): string
    {
        return DB::selectOne('select a.attname from pg_index i join pg_attribute a on a.attrelid = i.indrelid and a.attnum = any(i.indkey) where i.indrelid = ?::regclass and i.indisprimary', [$table])->attname;
    }

    public static function restore(BackupRun $run, bool $replace): array
    {
        $contents = PrivateStorage::get($run->path);
        if ($run->sha256 && ! hash_equals($run->sha256, hash('sha256', $contents))) {
            throw new \RuntimeException('The stored file does not match its checksum, so it was not restored.');
        }
        $counts = Backup::restore(Backup::parse(json_decode($contents, true)), $replace);
        Cache::flush();

        return $counts;
    }
}
