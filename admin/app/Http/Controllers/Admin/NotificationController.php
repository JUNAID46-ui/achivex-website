<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use Illuminate\Http\Request;

// Each user only ever sees and changes their own notifications.
class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $view = in_array($request->input('view'), ['unread', 'archived'], true) ? $request->input('view') : 'all';
        $items = AdminNotification::for($request->user())
            ->when($view === 'archived', fn ($q) => $q->whereNotNull('archived_at'), fn ($q) => $q->whereNull('archived_at'))
            ->when($view === 'unread', fn ($q) => $q->whereNull('read_at'))
            ->when(in_array($request->input('severity'), AdminNotification::SEVERITIES, true), fn ($q) => $q->where('severity', $request->input('severity')))
            ->latest('created_at')->latest('id')->paginate(30)->withQueryString();

        return view('admin.notifications.index', ['items' => $items, 'view' => $view]);
    }

    // Opening a notification marks it read, then goes to the related page.
    public function open(Request $request, int $id)
    {
        $n = AdminNotification::for($request->user())->findOrFail($id);
        $n->update(['read_at' => $n->read_at ?? now()]);
        // Only links inside the admin are followed, never another site.
        $url = (string) $n->url;
        $internal = str_starts_with($url, url('/').'/') || (str_starts_with($url, '/') && ! str_starts_with($url, '//') && ! str_starts_with($url, '/\\'));
        $url = $internal ? $url : route('admin.notifications');

        return redirect()->to($url);
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate(['action' => 'required|in:read,unread,archive,unarchive']);
        $n = AdminNotification::for($request->user())->findOrFail($id);
        $n->update(match ($data['action']) {
            'read' => ['read_at' => now()],
            'unread' => ['read_at' => null],
            'archive' => ['archived_at' => now(), 'read_at' => $n->read_at ?? now()],
            'unarchive' => ['archived_at' => null],
        });

        return back()->with('status', 'Notification updated.');
    }

    public function readAll(Request $request)
    {
        $count = AdminNotification::for($request->user())->whereNull('read_at')->update(['read_at' => now()]);

        return back()->with('status', "{$count} notifications marked as read.");
    }
}
