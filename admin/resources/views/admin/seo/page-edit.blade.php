@extends('admin.layouts.app')
@section('title', 'SEO: '.$row['title'])
@section('heading', $row['title'])
@section('subheading', $row['path'].' · '.$row['type'])
@section('actions')
  <a href="{{ route('admin.seo.pages') }}" class="btn-secondary">&larr; All pages</a>
  <a href="{{ rtrim(config('admin.site_url'), '/').$row['path'] }}" target="_blank" class="btn-secondary">@include('admin.partials.icon', ['name' => 'external']) View page</a>
@endsection
@section('content')
<form method="POST" action="{{ route('admin.seo.pages.save') }}" class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_380px]"
      x-data="{ title: @js(old('title', $meta?->title)), desc: @js(old('description', $meta?->description)), keyword: @js(old('focus_keyword', $meta?->focus_keyword ?? ($page['focus'] ?? ''))),
                fallbackTitle: @js($row['title']), fallbackDesc: @js($page['description'] ?? $row['description']), template: @js($template), path: @js($row['path']), content: @js($page['content'] ?? ''),
                get fullTitle() { return this.path === '/' && !this.title ? @js(\App\Models\Content\SiteSetting::group('seo')['homeTitle'] ?? $row['seo_title']) : this.template.replace('%title%', this.title || this.fallbackTitle) },
                get fullDesc() { return this.desc || this.fallbackDesc },
                get result() { return seoChecks({ title: this.fullTitle, description: this.fullDesc, keyword: this.keyword, slug: this.path, content: this.content }) } }">
  @csrf
  <input type="hidden" name="path" value="{{ $row['path'] }}">
  <div class="space-y-6">
    <div class="card card-body">
      <p class="label">Google preview</p>
      <div class="border border-neutral-200 p-4">
        <p class="text-xs text-neutral-600">{{ parse_url(config('admin.site_public_url'), PHP_URL_HOST) }}{{ $row['path'] }}</p>
        <p class="mt-1 text-[19px] leading-snug text-[#1a0dab]" x-text="fullTitle"></p>
        <p class="mt-1 text-[13px] leading-snug text-neutral-600" x-text="fullDesc"></p>
      </div>
    </div>
    <div class="card card-body space-y-4">
      <div><label for="focus_keyword" class="label">Focus keyword</label><input id="focus_keyword" name="focus_keyword" x-model="keyword" class="input" placeholder="The main phrase people search for"></div>
      <div><label for="title" class="label">SEO title <span class="font-normal text-neutral-400" x-text="'(' + fullTitle.length + ' of 60)'"></span></label>
        <input id="title" name="title" x-model="title" class="input" :placeholder="fallbackTitle"><p class="help">Shown as: <code x-text="template"></code>. Leave empty to use the page name.</p></div>
      <div><label for="description" class="label">Meta description <span class="font-normal text-neutral-400" x-text="'(' + fullDesc.length + ' of 160)'"></span></label>
        <textarea id="description" name="description" x-model="desc" rows="3" class="input" :placeholder="fallbackDesc"></textarea></div>
      @include('admin.partials.field', ['field' => ['name' => 'og_image', 'label' => 'Social share image', 'type' => 'image'], 'value' => $meta?->og_image, 'record' => new \App\Models\Content\SeoMeta])
      <div><label for="canonical" class="label">Canonical URL (advanced)</label><input id="canonical" name="canonical" value="{{ old('canonical', $meta?->canonical) }}" class="input" placeholder="Leave empty to use this page's own URL"></div>
      @include('admin.partials.field', ['field' => ['name' => 'noindex', 'label' => 'Hide this page from search engines (noindex)', 'type' => 'toggle'], 'value' => $meta?->noindex, 'record' => new \App\Models\Content\SeoMeta])
      <div class="border-t border-neutral-100 pt-4">
        @include('admin.seo.partials.schema-field', ['name' => 'schema', 'value' => $meta?->schema, 'pageUrl' => rtrim(config('admin.site_public_url'), '/').$row['path'], 'pageTitle' => $row['title']])
      </div>
      <button class="btn-primary">Save SEO settings</button>
    </div>
  </div>
  <div class="card self-start">
    <div class="card-header"><h2 class="card-title">Analysis</h2><span class="score" :class="result.score >= 80 ? 'bg-green-500' : result.score >= 50 ? 'bg-ink' : 'bg-red-500'" x-text="result.score"></span></div>
    <ul class="card-body space-y-2.5">
      <template x-for="c in result.checks" :key="c.label">
        <li class="flex gap-2 text-[13px]"><span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full" :class="c.status === 'good' ? 'bg-green-500' : c.status === 'ok' ? 'bg-ink' : 'bg-red-500'"></span><span x-text="c.label"></span></li>
      </template>
    </ul>
    <div class="border-t border-neutral-100 px-5 py-3 text-xs text-neutral-500">The page text itself is part of the website code. Ask your developer to update it, or contact AchiveX.</div>
  </div>
</form>
@endsection
