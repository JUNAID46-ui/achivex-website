@extends('admin.layouts.app')
@section('title', 'Cache')
@section('heading', 'Cache')
@section('subheading', 'Saved results that keep the admin fast. Clearing is safe: anything removed is rebuilt on the next visit.')
@section('content')
@php $build = \App\Models\AppSetting::group('site_build'); $hook = \App\Services\SiteRebuild::active(); $server = \App\Services\SiteBuilder::enabled(); $instant = \App\Services\InstantPublish::enabled(); @endphp
<div class="card mb-6">
  <div class="card-header"><h2 class="card-title">Website build</h2></div>
  <div class="card-body flex flex-wrap items-center justify-between gap-4 text-[13px]">
    <div>
      @if(! $hook)
        <p class="text-neutral-600">Automatic builds are off. On Hostinger set SITE_BUILD=server (see docs/HOSTINGER-MOVE.md); on Vercel add SITE_DEPLOY_HOOK.</p>
      @elseif($server)
        <p class="text-neutral-600">The website is built on this server within a minute of every change.</p>
        @if(!empty($build['running']))<p class="mt-1 font-semibold text-neutral-700">Building now...</p>
        @elseif(!empty($build['requested']))<p class="mt-1 text-neutral-500">A build is waiting to start.</p>@endif
        @if(!empty($build['finished_at']))<p class="mt-1 text-neutral-500">Last build: {{ \Carbon\Carbon::parse($build['finished_at'])->diffForHumans() }}{{ empty($build['last_ok']) ? ', failed' : '' }}.</p>@endif
        @if(empty($build['last_ok']) && !empty($build['last_error']))<pre class="mt-2 max-h-40 overflow-auto whitespace-pre-wrap bg-neutral-50 p-2 text-[11px] text-red-700">{{ $build['last_error'] }}</pre>@endif
      @else
        <p class="text-neutral-600">The website rebuilds by itself after content changes, at most once every {{ \App\Services\SiteRebuild::GAP }} seconds.</p>
        @if(!empty($build['last_at']))<p class="mt-1 text-neutral-500">Last build: {{ \Carbon\Carbon::parse($build['last_at'])->diffForHumans() }} ({{ $build['last_reason'] ?? '' }}){{ empty($build['last_ok']) ? ', could not be started' : '' }}.@if(!empty($build['pending'])) A change is waiting for the next build.@endif</p>@endif
      @endif
      @if($instant)<p class="mt-1 text-neutral-500">Instant publishing is on: new pages go live the moment they are saved.</p>@endif
    </div>
    @if($hook || $instant)<form method="POST" action="{{ route('admin.site.rebuild') }}">@csrf<button class="btn-primary" data-loading>Publish</button></form>@endif
  </div>
</div>
<div class="grid gap-6 xl:grid-cols-2">
  <div class="card">
    <div class="card-header"><h2 class="card-title">Clear caches</h2></div>
    <div class="card-body space-y-3 text-[13px]">
      @foreach($known as $key => $label)
        <form method="POST" action="{{ route('admin.cache.clear') }}" class="flex items-center justify-between gap-3">
          @csrf <input type="hidden" name="target" value="{{ $key }}">
          <span>{{ $label }}</span>
          <button class="btn-secondary">Clear</button>
        </form>
      @endforeach
      <form method="POST" action="{{ route('admin.cache.clear') }}" class="flex items-center justify-between gap-3">
        @csrf <input type="hidden" name="target" value="views">
        <span>Compiled page templates on this server ({{ $views }} files)</span>
        <button class="btn-secondary">Clear</button>
      </form>
      <form method="POST" action="{{ route('admin.cache.clear') }}" class="flex items-center justify-between gap-3">
        @csrf <input type="hidden" name="target" value="expired">
        <span>Expired entries ({{ $entries->where('expired', true)->count() }})</span>
        <button class="btn-secondary">Remove</button>
      </form>
    </div>
    <div class="flex justify-end pt-2">
      <form method="POST" action="{{ route('admin.cache.clear') }}" onsubmit="return confirm('Clear all cached data? Login attempt counters are reset too.')">
        @csrf <input type="hidden" name="target" value="all">
        <button class="btn-primary">@include('admin.partials.icon', ['name' => 'refresh']) Clear all cache</button>
      </form>
    </div>
  </div>

  <div class="card card-body space-y-3 text-[13px]">
    <h2 class="card-title">What about the website?</h2>
    <p>The website has no cache to clear. It reads pages, posts and settings from the database each time it loads, so a change saved here shows on the site right away. If a visitor still sees old content, a normal browser refresh fixes it.</p>
    <p>Images and scripts of the website are cached by browsers for a long time on purpose, which keeps it fast. Each new deployment uses new file names, so visitors always get the latest version.</p>
  </div>
</div>

<div class="card mt-6 overflow-x-auto">
  <div class="card-header"><h2 class="card-title">Cached entries ({{ $entries->count() }})</h2></div>
  <table class="table">
    <thead><tr><th>Entry</th><th>What it is</th><th>Size</th><th>Expires</th></tr></thead>
    <tbody>
      @forelse($entries as $e)
        <tr>
          <td class="font-mono text-xs">{{ \Illuminate\Support\Str::limit($e->key, 60) }}</td>
          <td>{{ $e->label }}</td>
          <td class="whitespace-nowrap text-neutral-500">{{ number_format($e->size / 1024, 1) }} KB</td>
          <td class="whitespace-nowrap {{ $e->expired ? 'text-red-600' : 'text-neutral-500' }}">{{ $e->expires ? ($e->expired ? 'Expired' : $e->expires->diffForHumans()) : 'Never' }}</td>
        </tr>
      @empty
        <tr><td colspan="4" class="py-10 text-center text-neutral-500">The cache is empty.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
