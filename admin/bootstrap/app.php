<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias(['area' => \App\Http\Middleware\EnsureArea::class, 'site' => \App\Http\Middleware\UseSelectedSite::class]);
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        // The inbox hook is called by the database, not a browser form, and takes no input.
        $middleware->validateCsrfTokens(except: ['hooks/inbox', 'hooks/daily']);
        // On Vercel requests reach PHP through Vercel's proxy, so trust it to get https links.
        if (env('VERCEL')) {
            $middleware->trustProxies(at: '*');
        }
        $middleware->redirectGuestsTo(fn () => route('admin.login'));
        $middleware->redirectUsersTo(fn () => route('admin.dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Keep a scrubbed copy of every reported error for the System Health page.
        $exceptions->reportable(fn (\Throwable $e) => \App\Models\ErrorLog::capture($e));
    })->create();
