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
      <div><label for="password" class="label">New password</label><input id="password" type="password" name="password" class="input" autocomplete="new-password"><p class="help">At least 8 characters. Leave empty to keep your password.</p></div>
      <div><label for="password_confirmation" class="label">Confirm new password</label><input id="password_confirmation" type="password" name="password_confirmation" class="input" autocomplete="new-password"></div>
    </div>
  </div>
  <div class="lg:col-span-2"><button class="btn-primary">Save profile</button></div>
</form>
@endsection
