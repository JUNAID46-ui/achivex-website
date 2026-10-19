<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $logs = ActivityLog::with('user')
            ->when($request->input('user'), fn ($q, $u) => $q->where('user_id', $u))
            ->when($request->input('action'), fn ($q, $a) => $q->where('action', $a))
            ->latest('created_at')->paginate(40)->withQueryString();

        return view('admin.activity.index', [
            'logs' => $logs,
            'users' => User::orderBy('name')->pluck('name', 'id'),
            'actions' => ActivityLog::select('action')->distinct()->orderBy('action')->pluck('action'),
        ]);
    }
}
