<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

// Browser hardening for every admin response: no framing by other sites, no MIME sniffing,
// no referrer leaks to outside links, and the admin is kept out of search results.
class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $headers = [
            'X-Frame-Options' => 'SAMEORIGIN',
            'X-Content-Type-Options' => 'nosniff',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), payment=()',
            'Cross-Origin-Opener-Policy' => 'same-origin',
        ];
        // The public sitemap and robots files are meant to be read by search engines.
        if (! $request->routeIs('sitemap', 'robots', 'llms')) {
            $headers['X-Robots-Tag'] = 'noindex, nofollow';
        }
        if ($request->isSecure()) {
            $headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';
        }
        foreach ($headers as $name => $value) {
            if (! $response->headers->has($name)) {
                $response->headers->set($name, $value);
            }
        }

        return $response;
    }
}
