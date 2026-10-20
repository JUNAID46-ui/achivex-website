<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use App\Services\TwoFactor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email', 'not_regex:/[\r\n]/'],
            'password' => ['required'],
        ]);

        $key = Str::lower($request->input('email')).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'email' => 'Too many attempts. Try again in '.RateLimiter::availableIn($key).' seconds.',
            ]);
        }

        if (! Auth::validate($credentials)) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages(['email' => 'These details do not match our records.']);
        }

        RateLimiter::clear($key);
        $user = User::where('email', $credentials['email'])->firstOrFail();

        // With two-step login on, the password alone does not sign in: the code comes next.
        if ($user->hasTwoFactor()) {
            $request->session()->regenerate();
            $request->session()->put('2fa', ['id' => $user->id, 'remember' => $request->boolean('remember'), 'until' => now()->addMinutes(5)->timestamp]);

            return redirect()->route('admin.two-factor');
        }

        return $this->signIn($request, $user, $request->boolean('remember'));
    }

    public function showTwoFactor(Request $request)
    {
        if (! $this->pending($request)) {
            return redirect()->route('admin.login');
        }

        return view('admin.auth.two-factor');
    }

    public function twoFactor(Request $request)
    {
        $request->validate(['code' => 'required|string|max:20']);
        if (! $user = $this->pending($request)) {
            return redirect()->route('admin.login')->withErrors(['email' => 'The sign in took too long. Please log in again.']);
        }

        $key = '2fa|'.$user->id;
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages(['code' => 'Too many attempts. Try again in '.RateLimiter::availableIn($key).' seconds.']);
        }

        $code = $request->input('code');
        $ok = strlen(preg_replace('/\D/', '', $code)) === 6 && ! str_contains($code, '-')
            ? TwoFactor::verify($user->two_factor_secret, $code, $user->id)
            : TwoFactor::useRecoveryCode($user, $code);
        if (! $ok) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages(['code' => 'This code is not correct.']);
        }

        RateLimiter::clear($key);
        $remember = (bool) $request->session()->pull('2fa')['remember'];

        return $this->signIn($request, $user, $remember);
    }

    // The user who passed the password step and still needs a code, if the 5 minutes are not up.
    private function pending(Request $request): ?User
    {
        $p = $request->session()->get('2fa');
        if (! $p || $p['until'] < now()->timestamp) {
            return null;
        }

        return User::find($p['id']);
    }

    private function signIn(Request $request, User $user, bool $remember)
    {
        Auth::login($user, $remember);
        $request->session()->regenerate();
        $user->forceFill(['last_login_at' => now()])->save();
        ActivityLog::record('login', 'User', $user->name, 'Signed in');

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('status', 'You have been signed out.');
    }
}
