<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\AppSetting;
use App\Support\Sites;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

// Rebuilds the static website after content changes, so new and edited pages are real pages at once
// (a page that is not built yet answers with a 404 status, which search engines treat as missing).
// The build is started by calling the hosting's deploy hook (SITE_DEPLOY_HOOK in .env). Builds are at
// least GAP seconds apart; a change in between is remembered and built on the next admin request.
class SiteRebuild
{
    public const GAP = 90;

    private static ?string $reason = null;

    public static function hook(): string
    {
        return (string) config('services.site_deploy_hook', '');
    }

    // Notes a change during this request; one build request is sent when the request ends.
    public static function later(string $reason): void
    {
        if ((static::hook() !== '' || InstantPublish::enabled()) && Sites::active() === null) {
            static::$reason ??= $reason;
        }
    }

    // Runs at the end of every request: sends the noted change, or a change that had to wait.
    public static function flush(): void
    {
        if (Sites::active() !== null) {
            return;
        }
        // On Hostinger new pages go live at once, before the full build.
        if (static::$reason !== null) {
            try {
                InstantPublish::sync();
            } catch (\Throwable) {
            }
        }
        if (static::hook() === '') {
            static::$reason = null;

            return;
        }
        try {
            $state = AppSetting::group('site_build');
            if (static::$reason !== null) {
                static::request(static::$reason, $state);
            } elseif (! empty($state['pending'])) {
                static::request($state['pending_reason'] ?? 'Content changed', $state);
            }
        } catch (\Throwable) {
            // Never break an admin action because the build could not be started.
        } finally {
            static::$reason = null;
        }
    }

    private static function request(string $reason, array $state): void
    {
        $last = $state['last_at'] ?? null;
        if ($last && Carbon::parse($last)->addSeconds(self::GAP)->isFuture()) {
            if (empty($state['pending'])) {
                AppSetting::put('site_build', ['pending' => true, 'pending_reason' => $reason]);
            }

            return;
        }
        static::start($reason);
    }

    // Starts a build now. Used by the "Publish" button and after content changes.
    public static function start(string $reason): bool
    {
        if (static::hook() === '') {
            return false;
        }
        try {
            $token = (string) config('services.site_deploy_token', '');
            // GitHub (Hostinger builds) needs a token and an event name; Vercel's hook needs nothing.
            $ok = $token !== ''
                ? Http::timeout(8)->withToken($token)->accept('application/vnd.github+json')
                    ->post(static::hook(), ['event_type' => 'site-rebuild', 'client_payload' => ['reason' => mb_substr($reason, 0, 100)]])->successful()
                : Http::timeout(8)->post(static::hook())->successful();
        } catch (\Throwable) {
            $ok = false;
        }
        AppSetting::put('site_build', ['last_at' => now()->toIso8601String(), 'last_ok' => $ok, 'last_reason' => $reason, 'pending' => false]);
        ActivityLog::record('site_rebuild', 'Website', 'Website build', ($ok ? 'Build started: ' : 'Build could not be started: ').$reason, ['module' => 'Website']);

        return $ok;
    }
}
