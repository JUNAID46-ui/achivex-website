@extends('admin.layouts.app')
@php
  $isNew = ! $record->exists;
  $path = $record->exists ? $resource->sitePath($record) : null;
  $siteUrl = rtrim(config('admin.site_url'), '/');
  $publicUrl = rtrim(config('admin.site_public_url'), '/');
  $main = collect($resource->fields())->reject(fn ($f) => $f['side'] ?? false);
  $side = collect($resource->fields())->filter(fn ($f) => ($f['side'] ?? false) && ! ($resource->hasPublished && $f['name'] === 'published'));
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
      class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]" data-unsaved-warning>
  @csrf
  @unless($isNew) @method('PUT') @endunless

  <div class="min-w-0 space-y-6">
    <div class="card card-body space-y-5">
      @foreach($main as $field)
        {{-- Blog posts write the opening keyword, opening paragraph and sections in one article editor. --}}
        @if($resource->slug === 'blog-posts' && in_array($field['name'], ['intro_keyword', 'intro_text', 'sections']))
          @if($field['name'] === 'intro_keyword') @include('admin.resources.article-editor') @endif
          @continue
        @endif
        @include('admin.partials.field', ['field' => $field, 'value' => $value($field)])
      @endforeach
    </div>

    @if($resource->seo)
      @php
        $template = \App\Models\Content\SiteSetting::group('seo')['titleTemplate'] ?? '%title% | AchiveX LLC';
        $defaultDesc = $record->excerpt ?? $record->summary ?? $record->description ?? '';
        $seoIn = $record->exists ? $resource->seoContent($record) : [];
        $content = $seoIn['text'] ?? '';
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
              first: @js($seoIn['first'] ?? ''),
              headings: @js($seoIn['headings'] ?? []),
              raw: @js($seoIn['raw'] ?? ''),
              paragraphs: @js($seoIn['paragraphs'] ?? []),
              fromPaste(p) { if (p.title) this.fallbackTitle = p.title; if (!p.seo) return; this.content = p.seo.text; this.first = p.seo.first; this.headings = p.seo.headings; this.raw = p.seo.raw; this.paragraphs = p.seo.paragraphs; if (!this.desc) this.desc = p.description; if (!this.keyword && p.intro_keyword) this.keyword = p.intro_keyword; if (p.title) this.fallbackTitle = p.title; this.$nextTick(() => { const sl = this.$root.closest('form')?.querySelector(`[name='slug']`); if (sl && sl.value) this.slug = sl.value }) },
              get fullTitle() { return this.template.replace('%title%', this.title || this.fallbackTitle) },
              get fullDesc() { return this.desc || this.fallbackDesc },
              get result() { return seoChecks({ title: this.fullTitle, description: this.fullDesc, keyword: this.keyword, slug: this.slug, content: this.content, first: this.first, headings: this.headings, raw: this.raw, paragraphs: this.paragraphs }) },
           }" @quickpaste.window="fromPaste($event.detail)">
        <div class="card-header">
          <h2 class="card-title">SEO</h2>
          <span class="score" :class="result.score >= 80 ? 'bg-green-500' : result.score >= 50 ? 'bg-ink' : 'bg-red-500'" x-text="result.score"></span>
        </div>
        <div class="card-body grid gap-6 lg:grid-cols-2">
          <div class="space-y-4">
            <div>
              <p class="label">Google preview</p>
              <div class="border border-neutral-200 bg-white p-4">
                <p class="truncate text-xs text-neutral-600">{{ parse_url($publicUrl, PHP_URL_HOST) }}{{ $path ?? '/' }}</p>
                <p class="mt-1 truncate text-[18px] leading-snug text-[#1a0dab]" x-text="fullTitle"></p>
                <p class="mt-1 line-clamp-2 text-[13px] leading-snug text-neutral-600" x-text="fullDesc"></p>
              </div>
            </div>
            <div>
              <label for="seo_keyword" class="label">Focus keyword</label>
              <input id="seo_keyword" name="seo_keyword" x-model="keyword" class="input" placeholder="e.g. cloud migration">
            </div>
            <div>
              <label for="seo_title" class="label">SEO title <span class="font-normal text-neutral-400" x-text="'(' + fullTitle.length + ' of 60)'"></span></label>
              <input id="seo_title" name="seo_title" x-model="title" class="input" :placeholder="fallbackTitle">
              <p class="help">Leave empty to use the {{ strtolower($resource->singular) }} title.</p>
            </div>
            <div>
              <label for="seo_description" class="label">Meta description <span class="font-normal text-neutral-400" x-text="'(' + fullDesc.length + ' of 160)'"></span></label>
              <textarea id="seo_description" name="seo_description" x-model="desc" rows="3" class="input" :placeholder="fallbackDesc"></textarea>
            </div>
            @include('admin.partials.field', ['field' => ['name' => 'seo_og_image', 'label' => 'Social share image (optional)', 'type' => 'image'], 'value' => $seoMeta?->og_image])
            @include('admin.partials.field', ['field' => ['name' => 'seo_noindex', 'label' => 'Hide from search engines (noindex)', 'type' => 'toggle'], 'value' => $seoMeta?->noindex])
            <div class="border-t border-neutral-100 pt-4">
              @include('admin.seo.partials.schema-field', ['name' => 'seo_schema', 'value' => $seoMeta?->schema, 'pageUrl' => rtrim(config('admin.site_public_url'), '/').($record->exists ? $resource->sitePath($record) : '/'), 'pageTitle' => $record->title ?? $record->name ?? ''])
            </div>
          </div>
          <div>
            <p class="label">Analysis</p>
            <ul class="space-y-2">
              <template x-for="c in result.checks" :key="c.label">
                <li class="flex gap-2 text-[13px]">
                  <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full" :class="c.status === 'good' ? 'bg-green-500' : c.status === 'ok' ? 'bg-ink' : 'bg-red-500'"></span>
                  <span x-text="c.label"></span>
                </li>
              </template>
            </ul>
            @if($analysis)
              <details class="mt-5  bg-neutral-50 p-3 text-[13px]">
                <summary class="cursor-pointer font-semibold text-ink">Full content analysis (last save, score {{ $analysis['score'] }})</summary>
                <ul class="mt-3 space-y-2">
                  @foreach($analysis['checks'] as $c)
                    <li class="flex gap-2">
                      <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full {{ ['good' => 'bg-green-500', 'ok' => 'bg-ink', 'bad' => 'bg-red-500'][$c['status']] }}"></span>
                      <span>{{ $c['label'] }} @if($c['tip'])<span class="text-neutral-500">{{ $c['tip'] }}</span>@endif</span>
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
      <div class="card-header"><h2 class="card-title">{{ $resource->hasPublished ? 'Publish' : 'Save' }}</h2>
        @if($state)<span class="badge-{{ ['published' => 'green', 'scheduled' => 'blue', 'review' => 'warn', 'archived' => 'gray', 'draft' => 'gray'][$state] }}">{{ \App\Services\ContentWorkflow::STATES[$state] }}</span>@endif
      </div>
      <div class="card-body space-y-4">
        @if($resource->hasPublished)
          @php $chosen = old('workflow_status', $state ?? 'draft'); @endphp
          <div x-data="{ status: @js($chosen) }" class="space-y-3">
            <div>
              <label for="workflow_status" class="label">Status</label>
              <select id="workflow_status" name="workflow_status" x-model="status" class="input">
                @foreach(\App\Services\ContentWorkflow::STATES as $k => $label)
                  @php $locked = in_array($k, ['published', 'scheduled'], true) && ! $canPublish && $k !== $state; @endphp
                  <option value="{{ $k }}" @disabled($locked)>{{ $label }}{{ $locked ? ' (editors only)' : '' }}</option>
                @endforeach
              </select>
              @error('workflow_status')<p class="error">{{ $message }}</p>@enderror
              <p class="help" x-show="status === 'review'">Editors get a notification to review it. It stays off the website.</p>
              <p class="help" x-show="status === 'archived'">Archived items stay saved but are hidden from the website.</p>
            </div>
            <div x-show="status === 'scheduled'" x-cloak>
              <label for="publish_at" class="label">Go live at (UTC)</label>
              <input type="datetime-local" id="publish_at" name="publish_at" class="input" value="{{ old('publish_at', $record->publish_at?->format('Y-m-d\TH:i')) }}">
              @error('publish_at')<p class="error">{{ $message }}</p>@enderror
              <p class="help">The website shows it automatically at this time.</p>
            </div>
          </div>
        @endif
        @foreach($side as $field)
          @include('admin.partials.field', ['field' => $field, 'value' => $value($field)])
        @endforeach
        @unless($isNew)
          <a href="{{ route('admin.resources.preview', [$resource->slug, $record->getKey()]) }}" target="_blank" class="flex items-center gap-1.5 text-[13px] font-semibold text-ink hover:underline">
            @include('admin.partials.icon', ['name' => 'eye']) Preview (saved version)
          </a>
        @endunless
        @if($path && in_array($state, [null, 'published'], true))
          <a href="{{ $siteUrl.$path }}" target="_blank" class="flex items-center gap-1.5 text-[13px] font-semibold text-ink hover:underline">
            @include('admin.partials.icon', ['name' => 'external']) View on website
          </a>
        @endif
        @if($meta)
          <dl class="space-y-1 border-t border-neutral-100 pt-3 text-xs text-neutral-500">
            <div>Created by <span class="font-semibold text-neutral-700">{{ $meta->created_by_name ?? 'unknown (before tracking)' }}</span>@if($meta->created_by_name) on {{ $meta->created_at?->format('M j, Y') }}@endif</div>
            <div>Last changed by <span class="font-semibold text-neutral-700">{{ $meta->updated_by_name ?? 'unknown' }}</span>, <time datetime="{{ $meta->updated_at?->toIso8601String() }}" title="{{ $meta->updated_at?->format('Y-m-d H:i') }} UTC">{{ $meta->updated_at?->diffForHumans() }}</time></div>
          </dl>
        @endif
      </div>
      <div class="flex items-center justify-between gap-2 pt-2">
        @if(! $isNew && ! ($blocker = $resource->deleteBlocker($record)))
          <button type="submit" form="delete-form" class="text-[13px] font-semibold text-red-600 hover:underline">Delete</button>
        @elseif(! $isNew)
          <span class="text-xs text-neutral-500">{{ $blocker }}</span>
        @else
          <span></span>
        @endif
        <button class="btn-primary" data-loading>{{ $isNew ? 'Save' : 'Update' }}</button>
      </div>
    </div>

    @if($health)
      <div class="card">
        <div class="card-header"><h2 class="card-title">Checks</h2><span class="badge-{{ collect($health)->contains('level', 'warning') ? 'warn' : 'gray' }}">{{ count($health) }}</span></div>
        <ul class="card-body space-y-2.5 text-[13px]">
          @foreach($health as $h)
            <li class="flex gap-2">
              <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full {{ $h['level'] === 'warning' ? 'bg-ink' : 'bg-neutral-300' }}"></span>
              <span><span class="font-semibold text-neutral-700">{{ $h['label'] }}</span><span class="block text-xs text-neutral-500">{{ $h['fix'] }}</span></span>
            </li>
          @endforeach
        </ul>
      </div>
    @endif

    @unless($isNew || $resource->slug === 'users')
      <div class="card">
        <div class="card-header"><h2 class="card-title">Revisions</h2>
          @if($revisions->isNotEmpty())<a href="{{ route('admin.resources.revisions', [$resource->slug, $record->getKey()]) }}" class="text-[13px] font-semibold text-ink hover:underline">View all</a>@endif
        </div>
        <div class="card-body text-[13px]">
          @forelse($revisions as $rev)
            <p class="flex justify-between gap-2 py-0.5"><span class="text-neutral-600">{{ $rev->user_name ?? 'System' }}</span><time class="text-neutral-400" datetime="{{ $rev->created_at?->toIso8601String() }}" title="{{ $rev->created_at?->format('Y-m-d H:i') }} UTC">{{ $rev->created_at?->diffForHumans() }}</time></p>
          @empty
            <p class="text-neutral-500">No earlier versions yet. A copy is saved every time this item changes.</p>
          @endforelse
        </div>
      </div>
    @endunless
  </aside>
</form>

@unless($isNew)
  <form id="delete-form" method="POST" action="{{ route('admin.resources.destroy', [$resource->slug, $record->getKey()]) }}"
        onsubmit="return confirm('Delete this {{ strtolower($resource->singular) }}?{{ $resource->slug === 'users' ? ' This cannot be undone.' : ' A copy is kept under Recently deleted.' }}')" class="hidden">
    @csrf @method('DELETE')
  </form>
@endunless
@endsection
