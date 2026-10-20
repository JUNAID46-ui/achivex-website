<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Content\ContactSubmission;
use App\Models\Content\WhitepaperRequest;
use App\Support\Csv;
use Illuminate\Http\Request;

class InboxController extends Controller
{
    public function messages(Request $request)
    {
        $filter = $request->input('filter', 'all');
        $messages = ContactSubmission::query()
            ->when($filter === 'unread', fn ($q) => $q->whereNull('read_at'))
            ->when($filter === 'starred', fn ($q) => $q->where('starred', true))
            ->when($request->input('q'), fn ($q, $t) => $q->where(fn ($w) => $w->where('name', 'ilike', "%$t%")->orWhere('email', 'ilike', "%$t%")->orWhere('message', 'ilike', "%$t%")))
            ->orderByDesc('created_at')->paginate(25)->withQueryString();

        return view('admin.inbox.messages', [
            'messages' => $messages,
            'filter' => $filter,
            'counts' => [
                'all' => ContactSubmission::count(),
                'unread' => ContactSubmission::whereNull('read_at')->count(),
                'starred' => ContactSubmission::where('starred', true)->count(),
            ],
        ]);
    }

    public function show(ContactSubmission $message)
    {
        if (! $message->read_at) {
            $message->forceFill(['read_at' => now()])->save();
        }
        $previous = ContactSubmission::where('email', $message->email)->where('id', '!=', $message->id)->orderByDesc('created_at')->get();

        return view('admin.inbox.show', compact('message', 'previous'));
    }

    public function star(ContactSubmission $message)
    {
        $message->forceFill(['starred' => ! $message->starred])->save();

        return back();
    }

    public function unread(ContactSubmission $message)
    {
        $message->forceFill(['read_at' => null])->save();

        return redirect()->route('admin.inbox.messages')->with('status', 'Marked as unread.');
    }

    public function destroy(ContactSubmission $message)
    {
        $message->delete();
        ActivityLog::record('deleted', 'Message', $message->name.' <'.$message->email.'>');

        return redirect()->route('admin.inbox.messages')->with('status', 'Message deleted.');
    }

    public function exportMessages()
    {
        return $this->csv('contact-messages', ['Date', 'Name', 'Email', 'Phone', 'Service', 'Message', 'Read'],
            ContactSubmission::orderByDesc('created_at')->get()->map(fn ($m) => [
                $m->created_at, $m->name, $m->email, $m->phone, $m->service, $m->message, $m->read_at ? 'Yes' : 'No',
            ]));
    }

    public function requests(Request $request)
    {
        $requests = WhitepaperRequest::with('whitepaper')
            ->when($request->input('status') === 'pending', fn ($q) => $q->whereNull('handled_at'))
            ->orderByDesc('created_at')->paginate(25)->withQueryString();

        return view('admin.inbox.requests', ['requests' => $requests, 'pending' => WhitepaperRequest::whereNull('handled_at')->count()]);
    }

    public function handled(WhitepaperRequest $guideRequest)
    {
        $guideRequest->forceFill(['handled_at' => $guideRequest->handled_at ? null : now()])->save();

        return back();
    }

    public function exportRequests()
    {
        return $this->csv('guide-requests', ['Date', 'Guide', 'Name', 'Email', 'Company', 'Job title', 'Sent'],
            WhitepaperRequest::with('whitepaper')->orderByDesc('created_at')->get()->map(fn ($r) => [
                $r->created_at, $r->whitepaper?->title ?? $r->whitepaper_slug, $r->name, $r->email, $r->company, $r->job_title, $r->handled_at ? 'Yes' : 'No',
            ]));
    }

    private function csv(string $name, array $header, $rows)
    {
        ActivityLog::record('exported', 'CSV', $name);

        return response()->streamDownload(function () use ($header, $rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $header);
            foreach ($rows as $row) {
                fputcsv($out, Csv::row($row));
            }
            fclose($out);
        }, $name.'-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }
}
