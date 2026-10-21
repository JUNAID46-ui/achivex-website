@extends('admin.layouts.app')
@section('title', 'Analytics')
@section('heading', 'Analytics')
@section('subheading', 'Visits recorded by the AchiveX website. No cookies or third party trackers are used.')
@section('actions')
  <div class="flex  bg-slate-100 p-0.5">
    @foreach([7 => '7 days', 30 => '30 days', 90 => '90 days', 365 => '12 months'] as $d => $l)
      <a href="{{ route('admin.analytics', ['days' => $d]) }}" class="px-3 py-1.5 text-[13px] {{ $days === $d ? 'bg-ink font-semibold text-white' : 'text-slate-600 hover:text-ink' }}">{{ $l }}</a>
    @endforeach
  </div>
  <a href="{{ route('admin.analytics.export', ['days' => $days]) }}" class="btn-secondary">@include('admin.partials.icon', ['name' => 'download']) CSV</a>
@endsection
@section('content')
<div class="grid gap-x-10 gap-y-6 border-b border-slate-100 pb-8 sm:grid-cols-2 xl:grid-cols-4">
  @foreach([['Page views', $summary['views'], $summary['views_change'], 'text-neon-purple'], ['Visitors', $summary['visitors'], $summary['visitors_change'], 'text-neon-blue'], ['Pages per visitor', $summary['pages_per_visitor'], null, 'text-neon-green'], ['Top page', $topPages[0]['path'] ?? 'None yet', null, 'text-ink text-2xl']] as [$label, $value, $chg, $tone])
    <div class="py-2">
      <p class="stat-label">{{ $label }}</p>
      <p class="stat-value truncate {{ $tone }}">{{ is_numeric($value) ? number_format($value, is_float($value) ? 1 : 0) : $value }}</p>
      @if($chg !== null)<p class="mt-1 text-xs {{ $chg >= 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ $chg >= 0 ? '+' : '' }}{{ $chg }}% vs previous period</p>@endif
    </div>
  @endforeach
</div>
<div class="card mt-10"><div class="card-header"><h2 class="card-title">Views and visitors</h2></div><div class="card-body"><div class="h-72"><canvas id="chart"></canvas></div></div></div>
<div class="mt-10 grid gap-x-10 gap-y-10 xl:grid-cols-3">
  <div class="card xl:col-span-2 overflow-x-auto">
    <div class="card-header"><h2 class="card-title">Top pages</h2></div>
    <table class="table">
      <thead><tr><th>Page</th><th class="text-right">Views</th><th class="text-right">Visitors</th></tr></thead>
      <tbody>
        @forelse($topPages as $p)
          @php $max = $topPages[0]['views'] ?: 1; @endphp
          <tr><td><a href="{{ rtrim(config('admin.site_url'), '/').$p['path'] }}" target="_blank" class="text-ink hover:underline">{{ $p['path'] }}</a>
            <div class="mt-1 h-1  bg-slate-100"><div class="h-1  bg-gradient-to-r from-neon-purple to-neon-blue" style="width: {{ round($p['views'] * 100 / $max) }}%"></div></div></td>
            <td class="text-right font-semibold">{{ $p['views'] }}</td><td class="text-right text-slate-500">{{ $p['visitors'] }}</td></tr>
        @empty
          <tr><td colspan="3" class="py-12 text-center text-slate-500">No visits in this period.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="space-y-6">
    <div class="card"><div class="card-header"><h2 class="card-title">Referrers</h2></div>
      <ul class="divide-y divide-slate-100">
        @forelse($referrers as $host => $count)
          <li class="flex justify-between px-5 py-2.5 text-[13px]"><span class="truncate">{{ $host }}</span><span class="font-semibold text-ink">{{ $count }}</span></li>
        @empty
          <li class="px-5 py-8 text-center text-[13px] text-slate-500">No outside referrers yet. Direct visits are not listed.</li>
        @endforelse
      </ul>
    </div>
    <div class="card"><div class="card-header"><h2 class="card-title">Devices</h2></div>
      <div class="card-body">@if(array_sum($devices))<div class="mx-auto h-48 max-w-[220px]"><canvas id="devices"></canvas></div>@else<p class="py-8 text-center text-[13px] text-slate-500">No data yet.</p>@endif</div>
    </div>
  </div>
</div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const d = @json($daily);
  new Chart(document.getElementById('chart'), { type: 'bar', data: { labels: d.labels, datasets: [
      { label: 'Page views', data: d.views, backgroundColor: '#A855F7', borderRadius: 6 },
      { label: 'Visitors', data: d.uniques, backgroundColor: '#22C55E', borderRadius: 6 } ] },
    options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { boxWidth: 10 } } }, scales: { x: { grid: { display: false }, ticks: { maxTicksLimit: 12 } }, y: { beginAtZero: true, ticks: { precision: 0 } } } } })
  const dev = document.getElementById('devices')
  if (dev) { const v = @json($devices); new Chart(dev, { type: 'doughnut', data: { labels: Object.keys(v), datasets: [{ data: Object.values(v), backgroundColor: ['#A855F7', '#3B82F6', '#22C55E', '#EF4444'], borderWidth: 0 }] }, options: { maintainAspectRatio: false, cutout: '65%', plugins: { legend: { position: 'bottom' } } } }) }
})
</script>
@endpush
