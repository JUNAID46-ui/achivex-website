<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Services\TwoFactor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = auth()->user();
        $pending = session('2fa-setup');

        return view('admin.profile.edit', [
            'user' => $user,
            'setupQr' => $pending ? TwoFactor::qrSvg($pending, $user->email) : null,
            'setupSecret' => $pending,
            'recoveryCodes' => session('2fa-recovery'),
            'sessions' => \App\Services\Security\Sessions::forUser($user),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => 'required|max:120',
            'email' => ['required', 'email', 'max:200', 'not_regex:/[\r\n]/', Rule::unique('users')->ignore($user->id)],
            'current_password' => 'nullable|required_with:password',
            'password' => ['nullable', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
        ]);
        if (! empty($data['password'])) {
            if (! Hash::check($data['current_password'], $user->password)) {
                return back()->withErrors(['current_password' => 'Your current password is not correct.']);
            }
            $user->password = $data['password'];
        }
        $passwordChanged = $user->isDirty('password');
        $user->fill(['name' => $data['name'], 'email' => $data['email']])->save();
        ActivityLog::record($passwordChanged ? 'password_changed' : 'updated', 'Profile', $user->name, $passwordChanged ? 'Changed own password; other sessions were signed out' : null, ['module' => 'Security', 'id' => $user->id]);

        return back()->with('status', 'Profile updated.');
    }

    // Step 1: a new secret is kept in the session until the user proves the app works.
    public function startTwoFactor(Request $request)
    {
        $request->session()->put('2fa-setup', TwoFactor::newSecret());

        return redirect()->to(route('admin.profile').'#two-factor');
    }

    // Step 2: the first code from the app turns two-step login on.
    public function confirmTwoFactor(Request $request)
    {
        $request->validate(['code' => 'required|string|max:10']);
        $secret = $request->session()->get('2fa-setup');
        $user = $request->user();
        if (! $secret || ! TwoFactor::verify($secret, $request->input('code'), $user->id)) {
            return redirect()->to(route('admin.profile').'#two-factor')->withErrors(['code' => 'This code is not correct. Check the time on your phone and try again.']);
        }

        $codes = TwoFactor::recoveryCodes();
        $user->forceFill(['two_factor_secret' => $secret, 'two_factor_recovery_codes' => $codes, 'two_factor_confirmed_at' => now()])->save();
        $request->session()->forget('2fa-setup');
        ActivityLog::record('two_factor', 'Profile', $user->name, 'Turned on two-step login', ['module' => 'Security', 'id' => $user->id]);

        return redirect()->to(route('admin.profile').'#two-factor')->with('2fa-recovery', $codes)->with('status', 'Two-step login is on.');
    }

    public function disableTwoFactor(Request $request)
    {
        $request->validate(['password' => 'required']);
        $user = $request->user();
        if (! Hash::check($request->input('password'), $user->password)) {
            return redirect()->to(route('admin.profile').'#two-factor')->withErrors(['disable_password' => 'Your password is not correct.']);
        }

        $user->forceFill(['two_factor_secret' => null, 'two_factor_recovery_codes' => null, 'two_factor_confirmed_at' => null])->save();
        ActivityLog::record('two_factor', 'Profile', $user->name, 'Turned off two-step login', ['module' => 'Security', 'id' => $user->id]);

        return redirect()->to(route('admin.profile').'#two-factor')->with('status', 'Two-step login is off.');
    }

    public function endOtherSessions(Request $request)
    {
        $request->validate(['password' => 'required']);
        if (! Hash::check($request->input('password'), $request->user()->password)) {
            return redirect()->to(route('admin.profile').'#sessions')->withErrors(['sessions_password' => 'Your password is not correct.']);
        }
        $count = \App\Services\Security\Sessions::endOthers($request->user());
        ActivityLog::record('logout_all', 'Profile', $request->user()->name, "Signed out other devices ({$count} sessions)", ['module' => 'Security', 'id' => $request->user()->id]);

        return redirect()->to(route('admin.profile').'#sessions')->with('status', 'You have been signed out on your other devices.');
    }
}
