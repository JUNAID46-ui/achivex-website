@extends('admin.layouts.app')
@section('title', 'Cache')
@section('heading', 'Cache')
@section('subheading', 'Saved results that keep the admin fast. Clearing is safe: anything removed is rebuilt on the next visit.')
@section('content')
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
    <div class="flex justify-end border-t border-slate-200 bg-slate-50 px-5 py-3">
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
          <td class="whitespace-nowrap text-slate-500">{{ number_format($e->size / 1024, 1) }} KB</td>
          <td class="whitespace-nowrap {{ $e->expired ? 'text-red-600' : 'text-slate-500' }}">{{ $e->expires ? ($e->expired ? 'Expired' : $e->expires->diffForHumans()) : 'Never' }}</td>
        </tr>
      @empty
        <tr><td colspan="4" class="py-10 text-center text-slate-500">The cache is empty.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
