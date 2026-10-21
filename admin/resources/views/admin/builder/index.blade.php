@extends('admin.layouts.app')
@section('title', 'Page Builder')
@section('heading', 'Page Builder: '.$page['title'])
@section('subheading', $page['kind'] === 'builtin'
    ? 'This page has a fixed layout. Sections you add here appear at the bottom of the page, above the footer. To change its own text and images use Edit Page.'
    : 'Add sections, drag them to reorder, click the eye to show or hide, and click a section to edit it. Changes go live as soon as you save.')
@section('actions')
  <form method="GET" class="flex items-center gap-2">
    <label for="page-pick" class="sr-only">Page</label>
    <select id="page-pick" name="page" class="input w-72 py-1.5" onchange="this.form.submit()">
      @foreach($pages as $p)
        <option value="{{ $p['path'] }}" @selected($p['path'] === $page['path'])>{{ $p['kind'] === 'custom' ? '★ ' : '' }}{{ $p['title'] }} ({{ $p['path'] }})</option>
      @endforeach
    </select>
  </form>
  @if($page['kind'] !== 'home')<a href="{{ route('admin.pages.edit', ['path' => $page['path']]) }}" class="btn-secondary">Edit Page</a>@endif
  <a href="{{ route('admin.seo.pages.edit', ['path' => $page['path']]) }}" class="btn-secondary">Fix SEO</a>
  <a href="{{ $siteUrl.$page['path'] }}" target="_blank" class="btn-secondary">@include('admin.partials.icon', ['name' => 'external']) View</a>
@endsection

@section('content')
<div x-data="builderApp(@js(['sections' => $sections, 'library' => $library, 'page' => $page, 'heroStats' => $heroStats, 'cases' => $cases, 'siteUrl' => $siteUrl, 'urls' => [
        'save' => url('admin/builder/sections'), 'order' => route('admin.builder.order'), 'stats' => route('admin.builder.hero-stats'), 'add' => route('admin.builder.add'),
     ]]))" x-init="init()" class="grid gap-5 lg:grid-cols-[230px_minmax(0,1fr)] xl:grid-cols-[230px_340px_minmax(0,1fr)] 2xl:grid-cols-[260px_400px_minmax(0,1fr)]">

  {{-- Section list --}}
  <div class="card self-start">
    <div class="card-header"><h2 class="card-title">Sections</h2><span class="text-xs text-slate-400" x-text="sections.length + ' blocks'"></span></div>
    <p x-show="!sections.length" class="px-4 py-6 text-center text-[13px] text-slate-500">No sections yet. Add the first one below.</p>
    <ul x-ref="list" class="divide-y divide-slate-100">
      <template x-for="s in sections" :key="s.id">
        <li :data-id="s.id" class="flex items-center gap-2 px-3 py-2.5 transition" :class="current && current.id === s.id ? 'bg-accent-50' : 'hover:bg-slate-50'">
          <span class="handle cursor-grab text-slate-300 hover:text-slate-500" title="Drag to reorder">@include('admin.partials.icon', ['name' => 'grip'])</span>
          <button type="button" class="min-w-0 flex-1 text-left" @click="select(s)">
            <span class="block truncate text-[13.5px] font-semibold" :class="s.visible ? 'text-ink' : 'text-slate-400 line-through'" x-text="s.label"></span>
            <span class="block text-[11px] text-slate-400" x-text="s.updated_at ? 'Edited ' + s.updated_at : ''"></span>
          </button>
          <button type="button" @click="toggle(s)" class="rounded p-1 hover:bg-white" :class="s.visible ? 'text-emerald-600' : 'text-slate-400'" :title="s.visible ? 'Visible: click to hide' : 'Hidden: click to show'">
            <template x-if="s.visible">@include('admin.partials.icon', ['name' => 'eye'])</template>
            <template x-if="!s.visible">@include('admin.partials.icon', ['name' => 'eye-off'])</template>
          </button>
          <button type="button" x-show="page.kind !== 'home'" @click="remove(s)" class="rounded p-1 text-slate-400 hover:bg-white hover:text-red-600" title="Delete section">@include('admin.partials.icon', ['name' => 'trash'])</button>
        </li>
      </template>
    </ul>
    <div class="border-t border-slate-200 p-3" x-data="{ open: false }">
      <button type="button" class="btn-primary w-full justify-center" @click="open = !open">@include('admin.partials.icon', ['name' => 'plus']) Add section</button>
      <ul x-show="open" @click.outside="open = false" class="mt-2 max-h-80 divide-y divide-slate-100 overflow-y-auto rounded-md border border-slate-200 bg-white">
        <template x-for="t in library" :key="t.type">
          <li><button type="button" class="block w-full px-3 py-2 text-left hover:bg-accent-50" @click="open = false; addSection(t.type)">
            <span class="block text-[13px] font-semibold text-ink" x-text="t.label"></span>
            <span class="block text-[11px] text-slate-500" x-text="t.description"></span>
          </button></li>
        </template>
      </ul>
    </div>
  </div>

  {{-- Editor --}}
  <div class="card self-start xl:sticky xl:top-16 xl:max-h-[calc(100vh-5rem)] xl:overflow-y-auto">
    <template x-if="!current">
      <div class="card-body py-16 text-center text-[13px] text-slate-500">Choose a section on the left to edit it.</div>
    </template>
    <template x-if="current">
      <div>
        <div class="card-header">
          <div class="min-w-0">
            <input x-model="label" class="w-full truncate border-0 p-0 text-[15px] font-bold text-ink focus:ring-0" aria-label="Section name">
            <p class="mt-0.5 text-xs text-slate-500" x-text="current.schema.description"></p>
          </div>
        </div>
        <div class="card-body space-y-4">
          <template x-if="!current.schema.fields.length">
            <p class="rounded-md bg-slate-50 p-4 text-[13px] text-slate-600">This section has no text to edit here. <a href="{{ route('admin.settings') }}" class="font-semibold text-ink underline">Open Settings</a> to change the contact details it shows.</p>
          </template>
          @include('admin.partials.content-fields', ['fields' => 'current.schema.fields', 'extra' => '<template x-if="f.type === \'case\'"><select class="input" x-model="form[f.name]" @change="dirty = true"><template x-for="(title, slug) in cases" :key="slug"><option :value="slug" x-text="title" :selected="form[f.name] === slug"></option></template></select></template>'])

          {{-- Hero figures (stored in hero_stats) --}}
          <template x-if="current.schema.heroStats">
            <div class="rounded-md border border-slate-200 p-3">
              <p class="label">Hero figures (the three floating cards)</p>
              <p class="help mb-3">Use real, verifiable figures only.</p>
              <template x-for="(st, i) in heroStats" :key="i">
                <div class="mb-2 grid grid-cols-[90px_1fr] gap-2">
                  <input class="input text-[13px] font-bold" x-model="st.value" @input="statsDirty = true" placeholder="20" :aria-label="'Figure ' + (i + 1)">
                  <input class="input text-[13px]" x-model="st.label" @input="statsDirty = true" placeholder="What the figure means" :aria-label="'Label ' + (i + 1)">
                </div>
              </template>
            </div>
          </template>
        </div>
        <div class="flex items-center justify-between gap-2 border-t border-slate-200 bg-slate-50 px-5 py-3">
          <span class="text-xs" :class="dirty || statsDirty ? 'text-amber-600 font-semibold' : 'text-slate-400'" x-text="dirty || statsDirty ? 'Unsaved changes' : 'All changes saved'"></span>
          <button type="button" class="btn-primary" @click="save()" :disabled="saving" x-text="saving ? 'Saving...' : 'Save and publish'"></button>
        </div>
      </div>
    </template>
  </div>

  {{-- Live preview --}}
  <div class="card self-start overflow-hidden lg:col-span-2 xl:col-span-1 xl:sticky xl:top-16">
    <div class="card-header py-2.5">
      <h2 class="card-title">Live preview</h2>
      <div class="flex items-center gap-1">
        <template x-for="d in [['desktop', '100%'], ['tablet', '768px'], ['mobile', '390px']]" :key="d[0]">
          <button type="button" class="rounded p-1.5" :class="device === d[0] ? 'bg-ink text-white' : 'text-slate-500 hover:bg-slate-100'" @click="device = d[0]" :title="d[0]">
            <span x-show="d[0] === 'desktop'">@include('admin.partials.icon', ['name' => 'monitor'])</span>
            <span x-show="d[0] === 'tablet'">@include('admin.partials.icon', ['name' => 'tablet'])</span>
            <span x-show="d[0] === 'mobile'">@include('admin.partials.icon', ['name' => 'phone'])</span>
          </button>
        </template>
        <button type="button" class="rounded p-1.5 text-slate-500 hover:bg-slate-100" @click="reload()" title="Reload preview">@include('admin.partials.icon', ['name' => 'refresh'])</button>
      </div>
    </div>
    {{-- The page renders at its real width and is scaled down to fit, so the desktop layout looks right on a laptop. --}}
    <div x-ref="stage" class="overflow-hidden bg-slate-100 p-3" :style="'height:' + (stageHeight + 24) + 'px'">
      <div class="mx-auto origin-top-left" :style="'width:' + frameWidth * scale + 'px; height:' + stageHeight + 'px'">
        <iframe :src="previewUrl" title="Website preview" class="origin-top-left rounded-md bg-white shadow ring-1 ring-slate-200"
                :style="'width:' + frameWidth + 'px; height:' + (stageHeight / scale) + 'px; transform: scale(' + scale + ')'"></iframe>
      </div>
    </div>
    <p class="border-t border-slate-200 px-4 py-2 text-[11px] text-slate-500">Preview of <span x-text="siteUrl + page.path"></span>. Pages saved as drafts do not show in the preview until published. If it stays blank, start the website (npm run dev) or set SITE_URL in the admin .env file.</p>
  </div>
</div>
@endsection

@push('scripts')
<script>
function builderApp(cfg) {
  const { toForm, fromForm } = contentForm
  return {
    ...cfg,
    ...contentForm.methods,
    current: null, form: {}, label: '', dirty: false, statsDirty: false, saving: false, device: 'desktop', stamp: Date.now(), stageWidth: 800,
    get previewUrl() { return this.siteUrl + this.page.path + '?preview=' + this.stamp },
    get frameWidth() { return { desktop: 1366, tablet: 820, mobile: 390 }[this.device] },
    get scale() { return Math.min(1, (this.stageWidth - 24) / this.frameWidth) },
    get stageHeight() { return Math.max(520, window.innerHeight - 190) },
    init() {
      const measure = () => (this.stageWidth = this.$refs.stage?.clientWidth || 800)
      this.$nextTick(measure)
      new ResizeObserver(measure).observe(this.$el)
      while (this.heroStats.length < 3) this.heroStats.push({ value: '', label: '' })
      Sortable.create(this.$refs.list, {
        handle: '.handle', animation: 150,
        onEnd: async () => {
          const ids = [...this.$refs.list.querySelectorAll('li[data-id]')].map((li) => Number(li.dataset.id))
          this.sections.sort((a, b) => ids.indexOf(a.id) - ids.indexOf(b.id))
          try { const r = await adminFetch(this.urls.order, { method: 'POST', body: JSON.stringify({ ids }) }); toast(r.message); this.reload() } catch (e) { toast(e.message, 'error') }
        },
      })
      if (this.sections.length) this.select(this.sections[0])
    },
    select(s) {
      if ((this.dirty || this.statsDirty) && !confirm('You have unsaved changes. Leave this section without saving?')) return
      this.current = s
      this.form = toForm(s.schema.fields, s.data || {})
      this.label = s.label
      this.dirty = false
      this.statsDirty = false
    },
    async save() {
      this.saving = true
      try {
        const data = fromForm(this.current.schema.fields, this.form)
        const r = await adminFetch(this.urls.save + '/' + this.current.id, { method: 'POST', body: JSON.stringify({ data, label: this.label }) })
        this.current.data = data
        this.current.label = this.label
        this.current.updated_at = 'just now'
        if (this.current.schema.heroStats && this.statsDirty) await adminFetch(this.urls.stats, { method: 'POST', body: JSON.stringify({ stats: this.heroStats }) })
        this.dirty = false
        this.statsDirty = false
        toast(r.message)
        this.reload()
      } catch (e) { toast(e.message, 'error') }
      this.saving = false
    },
    async toggle(s) {
      try { const r = await adminFetch(this.urls.save + '/' + s.id + '/toggle', { method: 'POST' }); s.visible = r.visible; toast(r.message); this.reload() } catch (e) { toast(e.message, 'error') }
    },
    async addSection(type) {
      try {
        const r = await adminFetch(this.urls.add, { method: 'POST', body: JSON.stringify({ page: this.page.path, type }) })
        this.sections.push(r.section)
        this.dirty = false; this.statsDirty = false
        this.select(r.section)
        toast(r.message)
        this.reload()
      } catch (e) { toast(e.message, 'error') }
    },
    async remove(s) {
      if (!confirm('Delete the section "' + s.label + '"? This cannot be undone.')) return
      try {
        const r = await adminFetch(this.urls.save + '/' + s.id, { method: 'DELETE' })
        this.sections = this.sections.filter((x) => x.id !== s.id)
        if (this.current && this.current.id === s.id) { this.current = null; this.dirty = false }
        toast(r.message)
        this.reload()
      } catch (e) { toast(e.message, 'error') }
    },
    reload() { this.stamp = Date.now() },
  }
}
</script>
@endpush
