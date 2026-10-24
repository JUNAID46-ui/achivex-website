<?php

namespace App\Http\Controllers;

use App\Services\InboxNotifier;

// Called by the database after a new contact message or guide request. It takes no input:
// it only sends emails for rows that have not been emailed yet, so calling it is harmless.
class InboxHookController extends Controller
{
    public function __invoke()
    {
        $sent = InboxNotifier::run();

        return response()->json(['sent' => $sent, 'status' => InboxNotifier::status()]);
    }
}
