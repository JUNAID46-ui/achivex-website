<?php

namespace App\Http\Middleware;

use App\Models\Site;
use App\Support\Sites;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

// Points the admin at the website chosen in the site switcher for this request. Runs after the
// session and sign in, which stay on the main database.
class UseSelectedSite
{
    public function handle(Request $request, Closure $next)
    {
        $id = $request->session()->get('site_id');
        $site = $id ? Site::find($id) : null;
        if ($id && ! $site) {
            $request->session()->forget('site_id');
        }
        if ($site) {
            Sites::activate($site);
            $flag = 'site_user_'.$site->id;
            if ($request->user() && ! $request->session()->get($flag)) {
                Sites::mirrorUser($request->user());
                $request->session()->put($flag, true);
            }
        }
        View::share('currentSite', $site);
        View::share('siteList', rescue(fn () => Site::orderBy('name')->get(['id', 'name', 'site_url', 'public_url', 'status']), collect(), false));

        try {
            return $next($request);
        } finally {
            Sites::reset();
        }
    }
}
