<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\InboxHookController;
use App\Http\Controllers\PublicSeoController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

// Public SEO files generated from the admin settings
Route::get('/sitemap.xml', [PublicSeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [PublicSeoController::class, 'robots'])->name('robots');
Route::get('/llms.txt', [PublicSeoController::class, 'llms'])->name('llms');

// New contact messages and guide requests are emailed to the owner (called by a database trigger)
Route::post('/hooks/inbox', InboxHookController::class)->middleware('throttle:30,1')->name('hooks.inbox');

// Daily maintenance, called by Vercel Cron (vercel.json)
Route::match(['get', 'post'], '/hooks/daily', \App\Http\Controllers\DailyHookController::class)->middleware('throttle:5,1')->name('hooks.daily');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [Admin\AuthController::class, 'showLogin'])->name('login');
        Route::post('login', [Admin\AuthController::class, 'login'])->middleware('throttle:20,1');
        Route::get('two-factor', [Admin\AuthController::class, 'showTwoFactor'])->name('two-factor');
        Route::post('two-factor', [Admin\AuthController::class, 'twoFactor'])->middleware('throttle:20,1');
        Route::get('forgot-password', [Admin\PasswordResetController::class, 'request'])->name('password.request');
        Route::post('forgot-password', [Admin\PasswordResetController::class, 'send'])->name('password.email')->middleware('throttle:5,1');
        Route::get('reset-password/{token}', [Admin\PasswordResetController::class, 'edit'])->name('password.reset');
        Route::post('reset-password', [Admin\PasswordResetController::class, 'update'])->name('password.update');
    });

    Route::middleware('auth')->group(function () {
        Route::post('logout', [Admin\AuthController::class, 'logout'])->name('logout');
        Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');
        Route::post('quick-draft', [Admin\DashboardController::class, 'quickDraft'])->name('quick-draft')->middleware('area:posts');

        // Page builder
        Route::middleware('area:builder')->group(function () {
            Route::get('builder', [Admin\BuilderController::class, 'index'])->name('builder');
            Route::post('builder/sections/{section}', [Admin\BuilderController::class, 'save'])->name('builder.save');
            Route::post('builder/order', [Admin\BuilderController::class, 'order'])->name('builder.order');
            Route::post('builder/sections/{section}/toggle', [Admin\BuilderController::class, 'toggle'])->name('builder.toggle');
            Route::post('builder/hero-stats', [Admin\BuilderController::class, 'heroStats'])->name('builder.hero-stats');
            Route::post('builder/add', [Admin\BuilderController::class, 'add'])->name('builder.add');
            Route::delete('builder/sections/{section}', [Admin\BuilderController::class, 'destroy'])->name('builder.destroy');

            // Pages
            Route::get('pages', [Admin\PageController::class, 'index'])->name('pages.index');
            Route::get('pages/create', [Admin\PageController::class, 'create'])->name('pages.create');
            Route::post('pages', [Admin\PageController::class, 'store'])->name('pages.store');
            Route::get('pages/edit', [Admin\PageController::class, 'edit'])->name('pages.edit');
            Route::post('pages/update', [Admin\PageController::class, 'update'])->name('pages.update');
            Route::post('pages/reset', [Admin\PageController::class, 'reset'])->name('pages.reset');
            Route::post('pages/delete', [Admin\PageController::class, 'destroy'])->name('pages.destroy');
            Route::post('pages/revisions/{revision}', [Admin\PageController::class, 'restoreRevision'])->whereNumber('revision')->name('pages.restore-revision');
        });

        // Generic content types
        Route::get('r/{resource}', [Admin\ResourceController::class, 'index'])->name('resources.index');
        Route::get('r/{resource}/create', [Admin\ResourceController::class, 'create'])->name('resources.create');
        Route::post('r/{resource}', [Admin\ResourceController::class, 'store'])->name('resources.store');
        Route::post('r/{resource}/bulk', [Admin\ResourceController::class, 'bulk'])->name('resources.bulk');
        Route::get('r/{resource}/{id}/edit', [Admin\ResourceController::class, 'edit'])->name('resources.edit');
        Route::put('r/{resource}/{id}', [Admin\ResourceController::class, 'update'])->name('resources.update');
        Route::delete('r/{resource}/{id}', [Admin\ResourceController::class, 'destroy'])->name('resources.destroy');
        Route::get('r/{resource}/deleted', [Admin\ResourceController::class, 'deleted'])->name('resources.deleted');
        Route::post('r/{resource}/deleted/{revision}', [Admin\ResourceController::class, 'restoreDeleted'])->whereNumber('revision')->name('resources.restore-deleted');
        Route::get('r/{resource}/{id}/revisions', [Admin\ResourceController::class, 'revisions'])->name('resources.revisions');
        Route::post('r/{resource}/{id}/revisions/{revision}', [Admin\ResourceController::class, 'restore'])->whereNumber('revision')->name('resources.restore');
        Route::get('r/{resource}/{id}/preview', [Admin\ResourceController::class, 'preview'])->name('resources.preview');

        // Media library
        Route::middleware('area:media')->group(function () {
            Route::get('media', [Admin\MediaController::class, 'index'])->name('media.index');
            Route::get('media/list', [Admin\MediaController::class, 'list'])->name('media.list');
            Route::post('media', [Admin\MediaController::class, 'store'])->name('media.store');
            Route::post('media/sign', [Admin\MediaController::class, 'sign'])->name('media.sign')->middleware('throttle:120,1');
            Route::post('media/complete', [Admin\MediaController::class, 'complete'])->name('media.complete')->middleware('throttle:120,1');
            Route::patch('media/{media}', [Admin\MediaController::class, 'update'])->name('media.update');
            Route::delete('media/{media}', [Admin\MediaController::class, 'destroy'])->name('media.destroy');
        });

        // Inbox
        Route::middleware('area:inbox')->prefix('inbox')->name('inbox.')->group(function () {
            Route::get('messages', [Admin\InboxController::class, 'messages'])->name('messages');
            Route::get('messages/export', [Admin\InboxController::class, 'exportMessages'])->name('messages.export');
            Route::get('messages/{message}', [Admin\InboxController::class, 'show'])->name('messages.show');
            Route::post('messages/{message}/star', [Admin\InboxController::class, 'star'])->name('messages.star');
            Route::post('messages/{message}/reply', [Admin\MailController::class, 'reply'])->name('messages.reply')->middleware('throttle:20,1');
            Route::post('messages/{message}/unread', [Admin\InboxController::class, 'unread'])->name('messages.unread');
            Route::delete('messages/{message}', [Admin\InboxController::class, 'destroy'])->name('messages.destroy');
            Route::get('requests', [Admin\InboxController::class, 'requests'])->name('requests');
            Route::get('requests/export', [Admin\InboxController::class, 'exportRequests'])->name('requests.export');
            Route::post('requests/{guideRequest}/handled', [Admin\InboxController::class, 'handled'])->name('requests.handled');
        });

        // SEO
        Route::middleware('area:seo')->prefix('seo')->name('seo.')->group(function () {
            Route::get('/', [Admin\SeoController::class, 'overview'])->name('overview');
            Route::get('pages', [Admin\SeoController::class, 'pages'])->name('pages');
            Route::get('pages/edit', [Admin\SeoController::class, 'editPage'])->name('pages.edit');
            Route::post('pages/edit', [Admin\SeoController::class, 'savePage'])->name('pages.save');
            Route::get('appearance', [Admin\SeoController::class, 'appearance'])->name('appearance');
            Route::post('appearance', [Admin\SeoController::class, 'saveAppearance']);
            Route::get('sitemap', [Admin\SeoController::class, 'sitemap'])->name('sitemap');
            Route::get('sitemap/download', [Admin\SeoController::class, 'downloadSitemap'])->name('sitemap.download');
            Route::get('robots', [Admin\SeoController::class, 'robots'])->name('robots');
            Route::post('robots', [Admin\SeoController::class, 'saveRobots']);
            Route::get('404', [Admin\SeoController::class, 'notFound'])->name('notfound');
            Route::post('404/redirect', [Admin\SeoController::class, 'redirectNotFound'])->name('notfound.redirect');
            Route::post('404/clear', [Admin\SeoController::class, 'clearNotFound'])->name('notfound.clear');
            Route::get('local', [Admin\SeoController::class, 'schema'])->name('schema');
            Route::post('local', [Admin\SeoController::class, 'saveSchema']);
            Route::get('webmaster', [Admin\SeoController::class, 'webmaster'])->name('webmaster');
            Route::post('webmaster', [Admin\SeoController::class, 'saveWebmaster']);
            Route::get('audit', [Admin\AuditController::class, 'seo'])->name('audit');
            Route::get('ai-visibility', [Admin\AuditController::class, 'ai'])->name('ai');
            Route::post('audits/{type}', [Admin\AuditController::class, 'run'])->name('audits.run')->middleware('throttle:6,1');
            Route::get('audits/{run}/download/{format}', [Admin\AuditController::class, 'download'])->whereNumber('run')->name('audits.download');
            Route::post('llms', [Admin\AuditController::class, 'saveLlms'])->name('llms');
            Route::get('llms/draft', [Admin\AuditController::class, 'draftLlms'])->name('llms.draft');
        });

        Route::get('analytics', [Admin\AnalyticsController::class, 'index'])->name('analytics')->middleware('area:analytics');
        Route::get('analytics/export', [Admin\AnalyticsController::class, 'export'])->name('analytics.export')->middleware('area:analytics');

        Route::middleware('area:settings')->group(function () {
            Route::get('settings', [Admin\SettingsController::class, 'index'])->name('settings');
            Route::post('settings', [Admin\SettingsController::class, 'save']);
            Route::post('settings/storage', [Admin\SettingsController::class, 'saveStorage'])->name('settings.storage');
            Route::post('settings/mail', [Admin\SettingsController::class, 'saveMail'])->name('settings.mail');
            Route::post('settings/test-email', [Admin\MailController::class, 'test'])->name('settings.test-email')->middleware('throttle:5,1');
        });

        Route::get('activity', [Admin\ActivityController::class, 'index'])->name('activity')->middleware('area:activity');
        Route::get('activity/export', [Admin\ActivityController::class, 'export'])->name('activity.export')->middleware(['area:activity', 'throttle:10,1']);

        // System Health (administrators only)
        Route::middleware('area:tools')->group(function () {
            Route::get('health', [Admin\HealthController::class, 'index'])->name('health');
            Route::post('health/thresholds', [Admin\HealthController::class, 'saveThresholds'])->name('health.thresholds');
        });

        // Security Center (administrators only)
        Route::middleware('area:security')->group(function () {
            Route::get('security', [Admin\SecurityController::class, 'index'])->name('security');
            Route::post('security/scan', [Admin\SecurityController::class, 'run'])->name('security.run')->middleware('throttle:6,1');
            Route::post('security/users/{user}/sessions', [Admin\SecurityController::class, 'endSessions'])->name('security.sessions')->middleware('throttle:10,1');
        });

        // Backup and cache tools (administrators only)
        Route::middleware('area:tools')->group(function () {
            Route::get('backup', [Admin\BackupController::class, 'index'])->name('backup');
            Route::get('backup/download', [Admin\BackupController::class, 'download'])->name('backup.download')->middleware('throttle:10,1');
            Route::post('backup/setup', [Admin\BackupController::class, 'setup'])->name('backup.setup');
            Route::post('backup/runs', [Admin\BackupController::class, 'store'])->name('backup.store')->middleware('throttle:6,1');
            Route::post('backup/settings', [Admin\BackupController::class, 'saveSettings'])->name('backup.settings');
            Route::post('backup/runs/{run}/verify', [Admin\BackupController::class, 'verify'])->name('backup.verify')->middleware('throttle:10,1');
            Route::get('backup/runs/{run}/download', [Admin\BackupController::class, 'downloadStored'])->name('backup.stored')->middleware('throttle:10,1');
            Route::post('backup/runs/{run}/delete', [Admin\BackupController::class, 'destroy'])->name('backup.destroy');
            Route::get('backup/runs/{run}/preview', [Admin\BackupController::class, 'preview'])->name('backup.preview');
            Route::post('backup/runs/{run}/restore', [Admin\BackupController::class, 'restore'])->name('backup.restore')->middleware('throttle:5,1');
            Route::post('backup/upload/sign', [Admin\BackupController::class, 'uploadSign'])->name('backup.upload.sign')->middleware('throttle:10,1');
            Route::post('backup/upload/{run}/complete', [Admin\BackupController::class, 'uploadComplete'])->name('backup.upload.complete');
            Route::post('backup/restore', [Admin\BackupController::class, 'restoreUpload'])->name('backup.restore-upload')->middleware('throttle:5,1');
            Route::get('cache', [Admin\CacheController::class, 'index'])->name('cache');
            Route::post('cache/clear', [Admin\CacheController::class, 'clear'])->name('cache.clear');
            Route::get('custom-code', [Admin\CustomCodeController::class, 'index'])->name('custom-code');
            Route::post('custom-code', [Admin\CustomCodeController::class, 'save']);
        });

        // Notifications (every signed in user, own notifications only)
        Route::get('notifications', [Admin\NotificationController::class, 'index'])->name('notifications');
        Route::post('notifications/read-all', [Admin\NotificationController::class, 'readAll'])->name('notifications.read-all');
        Route::get('notifications/{id}/open', [Admin\NotificationController::class, 'open'])->whereNumber('id')->name('notifications.open');
        Route::post('notifications/{id}', [Admin\NotificationController::class, 'update'])->whereNumber('id')->name('notifications.update');

        Route::get('profile', [Admin\ProfileController::class, 'edit'])->name('profile');
        Route::post('profile', [Admin\ProfileController::class, 'update']);
        Route::post('profile/two-factor', [Admin\ProfileController::class, 'startTwoFactor'])->name('profile.two-factor');
        Route::post('profile/sessions', [Admin\ProfileController::class, 'endOtherSessions'])->name('profile.sessions')->middleware('throttle:10,1');
        Route::post('profile/two-factor/confirm', [Admin\ProfileController::class, 'confirmTwoFactor'])->name('profile.two-factor.confirm')->middleware('throttle:10,1');
        Route::post('profile/two-factor/disable', [Admin\ProfileController::class, 'disableTwoFactor'])->name('profile.two-factor.disable')->middleware('throttle:10,1');
    });
});
