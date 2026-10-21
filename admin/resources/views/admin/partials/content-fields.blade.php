{{-- Renders a content form. Needs an Alpine scope with "form", "dirty" and contentForm.methods.
     $fields is the JS expression for the field list. --}}
<template x-for="f in {{ $fields }}" :key="f.name">
  <div>
    <label class="label" x-text="f.label"></label>
    @include('admin.partials.content-input', ['t' => 'form', 'f' => 'f'])
    @isset($extra){!! $extra !!}@endisset

    {{-- Group of fields --}}
    <template x-if="f.type === 'group'">
      <div class="space-y-3  border border-slate-200 bg-slate-50/70 p-3">
        <template x-for="gf in f.fields" :key="gf.name">
          <div><label class="label text-xs" x-text="gf.label"></label>@include('admin.partials.content-input', ['t' => 'form[f.name]', 'f' => 'gf'])</div>
        </template>
      </div>
    </template>

    {{-- Repeating items --}}
    <template x-if="f.type === 'repeater'">
      <div class="space-y-2">
        <template x-for="(item, i) in form[f.name]" :key="i">
          <div class="border border-slate-200 bg-slate-50/70">
            <div class="flex items-center gap-1 border-b border-slate-200 px-2.5 py-1.5">
              <button type="button" class="min-w-0 flex-1 truncate text-left text-[12.5px] font-semibold text-ink" @click="item._open = !item._open" x-text="(i + 1) + '. ' + ((f.fields[0] && item[f.fields[0].name] && typeof item[f.fields[0].name] === 'string' ? item[f.fields[0].name] : '') || 'Item')"></button>
              <button type="button" class="btn-ghost btn-sm px-1.5" @click="moveItem(form[f.name], i, -1)" title="Move up">@include('admin.partials.icon', ['name' => 'arrow-up', 'class' => 'h-3.5 w-3.5'])</button>
              <button type="button" class="btn-ghost btn-sm px-1.5" @click="moveItem(form[f.name], i, 1)" title="Move down">@include('admin.partials.icon', ['name' => 'arrow-down', 'class' => 'h-3.5 w-3.5'])</button>
              <button type="button" class="btn-ghost btn-sm px-1.5 text-red-600" @click="form[f.name].splice(i, 1); dirty = true" title="Remove">@include('admin.partials.icon', ['name' => 'trash', 'class' => 'h-3.5 w-3.5'])</button>
            </div>
            <div x-show="item._open" class="space-y-3 p-3">
              <template x-for="sf in f.fields" :key="sf.name">
                <div><label class="label text-xs" x-text="sf.label"></label>@include('admin.partials.content-input', ['t' => 'item', 'f' => 'sf'])</div>
              </template>
            </div>
          </div>
        </template>
        <button type="button" class="btn-secondary btn-sm" @click="addItem(f, form[f.name])">@include('admin.partials.icon', ['name' => 'plus']) Add item</button>
      </div>
    </template>
  </div>
</template>
