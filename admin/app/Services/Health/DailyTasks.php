<?php

namespace App\Services\Health;

use App\Models\AppSetting;
use App\Models\ErrorLog;
use App\Services\Backup\BackupManager;
use App\Services\Notifier;
use Illuminate\Support\Facades\Cache;

// Run once a day by a Vercel cron job (see vercel.json). Each step is independent, and the result
// of every step is saved so the System Health page can show when it last ran and what failed.
class DailyTasks
{
    public static function run(): array
    {
        $lock = Cache::lock('daily-tasks', 600);
        if (! $lock->get()) {
            return ['skipped' => 'Already running'];
        }
        try {
            $last = AppSetting::group('daily')['last_run'] ?? null;
            if ($last && \Carbon\Carbon::parse($last)->gt(now()->subHours(20))) {
                return ['skipped' => 'Ran less than 20 hours ago'];
            }
            $results = [];
            $step = function (string $name, callable $fn) use (&$results) {
                try {
                    $results[$name] = ['ok' => true, 'detail' => (string) $fn()];
                } catch (\Throwable $e) {
                    report($e);
                    $results[$name] = ['ok' => false, 'detail' => class_basename($e).': '.mb_substr($e->getMessage(), 0, 200)];
                }
            };

            $step('health', fn () => HealthCheck::notify(HealthCheck::run()).' notifications');
            $step('backup', function () {
                $s = BackupManager::settings();
                if (! $s['scheduled']) {
                    return 'Daily backup is off';
                }
                $run = BackupManager::create('scheduled', array_keys(array_filter(['inbox' => $s['inbox'], 'analytics' => $s['analytics']])));
                if ($run->status !== 'verified') {
                    throw new \RuntimeException("Backup #{$run->id} failed: {$run->error}");
                }

                return "Backup #{$run->id} verified";
            });
            $step('retention', fn () => BackupManager::applyRetention().' old backup files removed');
            $step('error_log', fn () => ErrorLog::where('created_at', '<', now()->subDays(90))->delete().' errors older than 90 days removed');

            AppSetting::put('daily', ['last_run' => now()->toIso8601String(), 'results' => $results]);
            $failed = collect($results)->reject(fn ($r) => $r['ok'])->keys();
            if ($failed->isNotEmpty()) {
                Notifier::send('task_failed', 'warning', 'Daily task: '.$failed->implode(', ').' failed', collect($results)->only($failed->all())->map(fn ($r) => $r['detail'])->implode(' / '), 'System Health', route('admin.health'), 'tools', 'daily-failed-'.now()->toDateString());
            }

            return $results;
        } finally {
            $lock->release();
        }
    }
}
