<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title>Two-step login · AchiveX Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body class="flex min-h-screen items-center justify-center bg-wp-bg px-4">
  <div class="w-full max-w-sm">
    <div class="mb-8 flex justify-center">
      <img src="{{ asset('brand/logo-dark.png') }}" alt="AchiveX LLC" class="h-24">
    </div>
    <form method="POST" action="{{ route('admin.two-factor') }}" class="card card-body space-y-5">
      @csrf
      <div>
        <label for="code" class="label">Code from your authenticator app</label>
        <input id="code" name="code" required autofocus inputmode="numeric" autocomplete="one-time-code" class="input" placeholder="123456">
        @error('code')<p class="error">{{ $message }}</p>@enderror
        <p class="help mt-2">Lost your phone? Enter one of your recovery codes instead.</p>
      </div>
      <div class="flex items-center justify-between">
        <a href="{{ route('admin.login') }}" class="text-[13px] text-neutral-600 hover:text-ink">&larr; Back</a>
        <button class="btn-primary">Continue</button>
      </div>
    </form>
  </div>
</body>
</html>
