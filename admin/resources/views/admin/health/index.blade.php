@extends('admin.layouts.app')
@section('title', 'System Health')
@section('heading', 'System Health')
@section('subheading', 'Measured just now from this server. Anything that cannot be measured here is shown as "Unknown", not as passing.')
@section('actions')
  <a href="{{ route('admin.health', ['live' => 1]) }}" class="btn-primary">@include('admin.partials.icon', ['name' => 'pulse']) Run live checks</a>
@endsection
@section('content')
@php
  $pill = ['ok' => 'bg-emerald-50 text-emerald-700 ring-emerald-200', 'warning' => 'bg-slate-50 text-ink ring-slate-300', 'critical' => 'bg-red-50 text-red-700 ring-red-200', 'unknown' => 'bg-slate-50 text-slate-600 ring-slate-200'];
  $dot = ['ok' => 'bg-neon-green', 'warning' => 'bg-ink', 'critical' => 'bg-red-500', 'unknown' => 'bg-slate-400'];
  $label = ['ok' => 'OK', 'warning' => 'Warning', 'critical' => 'Critical', 'unknown' => 'Unknown'];
@endphp
<p class="mb-4 text-xs text-slate-500">Measured <time datetime="{{ $measuredAt->toIso8601String() }}">{{ $measuredAt->format('Y-m-d H:i:s') }} UTC</time>{{ $live ? ', including live connection checks.' : '. Live checks (storage key, website) run only when you click the button.' }}</p>
<div class="grid gap-x-10 sm:grid-cols-2 xl:grid-cols-3">
  @foreach($checks as $c)
    <div class="border-t border-slate-100 py-4">
      <div class="flex items-start justify-between gap-2">
        <p class="text-[13px] font-semibold text-slate-600">{{ $c['label'] }}</p>
        <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold ring-1 {{ $pill[$c['status']] }}">
          <span class="h-1.5 w-1.5 rounded-full {{ $dot[$c['status']] }}"></span>{{ $label[$c['status']] }}
        </span>
      </div>
      <p class="mt-1 text-xl font-extrabold tracking-tight {{ ['ok' => 'text-ink', 'warning' => 'text-ink', 'critical' => 'text-neon-red', 'unknown' => 'text-slate-400'][$c['status']] }}">{{ $c['value'] }}</p>
      @if($c['percent'] !== null)
        <div class="mt-2 h-2 rounded-full bg-slate-100" role="progressbar" aria-valuenow="{{ $c['percent'] }}" aria-valuemin="0" aria-valuemax="100" aria-label="{{ $c['label'] }} used">
          <div class="h-2 rounded-full {{ $dot[$c['status']] }}" style="width: {{ min(100, $c['percent']) }}%"></div>
        </div>
        <p class="mt-1 text-xs text-slate-500">{{ $c['percent'] }}% used · warning at {{ $thresholds['warn'] }}%, critical at {{ $thresholds['critical'] }}%</p>
      @endif
      @if($c['detail'])<p class="mt-1 text-xs text-slate-500">{{ $c['detail'] }}</p>@endif
    </div>
  @endforeach
</div>

<div class="mt-6 grid gap-6 xl:grid-cols-2">
  <div class="card">
    <div class="card-header"><h2 class="card-title">Application</h2></div>
    <dl class="card-body grid grid-cols-2 gap-x-4 gap-y-2 text-[13px]">
      @foreach($env as $k => $v)<dt class="text-slate-500">{{ $k }}</dt><dd class="font-semibold text-ink">{{ $v }}</dd>@endforeach
    </dl>
  </div>
  <div class="card">
    <div class="card-header"><h2 class="card-title">Daily scheduled task</h2></div>
    <div class="card-body text-[13px]">
      @if(! empty($daily['last_run']))
        <p>Last run {{ \Carbon\Carbon::parse($daily['last_run'])->format('Y-m-d H:i') }} UTC.</p>
        <ul class="mt-2 space-y-1">
          @foreach($daily['results'] ?? [] as $step => $r)
            <li class="flex gap-2"><span class="{{ $r['ok'] ? 'text-emerald-600' : 'text-red-600' }}">{{ $r['ok'] ? '✓' : '✗' }}</span><span><span class="font-semibold">{{ ucfirst(str_replace('_', ' ', $step)) }}</span>: {{ $r['detail'] }}</span></li>
          @endforeach
        </ul>
      @else
        <p class="text-slate-500">Not run yet. Vercel calls it once a day (06:17 UTC) after the admin is deployed with the cron setting.</p>
      @endif
    </div>
  </div>
</div>

<form method="POST" action="{{ route('admin.health.thresholds') }}" class="card mt-6">
  @csrf
  <div class="card-header"><h2 class="card-title">Warning thresholds</h2></div>
  <div class="card-body grid gap-4 text-[13px] sm:grid-cols-2 lg:grid-cols-5">
    <div><label class="label" for="storage_mb">File storage limit (MB)</label><input id="storage_mb" type="number" name="storage_mb" value="{{ $thresholds['storage_mb'] }}" class="input"><p class="help">Supabase Free: 1024</p></div>
    <div><label class="label" for="database_mb">Database limit (MB)</label><input id="database_mb" type="number" name="database_mb" value="{{ $thresholds['database_mb'] }}" class="input"><p class="help">Supabase Free: 500</p></div>
    <div><label class="label" for="warn">Warning at (%)</label><input id="warn" type="number" name="warn" value="{{ $thresholds['warn'] }}" class="input"></div>
    <div><label class="label" for="critical">Critical at (%)</label><input id="critical" type="number" name="critical" value="{{ $thresholds['critical'] }}" class="input"></div>
    <div><label class="label" for="backup_days">Warn if no backup for (days)</label><input id="backup_days" type="number" name="backup_days" value="{{ $thresholds['backup_days'] }}" class="input"></div>
  </div>
  <div class="flex justify-end pt-2"><button class="btn-secondary">Save thresholds</button></div>
</form>

<div class="card mt-6 overflow-x-auto">
  <div class="card-header"><h2 class="card-title">Recent application errors</h2><span class="text-xs text-slate-500">Messages are shortened; SQL values and credentials are removed. Kept 90 days.</span></div>
  <table class="table">
    <thead><tr><th>When (UTC)</th><th>Error</th><th>Where</th><th>Request</th></tr></thead>
    <tbody>
      @forelse($errorLogs as $e)
        <tr>
          <td class="whitespace-nowrap text-slate-500">{{ $e->created_at?->format('Y-m-d H:i') }}</td>
          <td><span class="font-semibold text-ink">{{ class_basename($e->exception) }}</span><span class="block max-w-xl break-words text-[12.5px] text-slate-600">{{ $e->message }}</span></td>
          <td class="font-mono text-[11px] text-slate-500">{{ $e->location }}</td>
          <td class="text-[12.5px] text-slate-500">{{ $e->method }} {{ $e->path }}</td>
        </tr>
      @empty
        <tr><td colspan="4" class="py-10 text-center text-slate-500">No errors recorded.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
<div class="mt-4">{{ $errorLogs->links('admin.partials.pagination') }}</div>
@endsection
