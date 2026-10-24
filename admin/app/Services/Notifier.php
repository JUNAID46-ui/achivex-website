<?php

namespace App\Services;

use App\Models\AdminNotification;
use App\Models\User;

// Creates in-app notifications for every user whose role can open the related area.
class Notifier
{
    /**
     * @param  string  $area  permission area of the related page; only users who can open it are notified
     * @param  string|null  $dedupe  same key within $quietHours creates nothing new (for repeating checks)
     */
    public static function send(string $type, string $severity, string $title, ?string $body = null, ?string $module = null, ?string $url = null, string $area = 'tools', ?string $dedupe = null, int $quietHours = 24): int
    {
        $severity = in_array($severity, AdminNotification::SEVERITIES, true) ? $severity : 'info';
        if ($dedupe && AdminNotification::where('dedupe_key', $dedupe)->where('created_at', '>=', now()->subHours($quietHours))->exists()) {
            return 0;
        }
        $count = 0;
        foreach (User::all() as $user) {
            if (! $user->canAccess($area)) {
                continue;
            }
            AdminNotification::create([
                'user_id' => $user->id, 'type' => $type, 'severity' => $severity, 'title' => mb_substr($title, 0, 250),
                'body' => $body ? mb_substr($body, 0, 2000) : null, 'module' => $module, 'url' => $url, 'dedupe_key' => $dedupe,
            ]);
            $count++;
        }

        return $count;
    }

    public static function unread(User $user): int
    {
        return AdminNotification::for($user)->whereNull('read_at')->whereNull('archived_at')->count();
    }
}
