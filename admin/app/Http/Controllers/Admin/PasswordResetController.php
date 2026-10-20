<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;

// "Forgot password" for admin users. The reset link is sent over SMTP.
class PasswordResetController extends Controller
{
    public function request()
    {
        return view('admin.auth.forgot');
    }

    public function send(Request $request)
    {
        $request->validate(['email' => 'required|email|not_regex:/[\r\n]/']);
        try {
            Password::sendResetLink($request->only('email'));
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->withErrors(['email' => 'The email could not be sent. Check the MAIL settings in the .env file.']);
        }

        // Same message either way, so the form does not reveal which emails have accounts.
        return back()->with('status', 'If that email belongs to an admin account, a reset link is on its way. Check your inbox and spam folder.');
    }

    public function edit(Request $request, string $token)
    {
        return view('admin.auth.reset', ['token' => $token, 'email' => $request->query('email')]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|not_regex:/[\r\n]/',
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $status = Password::reset($request->only('email', 'password', 'password_confirmation', 'token'), function (User $user, string $password) {
            $user->forceFill(['password' => $password])->save();
            ActivityLog::record('updated', 'User', $user->name, 'Password reset by email');
        });

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('admin.login')->with('status', 'Your password has been changed. You can log in now.')
            : back()->withInput($request->only('email'))->withErrors(['email' => __($status)]);
    }
}
