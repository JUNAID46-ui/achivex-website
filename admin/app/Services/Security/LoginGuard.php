<?php

namespace App\Services\Security;

use App\Models\ActivityLog;
use App\Models\BlockedIp;
use App\Services\InboxNotifier;
use App\Services\Notifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

// Blocks an IP address from the whole admin after repeated failed sign ins, keeps a record of it
// in the Security Center, and tells the administrators in the admin and by email.
class LoginGuard
{
    // Wrong passwords or two-step codes from one IP address, within WINDOW_MINUTES, before it is blocked.
    public const MAX_FAILURES = 3;

    public const WINDOW_MINUTES = 60;

    public const BLOCK_HOURS = 24;

    public static function blocked(string $ip): ?BlockedIp
    {
        return BlockedIp::active()->where('ip', $ip)->latest('id')->first();
    }

    // Called after every failed sign in (already written to the activity log). Returns the new block, if any.
    // A problem here (for example the table not created yet) is reported but never breaks signing in.
    public static function failed(Request $request): ?BlockedIp
    {
        return rescue(fn () => static::check($request), null);
    }

    private static function check(Request $request): ?BlockedIp
    {
        $ip = (string) $request->ip();
        if ($ip === '' || static::blocked($ip)) {
            return null;
        }
        // Failures before an administrator lifted an earlier block do not count again.
        $since = now()->subMinutes(self::WINDOW_MINUTES);
        $lifted = BlockedIp::where('ip', $ip)->max('unblocked_at');
        if ($lifted && \Illuminate\Support\Carbon::parse($lifted)->gt($since)) {
            $since = \Illuminate\Support\Carbon::parse($lifted);
        }
        $failures = ActivityLog::where('action', 'login_failed')->where('ip', $ip)->where('created_at', '>', $since)->get(['subject_label', 'created_at']);
        if ($failures->count() < self::MAX_FAILURES) {
            return null;
        }

        $block = BlockedIp::create([
            'ip' => $ip,
            'attempts' => $failures->count(),
            'emails' => $failures->pluck('subject_label')->filter()->unique()->take(10)->values()->all(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 300),
            'reason' => $failures->count().' failed sign ins within '.self::WINDOW_MINUTES.' minutes',
            'first_attempt_at' => $failures->min('created_at'),
            'blocked_at' => now(),
            'expires_at' => now()->addHours(self::BLOCK_HOURS),
        ]);
        ActivityLog::record('ip_blocked', 'IP address', $ip, $block->reason, ['module' => 'Security', 'context' => ['emails' => $block->emails]]);
        Notifier::send('security', 'critical', "IP address {$ip} blocked from the admin", $block->reason.'. Emails tried: '.(implode(', ', $block->emails ?? []) ?: 'none').'.', 'Security', route('admin.security').'#blocked', 'security');
        static::email($block);

        return $block;
    }

    // Emails the alert to the addresses in Settings > Email (otherwise every administrator).
    public static function email(BlockedIp $block): void
    {
        if (! InboxNotifier::ready() || ! ($to = InboxNotifier::recipients())) {
            return;
        }
        try {
            Mail::send('emails.ip-blocked', ['block' => $block], fn ($m) => $m->to($to)->subject("Security alert: {$block->ip} blocked after failed admin sign ins"));
            $block->forceFill(['notified_at' => now()])->save();
        } catch (\Throwable $e) {
            // The block stands even when the email cannot be sent; the in-admin notification is already there.
            report($e);
        }
    }

    public static function unblock(BlockedIp $block, string $by): void
    {
        $block->forceFill(['unblocked_at' => now(), 'unblocked_by' => mb_substr($by, 0, 120)])->save();
        ActivityLog::record('ip_unblocked', 'IP address', $block->ip, 'Block lifted', ['module' => 'Security']);
    }

    public static function keep(BlockedIp $block): void
    {
        $block->forceFill(['expires_at' => null])->save();
        ActivityLog::record('ip_blocked', 'IP address', $block->ip, 'Block made permanent', ['module' => 'Security']);
    }
}
