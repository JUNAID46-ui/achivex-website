@extends('admin.layouts.app')
@section('title', 'Backup and Restore')
@section('heading', 'Backup and Restore')
@section('subheading', 'Stored backups are checked after saving: the file is read back and its SHA-256 and row counts must match.')
@section('content')
@php
  $ready = $storage['exists'] && $storage['public'] === false;
  $badge = ['verified' => 'green', 'failed' => 'red', 'running' => 'gray', 'completed' => 'amber', 'deleted' => 'gray'];
@endphp

@if(! $ready)
  <div class="mb-6 rounded-lg border {{ $storage['public'] ? 'border-red-200 bg-red-50 text-red-800' : 'border-amber-200 bg-amber-50 text-amber-900' }} px-5 py-4 text-[13px]">
    @if($storage['public'])
      <p class="font-semibold">The "backups" bucket in Supabase is public.</p>
      <p class="mt-1">Backups contain inbox messages and must not be public. Make the bucket private in Supabase (Storage, backups, Edit bucket). Until then, backups are not stored there.</p>
    @else
      <p class="font-semibold">Private backup storage is not set up.</p>
      <p class="mt-1">{{ $storage['error'] ?? 'Backups can be stored in a private Supabase bucket named "backups", with history, checks and daily backups.' }}</p>
      @if(! $storage['error'] || str_contains((string) $storage['error'], 'answered'))
        <form method="POST" action="{{ route('admin.backup.setup') }}" class="mt-3">@csrf<button class="btn-primary" data-loading>Set up private backup storage</button></form>
      @endif
    @endif
    @error('storage')<p class="mt-2 font-semibold">{{ $message }}</p>@enderror
  </div>
@endif

<div class="grid gap-6 xl:grid-cols-3">
  <form method="POST" action="{{ route('admin.backup.store') }}" class="card">
    @csrf
    <div class="card-header"><h2 class="card-title">Create a stored backup</h2></div>
    <div class="card-body space-y-3 text-[13px]">
      <label class="flex items-start gap-2"><input type="checkbox" checked disabled class="mt-0.5"> <span><strong>Website content</strong> ({{ number_format($counts['content']) }} rows)</span></label>
      <label class="flex items-start gap-2"><input type="checkbox" name="inbox" value="1" checked class="mt-0.5"> <span><strong>Inbox</strong> ({{ number_format($counts['inbox']) }} rows)</span></label>
      <label class="flex items-start gap-2"><input type="checkbox" name="analytics" value="1" class="mt-0.5"> <span><strong>Analytics</strong> ({{ number_format($counts['analytics']) }} rows). Can make the file large.</span></label>
      @error('backup')<p class="error">{{ $message }}</p>@enderror
    </div>
    <div class="flex flex-wrap justify-end gap-2 border-t border-slate-200 bg-slate-50 px-5 py-3">
      <button formaction="{{ route('admin.backup.download') }}" formmethod="GET" class="btn-secondary">@include('admin.partials.icon', ['name' => 'download']) Download only</button>
      <button class="btn-primary" data-loading @disabled(! $ready)>Save and verify</button>
    </div>
  </form>

  <form method="POST" action="{{ route('admin.backup.settings') }}" class="card">
    @csrf
    <div class="card-header"><h2 class="card-title">Schedule and retention</h2></div>
    <div class="card-body space-y-3 text-[13px]">
      <label class="flex items-start gap-2"><input type="checkbox" name="scheduled" value="1" @checked($settings['scheduled']) class="mt-0.5"> <span><strong>Daily backup</strong>, made by the daily task on Vercel</span></label>
      <div class="ml-6 space-y-1">
        <label class="flex items-center gap-2"><input type="checkbox" name="inbox" value="1" @checked($settings['inbox'])> Include inbox</label>
        <label class="flex items-center gap-2"><input type="checkbox" name="analytics" value="1" @checked($settings['analytics'])> Include analytics</label>
      </div>
      <div>
        <label for="keep" class="label">Keep the newest</label>
        <div class="flex items-center gap-2"><input id="keep" type="number" name="keep" min="1" max="100" value="{{ $settings['keep'] }}" class="input w-24"> <span>backups. Older backup files are deleted; their history stays.</span></div>
      </div>
    </div>
    <div class="flex justify-end border-t border-slate-200 bg-slate-50 px-5 py-3"><button class="btn-secondary">Save settings</button></div>
  </form>

  <div class="card" x-data="backupUpload()">
    <div class="card-header"><h2 class="card-title">Restore from a file</h2></div>
    <div class="card-body space-y-3 text-[13px]">
      @if($ready)
        <p>Choose an AchiveX backup file (.json). It is uploaded to private storage and checked. You see exactly what will change before anything is restored.</p>
        <input type="file" accept=".json,application/json" class="input" x-ref="file" aria-label="Backup file">
        <div x-show="progress !== null" class="h-2 rounded bg-slate-100"><div class="h-2 rounded bg-ink" :style="`width:${progress}%`"></div></div>
        <p class="error" x-show="error" x-text="error"></p>
      @else
        <form method="POST" action="{{ route('admin.backup.restore-upload') }}" enctype="multipart/form-data" class="space-y-3" x-data="{ mode: 'merge' }">
          @csrf
          <p>Without private storage, files up to {{ $maxUploadMb }} MB can be restored in one step.</p>
          <input name="file" type="file" accept=".json,application/json" required class="input" aria-label="Backup file">
          <label class="flex items-start gap-2"><input type="radio" name="mode" value="merge" x-model="mode" checked class="mt-0.5"> <span><strong>Merge</strong>: add and update only.</span></label>
          <label class="flex items-start gap-2"><input type="radio" name="mode" value="replace" x-model="mode" class="mt-0.5"> <span><strong>Replace</strong>: also delete items not in the backup.</span></label>
          <label class="label" for="confirm_text_up">Type RESTORE to confirm</label>
          <input id="confirm_text_up" name="confirm_text" class="input" autocomplete="off" required>
          @error('file')<p class="error">{{ $message }}</p>@enderror @error('confirm_text')<p class="error">{{ $message }}</p>@enderror
          <button class="btn-primary" data-loading>Restore</button>
        </form>
      @endif
    </div>
    @if($ready)
      <div class="flex justify-end border-t border-slate-200 bg-slate-50 px-5 py-3"><button type="button" class="btn-primary" @click="start()" :disabled="busy">@include('admin.partials.icon', ['name' => 'upload']) <span x-text="busy ? 'Uploading…' : 'Upload and preview'"></span></button></div>
    @endif
  </div>
</div>

<div class="card mt-6 overflow-x-auto">
  <div class="card-header"><h2 class="card-title">Backup history</h2><span class="text-xs text-slate-500">Times in UTC</span></div>
  <table class="table">
    <thead><tr><th>#</th><th>Created</th><th>Type</th><th>Scope</th><th>Size</th><th>Status</th><th></th></tr></thead>
    <tbody>
      @forelse($runs as $r)
        <tr>
          <td class="text-slate-500">{{ $r->id }}</td>
          <td class="whitespace-nowrap">{{ $r->created_at?->format('Y-m-d H:i') }}<span class="block text-xs text-slate-500">{{ $r->user?->name ?? ($r->trigger === 'scheduled' ? 'Daily task' : 'System') }}</span></td>
          <td>{{ ['manual' => 'Manual', 'scheduled' => 'Daily', 'pre-restore' => 'Safety copy before restore', 'upload' => 'Uploaded file'][$r->trigger] ?? $r->trigger }}</td>
          <td class="text-slate-600">{{ implode(', ', $r->scope ?? []) ?: '–' }}</td>
          <td class="whitespace-nowrap">{{ $r->size ? \App\Services\Health\HealthCheck::mb((int) $r->size) : '–' }}@if($r->sha256)<span class="block font-mono text-[11px] text-slate-400" title="SHA-256 {{ $r->sha256 }}">{{ substr($r->sha256, 0, 12) }}…</span>@endif</td>
          <td>
            <span class="badge-{{ $badge[$r->status] ?? 'gray' }}">{{ $r->status === 'verified' ? 'Verified' : ucfirst($r->status) }}</span>
            @if($r->verified_at)<span class="block text-[11px] text-slate-500">checked {{ $r->verified_at->diffForHumans() }}</span>@endif
            @if($r->error && $r->status !== 'verified')<span class="block max-w-xs text-[11px] text-slate-500">{{ $r->error }}</span>@endif
          </td>
          <td class="whitespace-nowrap text-right">
            @if($r->path && ! $r->deleted_at && $r->status === 'verified')
              <div class="flex justify-end gap-1">
                <a href="{{ route('admin.backup.preview', $r->id) }}" class="btn-secondary btn-sm">Restore…</a>
                <a href="{{ route('admin.backup.stored', $r->id) }}" class="btn-ghost btn-sm" title="One minute download link">Download</a>
                <form method="POST" action="{{ route('admin.backup.verify', $r->id) }}">@csrf<button class="btn-ghost btn-sm">Check</button></form>
                <form method="POST" action="{{ route('admin.backup.destroy', $r->id) }}" onsubmit="return confirm('Delete backup #{{ $r->id }} from storage? This cannot be undone.')">@csrf<input type="hidden" name="confirm" value="1"><button class="btn-ghost btn-sm text-red-600">Delete</button></form>
              </div>
            @endif
          </td>
        </tr>
      @empty
        <tr><td colspan="7" class="py-10 text-center text-slate-500">No stored backups yet.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

<div class="card mt-6">
  <div class="card-header"><h2 class="card-title">What a backup contains</h2></div>
  <div class="card-body grid gap-4 text-[13px] md:grid-cols-2">
    <div>
      <p class="font-semibold text-ink">Included</p>
      <ul class="mt-1 list-disc space-y-0.5 pl-5 text-slate-600">
        <li>Pages, page builder sections, posts, case studies, news, guides, careers, team, milestones, locations, hero stats</li>
        <li>Site settings, SEO settings, redirects, custom code, llms.txt</li>
        <li>The media library list (file names, links, alt text)</li>
        <li>Optional: inbox (messages, guide requests) and analytics (page views, 404 log)</li>
      </ul>
    </div>
    <div>
      <p class="font-semibold text-ink">Not included: back these up separately</p>
      <ul class="mt-1 list-disc space-y-0.5 pl-5 text-slate-600">
        <li>The uploaded image and PDF files themselves (in the Supabase "media" bucket)</li>
        <li>Admin users, passwords, two-step secrets and sessions (never exported, for safety)</li>
        <li>Saved email and storage keys, and Vercel environment variables</li>
        <li>Content revisions, notifications, activity log and audit history</li>
        <li>The website and admin code (in GitHub)</li>
      </ul>
      <p class="mt-2 text-xs text-slate-500">Supabase also keeps its own daily database backups on paid plans.</p>
    </div>
  </div>
</div>

<script>
function backupUpload() {
  const csrf = document.querySelector('meta[name=csrf-token]').content
  const post = (url, body) => fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf }, body: JSON.stringify(body || {}) })
  return {
    busy: false, progress: null, error: '',
    async start() {
      const file = this.$refs.file.files[0]
      this.error = ''
      if (!file) { this.error = 'Choose a backup file first.'; return }
      this.busy = true
      try {
        const signRes = await post(@js(route('admin.backup.upload.sign')), { name: file.name, size: file.size })
        const sign = await signRes.json()
        if (!signRes.ok) throw new Error(sign.message || 'The upload could not start.')
        await new Promise((resolve, reject) => {
          const xhr = new XMLHttpRequest()
          xhr.open('PUT', sign.url)
          xhr.setRequestHeader('Content-Type', 'application/json')
          xhr.setRequestHeader('x-upsert', 'false')
          xhr.upload.onprogress = (e) => { if (e.lengthComputable) this.progress = Math.round(e.loaded / e.total * 100) }
          xhr.onload = () => (xhr.status < 300 ? resolve() : reject(new Error('Storage refused the file (' + xhr.status + ').')))
          xhr.onerror = () => reject(new Error('The upload was interrupted.'))
          xhr.send(file)
        })
        const doneRes = await post(sign.complete)
        const done = await doneRes.json()
        if (!doneRes.ok) throw new Error(done.message || 'The file is not a valid backup.')
        window.location = done.preview
      } catch (e) {
        this.error = e.message
        this.busy = false
        this.progress = null
      }
    },
  }
}
</script>
@endsection
