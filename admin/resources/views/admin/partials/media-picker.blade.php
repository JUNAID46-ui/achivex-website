{{-- Global media picker. Open it with pickMedia(url => ...) from any field. --}}
<div x-data="{
        open: false, items: [], q: '', loading: false, uploading: false, progress: '', onSelect: null, error: '',
        async load() { this.loading = true; try { this.items = await adminFetch('{{ route('admin.media.list') }}?q=' + encodeURIComponent(this.q)) } catch (e) { this.error = e.message } this.loading = false },
        async upload(files) {
          if (!files.length) return
          this.uploading = true; this.error = ''
          try { await uploadMedia(files, (p) => this.progress = (p.total > 1 ? (p.index + 1) + '/' + p.total + ' ' : '') + p.percent + '%') } catch (e) { this.error = e.message }
          this.progress = ''; await this.load()
          this.uploading = false
        },
        url(m) { return m.public_url || '{{ rtrim(asset('storage'), '/') }}/' + m.path },
        choose(m) { this.onSelect && this.onSelect(this.url(m)); this.open = false },
     }"
     @media-pick.window="onSelect = $event.detail.onSelect; open = true; load()"
     @keydown.escape.window="open = false"
     x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" role="dialog" aria-modal="true" aria-label="Media library">
  <div class="flex max-h-[85vh] w-full max-w-4xl flex-col overflow-hidden rounded-lg bg-white shadow-2xl" @click.outside="open = false">
    <div class="flex items-center justify-between border-b border-slate-200 px-5 py-3">
      <h2 class="text-base font-bold text-navy">Choose a file</h2>
      <button class="btn-ghost" @click="open = false" aria-label="Close">@include('admin.partials.icon', ['name' => 'x'])</button>
    </div>
    <div class="flex flex-wrap items-center gap-2 border-b border-slate-200 px-5 py-3">
      <input x-model="q" @input.debounce.300ms="load()" class="input w-56 py-1.5" placeholder="Search files">
      <label class="btn-primary cursor-pointer">
        @include('admin.partials.icon', ['name' => 'upload']) <span x-text="uploading ? 'Uploading ' + progress : 'Upload files'"></span>
        <input type="file" multiple class="sr-only" @change="upload($event.target.files); $event.target.value = ''">
      </label>
      <p class="text-xs text-red-600" x-text="error"></p>
    </div>
    <div class="flex-1 overflow-y-auto p-5"
         @dragover.prevent @drop.prevent="upload($event.dataTransfer.files)">
      <p x-show="loading" class="py-10 text-center text-sm text-slate-500">Loading...</p>
      <p x-show="!loading && !items.length" class="py-10 text-center text-sm text-slate-500">No files yet. Upload one, or drop files here.</p>
      <div class="grid grid-cols-3 gap-3 sm:grid-cols-5">
        <template x-for="m in items" :key="m.id">
          <button type="button" @click="choose(m)" class="group overflow-hidden rounded-md border border-slate-200 text-left hover:ring-2 hover:ring-amber">
            <template x-if="(m.mime || '').startsWith('image/')"><img :src="url(m)" :alt="m.alt || ''" class="aspect-square w-full bg-slate-100 object-cover"></template>
            <template x-if="!(m.mime || '').startsWith('image/')"><div class="flex aspect-square w-full items-center justify-center bg-slate-100 text-xs font-bold uppercase text-slate-500" x-text="m.filename.split('.').pop()"></div></template>
            <p class="truncate px-2 py-1 text-[11px] text-slate-600" x-text="m.filename"></p>
          </button>
        </template>
      </div>
    </div>
  </div>
</div>

{{-- Toast messages --}}
<div x-data="{ items: [] }" @toast.window="const t = { id: Date.now(), ...$event.detail }; items.push(t); setTimeout(() => items = items.filter(i => i.id !== t.id), 3500)"
     class="fixed bottom-5 right-5 z-[60] space-y-2">
  <template x-for="t in items" :key="t.id">
    <div class="rounded-md px-4 py-2.5 text-[13px] font-semibold text-white shadow-lg" :class="t.type === 'error' ? 'bg-red-600' : 'bg-navy'" x-text="t.message"></div>
  </template>
</div>
