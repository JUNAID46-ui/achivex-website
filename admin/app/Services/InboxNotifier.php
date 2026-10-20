<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\Content\ContactSubmission;
use App\Models\Content\WhitepaperRequest;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

// Emails new contact messages and guide requests to the owner's own inbox, once each.
// The database calls /hooks/inbox after every new row; this sends whatever has not been sent yet.
class InboxNotifier
{
    // Addresses from Settings > Email, otherwise every administrator.
    public static function recipients(): array
    {
        $saved = (string) (AppSetting::group('mail')['notify_to'] ?? '');
        $emails = array_values(array_filter(array_map('trim', explode(',', $saved)), fn ($e) => filter_var($e, FILTER_VALIDATE_EMAIL)));

        return $emails ?: User::where('role', 'administrator')->pluck('email')->all();
    }

    public static function ready(): bool
    {
        return config('mail.default') === 'smtp' && (bool) config('mail.mailers.smtp.password');
    }

    public static function run(int $limit = 20): int
    {
        if (! static::ready() || ! ($to = static::recipients())) {
            return 0;
        }

        $sent = 0;
        foreach ([ContactSubmission::class => 'message', WhitepaperRequest::class => 'guide'] as $model => $kind) {
            foreach ($model::whereNull('notified_at')->orderBy('id')->limit($limit)->pluck('id') as $id) {
                // Claim the row first, so two calls at the same moment never send the same email twice.
                if (! $model::whereKey($id)->whereNull('notified_at')->update(['notified_at' => now()])) {
                    continue;
                }
                $item = $model::find($id);
                try {
                    Mail::send('emails.new-inbox-item', ['item' => $item, 'kind' => $kind], function ($m) use ($to, $item, $kind) {
                        $name = Str::limit(trim(preg_replace('/\s+/', ' ', (string) $item->name)), 80);
                        $m->to($to)->subject($kind === 'message' ? "New website message from {$name}" : "New guide request from {$name}");
                        if (filter_var($item->email, FILTER_VALIDATE_EMAIL)) {
                            $m->replyTo($item->email, $name);
                        }
                    });
                    $sent++;
                } catch (\Throwable $e) {
                    // Most likely the mail server is unreachable. Leave the row for the next call.
                    report($e);
                    $model::whereKey($id)->update(['notified_at' => null]);

                    return $sent;
                }
            }
        }

        return $sent;
    }
}
