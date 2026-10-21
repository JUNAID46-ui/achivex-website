@extends('admin.layouts.app')
@section('title', 'Media Library')
@section('heading', 'Media Library')
@section('subheading', $media->total().' files · '.number_format($totalSize / 1048576, 1).' MB used · up to '.($maxUpload >= 1024 ? round($maxUpload / 1024, 1).' GB' : $maxUpload.' MB').' per file')

@section('content')
<div x-data="{ selected: null, dragging: false, busy: false, progress: '', error: '',
        async send(files) {
          if (!files.length || this.busy) return
          this.busy = true; this.error = ''
          try { await uploadMedia(files, (p) => this.progress = (p.total > 1 ? 'File ' + (p.index + 1) + ' of ' + p.total + ': ' : '') + p.percent + '%'); window.location.reload() }
          catch (e) { this.error = e.message; this.busy = false }
        } }" class="space-y-4">
  @unless($cloud)
    <div class="border-l-4 border-ink bg-slate-100 px-4 py-3 text-[13px] text-slate-700">
      <strong>Images are saved on this computer only.</strong> The live website cannot show them until image storage is connected.
      @if(auth()->user()->canAccess('settings'))<a href="{{ route('admin.settings') }}#storage" class="font-semibold text-ink underline">Connect image storage in Settings</a>@endif
    </div>
  @endunless
  <div
        class="card flex flex-col items-center justify-center gap-3 border-2 border-dashed px-6 py-10 text-center transition"
        :class="dragging ? 'border-ink bg-slate-100' : 'border-slate-300'"
        @dragover.prevent="dragging = true" @dragleave.prevent="dragging = false"
        @drop.prevent="dragging = false; send($event.dataTransfer.files)">
    <span class="text-slate-400">@include('admin.partials.icon', ['name' => 'upload', 'class' => 'h-8 w-8'])</span>
    <p class="text-[14px] font-semibold text-ink">Drop files to upload</p>
    <p class="text-xs text-slate-500">Images, PDFs, documents and videos, up to {{ $maxUpload >= 1024 ? round($maxUpload / 1024, 1).' GB' : $maxUpload.' MB' }} each</p>
    <label class="btn-primary cursor-pointer" :class="busy && 'pointer-events-none opacity-60'"><span x-text="busy ? 'Uploading ' + progress : 'Select files'">Select files</span>
      <input type="file" multiple class="sr-only" @change="send($event.target.files); $event.target.value = ''">
    </label>
    <p x-show="error" x-text="error" class="text-[13px] font-semibold text-red-600"></p>
  </div>

  <div class="flex flex-wrap items-center justify-between gap-3">
    <div class="flex gap-1 text-[13px]">
      @foreach(['' => 'All', 'image' => 'Images', 'document' => 'Documents'] as $k => $l)
        <a href="{{ request()->fullUrlWithQuery(['type' => $k ?: null, 'page' => null]) }}" class="px-3 py-1.5 {{ request('type', '') === $k ? 'bg-ink font-semibold text-white' : 'text-slate-600 hover:bg-white' }}">{{ $l }}</a>
      @endforeach
    </div>
    <form method="GET" class="flex gap-2">
      <input name="q" value="{{ request('q') }}" class="input w-60 py-1.5" placeholder="Search files" aria-label="Search files">
      <button class="btn-secondary">Search</button>
    </form>
  </div>

  <div class="grid grid-cols-2 gap-4 sm:grid-cols-4 lg:grid-cols-6">
    @forelse($media as $m)
      <button type="button" @click="selected = @js($m)" class="group overflow-hidden  border border-slate-200 bg-white text-left  hover:ring-2 hover:ring-ink">
        @if($m->isImage())
          <img src="{{ $m->url }}" alt="{{ $m->alt }}" class="aspect-square w-full bg-slate-100 object-cover" loading="lazy">
        @else
          <div class="flex aspect-square w-full items-center justify-center bg-slate-100 text-sm font-bold uppercase text-slate-500">{{ pathinfo($m->filename, PATHINFO_EXTENSION) }}</div>
        @endif
        <p class="truncate px-2.5 py-1.5 text-xs text-slate-600">{{ $m->filename }}</p>
      </button>
    @empty
      <p class="col-span-full py-12 text-center text-slate-500">The library is empty. Upload your first file above.</p>
    @endforelse
  </div>
  {{ $media->links('admin.partials.pagination') }}

  {{-- Details drawer --}}
  <div x-show="selected" x-cloak class="fixed inset-0 z-50 flex justify-end bg-black/40" @click.self="selected = null" @keydown.escape.window="selected = null">
    <div class="h-full w-full max-w-md overflow-y-auto bg-white p-6 shadow-2xl" x-show="selected">
      <template x-if="selected">
        <div class="space-y-4">
          <div class="flex items-center justify-between"><h2 class="text-lg font-bold text-ink">File details</h2>
            <button class="btn-ghost" @click="selected = null">@include('admin.partials.icon', ['name' => 'x'])</button></div>
          <template x-if="(selected.mime || '').startsWith('image/')"><img :src="selected.url" class="w-full  bg-slate-100 ring-1 ring-slate-200" alt=""></template>
          <dl class="grid grid-cols-3 gap-y-2 text-[13px]">
            <dt class="text-slate-500">File name</dt><dd class="col-span-2 break-all" x-text="selected.filename"></dd>
            <dt class="text-slate-500">Type</dt><dd class="col-span-2" x-text="selected.mime"></dd>
            <dt class="text-slate-500">Size</dt><dd class="col-span-2" x-text="selected.human_size"></dd>
            <template x-if="selected.width"><dt class="text-slate-500">Dimensions</dt></template>
            <template x-if="selected.width"><dd class="col-span-2" x-text="selected.width + ' x ' + selected.height"></dd></template>
            <dt class="text-slate-500">Uploaded</dt><dd class="col-span-2" x-text="new Date(selected.created_at).toLocaleString()"></dd>
          </dl>
          <div>
            <label class="label">File URL</label>
            <div class="flex gap-2"><input class="input text-xs" :value="selected.url" readonly>
              <button type="button" class="btn-secondary" @click="copyText(selected.url, $event.target)">Copy</button></div>
          </div>
          <form method="POST" :action="'{{ url('admin/media') }}/' + selected.id" class="space-y-2">
            @csrf @method('PATCH')
            <label class="label" for="alt">Alternative text</label>
            <input id="alt" name="alt" class="input" :value="selected.alt" placeholder="Describe the image for screen readers and SEO">
            <button class="btn-primary">Save details</button>
          </form>
          <form method="POST" :action="'{{ url('admin/media') }}/' + selected.id" onsubmit="return confirm('Delete this file permanently? Pages using it will show a broken image.')">
            @csrf @method('DELETE')
            <button class="text-[13px] font-semibold text-red-600 hover:underline">Delete permanently</button>
          </form>
        </div>
      </template>
    </div>
  </div>
</div>
@endsection
