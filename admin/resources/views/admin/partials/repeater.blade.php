{{-- Repeatable group of fields. Submits one hidden JSON input named $name. --}}
<div x-data="repeater(@js($items ?? []), @js($sub))" class="space-y-3">
  <input type="hidden" name="{{ $name }}" :value="json">
  <template x-for="(item, i) in items" :key="item._k">
    <div class="border border-slate-200 bg-slate-50/60">
      <div class="flex items-center gap-2 border-b border-slate-200 px-3 py-2">
        <span class="text-xs font-semibold text-slate-500">{{ $itemLabel }} <span x-text="i + 1"></span></span>
        <span class="truncate text-[13px] font-semibold text-ink" x-text="item[fields[0].name]"></span>
        <div class="ml-auto flex items-center gap-1">
          <button type="button" class="btn-ghost btn-sm" @click="move(i, -1)" :disabled="i === 0" title="Move up">@include('admin.partials.icon', ['name' => 'arrow-up'])</button>
          <button type="button" class="btn-ghost btn-sm" @click="move(i, 1)" :disabled="i === items.length - 1" title="Move down">@include('admin.partials.icon', ['name' => 'arrow-down'])</button>
          <button type="button" class="btn-ghost btn-sm" @click="item._open = !item._open" x-text="item._open ? 'Close' : 'Edit'"></button>
          <button type="button" class="btn-ghost btn-sm text-red-600" @click="remove(i)" title="Remove">@include('admin.partials.icon', ['name' => 'trash'])</button>
        </div>
      </div>
      <div x-show="item._open" class="space-y-3 p-3">
        <template x-for="f in fields" :key="f.name">
          <div>
            <label class="label" x-text="f.label"></label>
            <template x-if="f.type === 'text' || f.type === 'url'">
              <input class="input" x-model="item[f.name]">
            </template>
            <template x-if="f.type === 'textarea'">
              <textarea class="input" rows="3" x-model="item[f.name]"></textarea>
            </template>
            <template x-if="f.type === 'lines'">
              <textarea class="input" rows="3" x-model="item[f.name]" placeholder="One per line"></textarea>
            </template>
            <template x-if="f.type === 'paragraphs'">
              <textarea class="input leading-relaxed" rows="6" x-model="item[f.name]"></textarea>
            </template>
            <template x-if="f.type === 'image'">
              <div class="flex gap-2">
                <input class="input text-[13px]" x-model="item[f.name]" placeholder="https://...">
                <button type="button" class="btn-secondary shrink-0" @click="pickMedia(u => item[f.name] = u)">Choose</button>
              </div>
            </template>
          </div>
        </template>
      </div>
    </div>
  </template>
  <button type="button" class="btn-secondary" @click="add()">@include('admin.partials.icon', ['name' => 'plus']) Add {{ strtolower($itemLabel) }}</button>
</div>
