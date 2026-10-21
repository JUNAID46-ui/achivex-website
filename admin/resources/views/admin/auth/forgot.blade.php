<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title>Forgot Password · AchiveX Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body class="flex min-h-screen items-center justify-center bg-wp-bg px-4">
  <div class="w-full max-w-sm">
    <div class="mb-8 flex justify-center">
      <img src="{{ asset('brand/logo-dark.png') }}" alt="AchiveX LLC" class="h-24">
    </div>
    @if(session('status'))
      <div class="mb-4  border-l-4 border-emerald-500 bg-white px-4 py-3 text-sm ">{{ session('status') }}</div>
    @endif
    <form method="POST" action="{{ route('admin.password.email') }}" class="card card-body space-y-5">
      @csrf
      <p class="text-[13px] text-slate-600">Enter the email of your admin account. We will send you a link to choose a new password.</p>
      <div>
        <label for="email" class="label">Email address</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus class="input">
        @error('email')<p class="error">{{ $message }}</p>@enderror
      </div>
      <button class="btn-primary w-full justify-center">Send reset link</button>
    </form>
    <p class="mt-6 text-center text-[13px]"><a href="{{ route('admin.login') }}" class="font-semibold text-ink hover:underline">&larr; Back to log in</a></p>
  </div>
</body>
</html>
