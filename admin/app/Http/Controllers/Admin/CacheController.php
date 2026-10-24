<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

// Shows and clears the admin's caches. The website itself has no server cache:
// it reads content straight from the database, so admin changes show there right away.
class CacheController extends Controller
{
    private const KNOWN = [
        'app-settings' => 'Admin settings (email server, image storage)',
        'site-routes' => 'Website page list used by SEO and the sitemap',
        'seo-index' => 'SEO scores for every page and post',
    ];

    public function index()
    {
        $prefix = config('cache.prefix');
        $rows = DB::table(config('cache.stores.database.table', 'cache'))
            ->select('key', 'expiration', DB::raw('length(value) as size'))->orderBy('key')->get()
            ->map(function ($r) use ($prefix) {
                $key = Str::after($r->key, $prefix);

                return (object) [
                    'key' => $key,
                    'label' => self::KNOWN[$key] ?? (str_contains($key, 'timer') || preg_match('/^[0-9a-f]{40}$|\|/', $key) ? 'Login and rate limit counter' : 'Other'),
                    'size' => (int) $r->size,
                    'expires' => $r->expiration ? now()->setTimestamp($r->expiration) : null,
                    'expired' => $r->expiration && $r->expiration < time(),
                ];
            });
        $viewPath = config('view.compiled');
        $views = is_dir($viewPath) ? count(File::glob($viewPath.'/*.php')) : 0;

        return view('admin.cache.index', ['entries' => $rows, 'known' => self::KNOWN, 'views' => $views]);
    }

    public function clear(Request $request)
    {
        $target = $request->validate(['target' => 'required|string|max:200'])['target'];
        $table = config('cache.stores.database.table', 'cache');

        $message = match (true) {
            $target === 'all' => tap('All cached data cleared. Pages rebuild it on their next visit.', fn () => Cache::flush()),
            $target === 'expired' => DB::table($table)->where('expiration', '<', time())->delete().' expired entries removed.',
            $target === 'views' => tap('Compiled page templates cleared.', fn () => Artisan::call('view:clear')),
            array_key_exists($target, self::KNOWN) => tap(self::KNOWN[$target].' cleared.', fn () => Cache::forget($target)),
            default => abort(422, 'Unknown cache.'),
        };
        ActivityLog::record('deleted', 'Cache', $target);

        return back()->with('status', $message);
    }
}
