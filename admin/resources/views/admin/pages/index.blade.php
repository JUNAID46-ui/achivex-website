@extends('admin.layouts.app')
@section('title', 'Pages')
@section('heading', 'Pages')
@section('subheading', 'Every page of the website. Edit its text and images, add sections, or fix its SEO.')
@section('actions')
  <a href="{{ route('admin.pages.create') }}" class="btn-primary">@include('admin.partials.icon', ['name' => 'plus']) Add New Page</a>
@endsection

@section('content')
<form method="GET" class="mb-4 flex flex-wrap gap-2">
  <label for="q" class="sr-only">Search</label>
  <input id="q" name="q" value="{{ request('q') }}" class="input w-64 py-1.5" placeholder="Search title or address">
  <label for="type" class="sr-only">Type</label>
  <select id="type" name="type" class="input w-48 py-1.5"><option value="">All page types</option>@foreach($types as $t)<option @selected(request('type') === $t)>{{ $t }}</option>@endforeach</select>
  <button class="btn-secondary">Filter</button>
  <span class="ml-auto self-center text-xs text-slate-500">{{ $rows->count() }} pages</span>
</form>

<div class="card overflow-x-auto">
  <table class="table">
    <thead><tr><th>Page</th><th>Type</th><th>Status</th><th class="w-16">SEO</th><th class="text-right">Actions</th></tr></thead>
    <tbody>
      @forelse($rows as $r)
        @php($score = $scores[$r['path']] ?? null)
        <tr>
          <td class="max-w-[320px]">
            <a href="{{ $r['kind'] === 'home' ? route('admin.builder', ['page' => '/']) : route('admin.pages.edit', ['path' => $r['path']]) }}" class="block truncate font-semibold text-navy hover:text-amber-600">{{ $r['title'] }}</a>
            <span class="block truncate text-xs text-slate-500">{{ $r['path'] }}</span>
          </td>
          <td class="whitespace-nowrap text-slate-600">{{ $r['type'] }}</td>
          <td class="whitespace-nowrap">
            @if(! $r['published'])<span class="badge-gray">Draft</span>
            @elseif($r['kind'] === 'custom')<span class="badge-green">Published</span>
            @elseif($r['edited'])<span class="badge-amber">Edited</span>
            @else<span class="badge-gray">Original</span>@endif
          </td>
          <td>@if($score !== null)<span class="score {{ \App\Services\SeoAnalyzer::color($score) }}">{{ $score }}</span>@else<span class="text-xs text-slate-400">n/a</span>@endif</td>
          <td>
            <div class="flex flex-wrap justify-end gap-1.5">
              @if($r['kind'] !== 'home')
                <a href="{{ route('admin.pages.edit', ['path' => $r['path']]) }}" class="btn-secondary btn-sm">@include('admin.partials.icon', ['name' => 'edit']) Edit Page</a>
              @endif
              <a href="{{ route('admin.builder', ['page' => $r['path']]) }}" class="btn-secondary btn-sm">@include('admin.partials.icon', ['name' => 'layout']) Sections</a>
              <a href="{{ route('admin.seo.pages.edit', ['path' => $r['path']]) }}" class="btn-secondary btn-sm">@include('admin.partials.icon', ['name' => 'search']) Fix SEO</a>
              <a href="{{ $siteUrl.$r['path'] }}" target="_blank" class="btn-ghost btn-sm" title="View on website">@include('admin.partials.icon', ['name' => 'external'])</a>
            </div>
          </td>
        </tr>
      @empty
        <tr><td colspan="5" class="py-14 text-center text-slate-500">No pages match this search.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
