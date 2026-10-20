@extends('admin.layouts.app')
@section('title', 'Backup and Restore')
@section('heading', 'Backup and Restore')
@section('subheading', 'Download a copy of the website content, or upload a backup to bring it back.')
@section('content')
<div class="grid gap-6 xl:grid-cols-2">
  <form method="GET" action="{{ route('admin.backup.download') }}" class="card">
    <div class="card-header"><h2 class="card-title">Download a backup</h2></div>
    <div class="card-body space-y-4 text-[13px]">
      <p>The backup is one JSON file with all pages, page builder sections, posts, case studies, news, guides, careers, team, milestones, locations, SEO settings, redirects and the media library list.</p>
      <label class="flex items-start gap-2"><input type="checkbox" checked disabled class="mt-0.5"> <span><strong>Website content</strong> ({{ number_format($counts['content']) }} rows)</span></label>
      <label class="flex items-start gap-2"><input type="checkbox" name="inbox" value="1" checked class="mt-0.5"> <span><strong>Inbox</strong>: contact messages and guide requests ({{ number_format($counts['inbox']) }} rows)</span></label>
      <label class="flex items-start gap-2"><input type="checkbox" name="analytics" value="1" class="mt-0.5"> <span><strong>Analytics</strong>: page views and 404 log ({{ number_format($counts['analytics']) }} rows). Can make the file large.</span></label>
      <p class="help">Not included, for safety: admin users and passwords, logins, and saved email or storage keys. Uploaded images and files stay in Supabase Storage; the backup keeps their links.</p>
    </div>
    <div class="flex justify-end border-t border-slate-200 bg-slate-50 px-5 py-3">
      <button class="btn-primary">@include('admin.partials.icon', ['name' => 'download']) Download backup</button>
    </div>
  </form>

  <form method="POST" action="{{ route('admin.backup.restore') }}" enctype="multipart/form-data" class="card"
        x-data="{ mode: 'merge' }" onsubmit="return confirm('Restore this backup now?')">
    @csrf
    <div class="card-header"><h2 class="card-title">Restore from a backup</h2></div>
    <div class="card-body space-y-4 text-[13px]">
      <div>
        <label for="backup-file" class="label">Backup file (.json, up to {{ $maxUploadMb }} MB)</label>
        <input id="backup-file" name="file" type="file" accept=".json,application/json" required class="input">
      </div>
      <fieldset class="space-y-2">
        <legend class="label">How to restore</legend>
        <label class="flex items-start gap-2"><input type="radio" name="mode" value="merge" x-model="mode" checked class="mt-0.5">
          <span><strong>Merge (safe)</strong>: adds missing items and updates items that are in the backup. Nothing else is removed.</span></label>
        <label class="flex items-start gap-2"><input type="radio" name="mode" value="replace" x-model="mode" class="mt-0.5">
          <span><strong>Replace</strong>: makes the tables in the backup exactly match it. Items added after the backup was made are deleted.</span></label>
      </fieldset>
      <p class="help" x-show="mode === 'replace'">Download a fresh backup first, so you can undo this.</p>
      <label class="flex items-start gap-2"><input type="checkbox" name="confirm" value="1" required class="mt-0.5"> <span>I understand this changes the live website.</span></label>
      <p class="help">The restore runs as one step. If anything fails, nothing is changed.</p>
    </div>
    <div class="flex justify-end border-t border-slate-200 bg-slate-50 px-5 py-3">
      <button class="btn-primary">@include('admin.partials.icon', ['name' => 'upload']) Restore backup</button>
    </div>
  </form>
</div>

<div class="card mt-6 overflow-x-auto">
  <div class="card-header"><h2 class="card-title">Recent backups and restores</h2></div>
  <table class="table">
    <thead><tr><th>When</th><th>User</th><th>Action</th><th>Details</th></tr></thead>
    <tbody>
      @forelse($history as $h)
        <tr>
          <td class="whitespace-nowrap text-slate-500" title="{{ $h->created_at }}">{{ $h->created_at?->diffForHumans() }}</td>
          <td class="font-semibold text-navy">{{ $h->user?->name ?? 'System' }}</td>
          <td>{{ $h->subject_label }}</td>
          <td class="text-slate-500">{{ $h->description }}</td>
        </tr>
      @empty
        <tr><td colspan="4" class="py-10 text-center text-slate-500">No backups yet.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
