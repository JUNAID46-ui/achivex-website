<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AuditRun;
use App\Models\User;
use App\Services\Audit\AuditRunner;
use App\Services\Security\Sessions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

// Security Center (administrators only). Shows sign ins, accounts, sensitive actions and scan results.
// It never shows passwords, tokens, keys or session ids.
class SecurityController extends Controller
{
    public function index(Request $request)
    {
        $run = $request->input('run') ? AuditRun::where('type', 'security')->findOrFail((int) $request->input('run'))
            : AuditRun::where('type', 'security')->where('status', 'completed')->latest('id')->first();
        $failed = ActivityLog::where('action', 'login_failed');

        return view('admin.security.index', [
            'users' => User::orderBy('role')->orderBy('name')->get(),
            'sessionCounts' => Sessions::counts(),
            'sessionsEnabled' => Sessions::enabled(),
            'logins' => ActivityLog::where('action', 'login')->latest('id')->limit(15)->get(),
            'failures' => (clone $failed)->latest('id')->limit(15)->get(),
            'failed24' => (clone $failed)->where('created_at', '>=', now()->subDay())->count(),
            'failed7' => (clone $failed)->where('created_at', '>=', now()->subDays(7))->count(),
            'sensitive' => ActivityLog::whereIn('action', ActivityLog::SENSITIVE)->whereNotIn('action', ['login', 'login_failed'])->latest('id')->limit(15)->get(),
            'run' => $run,
            'runs' => AuditRun::where('type', 'security')->latest('id')->limit(10)->get(['id', 'status', 'summary', 'started_at', 'error']),
        ]);
    }

    public function run()
    {
        $run = AuditRunner::run('security');

        return redirect()->route('admin.security', ['run' => $run->id])->with('status', $run->status === 'completed'
            ? "Security check finished with {$run->summary['total']} findings." : 'The security check stopped with an error.');
    }

    // Signs a user out on every device. Needs the administrator's own password.
    public function endSessions(Request $request, User $user)
    {
        $request->validate(['password' => 'required']);
        if (! Hash::check($request->input('password'), $request->user()->password)) {
            return back()->withErrors(['password' => 'Your password is not correct.']);
        }
        $count = Sessions::endOthers($user);
        ActivityLog::record('logout_all', 'User', $user->name, "Signed out on all other devices ({$count} sessions)", ['module' => 'Security', 'id' => $user->id]);

        return back()->with('status', "{$user->name} has been signed out on other devices.");
    }
}
