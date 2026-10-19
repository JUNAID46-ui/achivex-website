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

        // Unread message count for the admin bar and sidebar badge.
        View::composer('admin.layouts.app', function ($view) {
            $count = 0;
            try {
                $count = ContactSubmission::whereNull('read_at')->count();
            } catch (\Throwable) {
                // The inbox column may not exist before the first migration.
            }
            $view->with('unreadCount', $count);
        });
    }
}
