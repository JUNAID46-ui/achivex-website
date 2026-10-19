<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="robots" content="noindex, nofollow">
  <title>@yield('title', 'Dashboard') · AchiveX Admin</title>
  <link rel="icon" href="{{ asset('brand/mark-dark.png') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ filemtime(public_path('css/admin.css')) }}">
  <script src="{{ asset('vendor/sortable.min.js') }}"></script>
  <script src="{{ asset('vendor/chart.umd.js') }}"></script>
  <script src="{{ asset('js/admin.js') }}?v={{ filemtime(public_path('js/admin.js')) }}"></script>
  <script defer src="{{ asset('vendor/alpine.min.js') }}"></script>
  @stack('head')
</head>
<body x-data="{ sidebar: false }">
@php
  $user = auth()->user();
  $current = request()->route()?->getName();
  $currentResource = request()->route('resource');
  $isActive = function ($item) use ($current, $currentResource) {
      if (isset($item['resource'])) return $currentResource === $item['resource'];
      if (isset($item['route'])) return $current === $item['route'] || str_starts_with((string) $current, $item['route'].'.');
      return false;
  };
  $itemUrl = fn ($item) => isset($item['resource']) ? route('admin.resources.index', $item['resource']) : route($item['route']);
@endphp

{{-- Top admin bar --}}
<header class="fixed inset-x-0 top-0 z-40 flex h-12 items-center justify-between bg-wp-bar px-3 text-[13px] text-slate-200">
  <div class="flex items-center gap-1">
    <button class="btn-ghost text-slate-200 hover:bg-white/10 lg:hidden" @click="sidebar = !sidebar" aria-label="Toggle menu">@include('admin.partials.icon', ['name' => 'menu'])</button>
    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 px-2">
      <img src="{{ asset('brand/mark-light.png') }}" alt="" class="h-5">
      <span class="hidden font-semibold text-white sm:inline">AchiveX</span>
    </a>
    <a href="{{ config('admin.site_url') }}" target="_blank" rel="noopener" class="flex items-center gap-1.5 rounded px-2 py-1.5 hover:bg-white/10 hover:text-amber">
      @include('admin.partials.icon', ['name' => 'globe']) <span class="hidden sm:inline">Visit Site</span>
    </a>
    <div class="relative" x-data="{ open: false }" @click.outside="open = false">
      <button @click="open = !open" class="flex items-center gap-1.5 rounded px-2 py-1.5 hover:bg-white/10 hover:text-amber">
        @include('admin.partials.icon', ['name' => 'plus']) <span class="hidden sm:inline">New</span>
      </button>
      <div x-show="open" x-cloak x-transition class="absolute left-0 top-full mt-1 w-48 rounded-md bg-wp-bar py-1 shadow-xl ring-1 ring-white/10">
        <a class="block px-4 py-2 hover:bg-white/10 hover:text-amber" href="{{ route('admin.resources.create', 'blog-posts') }}">Blog Post</a>
        @if($user->canAccess('content'))
        <a class="block px-4 py-2 hover:bg-white/10 hover:text-amber" href="{{ route('admin.resources.create', 'case-studies') }}">Case Study</a>
        <a class="block px-4 py-2 hover:bg-white/10 hover:text-amber" href="{{ route('admin.resources.create', 'news') }}">News Post</a>
        <a class="block px-4 py-2 hover:bg-white/10 hover:text-amber" href="{{ route('admin.resources.create', 'jobs') }}">Job Opening</a>
        @endif
        <a class="block px-4 py-2 hover:bg-white/10 hover:text-amber" href="{{ route('admin.media.index') }}">Media</a>
        @if($user->isAdmin())
        <a class="block px-4 py-2 hover:bg-white/10 hover:text-amber" href="{{ route('admin.resources.create', 'users') }}">User</a>
        @endif
      </div>
    </div>
    @if($user->canAccess('inbox'))
    <a href="{{ route('admin.inbox.messages') }}" class="flex items-center gap-1.5 rounded px-2 py-1.5 hover:bg-white/10 hover:text-amber" title="Unread messages">
      @include('admin.partials.icon', ['name' => 'mail']) <span>{{ $unreadCount ?? 0 }}</span>
    </a>
    @endif
  </div>
  <div class="relative" x-data="{ open: false }" @click.outside="open = false">
    <button @click="open = !open" class="flex items-center gap-2 rounded px-2 py-1.5 hover:bg-white/10">
      <span class="hidden sm:inline">Howdy, {{ explode(' ', $user->name)[0] }}</span>
      <span class="flex h-7 w-7 items-center justify-center rounded-full bg-amber text-[11px] font-bold text-navy">{{ $user->initials() }}</span>
    </button>
    <div x-show="open" x-cloak x-transition class="absolute right-0 top-full mt-1 w-56 rounded-md bg-wp-bar py-1 shadow-xl ring-1 ring-white/10">
      <div class="border-b border-white/10 px-4 py-2.5">
        <p class="font-semibold text-white">{{ $user->name }}</p>
        <p class="text-xs text-slate-400">{{ \App\Models\User::ROLES[$user->role] ?? $user->role }}</p>
      </div>
      <a class="block px-4 py-2 hover:bg-white/10 hover:text-amber" href="{{ route('admin.profile') }}">Edit Profile</a>
      <form method="POST" action="{{ route('admin.logout') }}">@csrf
        <button class="block w-full px-4 py-2 text-left hover:bg-white/10 hover:text-amber">Log Out</button>
      </form>
    </div>
  </div>
</header>

{{-- Sidebar --}}
<div x-show="sidebar" x-cloak class="fixed inset-0 z-20 bg-black/40 lg:hidden" @click="sidebar = false"></div>
<aside class="fixed bottom-0 left-0 top-12 z-30 w-60 -translate-x-full overflow-y-auto bg-wp-side px-3 py-4 transition-transform lg:translate-x-0"
       :class="sidebar ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
  <nav class="space-y-0.5" aria-label="Admin">
    @foreach(config('admin.menu') as $item)
      @continue(! $user->canAccess($item['area']))
      @if(isset($item['children']))
        @php
          $children = collect($item['children'])->filter(fn ($c) => $user->canAccess($c['area']));
          $groupActive = $children->contains(fn ($c) => $isActive($c));
        @endphp
        @continue($children->isEmpty())
        <div x-data="{ open: {{ $groupActive ? 'true' : 'false' }} }">
          <button @click="open = !open" class="nav-link w-full {{ $groupActive ? 'text-white' : '' }}">
            @include('admin.partials.icon', ['name' => $item['icon'], 'class' => 'h-[18px] w-[18px]'])
            <span class="flex-1 text-left">{{ $item['label'] }}</span>
            @if(($item['badge'] ?? null) === 'unread' && ($unreadCount ?? 0) > 0)
              <span class="rounded-full bg-amber px-1.5 text-[10px] font-bold text-navy">{{ $unreadCount }}</span>
            @endif
            <span :class="open && 'rotate-180'" class="transition">@include('admin.partials.icon', ['name' => 'chevron', 'class' => 'h-3.5 w-3.5'])</span>
          </button>
          <div x-show="open" class="mt-0.5 space-y-0.5 pb-1">
            @foreach($children as $child)
              <a href="{{ $itemUrl($child) }}" class="nav-sub {{ $isActive($child) ? 'active' : '' }}">{{ $child['label'] }}</a>
            @endforeach
          </div>
        </div>
      @else
        <a href="{{ $itemUrl($item) }}" class="nav-link {{ $isActive($item) ? 'active' : '' }}">
          @include('admin.partials.icon', ['name' => $item['icon'], 'class' => 'h-[18px] w-[18px]'])
          <span>{{ $item['label'] }}</span>
        </a>
      @endif
    @endforeach
  </nav>
  <div class="mt-8 border-t border-white/10 px-3 pt-4 text-[11px] text-slate-500">
    AchiveX Admin · Laravel {{ app()->version() }}
  </div>
</aside>

{{-- Main --}}
<main class="min-h-screen pt-12 lg:pl-60">
  <div class="mx-auto max-w-[1400px] px-4 py-6 sm:px-6 lg:px-8">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
      <div>
        <h1 class="text-[22px] font-bold text-navy">@yield('heading')</h1>
        @hasSection('subheading')<p class="mt-0.5 text-[13px] text-slate-500">@yield('subheading')</p>@endif
      </div>
      <div class="flex flex-wrap items-center gap-2">@yield('actions')</div>
    </div>

    @if(session('status'))
      <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="mb-5 flex items-center justify-between rounded-md border-l-4 border-emerald-500 bg-white px-4 py-3 text-[13.5px] shadow-sm">
        <span>{{ session('status') }}</span>
        <button @click="show = false" class="text-slate-400 hover:text-slate-700">@include('admin.partials.icon', ['name' => 'x'])</button>
      </div>
    @endif
    @if($errors->any())
      <div class="mb-5 rounded-md border-l-4 border-red-500 bg-white px-4 py-3 text-[13.5px] shadow-sm">
        <p class="font-semibold text-red-700">Please fix the following:</p>
        <ul class="mt-1 list-disc pl-5 text-red-600">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
      </div>
    @endif

    @yield('content')
  </div>
</main>

@include('admin.partials.media-picker')
@stack('scripts')
</body>
</html>
