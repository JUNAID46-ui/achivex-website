<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Content\ContactSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

// Outgoing email from the admin: replies to contact messages and the SMTP test.
class MailController extends Controller
{
    public function reply(Request $request, ContactSubmission $message)
    {
        $data = $request->validate([
            'subject' => 'required|max:200',
            'body' => 'required|max:10000',
        ]);
        try {
            Mail::send('emails.reply', ['body' => $data['body'], 'original' => $message, 'sender' => $request->user()], function ($m) use ($message, $data) {
                $m->to($message->email, $message->name)->subject($data['subject']);
            });
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->withErrors(['body' => 'The email could not be sent: '.$e->getMessage()]);
        }
        ActivityLog::record('sent', 'Email', $message->email, $data['subject']);

        return back()->with('status', 'Reply sent to '.$message->email.'.');
    }

    public function test(Request $request)
    {
        $to = $request->user()->email;
        try {
            Mail::send('emails.test', [], fn ($m) => $m->to($to)->subject('AchiveX Admin test email'));
        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors(['mail' => 'Test email failed: '.$e->getMessage()]);
        }
        ActivityLog::record('sent', 'Email', $to, 'SMTP test');

        return back()->with('status', 'Test email sent to '.$to.'. Check your inbox.');
    }
}
