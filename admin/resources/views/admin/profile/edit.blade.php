@extends('admin.layouts.app')
@section('title', 'Profile')
@section('heading', 'Your Profile')
@section('content')
<form method="POST" class="grid max-w-4xl gap-6 lg:grid-cols-2">
  @csrf
  <div class="card">
    <div class="card-header"><h2 class="card-title">Account</h2></div>
    <div class="card-body space-y-4">
      <div><label for="name" class="label">Full name</label><input id="name" name="name" value="{{ old('name', $user->name) }}" class="input" required></div>
      <div><label for="email" class="label">Email</label><input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" class="input" required></div>
      <p class="text-[13px] text-slate-500">Role: <span class="font-semibold text-navy">{{ \App\Models\User::ROLES[$user->role] ?? $user->role }}</span></p>
    </div>
  </div>
  <div class="card">
    <div class="card-header"><h2 class="card-title">Change password</h2></div>
    <div class="card-body space-y-4">
      <div><label for="current_password" class="label">Current password</label><input id="current_password" type="password" name="current_password" class="input" autocomplete="current-password"></div>
      <div><label for="password" class="label">New password</label><input id="password" type="password" name="password" class="input" autocomplete="new-password"><p class="help">At least 10 characters with letters and numbers. Leave empty to keep your password.</p></div>
      <div><label for="password_confirmation" class="label">Confirm new password</label><input id="password_confirmation" type="password" name="password_confirmation" class="input" autocomplete="new-password"></div>
    </div>
  </div>
  <div class="lg:col-span-2"><button class="btn-primary">Save profile</button></div>
</form>

<div id="two-factor" class="card mt-6 max-w-4xl">
  <div class="card-header">
    <h2 class="card-title">Two-step login</h2>
    @if($user->hasTwoFactor())<span class="badge-green">On</span>@endif
  </div>
  <div class="card-body space-y-4 text-[13px]">
    @if($recoveryCodes)
      <div class="rounded-lg border border-amber-200 bg-amber-50 p-4">
        <p class="font-semibold text-amber-900">Save these recovery codes now. They are shown only once.</p>
        <p class="mt-1 text-amber-900">Each code works one time if you lose your phone.</p>
        <div class="mt-3 grid grid-cols-2 gap-2 font-mono text-[14px] sm:grid-cols-4">@foreach($recoveryCodes as $c)<span>{{ $c }}</span>@endforeach</div>
      </div>
    @endif

    @if($user->hasTwoFactor())
      <p class="text-slate-600">When you log in, you also enter a code from your authenticator app.</p>
      <form method="POST" action="{{ route('admin.profile.two-factor.disable') }}" class="flex flex-wrap items-end gap-3">
        @csrf
        <div><label for="disable_password" class="label">Your password</label><input id="disable_password" type="password" name="password" class="input" autocomplete="current-password" required></div>
        <button class="btn-secondary">Turn off two-step login</button>
      </form>
      @error('disable_password')<p class="error">{{ $message }}</p>@enderror
    @elseif($setupSecret)
      <ol class="list-decimal space-y-1 pl-5 text-slate-600">
        <li>Open Google Authenticator, Microsoft Authenticator or a similar app on your phone.</li>
        <li>Scan this QR code, or type the setup key.</li>
        <li>Enter the 6 digit code the app shows.</li>
      </ol>
      <div class="flex flex-wrap items-center gap-6">
        <div class="rounded-lg border border-slate-200 bg-white p-2">{!! $setupQr !!}</div>
        <div class="space-y-3">
          <p>Setup key: <span class="font-mono font-semibold tracking-wider">{{ trim(chunk_split($setupSecret, 4, ' ')) }}</span></p>
          <form method="POST" action="{{ route('admin.profile.two-factor.confirm') }}" class="flex flex-wrap items-end gap-3">
            @csrf
            <div><label for="code" class="label">Code from the app</label><input id="code" name="code" inputmode="numeric" autocomplete="one-time-code" class="input" required></div>
            <button class="btn-primary">Turn on</button>
          </form>
          @error('code')<p class="error">{{ $message }}</p>@enderror
        </div>
      </div>
    @else
      <p class="text-slate-600">Protect your account with a code from your phone, so a stolen password is not enough to log in.</p>
      <form method="POST" action="{{ route('admin.profile.two-factor') }}">@csrf<button class="btn-primary">Set up two-step login</button></form>
    @endif
  </div>
</div>
@endsection
