@extends('admin.layouts.app')
@section('title', 'Restore preview')
@section('heading', 'Restore preview: backup #'.$run->id)
@section('subheading', 'Nothing has changed yet. Check what the restore will do, then confirm.')
@section('actions')
  <a href="{{ route('admin.backup') }}" class="btn-secondary">&larr; Back</a>
@endsection
@section('content')
<div class="mb-4 flex flex-wrap gap-2 text-[13px]">
  <a href="{{ route('admin.backup.preview', [$run->id, 'mode' => 'merge']) }}" class="rounded-full px-3 py-1 {{ ! $replace ? 'bg-ink text-white' : 'bg-white ring-1 ring-slate-200' }}">Merge (safe)</a>
  <a href="{{ route('admin.backup.preview', [$run->id, 'mode' => 'replace']) }}" class="rounded-full px-3 py-1 {{ $replace ? 'bg-ink text-white' : 'bg-white ring-1 ring-slate-200' }}">Replace</a>
  <span class="self-center text-slate-500">{{ $replace ? 'Tables become exactly what the backup holds. Rows added since are deleted.' : 'Adds missing rows and updates rows that are in the backup. Nothing is deleted.' }}</span>
</div>
<div class="card overflow-x-auto">
  <table class="table">
    <thead><tr><th>Table</th><th class="text-right">Now</th><th class="text-right">In backup</th><th class="text-right">Updated</th><th class="text-right">Added</th><th class="text-right">Deleted</th></tr></thead>
    <tbody>
      @foreach($rows as $table => $r)
        <tr>
          <td class="font-mono text-[12.5px]">{{ $table }}</td>
          <td class="text-right">{{ number_format($r['current']) }}</td>
          <td class="text-right">{{ number_format($r['in_backup']) }}</td>
          <td class="text-right">{{ number_format($r['update']) }}</td>
          <td class="text-right {{ $r['add'] ? 'text-emerald-700' : '' }}">{{ number_format($r['add']) }}</td>
          <td class="text-right {{ $r['remove'] ? 'font-semibold text-red-600' : '' }}">{{ number_format($r['remove']) }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>
<form method="POST" action="{{ route('admin.backup.restore', $run->id) }}" class="card mt-6">
  @csrf <input type="hidden" name="mode" value="{{ $replace ? 'replace' : 'merge' }}">
  <div class="card-body space-y-3 text-[13px]">
    <p>Before restoring, a safety backup of the current data is saved automatically, so this can be undone. If any part of the restore fails, nothing is changed.</p>
    <label for="confirm_text" class="label">Type RESTORE to confirm</label>
    <input id="confirm_text" name="confirm_text" class="input w-60" autocomplete="off" required>
    @error('confirm_text')<p class="error">{{ $message }}</p>@enderror
  </div>
  <div class="flex justify-end pt-2">
    <button class="{{ $replace ? 'btn-danger' : 'btn-primary' }}" data-loading>{{ $replace ? 'Replace with this backup' : 'Merge this backup' }}</button>
  </div>
</form>
@endsection
