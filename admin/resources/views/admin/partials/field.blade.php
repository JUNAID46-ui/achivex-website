{{-- Renders one form field. Expects $field and $value (already in form shape). --}}
@php
  $name = $field['name'];
  $id = 'f-'.$name;
  $type = $field['type'];
  $val = in_array($type, ['repeater', 'sections', 'tags']) ? $value : old($name, $value);
  $locked = ($field['createOnly'] ?? false) && ($record->exists ?? false);
@endphp
<div class="{{ $field['class'] ?? '' }}">
  @if(!in_array($type, ['toggle']))
    <label for="{{ $id }}" class="label">{{ $field['label'] }}</label>
  @endif

  @switch($type)
    @case('textarea')
      <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $field['rows'] ?? 4 }}" class="input" placeholder="{{ $field['placeholder'] ?? '' }}">{{ $val }}</textarea>
      @break

    @case('lines')
    @case('paragraphs')
      <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $field['rows'] ?? ($type === 'paragraphs' ? 8 : 5) }}" class="input font-[450] leading-relaxed">{{ $val }}</textarea>
      @break

    @case('select')
      <select id="{{ $id }}" name="{{ $name }}" class="input">
        @foreach($field['options'] as $optValue => $optLabel)
          <option value="{{ $optValue }}" @selected((string) $val === (string) $optValue)>{{ $optLabel }}</option>
        @endforeach
      </select>
      @break

    @case('toggle')
      <label class="flex cursor-pointer items-center justify-between gap-3 py-1">
        <span class="text-[13px] font-semibold text-neutral-700">{{ $field['label'] }}</span>
        <input type="hidden" name="{{ $name }}" value="0">
        <input id="{{ $id }}" type="checkbox" name="{{ $name }}" value="1" @checked($val) class="peer sr-only">
        <span class="relative h-5 w-9 rounded-full bg-neutral-300 transition after:absolute after:left-0.5 after:top-0.5 after:h-4 after:w-4 after:rounded-full after:bg-white after:transition peer-checked:bg-green-500 peer-checked:after:translate-x-4 peer-focus-visible:ring-2 peer-focus-visible:ring-ink"></span>
      </label>
      @break

    @case('slug')
      <div class="flex  " x-data="{ touched: {{ $record->exists ? 'true' : 'false' }} }"
           x-init="$nextTick(() => { const src = document.getElementById('f-{{ $field['from'] }}'); if (src) src.addEventListener('input', () => { if (!touched) $refs.slug.value = slugify(src.value) }) })">
        @if(!empty($field['prefix']))<span class="inline-flex items-center  border border-r-0 border-neutral-300 bg-neutral-50 px-3 text-xs text-neutral-500">{{ (parse_url((string) config('admin.site_public_url'), PHP_URL_HOST) ?: '').$field['prefix'] }}</span>@endif
        <input id="{{ $id }}" x-ref="slug" name="{{ $name }}" value="{{ $val }}" @input="touched = true" @readonly($locked)
               class="input {{ !empty($field['prefix']) ? 'rounded-l-none' : '' }} {{ $locked ? 'bg-neutral-50 text-neutral-500' : '' }}">
      </div>
      @if($locked)<p class="help">The URL cannot change after people have requested this item.</p>@endif
      @break

    @case('image')
      <div x-data="{ url: @js($val) }" class="space-y-2">
        <template x-if="url && /\.(png|jpe?g|gif|webp|avif|svg)(\?|$)|images\.unsplash/i.test(url)">
          <img :src="url" alt="" class="max-h-40 w-full  bg-neutral-100 object-cover ring-1 ring-neutral-200">
        </template>
        <div class="flex gap-2">
          <input id="{{ $id }}" name="{{ $name }}" x-model="url" class="input text-[13px]" placeholder="https://...">
          <button type="button" class="btn-secondary shrink-0" @click="pickMedia(u => url = u)">Choose</button>
        </div>
      </div>
      @break

    @case('tags')
      @php $selected = (array) old($name, $val ?? []); @endphp
      <div class="grid max-h-64 gap-1 overflow-y-auto  border border-neutral-200 p-3 sm:grid-cols-2">
        @foreach($field['options'] as $optValue => $optLabel)
          <label class="flex items-center gap-2 text-[13px]">
            <input type="checkbox" name="{{ $name }}[]" value="{{ $optValue }}" @checked(in_array($optValue, $selected)) class="border-neutral-300 text-ink focus:ring-ink">
            {{ $optLabel }}
          </label>
        @endforeach
      </div>
      @break

    @case('repeater')
    @case('sections')
      @php
        $sub = $type === 'sections' ? \App\Admin\FieldValues::sectionFields() : $field['fields'];
        $items = old($name) ? json_decode(old($name), true) : ($val ?? []);
      @endphp
      @include('admin.partials.repeater', ['name' => $name, 'items' => $items, 'sub' => $sub, 'itemLabel' => $type === 'sections' ? 'Section' : ($field['itemLabel'] ?? 'Item')])
      @break

    @case('color')
      <div x-data="{ c: @js($val ?: '') }" class="flex items-center gap-2">
        <input type="color" :value="c || @js($field['default'] ?? '#000000')" @input="c = $event.target.value" class="h-9 w-12 shrink-0 cursor-pointer border border-neutral-300 bg-white p-0.5" aria-label="{{ $field['label'] }}">
        <input id="{{ $id }}" name="{{ $name }}" x-model="c" class="input font-mono text-[13px]" placeholder="{{ $field['default'] ?? '#000000' }}">
        <button type="button" class="btn-ghost btn-sm" @click="c = ''" title="Use the default">Reset</button>
      </div>
      @break

    @case('password')
      <input id="{{ $id }}" type="password" name="{{ $name }}" class="input" autocomplete="new-password">
      @break

    @default
      <input id="{{ $id }}" type="{{ in_array($type, ['email', 'url', 'number', 'date']) ? $type : 'text' }}" name="{{ $name }}" value="{{ $val }}"
             placeholder="{{ $field['placeholder'] ?? '' }}" class="input {{ !empty($field['big']) ? 'py-2.5 text-lg font-semibold' : '' }}">
  @endswitch

  @if(!empty($field['help']))<p class="help">{{ $field['help'] }}</p>@endif
  @error($name)<p class="error">{{ $message }}</p>@enderror
</div>
