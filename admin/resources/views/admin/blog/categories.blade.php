@extends('admin.layouts.app')
@section('title', 'Blog categories')
@section('heading', 'Blog categories')
@section('subheading', 'The names that label blog posts and fill the filter on the Blogs page. There are no separate category pages.')
@section('actions')
  <a href="{{ route('admin.resources.index', 'blog-posts') }}" class="btn-secondary">&larr; All blog posts</a>
@endsection
@section('content')
<form method="POST" class="max-w-2xl space-y-5" data-unsaved-warning
      x-data="{ items: @js($categories), used: @js($used), add() { this.items.push('') }, move(i, d) { const j = i + d; if (j < 0 || j >= this.items.length) return; const [v] = this.items.splice(i, 1); this.items.splice(j, 0, v) } }">
  @csrf
  <div class="card card-body space-y-3">
    <template x-for="(name, i) in items" :key="i">
      <div class="flex items-center gap-2">
        <input class="input" :name="`categories[${i}]`" x-model="items[i]" maxlength="40" required>
        <button type="button" class="btn-ghost btn-sm" @click="move(i, -1)" :disabled="i === 0" title="Move up">@include('admin.partials.icon', ['name' => 'arrow-up'])</button>
        <button type="button" class="btn-ghost btn-sm" @click="move(i, 1)" :disabled="i === items.length - 1" title="Move down">@include('admin.partials.icon', ['name' => 'arrow-down'])</button>
        <button type="button" class="btn-ghost btn-sm text-red-600" @click="items.splice(i, 1)" :disabled="items.length === 1" title="Remove">@include('admin.partials.icon', ['name' => 'trash'])</button>
      </div>
    </template>
    <button type="button" class="btn-secondary" @click="add()">@include('admin.partials.icon', ['name' => 'plus']) Add category</button>
    @error('categories')<p class="error">{{ $message }}</p>@enderror
    @error('categories.*')<p class="error">{{ $message }}</p>@enderror
  </div>
  <p class="help" x-show="used.length">In use on posts now: <span x-text="used.join(', ')"></span>. A removed name stays on the posts that use it until you change them.</p>
  <button class="btn-primary" data-loading>Save categories</button>
</form>
@endsection
