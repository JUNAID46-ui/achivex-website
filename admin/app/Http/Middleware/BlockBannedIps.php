<?php

namespace App\Http\Middleware;

use App\Services\Security\LoginGuard;
use Closure;
use Illuminate\Http\Request;

// Keeps IP addresses blocked by LoginGuard out of every admin page, including the sign in page.
class BlockBannedIps
{
    public function handle(Request $request, Closure $next)
    {
        $block = rescue(fn () => LoginGuard::blocked((string) $request->ip()), null, false);
        if ($block) {
            return response()->view('admin.errors.blocked', ['block' => $block], 403);
        }

        return $next($request);
    }
}
