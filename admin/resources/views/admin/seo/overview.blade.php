@extends('admin.layouts.app')
@section('title', 'SEO Overview')
@section('heading', 'SEO Overview')
@section('subheading', 'On-page SEO health for every page, post and service on the website.')
@section('actions')
  <a href="{{ route('admin.seo.pages') }}" class="btn-primary">Review all pages</a>
@endsection
@section('content')
@php $lastAudit = \App\Models\AuditRun::where('type', 'seo')->where('status', 'completed')->latest('id')->first(['id', 'summary', 'started_at']); @endphp
<div class="mb-6 flex flex-wrap items-center justify-between gap-3  border border-slate-200 bg-white px-5 py-3 text-[13px]">
  @if($lastAudit)
    <span>Last technical audit <time datetime="{{ $lastAudit->started_at?->toIso8601String() }}">{{ $lastAudit->started_at?->diffForHumans() }}</time>:
      <span class="font-semibold text-ink">{{ $lastAudit->summary['total'] ?? 0 }} findings</span>,
      {{ ($lastAudit->summary['by_severity']['critical'] ?? 0) + ($lastAudit->summary['by_severity']['high'] ?? 0) }} high or critical.</span>
  @else
    <span>No technical audit yet. It checks links, redirects, canonicals, sitemap and robots.txt.</span>
  @endif
  <a href="{{ route('admin.seo.audit') }}" class="btn-secondary btn-sm">Open SEO Audit</a>
</div>
<div class="grid gap-6 lg:grid-cols-[320px_minmax(0,1fr)]">
  <div class="card card-body flex flex-col items-center text-center">
    @php $deg = $avg * 3.6; $color = ['good' => '#10b981', 'ok' => '#F0A500', 'bad' => '#ef4444'][\App\Services\SeoAnalyzer::grade($avg)]; @endphp
    <div class="relative h-40 w-40 rounded-full" style="background: conic-gradient({{ $color }} {{ $deg }}deg, #E2E8F0 0)">
      <div class="absolute inset-3 flex flex-col items-center justify-center rounded-full bg-white">
        <span class="text-4xl font-extrabold text-ink">{{ $avg }}</span>
        <span class="text-xs text-slate-500">out of 100</span>
      </div>
    </div>
    <p class="mt-4 font-semibold text-ink">Site SEO score</p>
    <p class="mt-1 text-[13px] text-slate-500">Average of {{ $total }} URLs</p>
    <div class="mt-5 grid w-full grid-cols-3 gap-2 text-center text-xs">
      <a href="{{ route('admin.seo.pages', ['grade' => 'good']) }}" class="bg-emerald-50 p-2 text-emerald-700"><span class="block text-lg font-bold">{{ $counts['good'] ?? 0 }}</span>Good</a>
      <a href="{{ route('admin.seo.pages', ['grade' => 'ok']) }}" class="bg-slate-50 p-2 text-ink"><span class="block text-lg font-bold">{{ $counts['ok'] ?? 0 }}</span>Needs work</a>
      <a href="{{ route('admin.seo.pages', ['grade' => 'bad']) }}" class="bg-red-50 p-2 text-red-700"><span class="block text-lg font-bold">{{ $counts['bad'] ?? 0 }}</span>Poor</a>
    </div>
  </div>

  <div class="space-y-6">
    <div class="grid gap-4 sm:grid-cols-4">
      @foreach([['Redirects active', $redirects, route('admin.resources.index', 'redirects')], ['404 hits (7 days)', $notFound7, route('admin.seo.notfound')], ['Hidden from Google', $noindex, route('admin.seo.pages')], ['Search Console', !empty($settings['googleVerification']) ? 'Verified' : 'Not set', route('admin.seo.webmaster')]] as [$l, $v, $u])
        <a href="{{ $u }}" class="card card-body hover:ring-2 hover:ring-ink"><p class="text-xs font-semibold text-slate-500">{{ $l }}</p><p class="mt-1 text-xl font-extrabold text-ink">{{ $v }}</p></a>
      @endforeach
    </div>
    <div class="grid gap-6 xl:grid-cols-2">
      <div class="card">
        <div class="card-header"><h2 class="card-title">Most common problems</h2></div>
        <ul class="divide-y divide-slate-100">
          @forelse($issues as $label => $count)
            <li class="flex items-center justify-between gap-3 px-5 py-2.5 text-[13px]"><span class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-red-500"></span>{{ trim($label) }}</span><span class="badge-red">{{ $count }} pages</span></li>
          @empty
            <li class="px-5 py-8 text-center text-[13px] text-slate-500">No critical problems found.</li>
          @endforelse
        </ul>
      </div>
      <div class="card">
        <div class="card-header"><h2 class="card-title">Pages to improve first</h2></div>
        <ul class="divide-y divide-slate-100">
          @foreach($worst as $r)
            <li><a href="{{ route('admin.seo.pages.edit', ['path' => $r['path']]) }}" class="flex items-center gap-3 px-5 py-2.5 hover:bg-slate-50">
              <span class="score h-8 w-8 {{ \App\Services\SeoAnalyzer::color($r['score']) }}">{{ $r['score'] }}</span>
              <span class="min-w-0"><span class="block truncate text-[13px] font-semibold text-ink">{{ $r['title'] }}</span><span class="block truncate text-xs text-slate-500">{{ $r['path'] }}</span></span></a></li>
          @endforeach
        </ul>
      </div>
    </div>
    <div class="card overflow-x-auto">
      <div class="card-header"><h2 class="card-title">Score by content type</h2></div>
      <table class="table"><thead><tr><th>Type</th><th>URLs</th><th>Average score</th></tr></thead><tbody>
        @foreach($byType as $type => $t)
          <tr><td><a href="{{ route('admin.seo.pages', ['type' => $type]) }}" class="font-semibold text-ink hover:underline">{{ $type }}</a></td><td>{{ $t['count'] }}</td>
            <td><div class="flex items-center gap-3"><div class="h-2 w-40  bg-slate-100"><div class="h-2  {{ \App\Services\SeoAnalyzer::color($t['avg']) }}" style="width: {{ $t['avg'] }}%"></div></div><span class="font-semibold">{{ $t['avg'] }}</span></div></td></tr>
        @endforeach
      </tbody></table>
    </div>
    @if($duplicateTitles->count())
      <div class="card"><div class="card-header"><h2 class="card-title">Duplicate SEO titles</h2></div>
        <ul class="divide-y divide-slate-100">@foreach($duplicateTitles as $title => $group)<li class="px-5 py-2.5 text-[13px]"><span class="font-semibold">{{ $title }}</span> <span class="text-slate-500">used by {{ $group->pluck('path')->implode(', ') }}</span></li>@endforeach</ul>
      </div>
    @endif
  </div>
</div>
@endsection
