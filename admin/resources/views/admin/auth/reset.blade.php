<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title>Reset Password · AchiveX Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body class="flex min-h-screen items-center justify-center bg-wp-bg px-4">
  <div class="w-full max-w-sm">
    <div class="mb-8 flex justify-center">
      <img src="{{ asset('brand/logo-dark.png') }}" alt="AchiveX LLC" class="h-24">
    </div>
    @if(session('status'))
      <div class="mb-4 rounded-md border-l-4 border-emerald-500 bg-white px-4 py-3 text-sm shadow-sm">{{ session('status') }}</div>
    @endif
    <form method="POST" action="{{ route('admin.password.update') }}" class="card card-body space-y-5">
      @csrf
      <input type="hidden" name="token" value="{{ $token }}">
      <div>
        <label for="email" class="label">Email address</label>
        <input id="email" name="email" type="email" value="{{ old('email', $email) }}" required class="input">
        @error('email')<p class="error">{{ $message }}</p>@enderror
      </div>
      <div>
        <label for="password" class="label">New password</label>
        <input id="password" name="password" type="password" required autofocus autocomplete="new-password" class="input">
        @error('password')<p class="error">{{ $message }}</p>@enderror
      </div>
      <div>
        <label for="password_confirmation" class="label">Confirm new password</label>
        <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="input">
      </div>
      <button class="btn-primary w-full justify-center">Save new password</button>
    </form>
  </div>
</body>
</html>
