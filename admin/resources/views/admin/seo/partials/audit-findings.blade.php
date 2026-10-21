{{-- Findings of one audit run with filters, history and downloads. Expects: $type, $run, $runs, $previous, $findings, $categories --}}
@php
  $sevBadge = ['critical' => 'red', 'high' => 'red', 'medium' => 'amber', 'low' => 'gray', 'info' => 'gray'];
  $sum = $run?->summary ?? [];
  $prev = $previous?->summary ?? null;
@endphp
<div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_300px]">
  <div class="min-w-0 space-y-4">
    @if($run)
      <div class="grid gap-3 sm:grid-cols-3 lg:grid-cols-6">
        @foreach(['critical', 'high', 'medium', 'low', 'info'] as $s)
          @php $n = $sum['by_severity'][$s] ?? 0; $d = $prev ? $n - ($prev['by_severity'][$s] ?? 0) : null; @endphp
          <a href="{{ request()->fullUrlWithQuery(['severity' => $s]) }}" class="card px-4 py-3 hover:ring-1 hover:ring-ink">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ ucfirst($s) }}</p>
            <p class="text-2xl font-bold text-ink">{{ $n }}</p>
            @if($d)<p class="text-xs {{ $d > 0 ? 'text-red-600' : 'text-emerald-600' }}">{{ $d > 0 ? '+'.$d : $d }} since last run</p>@endif
          </a>
        @endforeach
        <div class="card px-4 py-3">
          <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Verified / advice</p>
          <p class="text-2xl font-bold text-ink">{{ $sum['verified'] ?? 0 }} <span class="text-base font-semibold text-slate-400">/ {{ $sum['recommendations'] ?? 0 }}</span></p>
        </div>
      </div>

      <form method="GET" class="flex flex-wrap items-center gap-2">
        <input type="hidden" name="run" value="{{ $run->id }}">
        <input name="q" value="{{ request('q') }}" class="input w-56 py-1.5" placeholder="Search checks, URLs, evidence" aria-label="Search findings">
        <select name="severity" class="input w-36 py-1.5" aria-label="Severity"><option value="">All severities</option>
          @foreach(array_keys(\App\Services\Audit\Finding::SEVERITIES) as $s)<option value="{{ $s }}" @selected(request('severity') === $s)>{{ ucfirst($s) }}</option>@endforeach</select>
        <select name="kind" class="input w-44 py-1.5" aria-label="Kind"><option value="">Verified and advice</option>
          <option value="verified" @selected(request('kind') === 'verified')>Verified findings</option><option value="recommendation" @selected(request('kind') === 'recommendation')>Recommendations</option></select>
        <select name="category" class="input w-44 py-1.5" aria-label="Category"><option value="">All categories</option>
          @foreach($categories as $c)<option value="{{ $c }}" @selected(request('category') === $c)>{{ $c }}</option>@endforeach</select>
        <button class="btn-secondary">Filter</button>
        @if(request()->hasAny(['q', 'severity', 'kind', 'category']))<a href="{{ request()->url() }}?run={{ $run->id }}" class="btn-ghost">Clear</a>@endif
        <span class="ml-auto text-xs text-slate-500">{{ $findings->count() }} shown</span>
      </form>

      <div class="card overflow-x-auto">
        <table class="table">
          <thead><tr><th>Severity</th><th>Finding</th><th>Affected URL</th><th>Evidence</th><th>Recommended fix</th></tr></thead>
          <tbody>
            @forelse($findings as $f)
              <tr>
                <td class="whitespace-nowrap">
                  <span class="badge-{{ $sevBadge[$f['severity']] }}">{{ ucfirst($f['severity']) }}</span>
                  <span class="mt-1 block text-[11px] {{ $f['kind'] === 'verified' ? 'text-emerald-700' : 'text-slate-500' }}" title="{{ $f['kind'] === 'verified' ? 'Observed in the admin data or the live website' : 'Advice based on good practice, not a measurement' }}">{{ $f['kind'] === 'verified' ? 'Verified' : 'Recommendation' }}</span>
                </td>
                <td><span class="font-semibold text-ink">{{ $f['check'] }}</span><span class="block text-xs text-slate-500">{{ $f['category'] }}</span></td>
                <td class="max-w-[220px] break-all text-[12.5px]">
                  @if($f['url'])
                    @php $u = str_starts_with($f['url'], 'http') ? $f['url'] : rtrim(config('admin.site_url'), '/').$f['url']; @endphp
                    <a href="{{ $u }}" target="_blank" rel="noopener" class="text-ink hover:underline">{{ $f['url'] }}</a>
                  @else<span class="text-slate-400">Site wide</span>@endif
                </td>
                <td class="max-w-sm text-[12.5px] text-slate-600">{{ $f['evidence'] }}</td>
                <td class="max-w-sm text-[12.5px]">{{ $f['fix'] }}</td>
              </tr>
            @empty
              <tr><td colspan="5" class="py-12 text-center text-slate-500">{{ ($sum['total'] ?? 0) ? 'No findings match these filters.' : 'No problems found in this run.' }}</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    @else
      <div class="card card-body py-14 text-center text-slate-500">
        <p class="font-semibold text-slate-700">No audit has been run yet.</p>
        <p class="mt-1 text-[13px]">Click "Run audit now". It reads the admin data and fetches a few pages of the live website. It does not change anything.</p>
      </div>
    @endif
  </div>

  <aside class="space-y-4">
    <div class="card">
      <div class="card-header"><h2 class="card-title">This run</h2></div>
      <div class="card-body space-y-2 text-[13px]">
        @if($run)
          <p>Run <span class="font-semibold">#{{ $run->id }}</span>, <time datetime="{{ $run->started_at?->toIso8601String() }}">{{ $run->started_at?->format('M j, Y H:i') }} UTC</time></p>
          <p class="text-slate-500">{{ $sum['total'] ?? 0 }} findings, {{ $run->user?->name ?? 'scheduled task' }}</p>
          <div class="flex gap-2 pt-1">
            <a href="{{ route('admin.seo.audits.download', [$run->id, 'csv']) }}" class="btn-secondary btn-sm">@include('admin.partials.icon', ['name' => 'download', 'class' => 'h-3 w-3']) CSV</a>
            <a href="{{ route('admin.seo.audits.download', [$run->id, 'json']) }}" class="btn-secondary btn-sm">@include('admin.partials.icon', ['name' => 'download', 'class' => 'h-3 w-3']) JSON</a>
          </div>
        @else
          <p class="text-slate-500">Not run yet.</p>
        @endif
      </div>
    </div>
    <div class="card">
      <div class="card-header"><h2 class="card-title">History</h2></div>
      <div class="divide-y divide-slate-100 text-[13px]">
        @forelse($runs as $r)
          <a href="{{ request()->url() }}?run={{ $r->id }}" class="flex items-center justify-between px-4 py-2.5 hover:bg-slate-50 {{ $run?->id === $r->id ? 'bg-accent-50' : '' }}">
            <span>{{ $r->started_at?->format('M j, H:i') }}</span>
            @if($r->status === 'completed')<span class="text-slate-500">{{ $r->summary['total'] ?? 0 }} findings</span>
            @elseif($r->status === 'failed')<span class="badge-red" title="{{ $r->error }}">Failed</span>
            @else<span class="badge-gray">Running</span>@endif
          </a>
        @empty
          <p class="px-4 py-4 text-slate-500">No runs yet.</p>
        @endforelse
      </div>
    </div>
  </aside>
</div>
