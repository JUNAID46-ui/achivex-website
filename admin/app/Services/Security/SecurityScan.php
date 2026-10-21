<?php

namespace App\Services\Security;

use App\Models\ActivityLog;
use App\Models\User;
use App\Services\Audit\Finding;
use Illuminate\Support\Facades\Route;

// Read only security checks of this admin. Nothing here changes data or contacts the website.
class SecurityScan
{
    // Routes that are public on purpose.
    public const PUBLIC_ROUTES = ['admin.login', 'admin.two-factor', 'admin.password.request', 'admin.password.email', 'admin.password.reset', 'admin.password.update',
        'hooks.inbox', 'hooks.daily', 'sitemap', 'robots', 'llms', 'storage.local', 'up'];

    // Signed in routes that every role may open.
    public const ANY_ROLE = ['admin.dashboard', 'admin.logout', 'admin.profile', 'admin.profile.two-factor', 'admin.profile.two-factor.confirm', 'admin.profile.two-factor.disable',
        'admin.profile.sessions', 'admin.notifications', 'admin.notifications.read-all', 'admin.notifications.open', 'admin.notifications.update',
        'admin.media.list', 'admin.media.store', 'admin.media.sign', 'admin.media.complete'];

    public static function run(): array
    {
        $findings = array_merge(
            static::authPatterns(),
            static::accounts(),
            static::routes(),
            static::config(),
            SecretScanner::findings(),
            DependencyCheck::findings(),
        );

        return ['findings' => Finding::sort($findings)];
    }

    // Suspicious sign in patterns in the last 24 hours, from the activity log.
    public static function authPatterns(): array
    {
        $f = [];
        $since = now()->subDay();
        $fails = ActivityLog::where('action', 'login_failed')->where('created_at', '>=', $since)->get(['subject_label', 'ip', 'created_at']);
        foreach ($fails->groupBy('subject_label')->filter(fn ($g) => $g->count() >= 5) as $email => $g) {
            $f[] = Finding::make('Repeated failed sign ins for one account', 'Sign in', 'high', null, "{$g->count()} failed attempts for {$email} in 24 hours from ".$g->pluck('ip')->unique()->count().' IP address(es).', 'If this was not you, change the password and turn on two-step login for this account.');
        }
        foreach ($fails->groupBy('ip')->filter(fn ($g) => $g->count() >= 10) as $ip => $g) {
            $f[] = Finding::make('Many failed sign ins from one IP address', 'Sign in', 'high', null, "{$g->count()} failed attempts from {$ip} in 24 hours, for ".$g->pluck('subject_label')->unique()->count().' account(s).', 'Sign in is already rate limited. If it continues, block the IP in the Vercel firewall.');
        }
        foreach (User::all() as $u) {
            $lastLogin = ActivityLog::where('action', 'login')->where('subject_id', (string) $u->id)->where('created_at', '>=', $since)->latest('id')->first();
            if (! $lastLogin) {
                continue;
            }
            $before = $fails->where('subject_label', strtolower($u->email))->where('created_at', '<', $lastLogin->created_at)->count();
            if ($before >= 3) {
                $f[] = Finding::make('Successful sign in after several failures', 'Sign in', 'medium', null, "{$u->name} signed in from {$lastLogin->ip} after {$before} failed attempts in 24 hours.", 'Confirm with the person that it was them.');
            }
            $seenBefore = ActivityLog::where('action', 'login')->where('subject_id', (string) $u->id)->where('ip', $lastLogin->ip)->where('created_at', '<', $since)->where('created_at', '>=', now()->subDays(60))->exists();
            $hadLogins = ActivityLog::where('action', 'login')->where('subject_id', (string) $u->id)->where('created_at', '<', $since)->where('created_at', '>=', now()->subDays(60))->exists();
            if ($hadLogins && ! $seenBefore) {
                $f[] = Finding::make('Sign in from a new IP address', 'Sign in', 'info', null, "{$u->name} signed in from {$lastLogin->ip}, not seen for this account in the previous 60 days.", 'Usually harmless (new network or phone). Check it if unexpected.');
            }
        }

        return $f;
    }

    public static function accounts(): array
    {
        $f = [];
        $users = User::all();
        foreach ($users as $u) {
            if (! $u->hasTwoFactor()) {
                $f[] = Finding::make('Two-step login is off', 'Accounts', $u->isAdmin() ? 'high' : 'medium', null, "{$u->name} (".(User::ROLES[$u->role] ?? $u->role).') signs in with a password only.', 'Ask them to turn it on under Profile, Two-step login.');
            }
            if ($u->last_login_at && $u->last_login_at->lt(now()->subDays(90))) {
                $f[] = Finding::make('Account not used for 90 days', 'Accounts', 'low', null, "{$u->name} last signed in ".$u->last_login_at->diffForHumans().'.', 'Remove accounts that are no longer needed.');
            }
        }
        $admins = $users->where('role', 'administrator')->count();
        if ($admins > 3) {
            $f[] = Finding::make('Many administrators', 'Accounts', 'low', null, "{$admins} accounts have full access.", 'Give people the lowest role that lets them do their work.');
        }

        return $f;
    }

    // Every route must be public on purpose, or require sign in plus a role check.
    public static function routes(): array
    {
        $f = [];
        foreach (Route::getRoutes() as $route) {
            $name = $route->getName();
            $mw = $route->gatherMiddleware();
            $uri = '/'.ltrim($route->uri(), '/');
            // Public on purpose: named public routes, guest only sign in pages, the home redirect and the /up health check.
            if (str_starts_with($uri, '/_') || in_array($name, self::PUBLIC_ROUTES, true) || in_array($uri, ['/', '/up'], true) || in_array('guest', $mw, true)) {
                continue;
            }
            if (! in_array('auth', $mw, true)) {
                $f[] = Finding::make('Route without sign in', 'Authorization', 'critical', null, implode('|', $route->methods())." {$uri} ({$name}) does not require sign in.", 'Add the auth middleware or list it as public on purpose.');

                continue;
            }
            $hasArea = collect($mw)->contains(fn ($m) => str_starts_with((string) $m, 'area:'));
            $checksInside = str_contains((string) $route->getActionName(), 'ResourceController');
            if (! $hasArea && ! $checksInside && ! in_array($name, self::ANY_ROLE, true) && ! in_array($uri, ['/admin/profile'], true)) {
                $f[] = Finding::make('Route without a role check', 'Authorization', 'medium', null, implode('|', $route->methods())." {$uri} ({$name}) is open to every signed in role.", 'Add an area middleware or confirm every role may use it.');
            }
        }

        return $f;
    }

    public static function config(): array
    {
        $f = [];
        $env = app()->environment();
        if (config('app.debug') && $env === 'production') {
            $f[] = Finding::make('Debug mode is on in production', 'Configuration', 'critical', null, 'APP_DEBUG is true.', 'Set APP_DEBUG=false. Debug pages can show internal details.');
        }
        if ($env !== 'production' && getenv('VERCEL')) {
            $f[] = Finding::make('Environment is not production', 'Configuration', 'medium', null, "APP_ENV is {$env} on Vercel.", 'Set APP_ENV=production.');
        }
        $key = (string) config('app.key');
        if ($key === '' || strlen(str_starts_with($key, 'base64:') ? base64_decode(substr($key, 7)) : $key) < 32) {
            $f[] = Finding::make('Encryption key missing or short', 'Configuration', 'critical', null, 'APP_KEY is not a 32 byte key.', 'Generate one with "php artisan key:generate --show" and set it on Vercel.');
        }
        if (getenv('VERCEL') && ! config('session.secure')) {
            $f[] = Finding::make('Session cookie without Secure flag', 'Sessions', 'high', null, 'SESSION_SECURE_COOKIE is off.', 'Set SESSION_SECURE_COOKIE=true.');
        }
        if ((int) config('session.lifetime') > 480) {
            $f[] = Finding::make('Long session lifetime', 'Sessions', 'low', null, 'Sessions last '.config('session.lifetime').' minutes without activity.', 'Consider 240 minutes or less.');
        }
        if (config('session.driver') !== 'database') {
            $f[] = Finding::make('Sessions are not stored in the database', 'Sessions', 'info', null, 'Session driver: '.config('session.driver').'. Active sessions cannot be listed or ended from here.', 'Use SESSION_DRIVER=database in production.');
        }
        if (! \App\Services\InboxNotifier::ready()) {
            $f[] = Finding::make('Email is not set up', 'Configuration', 'low', null, 'Password reset emails and alerts cannot be sent.', 'Set up the email server under Settings.');
        }

        return $f;
    }
}
