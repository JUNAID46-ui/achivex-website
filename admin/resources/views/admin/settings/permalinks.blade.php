@extends('admin.layouts.app')
@section('title', 'Permalinks')
@section('heading', 'Permalinks')
@section('subheading', 'The web address of each content type and of the services. A single item\'s address ends in its URL slug, set when you edit it.')
@section('content')
<form method="POST" class="max-w-4xl space-y-8" x-data="{ b: @js($bases), svc: @js($serviceMode), url(v) { return ('/' + v.replace(/^\/+|\/+$/g, '') + '/').replace('//', '/') } }" onsubmit="return confirm('Change the addresses? Old addresses will keep working by sending visitors to the new ones.')">
  @csrf
  <section>
    <div class="card-header"><h2 class="card-title">Content addresses</h2></div>
    <div class="divide-y divide-neutral-200">
      @foreach($labels as $type => $label)
        <div class="grid gap-3 py-4 md:grid-cols-[200px_minmax(0,1fr)]">
          <div>
            <label for="base-{{ $type }}" class="text-[13.5px] font-semibold text-ink">{{ $label }}</label>
            <p class="text-xs text-neutral-500">{{ $counts[$type] }} {{ \Illuminate\Support\Str::plural('item', $counts[$type]) }}</p>
          </div>
          <div>
            <input id="base-{{ $type }}" name="bases[{{ $type }}]" value="{{ $bases[$type] }}" x-model="b.{{ $type }}" class="input font-mono" spellcheck="false">
            @error("bases.$type")<p class="error">{{ $message }}</p>@enderror
            <p class="mt-1 font-mono text-xs text-neutral-500">{{ $site }}<span x-text="url(b.{{ $type }})"></span>example-item/</p>
            <p x-show="url(b.{{ $type }}) === '/'" class="text-xs text-neutral-500">Items sit at the root of the site. The list page stays at <span class="font-mono">{{ $defaults[$type] }}</span>.</p>
            @if($bases[$type] !== $defaults[$type])<p class="text-xs text-neutral-500">Default: <span class="font-mono">{{ $defaults[$type] }}</span></p>@endif
          </div>
        </div>
      @endforeach
      <div class="grid gap-3 py-4 md:grid-cols-[200px_minmax(0,1fr)]">
        <div>
          <p class="text-[13.5px] font-semibold text-ink">Services</p>
          <p class="text-xs text-neutral-500">{{ $counts['services'] }} services</p>
        </div>
        <div class="space-y-2">
          @foreach($serviceModes as $mode => $pattern)
            <label class="flex items-center gap-3 text-[13.5px]">
              <input type="radio" name="services" value="{{ $mode }}" x-model="svc" @checked($serviceMode === $mode) class="border-neutral-400 text-ink focus:ring-ink">
              <span class="font-mono">{{ $site }}{{ str_replace(['{category}', '{service}'], ['cloud', 'managed-services'], $pattern) }}</span>
              @if($mode === 'category')<span class="text-xs text-neutral-500">(default)</span>@endif
            </label>
          @endforeach
          @error('services')<p class="error">{{ $message }}</p>@enderror
          <p class="text-xs text-neutral-500">Service area pages such as <span class="font-mono">/services/cloud/</span> keep their address.</p>
        </div>
      </div>
    </div>
    @error('bases')<p class="error mt-2">{{ $message }}</p>@enderror
  </section>
  <section class="text-[13px] text-neutral-600">
    <div class="card-header"><h2 class="card-title">What happens when you save</h2></div>
    <ul class="mt-4 list-disc space-y-1 pl-5">
      <li>The website uses the new addresses on its next page load. Menus and links update by themselves.</li>
      <li>Old addresses that start with the default prefix send visitors and search engines to the new address.</li>
      <li>Use <span class="font-mono">/</span> to put items at the root, for example <span class="font-mono">/my-post/</span>. Slugs at the root must not clash with each other, pages or services; the admin checks this for you.</li>
      <li>SEO titles, descriptions and redirects saved for these pages move to the new addresses.</li>
      <li>Publish the website again afterwards, so the sitemap.xml copy it serves lists the new addresses.</li>
      <li>Changing addresses of a live site can lower rankings for a while. Change them rarely.</li>
    </ul>
  </section>
  <button class="btn-primary" data-loading>Save permalinks</button>
</form>
@endsection
