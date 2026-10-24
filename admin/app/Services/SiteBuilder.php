<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\AppSetting;
use Symfony\Component\Process\Process;

// Builds the website on the same server as the admin (Hostinger Business or higher, with Node.js).
// The admin only marks that a build is wanted (SiteRebuild::start). The cron job
// `php artisan site:build`, run every minute, does the build here and copies the result into the
// website folder, so no outside service is involved.
//
// .env: SITE_BUILD=server, SITE_SOURCE_PATH (the website folder of the code, with package.json),
// SITE_PUBLIC_PATH (the live website folder) and, if npm is not on the PATH, SITE_NPM.
class SiteBuilder
{
    public static function enabled(): bool
    {
        return config('services.site_build') === 'server'
            && is_file(static::source().'/package.json') && InstantPublish::root() !== '';
    }

    public static function source(): string
    {
        return rtrim((string) config('services.site_source_path', ''), '/');
    }

    // Asks for a build; the next cron run does it.
    public static function request(string $reason): void
    {
        AppSetting::put('site_build', ['requested' => true, 'requested_reason' => $reason, 'requested_at' => now()->toIso8601String()]);
    }

    // Called by the cron job. Returns a short report for the log.
    public static function runIfRequested(): string
    {
        if (! static::enabled()) {
            return 'Server builds are off (SITE_BUILD, SITE_SOURCE_PATH, SITE_PUBLIC_PATH).';
        }
        $state = AppSetting::group('site_build');
        if (empty($state['requested'])) {
            return 'Nothing to build.';
        }
        $lock = fopen(storage_path('framework/cache/site-build.lock'), 'c');
        if (! $lock || ! flock($lock, LOCK_EX | LOCK_NB)) {
            return 'A build is already running.';
        }
        try {
            // A change saved while this build runs asks for the next one.
            AppSetting::put('site_build', ['requested' => false, 'running' => true, 'started_at' => now()->toIso8601String()]);
            $reason = $state['requested_reason'] ?? 'Content changed';
            [$ok, $message] = static::build();
            AppSetting::put('site_build', ['running' => false, 'last_ok' => $ok, 'finished_at' => now()->toIso8601String(), 'last_error' => $ok ? null : $message]);
            ActivityLog::record('site_build', 'Website', 'Website build', ($ok ? 'Website built and published: ' : 'Website build failed: ').$reason, ['module' => 'Website']);

            return $message;
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    // Installs packages when package-lock.json changed, builds, then copies out/ to the live folder.
    private static function build(): array
    {
        $src = static::source();
        $npm = (string) config('services.site_npm', 'npm');
        $env = ['SITE_URL' => rtrim((string) config('admin.site_public_url'), '/'), 'ADMIN_URL' => rtrim((string) config('app.url'), '/'), 'NEXT_TELEMETRY_DISABLED' => '1'];

        $lockHash = @md5_file("{$src}/package-lock.json") ?: '';
        if (! is_dir("{$src}/node_modules") || (AppSetting::group('site_build')['lock_hash'] ?? '') !== $lockHash) {
            $install = static::run([$npm, 'ci', '--no-audit', '--no-fund'], $src, $env, 900);
            if ($install !== true) {
                return [false, 'npm ci failed: '.$install];
            }
            AppSetting::put('site_build', ['lock_hash' => $lockHash]);
        }
        $result = static::run([$npm, 'run', 'build'], $src, $env, 900);
        if ($result !== true) {
            return [false, 'Build failed: '.$result];
        }
        if (! is_file("{$src}/out/index.html") || ! is_file("{$src}/out/404.html")) {
            return [false, 'Build finished but out/ is incomplete, so the live website was left as it was.'];
        }
        $copied = static::publish("{$src}/out", InstantPublish::root());

        return [true, "Built and copied {$copied} changed files."];
    }

    private static function run(array $command, string $cwd, array $env, int $timeout): true|string
    {
        // Cron jobs start with a short PATH; when npm is given by its full path, node sits next to it.
        $base = getenv();
        if (str_contains($command[0], '/')) {
            $base['PATH'] = dirname($command[0]).':'.($base['PATH'] ?? '/usr/bin:/bin');
        }
        $process = new Process($command, $cwd, $env + $base, null, $timeout);
        $process->run();

        return $process->isSuccessful() ? true : mb_substr(trim($process->getErrorOutput() ?: $process->getOutput()), -1500);
    }

    // Copies changed files to the live folder: build assets first, pages last, so a visitor never
    // gets a page whose scripts are not there yet. Files from the previous build that are gone now
    // are removed; files the build never made (uploads, .htaccess edits, instant pages) are kept.
    public static function publish(string $from, string $to): int
    {
        $files = [];
        $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($from, \FilesystemIterator::SKIP_DOTS));
        foreach ($it as $file) {
            $files[] = substr($file->getPathname(), strlen($from) + 1);
        }
        usort($files, fn ($a, $b) => [str_ends_with($a, '.html'), $a] <=> [str_ends_with($b, '.html'), $b]);

        $copied = 0;
        foreach ($files as $rel) {
            $target = "{$to}/{$rel}";
            if (is_file($target) && filesize($target) === filesize("{$from}/{$rel}") && md5_file($target) === md5_file("{$from}/{$rel}")) {
                continue;
            }
            @mkdir(dirname($target), 0755, true);
            if (@copy("{$from}/{$rel}", "{$target}.tmp") && @rename("{$target}.tmp", $target)) {
                $copied++;
            }
        }

        $manifest = "{$to}/.build-files.json";
        $previous = is_file($manifest) ? (json_decode((string) file_get_contents($manifest), true) ?: []) : [];
        foreach (array_diff($previous, $files) as $rel) {
            if (! str_contains($rel, '..')) {
                @unlink("{$to}/{$rel}");
                @rmdir(dirname("{$to}/{$rel}"));
            }
        }
        file_put_contents($manifest, json_encode(array_values($files)));

        return $copied;
    }
}
