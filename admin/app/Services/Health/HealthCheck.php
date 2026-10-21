<?php

namespace App\Services\Health;

use App\Models\AppSetting;
use App\Models\BackupRun;
use App\Models\ErrorLog;
use App\Models\Media;
use App\Services\InboxNotifier;
use App\Services\Notifier;
use App\Services\SupabaseStorage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

// Measurements of this admin and its services. Each check reports what was actually measured;
// anything that cannot be measured here is shown as "unknown", never as passing.
class HealthCheck
{
    // Each: ['key', 'label', 'status' => ok|warning|critical|unknown, 'value', 'detail', 'percent' => ?int]
    public static function run(bool $live = false): array
    {
        $checks = [static::database()];
        $checks[] = static::databaseSize();
        $checks[] = static::storage();
        $checks[] = static::backups();
        $checks[] = static::dailyTask();
        $checks[] = static::errors();
        $checks[] = static::jobs();
        $checks[] = static::cache();
        $checks[] = static::mail();
        $checks[] = static::storageKey($live);
        $checks[] = static::website($live);

        return $checks;
    }

    private static function check(string $key, string $label, string $status, string $value, string $detail = '', ?int $percent = null): array
    {
        return compact('key', 'label', 'status', 'value', 'detail', 'percent');
    }

    private static function level(float $percent, float $warn, float $crit): string
    {
        return $percent >= $crit ? 'critical' : ($percent >= $warn ? 'warning' : 'ok');
    }

    public static function thresholds(): array
    {
        $s = AppSetting::group('health');

        return [
            'storage_mb' => (int) ($s['storage_mb'] ?? 1024),
            'database_mb' => (int) ($s['database_mb'] ?? 500),
            'warn' => (int) ($s['warn'] ?? 80),
            'critical' => (int) ($s['critical'] ?? 95),
            'backup_days' => (int) ($s['backup_days'] ?? 7),
        ];
    }

    public static function database(): array
    {
        try {
            $start = microtime(true);
            DB::select('select 1');
            $ms = (int) round((microtime(true) - $start) * 1000);

            return static::check('database', 'Database connection', $ms > 1500 ? 'warning' : 'ok', "Connected, {$ms} ms", 'Round trip of one query from this server to Supabase.');
        } catch (\Throwable $e) {
            return static::check('database', 'Database connection', 'critical', 'Not connected', class_basename($e));
        }
    }

    public static function databaseSize(): array
    {
        $t = static::thresholds();
        try {
            $bytes = (int) DB::selectOne('select pg_database_size(current_database()) as b')->b;
        } catch (\Throwable) {
            return static::check('database_size', 'Database size', 'unknown', 'Not measurable', 'The database did not report its size to this account.');
        }
        $pct = (int) round($bytes / ($t['database_mb'] * 1048576) * 100);

        return static::check('database_size', 'Database size', static::level($pct, $t['warn'], $t['critical']), static::mb($bytes)." of {$t['database_mb']} MB", 'Limit set under the thresholds below (Supabase Free plan: 500 MB).', $pct);
    }

    public static function storage(): array
    {
        $t = static::thresholds();
        $media = (int) Media::sum('size');
        $backups = (int) BackupRun::whereNull('deleted_at')->whereIn('status', ['verified', 'completed'])->sum('size');
        $bytes = $media + $backups;
        $pct = (int) round($bytes / ($t['storage_mb'] * 1048576) * 100);

        return static::check('storage', 'File storage', static::level($pct, $t['warn'], $t['critical']), static::mb($bytes)." of {$t['storage_mb']} MB",
            'Media library '.static::mb($media).', stored backups '.static::mb($backups).'. Counted from admin records; files uploaded to Supabase outside the admin are not included.', $pct);
    }

    public static function backups(): array
    {
        $t = static::thresholds();
        $last = BackupRun::where('status', 'verified')->latest('id')->first();
        $lastRun = BackupRun::latest('id')->first();
        if ($lastRun && $lastRun->status === 'failed') {
            return static::check('backups', 'Backups', 'critical', 'Last backup failed', $lastRun->created_at?->diffForHumans().': '.mb_substr((string) $lastRun->error, 0, 160));
        }
        if (! $last) {
            return static::check('backups', 'Backups', 'warning', 'No verified backup yet', 'Create one under Tools, Backup and Restore.');
        }
        $old = $last->verified_at?->lt(now()->subDays($t['backup_days']));

        return static::check('backups', 'Backups', $old ? 'warning' : 'ok', 'Last verified '.$last->verified_at?->diffForHumans(), static::mb((int) $last->size).', SHA-256 checked.');
    }

    public static function dailyTask(): array
    {
        $d = AppSetting::group('daily');
        if (empty($d['last_run'])) {
            return static::check('daily', 'Daily scheduled task', 'unknown', 'Has not run yet', 'Vercel runs it once a day after deployment. It checks health, makes the scheduled backup and applies retention.');
        }
        $at = \Carbon\Carbon::parse($d['last_run']);
        $failed = collect($d['results'] ?? [])->filter(fn ($r) => ($r['ok'] ?? true) === false)->keys();

        return static::check('daily', 'Daily scheduled task', $at->lt(now()->subHours(26)) ? 'warning' : ($failed->isNotEmpty() ? 'warning' : 'ok'),
            'Last run '.$at->diffForHumans(), $failed->isNotEmpty() ? 'Failed steps: '.$failed->implode(', ') : 'All steps finished.');
    }

    public static function errors(): array
    {
        $day = ErrorLog::where('created_at', '>=', now()->subDay())->count();
        $week = ErrorLog::where('created_at', '>=', now()->subDays(7))->count();

        return static::check('errors', 'Application errors', $day >= 20 ? 'critical' : ($day > 0 ? 'warning' : 'ok'), "{$day} in 24 hours", "{$week} in 7 days. Details below.");
    }

    public static function jobs(): array
    {
        if (config('queue.default') === 'sync') {
            $failed = rescue(fn () => DB::table('failed_jobs')->count(), 0, false);

            return static::check('jobs', 'Background jobs', $failed ? 'warning' : 'ok', 'Run immediately (no queue worker)', $failed ? "{$failed} failed jobs recorded." : 'Tasks run during the request, so none can be left waiting.');
        }
        $failed = DB::table('failed_jobs')->count();

        return static::check('jobs', 'Background jobs', $failed ? 'warning' : 'ok', "{$failed} failed", 'From the failed_jobs table.');
    }

    public static function cache(): array
    {
        if (config('cache.default') !== 'database') {
            return static::check('cache', 'Cache', 'ok', 'Driver: '.config('cache.default'), '');
        }
        $table = config('cache.stores.database.table', 'cache');
        $total = DB::table($table)->count();
        $expired = DB::table($table)->where('expiration', '<', now()->timestamp)->count();

        return static::check('cache', 'Cache', 'ok', "{$total} entries", "{$expired} expired. Manage under Tools, Cache.");
    }

    public static function mail(): array
    {
        $status = InboxNotifier::status();
        $last = rescue(fn () => DB::table('public.contact_submissions')->whereNotNull('notified_at')->max('notified_at'), null, false);

        return match ($status) {
            'ok' => static::check('mail', 'Email (SMTP)', 'ok', 'Set up', $last ? 'Last inbox email sent '.\Carbon\Carbon::parse($last)->diffForHumans().'.' : 'No inbox email sent yet.'),
            'mail_failed' => static::check('mail', 'Email (SMTP)', 'critical', 'Last send failed', 'See Settings, Email for the error.'),
            default => static::check('mail', 'Email (SMTP)', 'warning', 'Not set up', 'Alerts and password reset emails cannot be sent.'),
        };
    }

    public static function storageKey(bool $live): array
    {
        if (! SupabaseStorage::enabled()) {
            return static::check('storage_key', 'Supabase Storage', 'warning', 'Not connected', 'Media uploads and stored backups need the storage key under Settings.');
        }
        if (! $live) {
            return static::check('storage_key', 'Supabase Storage', 'unknown', 'Key saved', 'Click "Run live checks" to test the connection.');
        }
        $error = SupabaseStorage::check(SupabaseStorage::key());

        return static::check('storage_key', 'Supabase Storage', $error ? 'critical' : 'ok', $error ? 'Connection failed' : 'Connected', $error ?: 'The saved key was accepted just now.');
    }

    public static function website(bool $live): array
    {
        $url = rtrim((string) config('admin.site_url'), '/').'/';
        if (! $live) {
            return static::check('website', 'Public website', 'unknown', 'Not checked', 'Click "Run live checks" to load '.$url);
        }
        try {
            $start = microtime(true);
            $res = Http::timeout(6)->get($url);
            $ms = (int) round((microtime(true) - $start) * 1000);

            return static::check('website', 'Public website', $res->ok() ? ($ms > 3000 ? 'warning' : 'ok') : 'critical', 'HTTP '.$res->status().", {$ms} ms", $url);
        } catch (\Throwable $e) {
            return static::check('website', 'Public website', 'critical', 'Not reachable', class_basename($e).' for '.$url);
        }
    }

    public static function environment(): array
    {
        return [
            'Laravel' => app()->version(),
            'PHP' => PHP_VERSION,
            'Environment' => app()->environment(),
            'Debug mode' => config('app.debug') ? 'On' : 'Off',
            'Deployed commit' => ($sha = getenv('VERCEL_GIT_COMMIT_SHA')) ? substr($sha, 0, 7) : 'Not reported',
            'Region' => getenv('VERCEL_REGION') ?: 'Not reported',
            'Session driver' => config('session.driver'),
            'Cache driver' => config('cache.default'),
            'Queue' => config('queue.default'),
        ];
    }

    // Sends a notification for each check in warning or critical state, at most once a day per check.
    public static function notify(array $checks): int
    {
        $sent = 0;
        foreach ($checks as $c) {
            if (in_array($c['status'], ['warning', 'critical'], true) && $c['key'] !== 'errors') {
                $sent += Notifier::send("health_{$c['key']}", $c['status'], "{$c['label']}: {$c['value']}", $c['detail'], 'System Health', route('admin.health'), 'tools', "health-{$c['key']}-{$c['status']}");
            }
        }

        return $sent;
    }

    public static function mb(int $bytes): string
    {
        return $bytes >= 1073741824 ? round($bytes / 1073741824, 2).' GB' : round($bytes / 1048576, 1).' MB';
    }
}
