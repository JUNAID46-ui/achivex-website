@extends('admin.layouts.app')
@section('title', 'Recently deleted')
@section('heading', 'Recently deleted '.strtolower($resource->label))
@section('subheading', 'Copies kept when items were deleted. Restored items come back as drafts.')
@section('actions')
  <a href="{{ route('admin.resources.index', $resource->slug) }}" class="btn-secondary">&larr; All {{ strtolower($resource->label) }}</a>
@endsection
@section('content')
<div class="card overflow-x-auto">
  <table class="table">
    <thead><tr><th>Item</th><th>Deleted by</th><th>When (UTC)</th><th></th></tr></thead>
    <tbody>
      @forelse($items as $rev)
        <tr>
          <td class="font-semibold text-ink">{{ $rev->data[$resource->titleField] ?? $rev->record_key }}</td>
          <td>{{ $rev->user_name ?? 'System' }}</td>
          <td class="whitespace-nowrap text-slate-500">{{ $rev->created_at?->format('Y-m-d H:i') }}</td>
          <td class="text-right">
            <form method="POST" action="{{ route('admin.resources.restore-deleted', [$resource->slug, $rev->id]) }}" onsubmit="return confirm('Restore this item as a draft?')">
              @csrf <input type="hidden" name="confirm" value="1">
              <button class="btn-secondary btn-sm">Restore as draft</button>
            </form>
          </td>
        </tr>
      @empty
        <tr><td colspan="4" class="py-14 text-center text-slate-500">Nothing deleted recently.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
