<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title>Log In · AchiveX Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body class="flex min-h-screen items-center justify-center bg-wp-bg px-4">
  <div class="w-full max-w-sm">
    <div class="mb-8 flex justify-center">
      <img src="{{ asset('brand/logo-dark.png') }}" alt="AchiveX LLC" class="h-24">
    </div>
    @if(session('status'))
      <div class="mb-4  border-l-4 border-green-500 bg-white px-4 py-3 text-sm ">{{ session('status') }}</div>
    @endif
    <form method="POST" action="{{ route('admin.login') }}" class="card card-body space-y-5">
      @csrf
      <div>
        <label for="email" class="label">Email address</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="input">
        @error('email')<p class="error">{{ $message }}</p>@enderror
      </div>
      <div>
        <label for="password" class="label">Password</label>
        <input id="password" name="password" type="password" required autocomplete="current-password" class="input">
      </div>
      <div class="flex items-center justify-between">
        <label class="flex items-center gap-2 text-[13px] text-neutral-600">
          <input type="checkbox" name="remember" class="border-neutral-300 text-ink focus:ring-ink"> Remember me
        </label>
        <button class="btn-primary">Log In</button>
      </div>
    </form>
    <p class="mt-4 text-center text-[13px]"><a href="{{ route('admin.password.request') }}" class="font-semibold text-ink hover:underline">Forgot your password?</a></p>
    <p class="mt-3 text-center text-xs text-neutral-500">
      <a href="{{ config('admin.site_url') }}" class="hover:text-ink">&larr; Go to AchiveX website</a>
    </p>
  </div>
</body>
</html>
