@extends('admin.layouts.app')
@section('title', 'Notifications')
@section('heading', 'Notifications')
@section('subheading', 'Backups, security warnings, audits and other events that need your attention.')
@section('actions')
  <form method="POST" action="{{ route('admin.notifications.read-all') }}">@csrf<button class="btn-secondary" data-loading>Mark all as read</button></form>
@endsection
@section('content')
<div class="mb-4 flex flex-wrap items-center gap-2 text-[13px]">
  @foreach(['all' => 'Inbox', 'unread' => 'Unread', 'archived' => 'Archived'] as $k => $label)
    <a href="{{ route('admin.notifications', ['view' => $k]) }}" class="rounded-full px-3 py-1 {{ $view === $k ? 'bg-ink text-white' : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:text-ink' }}">{{ $label }}</a>
  @endforeach
  <form method="GET" class="ml-auto flex gap-2">
    <input type="hidden" name="view" value="{{ $view }}">
    <select name="severity" class="input w-40 py-1.5" aria-label="Severity" onchange="this.form.submit()">
      <option value="">All severities</option>
      @foreach(\App\Models\AdminNotification::SEVERITIES as $s)<option value="{{ $s }}" @selected(request('severity') === $s)>{{ ucfirst($s) }}</option>@endforeach
    </select>
  </form>
</div>
<div class="card divide-y divide-slate-100">
  @forelse($items as $n)
    <div class="flex flex-wrap items-start gap-3 px-5 py-4 {{ $n->read_at ? '' : 'bg-slate-50' }}">
      <span class="mt-0.5 badge-{{ ['critical' => 'red', 'warning' => 'warn', 'info' => 'gray'][$n->severity] ?? 'gray' }}">{{ ucfirst($n->severity) }}</span>
      <div class="min-w-0 flex-1">
        <a href="{{ route('admin.notifications.open', $n->id) }}" class="font-semibold text-ink hover:underline">{{ $n->title }}</a>
        @if($n->body)<p class="mt-0.5 text-[13px] text-slate-600">{{ $n->body }}</p>@endif
        <p class="mt-1 text-xs text-slate-400"><time datetime="{{ $n->created_at?->toIso8601String() }}" title="{{ $n->created_at?->format('Y-m-d H:i') }} UTC">{{ $n->created_at?->diffForHumans() }}</time>@if($n->module) · {{ $n->module }}@endif</p>
      </div>
      <div class="flex gap-1">
        <form method="POST" action="{{ route('admin.notifications.update', $n->id) }}">@csrf<input type="hidden" name="action" value="{{ $n->read_at ? 'unread' : 'read' }}"><button class="btn-ghost btn-sm">{{ $n->read_at ? 'Mark unread' : 'Mark read' }}</button></form>
        <form method="POST" action="{{ route('admin.notifications.update', $n->id) }}">@csrf<input type="hidden" name="action" value="{{ $n->archived_at ? 'unarchive' : 'archive' }}"><button class="btn-ghost btn-sm">{{ $n->archived_at ? 'Move to inbox' : 'Archive' }}</button></form>
      </div>
    </div>
  @empty
    <div class="px-5 py-14 text-center text-slate-500">
      <p class="font-semibold text-slate-700">No notifications here.</p>
      <p class="mt-1 text-[13px]">Failed backups, storage warnings, finished audits and security warnings will show up here.</p>
    </div>
  @endforelse
</div>
<div class="mt-4">{{ $items->links('admin.partials.pagination') }}</div>
@endsection
