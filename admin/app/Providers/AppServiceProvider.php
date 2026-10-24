<?php

namespace App\Providers;

use App\Models\Content\ContactSubmission;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        \Illuminate\Database\Connection::resolverFor('pgsql', fn ($pdo, $database, $prefix, $config) => new \App\Database\FastPostgresConnection($pdo, $database, $prefix, $config));
    }

    public function boot(): void
    {
        // Website content changed: rebuild the static website so the change is a real page (see SiteRebuild).
        \Illuminate\Support\Facades\Event::listen(['eloquent.saved: *', 'eloquent.deleted: *'], function (string $event, array $data) {
            $model = $data[0] ?? null;
            if (! $model instanceof \Illuminate\Database\Eloquent\Model || ! str_starts_with($model::class, 'App\\Models\\Content\\')) {
                return;
            }
            // Visitor activity is stored in content tables too but never changes a page.
            if (in_array(class_basename($model), ['ContactSubmission', 'PageView', 'NotFoundLog', 'WhitepaperRequest'], true)) {
                return;
            }
            \App\Services\SiteRebuild::later(class_basename($model).' '.(str_contains($event, 'deleted') ? 'deleted' : 'saved'));
        });
        $this->app->terminating(fn () => \App\Services\SiteRebuild::flush());

        // Admin passwords: at least 10 characters with letters and numbers, and not
        // found in known data breaches (checked with Have I Been Pwned, only a hash prefix is sent).
        \Illuminate\Validation\Rules\Password::defaults(fn () => \Illuminate\Validation\Rules\Password::min(10)->letters()->numbers()->uncompromised());

        // Some unzip tools skip empty folders; Laravel needs these to store sessions, cache and views.
        foreach (['framework/sessions', 'framework/cache/data', 'framework/views', 'logs', 'app/public'] as $dir) {
            if (! is_dir($path = storage_path($dir))) {
                @mkdir($path, 0775, true);
            }
        }

        // Email server saved on the Settings screen overrides the .env values.
        try {
            if ($mail = \App\Models\AppSetting::group('mail')) {
                config([
                    'mail.default' => 'smtp',
                    'mail.mailers.smtp.host' => $mail['host'] ?? config('mail.mailers.smtp.host'),
                    'mail.mailers.smtp.port' => (int) ($mail['port'] ?? 587),
                    'mail.mailers.smtp.username' => $mail['username'] ?? null,
                    'mail.mailers.smtp.password' => $mail['password'] ?? null,
                    'mail.from.address' => $mail['from_address'] ?? config('mail.from.address'),
                    'mail.from.name' => $mail['from_name'] ?? config('mail.from.name'),
                ]);
            }
        } catch (\Throwable) {
            // Settings table not created yet (before the first migration).
        }

        // Logo, contact details and signature for every email template.
        View::composer('emails.*', fn ($view) => $view->with('brand', \App\Support\EmailBrand::data()));

        // Unread message count for the admin bar and sidebar badge.
        View::composer('admin.layouts.app', function ($view) {
            $count = 0;
            try {
                $count = ContactSubmission::whereNull('read_at')->count();
            } catch (\Throwable) {
                // The inbox column may not exist before the first migration.
            }
            $view->with('unreadCount', $count);
            $notes = 0;
            try {
                $notes = auth()->check() ? \App\Services\Notifier::unread(auth()->user()) : 0;
            } catch (\Throwable) {
                // Notifications table not created yet.
            }
            $view->with('notificationCount', $notes);
        });
    }
}
