@extends('admin.layouts.app')
@section('title', 'Colors and Fonts')
@section('heading', 'Colors and Fonts')
@section('subheading', 'The website colours and fonts. Changes show on the website on its next page load; no new deploy is needed.')
@section('actions')
  <form method="POST" action="{{ route('admin.settings.theme.reset') }}" onsubmit="return confirm('Reset every colour and font to the brand defaults?')">@csrf<button class="btn-secondary">Reset to defaults</button></form>
@endsection
@push('head')
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?{{ collect($fontList)->map(fn ($f) => 'family='.str_replace(' ', '+', $f))->implode('&') }}&display=swap">
@endpush
@section('content')
<form method="POST" data-unsaved-warning
      x-data="{ c: @js($colors), f: @js($fonts), d: @js($defaults), pairs: @js($pairs),
        lum(h) { const v = [1, 3, 5].map(i => parseInt(h.slice(i, i + 2), 16) / 255).map(x => x <= 0.03928 ? x / 12.92 : ((x + 0.055) / 1.055) ** 2.4); return 0.2126 * v[0] + 0.7152 * v[1] + 0.0722 * v[2] },
        ratio(a, b) { if (!/^#[0-9a-f]{6}$/i.test(a) || !/^#[0-9a-f]{6}$/i.test(b)) return 0; const x = this.lum(a), y = this.lum(b); return Math.round((Math.max(x, y) + 0.05) / (Math.min(x, y) + 0.05) * 100) / 100 },
        get warnings() { return this.pairs.map(([fg, bg, label]) => [label, this.ratio(this.c[fg], this.c[bg])]).filter(([, r]) => r < 4.5) } }"
      class="grid gap-10 xl:grid-cols-[minmax(0,1fr)_420px]">
  @csrf
  <div class="space-y-10">
    @foreach($groups as $group => $items)
      <section>
        <div class="card-header"><h2 class="card-title">{{ $group }}</h2></div>
        <div class="divide-y divide-neutral-200">
          @foreach($items as $key => [$label, $help])
            <div class="flex flex-wrap items-center gap-4 py-3">
              <input type="color" x-model="c.{{ $key }}" class="h-10 w-12 cursor-pointer border border-ink bg-white p-0.5" aria-label="{{ $label }} colour picker">
              <input name="colors[{{ $key }}]" x-model="c.{{ $key }}" class="input w-32 font-mono uppercase" maxlength="7" aria-label="{{ $label }} hex value">
              <div class="min-w-0 flex-1">
                <p class="text-[13.5px] font-semibold text-ink">{{ $label }}</p>
                <p class="text-xs text-neutral-500">{{ $help }}</p>
              </div>
              <button type="button" class="btn-ghost btn-sm" x-show="c.{{ $key }}.toUpperCase() !== d.{{ $key }}" @click="c.{{ $key }} = d.{{ $key }}" title="Default {{ $defaults[$key] }}">Default</button>
            </div>
            @error("colors.$key")<p class="error">{{ $message }}</p>@enderror
          @endforeach
        </div>
      </section>
    @endforeach

    <section>
      <div class="card-header"><h2 class="card-title">Fonts</h2></div>
      <div class="grid gap-6 py-5 sm:grid-cols-2">
        @foreach(['heading' => 'Headings', 'body' => 'Body text'] as $k => $label)
          <div>
            <label for="font-{{ $k }}" class="label">{{ $label }}</label>
            <select id="font-{{ $k }}" name="fonts[{{ $k }}]" x-model="f.{{ $k }}" class="input">
              @foreach($fontList as $font)<option value="{{ $font }}">{{ $font }}{{ $font === $defaultFonts[$k] ? ' (default)' : '' }}</option>@endforeach
            </select>
            <p class="help">Google Fonts, loaded by the website only when chosen.</p>
          </div>
        @endforeach
      </div>
    </section>
    <button class="btn-primary" data-loading>Save colors and fonts</button>
  </div>

  {{-- Live preview of a dark and a light section --}}
  <aside class="xl:sticky xl:top-16 xl:self-start">
    <div class="card-header"><h2 class="card-title">Preview</h2></div>
    <div class="mt-4 border border-ink">
      <div class="p-6" :style="`background:${c.heroBg}`">
        <p class="text-xs uppercase tracking-widest" :style="`color:${c.mutedText}; font-family:'${f.body}'`">About AchiveX</p>
        <p class="mt-2 text-[28px] leading-tight" :style="`color:#FFFFFF; font-family:'${f.heading}'`">A technology partner that explains every step</p>
        <p class="mt-3 text-[14px] leading-relaxed" :style="`color:${c.mutedText}; font-family:'${f.body}'`">Page hero, Why AchiveX and dark cards.</p>
      </div>
      <div class="p-6" :style="`background:#FFFFFF`">
        <p class="text-xs font-semibold uppercase tracking-widest" :style="`color:${c.brand}; font-family:'${f.body}'`">What we do</p>
        <p class="mt-2 text-[22px] leading-tight" :style="`color:${c.heading}; font-family:'${f.heading}'`">Five areas, eight services</p>
        <p class="mt-2 text-[14px]" :style="`color:${c.text}; font-family:'${f.body}'`">Body text on a white section.</p>
        <div class="mt-4 p-4 text-[13px]" :style="`background:${c.cards}; border:1px solid ${c.lines}; color:${c.text}; font-family:'${f.body}'`">A card with its border</div>
        <span class="mt-4 inline-block rounded-full px-4 py-2 text-[13px] font-semibold" :style="`background:${c.brand}; color:#FFFFFF; font-family:'${f.body}'`">Contact us</span>
      </div>
      <div class="p-6" :style="`background:${c.greyBg}`">
        <p class="text-[20px]" :style="`color:${c.heading}; font-family:'${f.heading}'`">A grey section</p>
        <p class="mt-1 text-[14px]" :style="`color:${c.text}; font-family:'${f.body}'`">Bands between white sections.</p>
      </div>
      <div class="p-6" :style="`background:${c.darkBg}`">
        <p class="text-[34px] font-light" :style="`color:#FFFFFF; font-family:'${f.heading}'`">8</p>
        <p class="text-[14px]" :style="`color:${c.mutedText}; font-family:'${f.body}'`">Dark section: numbers and timeline</p>
      </div>
      <div class="p-6" :style="`background:${c.footerBg}`">
        <p class="text-[20px]" :style="`color:#FFFFFF; font-family:'${f.heading}'`">Ready to start?</p>
        <p class="mt-1 text-[13px]" :style="`color:${c.mutedText}; font-family:'${f.body}'`">Call to action and footer</p>
      </div>
    </div>
    <div class="mt-4 text-[13px]">
      <template x-if="warnings.length">
        <ul class="space-y-1 border border-red-600 p-3 text-red-600">
          <template x-for="[label, r] in warnings" :key="label"><li x-text="`${label}: ${r}:1. Text may be hard to read (aim for 4.5:1 or more).`"></li></template>
        </ul>
      </template>
      <p x-show="!warnings.length" class="text-neutral-500">All checked text and background pairs meet the 4.5:1 contrast level.</p>
    </div>
  </aside>
</form>
@endsection
