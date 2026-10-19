@extends('admin.layouts.app')
@section('title', 'SEO: Pages and Posts')
@section('heading', 'Pages and Posts')
@section('subheading', 'SEO title, description and score for every URL. Click a row to edit.')
@section('content')
<form method="GET" class="mb-4 flex flex-wrap gap-2">
  <input name="q" value="{{ request('q') }}" class="input w-64 py-1.5" placeholder="Search URL, title or keyword" aria-label="Search">
  <select name="type" class="input w-44 py-1.5" aria-label="Type"><option value="">All types</option>@foreach($types as $t)<option @selected(request('type') === $t)>{{ $t }}</option>@endforeach</select>
  <select name="grade" class="input w-40 py-1.5" aria-label="Score"><option value="">All scores</option><option value="bad" @selected(request('grade') === 'bad')>Poor</option><option value="ok" @selected(request('grade') === 'ok')>Needs work</option><option value="good" @selected(request('grade') === 'good')>Good</option></select>
  <select name="sort" class="input w-40 py-1.5" aria-label="Sort"><option value="score">Lowest score first</option><option value="path" @selected(request('sort') === 'path')>By URL</option></select>
  <button class="btn-secondary">Filter</button>
</form>
<div class="card overflow-x-auto">
  <table class="table">
    <thead><tr><th class="w-14">Score</th><th>Page</th><th>SEO title and description</th><th>Focus keyword</th><th>Type</th></tr></thead>
    <tbody>
      @forelse($rows as $r)
        <tr class="cursor-pointer" onclick="location.href='{{ route('admin.seo.pages.edit', ['path' => $r['path']]) }}'">
          <td><span class="score {{ \App\Services\SeoAnalyzer::color($r['score']) }}">{{ $r['score'] }}</span></td>
          <td class="max-w-[220px]"><p class="truncate font-semibold text-navy">{{ $r['title'] }}</p><p class="truncate text-xs text-slate-500">{{ $r['path'] }}</p>
            @if($r['noindex'])<span class="badge-red mt-1">noindex</span>@endif @if($r['custom'])<span class="badge-navy mt-1">custom</span>@endif</td>
          <td class="max-w-[420px]"><p class="truncate text-[13px] text-[#1a0dab]">{{ $r['seo_title'] }}</p><p class="line-clamp-2 text-xs text-slate-500">{{ $r['description'] }}</p></td>
          <td>{!! $r['keyword'] ? '<span class="badge-gray">'.e($r['keyword']).'</span>' : '<span class="text-xs text-red-600">Not set</span>' !!}</td>
          <td class="whitespace-nowrap text-slate-500">{{ $r['type'] }}</td>
        </tr>
      @empty
        <tr><td colspan="5" class="py-14 text-center text-slate-500">No URLs match these filters.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
