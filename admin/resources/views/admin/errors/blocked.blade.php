<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title>Access blocked · AchiveX Admin</title>
  <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body class="flex min-h-screen items-center justify-center bg-wp-bg px-4">
  <div class="w-full max-w-md text-center">
    <h1 class="text-2xl font-bold text-ink">Access blocked</h1>
    <p class="mt-3 text-[14px] text-neutral-600">Too many failed sign ins came from your network, so the admin is closed to it for now.
      @if($block->expires_at) Try again after {{ $block->expires_at->format('F j, Y g:i A') }} UTC. @endif
      If this is your own network, ask another administrator to lift the block in the Security Center.</p>
  </div>
</body>
</html>
