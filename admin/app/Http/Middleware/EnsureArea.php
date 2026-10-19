<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

// Blocks a section of the admin unless the user's role allows it.
class EnsureArea
{
    public function handle(Request $request, Closure $next, string $area)
    {
        abort_unless($request->user()?->canAccess($area), 403, 'You do not have permission to open this page.');

        return $next($request);
    }
}
