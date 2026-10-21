@extends('admin.layouts.app')
@section('title', 'Messages')
@section('heading', 'Messages')
@section('subheading', 'Messages sent through the contact form on the website.')
@section('actions')
  <a href="{{ route('admin.inbox.messages.export') }}" class="btn-secondary">@include('admin.partials.icon', ['name' => 'download']) Export CSV</a>
@endsection
@section('content')
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
  <div class="flex gap-1 text-[13px]">
    @foreach(['all' => 'All', 'unread' => 'Unread', 'starred' => 'Starred'] as $k => $l)
      <a href="{{ request()->fullUrlWithQuery(['filter' => $k === 'all' ? null : $k, 'page' => null]) }}" class="rounded-md px-3 py-1.5 {{ $filter === $k ? 'bg-ink font-semibold text-white' : 'text-slate-600 hover:bg-white' }}">{{ $l }} <span class="{{ $filter === $k ? 'text-white/70' : 'text-slate-400' }}">({{ $counts[$k] }})</span></a>
    @endforeach
  </div>
  <form method="GET" class="flex gap-2"><input name="q" value="{{ request('q') }}" class="input w-64 py-1.5" placeholder="Search messages" aria-label="Search messages"><button class="btn-secondary">Search</button></form>
</div>
<div class="card overflow-hidden">
  <ul class="divide-y divide-slate-100">
    @forelse($messages as $m)
      <li class="flex items-start gap-3 px-5 py-3.5 {{ $m->read_at ? '' : 'bg-accent-50/60' }}">
        <form method="POST" action="{{ route('admin.inbox.messages.star', $m) }}">@csrf
          <button class="mt-0.5 {{ $m->starred ? 'text-amber' : 'text-slate-300 hover:text-amber' }}" title="Star">@include('admin.partials.icon', ['name' => 'star', 'class' => 'h-4 w-4'.($m->starred ? ' fill-current' : '')])</button>
        </form>
        <a href="{{ route('admin.inbox.messages.show', $m) }}" class="min-w-0 flex-1">
          <div class="flex flex-wrap items-center gap-2">
            <span class="text-[14px] {{ $m->read_at ? 'text-slate-700' : 'font-bold text-ink' }}">{{ $m->name }}</span>
            <span class="text-xs text-slate-500">{{ $m->email }}</span>
            @if($m->service)<span class="badge-gray">{{ $m->service }}</span>@endif
            @unless($m->read_at)<span class="badge-amber">New</span>@endunless
          </div>
          <p class="mt-0.5 truncate text-[13px] text-slate-500">{{ $m->message }}</p>
        </a>
        <span class="shrink-0 text-xs text-slate-400" title="{{ $m->created_at }}">{{ $m->created_at?->diffForHumans() }}</span>
      </li>
    @empty
      <li class="py-16 text-center text-slate-500">No messages{{ $filter !== 'all' ? ' in this view' : ' yet' }}.</li>
    @endforelse
  </ul>
</div>
<div class="mt-4">{{ $messages->links('admin.partials.pagination') }}</div>
@endsection
