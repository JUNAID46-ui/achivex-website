<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AppSetting;
use App\Models\ErrorLog;
use App\Services\Health\HealthCheck;
use Illuminate\Http\Request;

// System Health (administrators only).
class HealthController extends Controller
{
    public function index(Request $request)
    {
        $live = $request->boolean('live');
        $checks = HealthCheck::run($live);
        HealthCheck::notify($checks);

        return view('admin.health.index', [
            'checks' => $checks,
            'live' => $live,
            'env' => HealthCheck::environment(),
            'thresholds' => HealthCheck::thresholds(),
            'daily' => AppSetting::group('daily'),
            'errorLogs' => ErrorLog::latest('id')->paginate(15)->withQueryString(),
            'measuredAt' => now(),
        ]);
    }

    public function saveThresholds(Request $request)
    {
        $data = $request->validate([
            'storage_mb' => 'required|integer|min:50|max:1048576',
            'database_mb' => 'required|integer|min:50|max:1048576',
            'warn' => 'required|integer|min:10|max:99',
            'critical' => 'required|integer|min:11|max:100|gt:warn',
            'backup_days' => 'required|integer|min:1|max:90',
        ]);
        AppSetting::put('health', array_map('intval', $data));
        ActivityLog::record('settings', 'System Health', 'Thresholds', null, ['module' => 'System Health', 'context' => $data]);

        return back()->with('status', 'Thresholds saved.');
    }
}
