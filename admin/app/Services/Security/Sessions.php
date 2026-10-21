<?php

namespace App\Services\Security;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

// Active admin sessions (database session driver). Session ids are never shown; only a short hash.
class Sessions
{
    public static function enabled(): bool
    {
        return config('session.driver') === 'database';
    }

    public static function forUser(User $user): array
    {
        if (! static::enabled()) {
            return [];
        }
        $cutoff = now()->subMinutes((int) config('session.lifetime'))->timestamp;

        return DB::table(config('session.table', 'sessions'))->where('user_id', $user->id)->where('last_activity', '>=', $cutoff)
            ->orderByDesc('last_activity')->get(['id', 'ip_address', 'user_agent', 'last_activity'])
            ->map(fn ($s) => [
                'ref' => substr(hash('sha256', $s->id), 0, 8),
                'ip' => $s->ip_address,
                'agent' => Str::limit((string) $s->user_agent, 80),
                'last' => \Carbon\Carbon::createFromTimestamp($s->last_activity),
                'current' => session()->isStarted() && hash_equals((string) $s->id, (string) session()->getId()),
            ])->all();
    }

    public static function counts(): array
    {
        if (! static::enabled()) {
            return [];
        }
        $cutoff = now()->subMinutes((int) config('session.lifetime'))->timestamp;

        return DB::table(config('session.table', 'sessions'))->whereNotNull('user_id')->where('last_activity', '>=', $cutoff)
            ->selectRaw('user_id, count(*) as n')->groupBy('user_id')->pluck('n', 'user_id')->all();
    }

    // Ends every session of the user except the current browser's, and stops "remember me" cookies.
    public static function endOthers(User $user): int
    {
        $user->forceFill(['remember_token' => Str::random(60)])->saveQuietly();
        if (! static::enabled()) {
            return 0;
        }

        return DB::table(config('session.table', 'sessions'))->where('user_id', $user->id)
            ->when(session()->isStarted(), fn ($q) => $q->where('id', '!=', session()->getId()))->delete();
    }
}
