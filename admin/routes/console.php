<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Hostinger cron jobs (hPanel > Advanced > Cron Jobs). On Vercel the same work runs through /hooks/daily.
Artisan::command('site:daily', function () {
    $this->line(json_encode(\App\Services\Health\DailyTasks::run()));
})->purpose('Run the daily admin tasks (backups, checks, clean-up)');

Artisan::command('site:publish', function () {
    $pages = \App\Services\InstantPublish::sync();
    // Pages written here went live on their own (scheduled posts), so the full build follows them.
    if ($pages > 0) {
        \App\Services\SiteRebuild::later('Scheduled content went live');
    }
    // A change that had to wait for the gap between builds is sent now.
    \App\Services\SiteRebuild::flush();
    $this->line("Pages written: {$pages}");
})->purpose('Write new pages to the website folder and send any waiting website build');

Artisan::command('site:build', function () {
    $this->line(\App\Services\SiteBuilder::runIfRequested());
})->purpose('Build the website on this server when the admin asked for it');
