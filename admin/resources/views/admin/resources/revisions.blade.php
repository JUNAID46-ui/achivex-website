@extends('admin.layouts.app')
@section('title', 'Revisions')
@section('heading', 'Revisions: '.$resource->titleOf($record))
@section('subheading', 'A copy is saved before every change. Restoring puts that copy back and keeps the current version here too.')
@section('actions')
  <a href="{{ route('admin.resources.edit', [$resource->slug, $record->getKey()]) }}" class="btn-secondary">&larr; Back to editing</a>
@endsection
@section('content')
<div class="grid gap-6 xl:grid-cols-[360px_minmax(0,1fr)]">
  <div class="card divide-y divide-slate-100">
    @forelse($revisions as $rev)
      <a href="{{ route('admin.resources.revisions', [$resource->slug, $record->getKey(), 'compare' => $rev->id]) }}"
         class="block px-4 py-3 hover:bg-slate-50 {{ $compare?->id === $rev->id ? 'bg-slate-100' : '' }}">
        <span class="font-semibold text-ink">{{ $rev->created_at?->format('M j, Y H:i') }} UTC</span>
        <span class="block text-xs text-slate-500">{{ $rev->user_name ?? 'System' }} · {{ $rev->action === 'delete' ? 'before delete' : 'before a change' }}</span>
      </a>
    @empty
      <p class="px-4 py-10 text-center text-slate-500">No earlier versions yet.</p>
    @endforelse
  </div>
  <div class="card">
    @if($compare)
      <div class="card-header"><h2 class="card-title">Copy from {{ $compare->created_at?->format('M j, Y H:i') }} compared with now</h2></div>
      <div class="card-body space-y-4">
        @forelse($diff as $field => $d)
          <div>
            <p class="label">{{ str_replace('_', ' ', ucfirst($field)) }}</p>
            <div class="grid gap-3 md:grid-cols-2 text-[13px]">
              <pre class="max-h-60 overflow-auto whitespace-pre-wrap  bg-red-50 p-3 text-red-900">{{ is_scalar($d['then']) || $d['then'] === null ? ($d['then'] ?? '(empty)') : json_encode($d['then'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
              <pre class="max-h-60 overflow-auto whitespace-pre-wrap  bg-emerald-50 p-3 text-emerald-900">{{ is_scalar($d['now']) || $d['now'] === null ? ($d['now'] ?? '(empty)') : json_encode($d['now'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
            </div>
          </div>
        @empty
          <p class="text-slate-500">This copy is the same as the current version.</p>
        @endforelse
      </div>
      @if($diff)
        <form method="POST" action="{{ route('admin.resources.restore', [$resource->slug, $record->getKey(), $compare->id]) }}" class="flex flex-wrap items-center justify-between gap-3 pt-2">
          @csrf
          <label class="flex items-center gap-2 text-[13px]"><input type="checkbox" name="confirm" value="1" class="border-slate-300" required> I want to replace the current version with this copy</label>
          <button class="btn-primary" data-loading>Restore this copy</button>
        </form>
      @endif
    @else
      <div class="card-body py-14 text-center text-slate-500">Choose a copy on the left to see what changed.</div>
    @endif
  </div>
</div>
@endsection
