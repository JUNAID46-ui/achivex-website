@extends('admin.layouts.app')
@section('title', 'Dashboard')
@section('heading', 'Dashboard')
@section('subheading', 'Welcome back, '.auth()->user()->name.'. Here is what is happening on achivex.com.')
@section('actions')
  <a href="{{ route('admin.builder') }}" class="btn-secondary">@include('admin.partials.icon', ['name' => 'layout']) Edit home page</a>
  <a href="{{ route('admin.resources.create', 'blog-posts') }}" class="btn-primary">@include('admin.partials.icon', ['name' => 'plus']) New post</a>
@endsection

@section('content')
@php
  $change = function ($v) {
      if ($v === null) return '<span class="text-neutral-400">No earlier data</span>';
      $cls = $v >= 0 ? 'text-green-600' : 'text-red-600';
      return '<span class="'.$cls.' font-semibold">'.($v >= 0 ? '+' : '').$v.'%</span> <span class="text-neutral-400">vs previous 30 days</span>';
  };
@endphp

{{-- Key figures --}}
<div class="grid gap-x-10 gap-y-6 border-b border-neutral-100 pb-8 sm:grid-cols-2 xl:grid-cols-4">
  <div class="py-2">
    <p class="stat-label">Page views, 30 days</p>
    <p class="stat-value text-ink">{{ number_format($summary['views']) }}</p>
    <p class="mt-1 text-xs">{!! $change($summary['views_change']) !!}</p>
  </div>
  <div class="py-2">
    <p class="stat-label">Visitors, 30 days</p>
    <p class="stat-value text-ink">{{ number_format($summary['visitors']) }}</p>
    <p class="mt-1 text-xs">{!! $change($summary['visitors_change']) !!}</p>
  </div>
  <a href="{{ route('admin.inbox.messages') }}" class="group py-2">
    <p class="stat-label group-hover:text-ink">Unread messages</p>
    <p class="stat-value {{ $unread ? 'text-red-600' : 'text-ink' }}">{{ $unread }}</p>
    <p class="mt-1 text-xs text-neutral-400">{{ $requests30 }} guide requests in 30 days</p>
  </a>
  <a href="{{ route('admin.seo.overview') }}" class="group py-2">
    <p class="stat-label group-hover:text-ink">Average SEO score</p>
    <p class="stat-value {{ ['good' => 'text-green-600', 'ok' => 'text-ink', 'bad' => 'text-red-600'][\App\Services\SeoAnalyzer::grade($seoAvg)] }}">{{ $seoAvg }}<span class="text-base text-neutral-400">/100</span></p>
    <p class="mt-1 text-xs text-neutral-500">
      <span class="text-green-700">{{ $seoCounts['good'] ?? 0 }} good</span> ·
      <span>{{ $seoCounts['ok'] ?? 0 }} need work</span> ·
      <span class="text-red-600">{{ $seoCounts['bad'] ?? 0 }} poor</span>
    </p>
  </a>
</div>

<div class="mt-10 grid gap-x-10 gap-y-10 xl:grid-cols-3">
  {{-- Traffic chart --}}
  <div class="card xl:col-span-2">
    <div class="card-header">
      <h2 class="card-title">Website traffic</h2>
      <a href="{{ route('admin.analytics') }}" class="text-[13px] font-semibold text-ink hover:underline">Full report &rarr;</a>
    </div>
    <div class="card-body"><div class="h-64"><canvas id="trafficChart"></canvas></div></div>
  </div>

  {{-- At a glance --}}
  <div class="card">
    <div class="card-header"><h2 class="card-title">At a glance</h2></div>
    <ul>
      @foreach($glance as $g)
        <li class="border-b border-neutral-200">
          <a href="{{ $g['route'] }}" class="group flex items-baseline justify-between py-3">
            <span class="text-[13.5px] text-neutral-600 group-hover:text-ink">{{ $g['label'] }}</span>
            <span class="font-mono text-lg font-bold text-ink">{{ $g['count'] }}</span>
          </a>
        </li>
      @endforeach
    </ul>
  </div>
</div>

<div class="mt-10 grid gap-x-10 gap-y-10 xl:grid-cols-3">
  {{-- Top pages --}}
  <div class="card">
    <div class="card-header"><h2 class="card-title">Top pages</h2><span class="text-xs text-neutral-400">30 days</span></div>
    <ul class="divide-y divide-neutral-100">
      @forelse($topPages as $p)
        <li class="flex items-center justify-between gap-3 px-5 py-2.5 text-[13px]">
          <span class="truncate text-neutral-700">{{ $p['path'] }}</span>
          <span class="font-semibold text-ink">{{ $p['views'] }}</span>
        </li>
      @empty
        <li class="px-5 py-8 text-center text-[13px] text-neutral-500">Views appear here once people visit the website.</li>
      @endforelse
    </ul>
  </div>

  {{-- Devices --}}
  <div class="card">
    <div class="card-header"><h2 class="card-title">Devices</h2></div>
    <div class="card-body">
      @if(array_sum($devices))
        <div class="mx-auto h-48 max-w-[220px]"><canvas id="deviceChart"></canvas></div>
      @else
        <p class="py-12 text-center text-[13px] text-neutral-500">No visits recorded yet.</p>
      @endif
    </div>
  </div>

  {{-- Site health --}}
  <div class="card">
    <div class="card-header"><h2 class="card-title">Site health</h2>
      <span class="badge-{{ collect($health)->where('ok', false)->count() ? 'warn' : 'green' }}">{{ collect($health)->where('ok', true)->count() }} of {{ count($health) }} passed</span>
    </div>
    <ul class="divide-y divide-neutral-100">
      @foreach($health as $h)
        <li>
          <a href="{{ $h['url'] }}" class="flex items-start gap-2.5 px-5 py-2.5 text-[13px] hover:bg-neutral-50">
            <span class="mt-0.5 {{ $h['ok'] ? 'text-green-500' : 'text-ink' }}">@include('admin.partials.icon', ['name' => $h['ok'] ? 'check' : 'alert'])</span>
            <span class="{{ $h['ok'] ? 'text-neutral-600' : 'font-semibold text-neutral-800' }}">{{ $h['label'] }}</span>
          </a>
        </li>
      @endforeach
    </ul>
  </div>
</div>

<div class="mt-10 grid gap-x-10 gap-y-10 xl:grid-cols-3">
  {{-- Recent messages --}}
  <div class="card">
    <div class="card-header"><h2 class="card-title">Latest messages</h2><a href="{{ route('admin.inbox.messages') }}" class="text-[13px] font-semibold text-ink hover:underline">Inbox &rarr;</a></div>
    <ul class="divide-y divide-neutral-100">
      @forelse($messages as $m)
        <li>
          <a href="{{ route('admin.inbox.messages.show', $m) }}" class="block px-5 py-3 hover:bg-neutral-50">
            <div class="flex items-center justify-between gap-2">
              <span class="text-[13px] {{ $m->read_at ? 'text-neutral-700' : 'font-bold text-ink' }}">{{ $m->name }}</span>
              <span class="text-[11px] text-neutral-400">{{ $m->created_at?->diffForHumans() }}</span>
            </div>
            <p class="mt-0.5 truncate text-xs text-neutral-500">{{ $m->message }}</p>
          </a>
        </li>
      @empty
        <li class="px-5 py-8 text-center text-[13px] text-neutral-500">No messages yet. Contact form messages land here.</li>
      @endforelse
    </ul>
  </div>

  {{-- Quick draft --}}
  @if(auth()->user()->canAccess('posts'))
  <form method="POST" action="{{ route('admin.quick-draft') }}" class="card">
    @csrf
    <div class="card-header"><h2 class="card-title">Quick draft</h2></div>
    <div class="card-body space-y-3">
      <div>
        <label for="qd-title" class="label">Title</label>
        <input id="qd-title" name="title" class="input" required>
      </div>
      <div>
        <label for="qd-content" class="label">Content</label>
        <textarea id="qd-content" name="content" rows="4" class="input" placeholder="What is on your mind?"></textarea>
      </div>
      <button class="btn-primary">Save draft</button>
    </div>
  </form>
  @endif

  {{-- Activity --}}
  <div class="card">
    <div class="card-header"><h2 class="card-title">Recent activity</h2>@if(auth()->user()->canAccess('activity'))<a href="{{ route('admin.activity') }}" class="text-[13px] font-semibold text-ink hover:underline">All &rarr;</a>@endif</div>
    <ul class="divide-y divide-neutral-100">
      @forelse($activity as $a)
        <li class="px-5 py-2.5 text-[13px]">
          <span class="font-semibold text-ink">{{ $a->user?->name ?? 'System' }}</span>
          <span class="text-neutral-600">{{ $a->action }} {{ strtolower((string) $a->subject_type) }}</span>
          <span class="text-neutral-800">{{ $a->subject_label }}</span>
          <span class="block text-[11px] text-neutral-400">{{ $a->created_at?->diffForHumans() }}</span>
        </li>
      @empty
        <li class="px-5 py-8 text-center text-[13px] text-neutral-500">Changes made in the admin are listed here.</li>
      @endforelse
    </ul>
  </div>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const daily = @json($daily);
    new Chart(document.getElementById('trafficChart'), {
      type: 'line',
      data: {
        labels: daily.labels,
        datasets: [
          { label: 'Page views', data: daily.views, borderColor: '#0A0A0A', backgroundColor: 'rgba(10,10,10,0.04)', fill: true, tension: 0.3, pointRadius: 0, borderWidth: 2 },
          { label: 'Visitors', data: daily.uniques, borderColor: '#7C3AED', backgroundColor: 'transparent', tension: 0.3, pointRadius: 0, borderWidth: 1.5 },
        ],
      },
      options: { maintainAspectRatio: false, interaction: { mode: 'index', intersect: false }, plugins: { legend: { position: 'bottom', labels: { boxWidth: 10 } } },
        scales: { x: { grid: { display: false }, ticks: { maxTicksLimit: 8 } }, y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#F1F5F9' }, border: { display: false } } } },
    })
    const dev = document.getElementById('deviceChart')
    if (dev) {
      const d = @json($devices);
      new Chart(dev, { type: 'doughnut', data: { labels: Object.keys(d), datasets: [{ data: Object.values(d), backgroundColor: ['#0A0A0A', '#7C3AED', '#9CA3AF', '#2563EB'], borderWidth: 2, borderColor: '#FFFFFF' }] },
        options: { maintainAspectRatio: false, cutout: '65%', plugins: { legend: { position: 'bottom', labels: { boxWidth: 10 } } } } })
    }
  })
</script>
@endpush
