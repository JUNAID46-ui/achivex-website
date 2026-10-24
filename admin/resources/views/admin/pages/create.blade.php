@extends('admin.layouts.app')
@section('title', 'Add New Page')
@section('heading', 'Add New Page')
@section('subheading', 'Create a new page on the website. After saving you can add and edit its sections in the Page Builder.')
@section('actions')
  <a href="{{ route('admin.pages.create') }}" class="btn-secondary">&larr; Choose another type</a>
@endsection

@section('content')
<form method="POST" action="{{ route('admin.pages.store') }}" class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]"
      x-data="{ title: @js(old('title', '')), slug: @js(old('slug', '')), touched: @js((bool) old('slug')) }">
  @csrf
  <div class="card card-body space-y-5">
    <div>
      <label for="title" class="label">Page title</label>
      <input id="title" name="title" x-model="title" @input="if (!touched) slug = slugify(title)" required class="input text-lg" placeholder="e.g. Our Pricing">
      @error('title')<p class="error">{{ $message }}</p>@enderror
    </div>
    <div>
      <label for="slug" class="label">Page address</label>
      <div class="flex items-center  ring-1 ring-neutral-300 focus-within:ring-2 focus-within:ring-ink">
        <span class="pl-3 text-[13px] text-neutral-500">{{ parse_url(config('admin.site_public_url'), PHP_URL_HOST) }}/</span>
        <input id="slug" name="slug" x-model="slug" @input="touched = true" required class="w-full border-0 bg-transparent py-2 pl-0.5 text-[14px] focus:ring-0" placeholder="our-pricing">
        <span class="pr-3 text-[13px] text-neutral-500">/</span>
      </div>
      @error('slug')<p class="error">{{ $message }}</p>@enderror
    </div>
    <div>
      <label for="description" class="label">Short description (used by Google)</label>
      <textarea id="description" name="description" rows="3" class="input" placeholder="One or two sentences about this page.">{{ old('description') }}</textarea>
    </div>
    <div class="flex items-center justify-between gap-4 border border-neutral-200 bg-neutral-50 p-4">
      <div>
        <p class="label mb-0">Template: {{ $template[0] }}</p>
        <p class="help mt-1">{{ $template[1] }} You can add, remove and restyle every section afterwards.</p>
      </div>
      <a href="{{ route('admin.pages.create') }}" class="btn-secondary btn-sm shrink-0">Change</a>
      <input type="hidden" name="layout" value="{{ $layout }}">
    </div>
  </div>
  <aside>
    <div class="card">
      <div class="card-header"><h2 class="card-title">Publish</h2></div>
      <div class="card-body">
        <label class="flex items-center gap-2 text-[13px]"><input type="hidden" name="published" value="0"><input type="checkbox" name="published" value="1" class="border-neutral-300 text-ink" @checked(old('published', true))> Publish on the website now</label>
        <p class="help mt-2">Leave unticked to keep it as a draft while you work on it.</p>
      </div>
      <div class="flex justify-end pt-2"><button class="btn-primary">Create page</button></div>
    </div>
  </aside>
</form>
@endsection
