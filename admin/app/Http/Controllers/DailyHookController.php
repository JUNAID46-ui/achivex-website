<?php

namespace App\Http\Controllers;

use App\Services\Health\DailyTasks;
use Illuminate\Http\Request;

// Called once a day by Vercel Cron. When CRON_SECRET is set, Vercel sends it as a bearer token and
// any other caller is refused. Without it, the call is still harmless: every step is idempotent and
// the tasks run at most once every 20 hours.
class DailyHookController extends Controller
{
    public function __invoke(Request $request)
    {
        $secret = (string) config('services.cron_secret', '');
        if ($secret !== '' && ! hash_equals('Bearer '.$secret, (string) $request->header('Authorization'))) {
            abort(401);
        }

        return response()->json(DailyTasks::run());
    }
}
