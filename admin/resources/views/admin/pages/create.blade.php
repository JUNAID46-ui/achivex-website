@extends('admin.layouts.app')
@section('title', 'Add New Page')
@section('heading', 'Add New Page')
@section('subheading', 'Create a new page on the website. After saving you can add and edit its sections in the Page Builder.')
@section('actions')
  <a href="{{ route('admin.pages.index') }}" class="btn-secondary">&larr; All pages</a>
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
      <div class="flex items-center rounded-md ring-1 ring-slate-300 focus-within:ring-2 focus-within:ring-ink">
        <span class="pl-3 text-[13px] text-slate-500">{{ parse_url(config('admin.site_public_url'), PHP_URL_HOST) }}/</span>
        <input id="slug" name="slug" x-model="slug" @input="touched = true" required class="w-full border-0 bg-transparent py-2 pl-0.5 text-[14px] focus:ring-0" placeholder="our-pricing">
        <span class="pr-3 text-[13px] text-slate-500">/</span>
      </div>
      @error('slug')<p class="error">{{ $message }}</p>@enderror
    </div>
    <div>
      <label for="description" class="label">Short description (used by Google)</label>
      <textarea id="description" name="description" rows="3" class="input" placeholder="One or two sentences about this page.">{{ old('description') }}</textarea>
    </div>
    <fieldset>
      <legend class="label">Starting layout</legend>
      <div class="grid gap-3 sm:grid-cols-3">
        @foreach(['basic' => ['Heading and text', 'Page heading, a text block and a call to action.'], 'image' => ['Heading, image and cards', 'Page heading, image with text, a cards grid and a call to action.'], 'blank' => ['Blank', 'Only the page heading. Add everything else yourself.']] as $val => [$name, $desc])
          <label class="cursor-pointer rounded-md border border-slate-200 p-4 has-[:checked]:border-ink has-[:checked]:bg-accent-50">
            <input type="radio" name="layout" value="{{ $val }}" class="text-ink" @checked(old('layout', 'basic') === $val)>
            <span class="mt-2 block font-semibold text-ink">{{ $name }}</span>
            <span class="mt-1 block text-xs text-slate-500">{{ $desc }}</span>
          </label>
        @endforeach
      </div>
    </fieldset>
  </div>
  <aside>
    <div class="card">
      <div class="card-header"><h2 class="card-title">Publish</h2></div>
      <div class="card-body">
        <label class="flex items-center gap-2 text-[13px]"><input type="hidden" name="published" value="0"><input type="checkbox" name="published" value="1" class="rounded border-slate-300 text-ink" @checked(old('published', true))> Publish on the website now</label>
        <p class="help mt-2">Leave unticked to keep it as a draft while you work on it.</p>
      </div>
      <div class="flex justify-end pt-2"><button class="btn-primary">Create page</button></div>
    </div>
  </aside>
</form>
@endsection
