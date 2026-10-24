{{-- One input for a content field. $t is the JS object holding the value, $f the field variable name. --}}
@php($v = $t.'['.$f.'.name]')
<template x-if="{{ $f }}.type === 'text' || {{ $f }}.type === 'link'"><input class="input text-[13.5px]" x-model="{{ $v }}" @input="dirty = true" :placeholder="{{ $f }}.type === 'link' ? '/contact-us/' : ''"></template>
<template x-if="{{ $f }}.type === 'textarea'"><textarea class="input text-[13.5px]" rows="4" x-model="{{ $v }}" @input="dirty = true"></textarea></template>
<template x-if="{{ $f }}.type === 'lines'">
  <div><textarea class="input text-[13.5px]" rows="5" x-model="{{ $v }}" @input="dirty = true"></textarea><p class="help">One item per line.</p></div>
</template>
<template x-if="{{ $f }}.type === 'image'">
  <div class="space-y-2">
    <img x-show="{{ $v }}" :src="{{ $v }}" alt="" class="max-h-40 min-h-[60px] w-full  bg-neutral-100 object-cover ring-1 ring-neutral-200">
    <div class="flex gap-2">
      <input class="input text-[13px]" x-model="{{ $v }}" @input="dirty = true" placeholder="Image URL">
      <button type="button" class="btn-secondary shrink-0" @click="pickMedia(u => { {{ $v }} = u; dirty = true })" x-text="{{ $v }} ? 'Replace' : 'Choose'"></button>
      <button type="button" x-show="{{ $v }}" class="btn-danger shrink-0" @click="{{ $v }} = ''; dirty = true">Remove</button>
    </div>
  </div>
</template>
<template x-if="{{ $f }}.type === 'images'">
  <div>
    <div class="grid grid-cols-3 gap-2">
      <template x-for="(src, k) in {{ $v }}" :key="k + src">
        <div class="group relative">
          <img :src="src" alt="" class="aspect-[4/3] w-full  bg-neutral-100 object-cover ring-1 ring-neutral-200">
          <div class="absolute inset-x-1 bottom-1 flex justify-between gap-1">
            <button type="button" class="bg-white/90 px-1.5 py-0.5 text-[11px] font-semibold text-ink " @click="pickMedia(u => { {{ $v }}.splice(k, 1, u); dirty = true })">Replace</button>
            <button type="button" class="bg-white/90 px-1.5 py-0.5 text-[11px] font-semibold text-red-600 " @click="{{ $v }}.splice(k, 1); dirty = true">Delete</button>
          </div>
        </div>
      </template>
    </div>
    <button type="button" class="btn-secondary btn-sm mt-2" @click="pickMedia(u => { {{ $v }}.push(u); dirty = true })">@include('admin.partials.icon', ['name' => 'plus']) Add image</button>
  </div>
</template>
<template x-if="{{ $f }}.type === 'select'">
  <select class="input text-[13.5px]" x-model="{{ $v }}" @change="dirty = true">
    <template x-for="(optLabel, optVal) in {{ $f }}.options" :key="optVal"><option :value="optVal" x-text="optLabel" :selected="{{ $v }} === optVal"></option></template>
  </select>
</template>
<template x-if="{{ $f }}.type === 'color'">
  <div class="flex items-center gap-2">
    <input type="color" class="h-9 w-12 shrink-0 cursor-pointer border border-neutral-300 bg-white p-0.5" :value="{{ $v }} || '#000000'" @input="{{ $v }} = $event.target.value; dirty = true">
    <input class="input font-mono text-[13px]" x-model="{{ $v }}" @input="dirty = true" placeholder="Default">
    <button type="button" x-show="{{ $v }}" class="btn-ghost btn-sm" @click="{{ $v }} = ''; dirty = true">Reset</button>
  </div>
</template>
