@extends('admin.layouts.app')
@section('title', 'Add New Page')
@section('heading', 'Add New Page')
@section('subheading', 'What would you like to add? Pick a type and you will go straight to the right form.')
@section('actions')
  <a href="{{ route('admin.pages.index') }}" class="btn-secondary">&larr; All pages</a>
@endsection

@section('content')
@php
  $content = [
    ['blog-posts', 'Blog post', 'An article with headings, featured image, FAQs and its own header colour or image.', 'file'],
    ['case-studies', 'Case study', 'A client story with challenge, solution and results.', 'star'],
    ['news', 'News article', 'A dated announcement for the Newsroom.', 'bell'],
    ['whitepapers', 'Whitepaper or eBook', 'A downloadable guide with a cover and a request form.', 'download'],
    ['jobs', 'Job opening', 'A role shown on the Careers page.', 'users'],
  ];
@endphp
<div class="space-y-8">
  <section>
    <h2 class="text-[15px] font-semibold text-ink">Website page</h2>
    <p class="help mt-1">A new page with its own address, built from sections in the Page Builder. Every section has text, images, colours, fonts and spacing you can change.</p>
    <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
      @foreach($templates as $key => [$name, $desc, $sections])
        <a href="{{ route('admin.pages.create', ['template' => $key]) }}" class="group card card-body block transition hover:ring-2 hover:ring-ink">
          <span class="block font-semibold text-ink">{{ $name }}</span>
          <span class="mt-1 block text-[13px] text-neutral-500">{{ $desc }}</span>
          <span class="mt-3 flex flex-wrap gap-1">
            @foreach($sections as $type)
              <span class="bg-neutral-100 px-2 py-0.5 text-[11px] text-neutral-600">{{ config("builder.$type.label", $type) }}</span>
            @endforeach
          </span>
        </a>
      @endforeach
    </div>
  </section>

  <section>
    <h2 class="text-[15px] font-semibold text-ink">Content item</h2>
    <p class="help mt-1">These use a ready made website template. Fill in the form and the page is created for you.</p>
    <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
      @foreach($content as [$slug, $name, $desc, $icon])
        @if(array_key_exists($slug, \App\Admin\Registry::map()))
          <a href="{{ route('admin.resources.create', $slug) }}" class="card card-body flex gap-3 transition hover:ring-2 hover:ring-ink">
            <span class="mt-0.5 text-neutral-500">@include('admin.partials.icon', ['name' => $icon])</span>
            <span>
              <span class="block font-semibold text-ink">{{ $name }}</span>
              <span class="mt-1 block text-[13px] text-neutral-500">{{ $desc }}</span>
            </span>
          </a>
        @endif
      @endforeach
    </div>
  </section>
</div>
@endsection
