@extends('admin.layouts.app')
@section('title', 'Edit Page: '.$page['title'])
@section('heading', 'Edit Page: '.$page['title'])
@section('subheading', 'A page made in the admin. Its content is built from sections in the Page Builder.')
@section('actions')
  <a href="{{ route('admin.pages.index') }}" class="btn-secondary">&larr; All pages</a>
  <a href="{{ route('admin.seo.pages.edit', ['path' => $page['path']]) }}" class="btn-secondary">@include('admin.partials.icon', ['name' => 'search']) Fix SEO</a>
  <a href="{{ $siteUrl.$page['path'] }}" target="_blank" class="btn-secondary">@include('admin.partials.icon', ['name' => 'external']) View</a>
@endsection

@section('content')
<div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
  <form method="POST" action="{{ route('admin.pages.update') }}" class="card" id="page-form">
    @csrf
    <input type="hidden" name="path" value="{{ $page['path'] }}">
    <div class="card-header"><h2 class="card-title">Page settings</h2></div>
    <div class="card-body space-y-5">
      <div>
        <label for="title" class="label">Page title</label>
        <input id="title" name="title" value="{{ old('title', $page['title']) }}" required class="input">
      </div>
      <div>
        <label for="slug" class="label">Page address</label>
        <div class="flex items-center rounded-md ring-1 ring-slate-300 focus-within:ring-2 focus-within:ring-navy">
          <span class="pl-3 text-[13px] text-slate-500">{{ parse_url(config('admin.site_public_url'), PHP_URL_HOST) }}/</span>
          <input id="slug" name="slug" value="{{ old('slug', trim($page['path'], '/')) }}" required class="w-full border-0 bg-transparent py-2 pl-0.5 text-[14px] focus:ring-0">
          <span class="pr-3 text-[13px] text-slate-500">/</span>
        </div>
        <p class="help">Changing the address breaks old links. Add a redirect from the old address under SEO, Redirects.</p>
      </div>
      <div>
        <label for="description" class="label">Short description (used by Google)</label>
        <textarea id="description" name="description" rows="3" class="input">{{ old('description', $page['description']) }}</textarea>
      </div>
      <label class="flex items-center gap-2 text-[13px]"><input type="hidden" name="published" value="0"><input type="checkbox" name="published" value="1" class="rounded border-slate-300 text-navy" @checked(old('published', $page['published']))> Published on the website</label>
    </div>
    <div class="flex justify-end border-t border-slate-200 bg-slate-50 px-5 py-3"><button class="btn-primary">Save settings</button></div>
  </form>

  <aside class="space-y-6">
    <div class="card card-body space-y-3">
      <p class="text-[13px] text-slate-600">This page has <strong>{{ $sectionCount }}</strong> {{ \Illuminate\Support\Str::plural('section', $sectionCount) }}. Add text, images, cards and galleries in the Page Builder.</p>
      <a href="{{ route('admin.builder', ['page' => $page['path']]) }}" class="btn-primary w-full justify-center">@include('admin.partials.icon', ['name' => 'layout']) Edit content in Page Builder</a>
    </div>
    <form method="POST" action="{{ route('admin.pages.destroy') }}" class="card card-body" onsubmit="return confirm('Delete this page and all its sections? This cannot be undone.')">
      @csrf
      <input type="hidden" name="path" value="{{ $page['path'] }}">
      <button class="btn-danger w-full justify-center">@include('admin.partials.icon', ['name' => 'trash']) Delete page</button>
    </form>
  </aside>
</div>
@endsection
