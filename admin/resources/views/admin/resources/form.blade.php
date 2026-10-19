@extends('admin.layouts.app')
@php
  $isNew = ! $record->exists;
  $path = $record->exists ? $resource->sitePath($record) : null;
  $siteUrl = rtrim(config('admin.site_url'), '/');
  $publicUrl = rtrim(config('admin.site_public_url'), '/');
  $main = collect($resource->fields())->reject(fn ($f) => $f['side'] ?? false);
  $side = collect($resource->fields())->filter(fn ($f) => $f['side'] ?? false);
  $value = fn ($f) => \App\Admin\FieldValues::forForm($f, $record->{$f['name']});
@endphp
@section('title', $isNew ? 'Add '.$resource->singular : 'Edit '.$resource->singular)
@section('heading', $isNew ? 'Add '.$resource->singular : 'Edit '.$resource->singular)
@section('actions')
  <a href="{{ route('admin.resources.index', $resource->slug) }}" class="btn-secondary">&larr; All {{ strtolower($resource->label) }}</a>
  @unless($isNew)<a href="{{ route('admin.resources.create', $resource->slug) }}" class="btn-secondary">@include('admin.partials.icon', ['name' => 'plus']) Add new</a>@endunless
@endsection

@section('content')
<form method="POST" action="{{ $isNew ? route('admin.resources.store', $resource->slug) : route('admin.resources.update', [$resource->slug, $record->getKey()]) }}"
      class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
  @csrf
  @unless($isNew) @method('PUT') @endunless

  <div class="min-w-0 space-y-6">
    <div class="card card-body space-y-5">
      @foreach($main as $field)
        @include('admin.partials.field', ['field' => $field, 'value' => $value($field)])
      @endforeach
    </div>

    @if($resource->seo)
      @php
        $template = \App\Models\Content\SiteSetting::group('seo')['titleTemplate'] ?? '%title% | AchiveX LLC';
        $defaultDesc = $record->excerpt ?? $record->summary ?? $record->description ?? '';
        $content = $record->exists ? $resource->seoContent($record)['text'] : '';
      @endphp
      <div class="card" x-data="{
              title: @js(old('seo_title', $seoMeta?->title)),
              desc: @js(old('seo_description', $seoMeta?->description)),
              keyword: @js(old('seo_keyword', $seoMeta?->focus_keyword)),
              fallbackTitle: @js($resource->titleOf($record)),
              fallbackDesc: @js($defaultDesc),
              template: @js($template),
              slug: @js($record->slug ?? ''),
              content: @js($content),
              get fullTitle() { return this.template.replace('%title%', this.title || this.fallbackTitle) },
              get fullDesc() { return this.desc || this.fallbackDesc },
              get result() { return seoChecks({ title: this.fullTitle, description: this.fullDesc, keyword: this.keyword, slug: this.slug, content: this.content }) },
           }">
        <div class="card-header">
          <h2 class="card-title">SEO</h2>
          <span class="score" :class="result.score >= 80 ? 'bg-emerald-500' : result.score >= 50 ? 'bg-amber' : 'bg-red-500'" x-text="result.score"></span>
        </div>
        <div class="card-body grid gap-6 lg:grid-cols-2">
          <div class="space-y-4">
            <div>
              <p class="label">Google preview</p>
              <div class="rounded-md border border-slate-200 bg-white p-4">
                <p class="truncate text-xs text-slate-600">{{ parse_url($publicUrl, PHP_URL_HOST) }}{{ $path ?? '/' }}</p>
                <p class="mt-1 truncate text-[18px] leading-snug text-[#1a0dab]" x-text="fullTitle"></p>
                <p class="mt-1 line-clamp-2 text-[13px] leading-snug text-slate-600" x-text="fullDesc"></p>
              </div>
            </div>
            <div>
              <label for="seo_keyword" class="label">Focus keyword</label>
              <input id="seo_keyword" name="seo_keyword" x-model="keyword" class="input" placeholder="e.g. cloud migration">
            </div>
            <div>
              <label for="seo_title" class="label">SEO title <span class="font-normal text-slate-400" x-text="'(' + fullTitle.length + ' of 60)'"></span></label>
              <input id="seo_title" name="seo_title" x-model="title" class="input" :placeholder="fallbackTitle">
              <p class="help">Leave empty to use the {{ strtolower($resource->singular) }} title.</p>
            </div>
            <div>
              <label for="seo_description" class="label">Meta description <span class="font-normal text-slate-400" x-text="'(' + fullDesc.length + ' of 160)'"></span></label>
              <textarea id="seo_description" name="seo_description" x-model="desc" rows="3" class="input" :placeholder="fallbackDesc"></textarea>
            </div>
            @include('admin.partials.field', ['field' => ['name' => 'seo_og_image', 'label' => 'Social share image (optional)', 'type' => 'image'], 'value' => $seoMeta?->og_image])
            @include('admin.partials.field', ['field' => ['name' => 'seo_noindex', 'label' => 'Hide from search engines (noindex)', 'type' => 'toggle'], 'value' => $seoMeta?->noindex])
          </div>
          <div>
            <p class="label">Analysis</p>
            <ul class="space-y-2">
              <template x-for="c in result.checks" :key="c.label">
                <li class="flex gap-2 text-[13px]">
                  <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full" :class="c.status === 'good' ? 'bg-emerald-500' : c.status === 'ok' ? 'bg-amber' : 'bg-red-500'"></span>
                  <span x-text="c.label"></span>
                </li>
              </template>
            </ul>
            @if($analysis)
              <details class="mt-5 rounded-md bg-slate-50 p-3 text-[13px]">
                <summary class="cursor-pointer font-semibold text-navy">Full content analysis (last save, score {{ $analysis['score'] }})</summary>
                <ul class="mt-3 space-y-2">
                  @foreach($analysis['checks'] as $c)
                    <li class="flex gap-2">
                      <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full {{ ['good' => 'bg-emerald-500', 'ok' => 'bg-amber', 'bad' => 'bg-red-500'][$c['status']] }}"></span>
                      <span>{{ $c['label'] }} @if($c['tip'])<span class="text-slate-500">{{ $c['tip'] }}</span>@endif</span>
                    </li>
                  @endforeach
                </ul>
              </details>
            @endif
          </div>
        </div>
      </div>
    @endif
  </div>

  <aside class="space-y-6">
    <div class="card xl:sticky xl:top-16">
      <div class="card-header"><h2 class="card-title">{{ $resource->hasPublished ? 'Publish' : 'Save' }}</h2></div>
      <div class="card-body space-y-4">
        @foreach($side as $field)
          @include('admin.partials.field', ['field' => $field, 'value' => $value($field)])
        @endforeach
        @if($path)
          <a href="{{ $siteUrl.$path }}" target="_blank" class="flex items-center gap-1.5 text-[13px] font-semibold text-navy hover:text-amber-600">
            @include('admin.partials.icon', ['name' => 'external']) View on website
          </a>
        @endif
      </div>
      <div class="flex items-center justify-between gap-2 border-t border-slate-200 bg-slate-50 px-5 py-3">
        @unless($isNew)
          <button type="submit" form="delete-form" class="text-[13px] font-semibold text-red-600 hover:underline">Delete</button>
        @else
          <span></span>
        @endunless
        <button class="btn-primary">{{ $isNew ? ($resource->hasPublished ? 'Publish' : 'Create') : 'Update' }}</button>
      </div>
    </div>
  </aside>
</form>

@unless($isNew)
  <form id="delete-form" method="POST" action="{{ route('admin.resources.destroy', [$resource->slug, $record->getKey()]) }}"
        onsubmit="return confirm('Delete this {{ strtolower($resource->singular) }}? This cannot be undone.')" class="hidden">
    @csrf @method('DELETE')
  </form>
@endunless
@endsection
