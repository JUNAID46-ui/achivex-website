@extends('admin.layouts.app')
@section('title', 'Edit Page: '.$page['title'])
@section('heading', 'Edit Page: '.$page['title'])
@section('subheading', 'Change the text and images of this page. Click Replace on an image to pick a new one, or Remove to take it off.')
@section('actions')
  <a href="{{ route('admin.pages.index') }}" class="btn-secondary">&larr; All pages</a>
  <a href="{{ route('admin.seo.pages.edit', ['path' => \App\Support\Permalinks::map($page['path'])]) }}" class="btn-secondary">@include('admin.partials.icon', ['name' => 'search']) Fix SEO</a>
  <a href="{{ $siteUrl.\App\Support\Permalinks::map($page['path']) }}" target="_blank" class="btn-secondary">@include('admin.partials.icon', ['name' => 'external']) View</a>
@endsection

@section('content')
<form method="POST" action="{{ route('admin.pages.update') }}" class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]"
      x-data="pageEditor(@js(['fields' => $fields, 'values' => $values]))" @submit="prepare($event)">
  @csrf
  <input type="hidden" name="path" value="{{ $page['path'] }}">
  <input type="hidden" name="content_json" x-ref="json">

  <div class="card card-body min-w-0 space-y-5">
    @include('admin.partials.content-fields', ['fields' => 'fields'])
  </div>

  <aside class="space-y-6">
    <div class="card xl:sticky xl:top-16">
      <div class="card-header"><h2 class="card-title">Save</h2>
        @if($saved)<span class="badge-warn">Edited</span>@else<span class="badge-gray">Original</span>@endif
      </div>
      <div class="card-body space-y-3 text-[13px] text-neutral-600">
        <p><span class="text-neutral-500">Address:</span> <span class="font-semibold">{{ \App\Support\Permalinks::map($page['path']) }}</span></p>
        <p><span class="text-neutral-500">Type:</span> {{ $page['type'] }}</p>
        @if($saved)<p><span class="text-neutral-500">Last saved:</span> {{ $saved->updated_at?->diffForHumans() }}</p>@endif
        <p :class="dirty ? 'font-semibold text-ink' : 'text-neutral-400'" x-text="dirty ? 'Unsaved changes' : 'No unsaved changes'"></p>
      </div>
      <div class="flex items-center justify-end gap-2 pt-2">
        <button class="btn-primary">Save and publish</button>
      </div>
    </div>
    <div class="card card-body space-y-3 text-[13px]">
      <p class="text-neutral-600">Want more on this page? Add extra sections such as text blocks, image galleries or cards. They appear at the bottom, above the footer.</p>
      <a href="{{ route('admin.builder', ['page' => $page['path']]) }}" class="btn-secondary w-full justify-center">@include('admin.partials.icon', ['name' => 'layout']) Add sections ({{ $sectionCount }})</a>
    </div>
    @if($saved)
      <div class="card card-body">
        <button type="submit" form="reset-form" class="text-[13px] font-semibold text-red-600 hover:underline">Restore the original content</button>
      </div>
    @endif
  </aside>
</form>
@if($saved)
  <form id="reset-form" method="POST" action="{{ route('admin.pages.reset') }}" class="hidden" onsubmit="return confirm('Undo all your changes to this page and restore the original content?')">
    @csrf
    <input type="hidden" name="path" value="{{ $page['path'] }}">
  </form>
@endif
@include('admin.pages.partials.revisions')
@endsection

@push('scripts')
<script>
function pageEditor(cfg) {
  return {
    ...contentForm.methods,
    fields: cfg.fields,
    form: contentForm.toForm(cfg.fields, cfg.values),
    dirty: false,
    init() {
      window.addEventListener('beforeunload', (e) => { if (this.dirty) { e.preventDefault(); e.returnValue = '' } })
    },
    prepare() {
      this.$refs.json.value = JSON.stringify(contentForm.fromForm(this.fields, this.form))
      this.dirty = false
    },
  }
}
</script>
@endpush
