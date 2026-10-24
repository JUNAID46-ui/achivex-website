@extends('admin.layouts.app')
@section('title', 'Activity Log')
@section('heading', 'Activity Log')
@section('subheading', 'Every change made in the admin, newest first. Entries cannot be edited or deleted.')
@section('actions')
  <a href="{{ route('admin.activity.export', request()->query()) }}" class="btn-secondary">@include('admin.partials.icon', ['name' => 'download']) Export CSV</a>
@endsection
@section('content')
<form method="GET" class="mb-4 flex flex-wrap items-end gap-2">
  <input name="q" value="{{ request('q') }}" class="input w-56 py-1.5" placeholder="Search item, record or details" aria-label="Search">
  <select name="user" class="input w-44 py-1.5" aria-label="User"><option value="">All users</option>
    @foreach($users as $id => $name)<option value="{{ $id }}" @selected(request('user') == $id)>{{ $name }}</option>@endforeach</select>
  <select name="action" class="input w-40 py-1.5" aria-label="Action"><option value="">All actions</option>
    @foreach($actions as $a)<option value="{{ $a }}" @selected(request('action') === $a)>{{ str_replace('_', ' ', ucfirst($a)) }}</option>@endforeach</select>
  <select name="module" class="input w-40 py-1.5" aria-label="Module"><option value="">All modules</option>
    @foreach($modules as $m)<option value="{{ $m }}" @selected(request('module') === $m)>{{ $m }}</option>@endforeach</select>
  <select name="status" class="input w-32 py-1.5" aria-label="Result"><option value="">Any result</option>
    <option value="success" @selected(request('status') === 'success')>Success</option><option value="failure" @selected(request('status') === 'failure')>Failure</option></select>
  <input type="date" name="from" value="{{ request('from') }}" class="input w-36 py-1.5" aria-label="From date">
  <input type="date" name="to" value="{{ request('to') }}" class="input w-36 py-1.5" aria-label="To date">
  <label class="flex items-center gap-1.5 text-[13px] text-neutral-600"><input type="checkbox" name="sensitive" value="1" @checked(request('sensitive')) class="border-neutral-300"> Security sensitive only</label>
  <button class="btn-secondary">Filter</button>
  @if(request()->hasAny(['q', 'user', 'action', 'module', 'status', 'from', 'to', 'sensitive']))<a href="{{ route('admin.activity') }}" class="btn-ghost">Clear</a>@endif
</form>
<div class="card overflow-x-auto">
  <table class="table">
    <thead><tr><th>When (UTC)</th><th>User</th><th>Action</th><th>Item</th><th>Details</th><th>IP</th></tr></thead>
    <tbody>
      @forelse($logs as $l)
        <tr>
          <td class="whitespace-nowrap text-neutral-500"><time datetime="{{ $l->created_at?->toIso8601String() }}" title="{{ $l->created_at?->diffForHumans() }}">{{ $l->created_at?->format('Y-m-d H:i') }}</time></td>
          <td><span class="font-semibold text-ink">{{ $l->user?->name ?? $l->user_name ?? 'System' }}</span>@if($l->user_role)<span class="block text-xs text-neutral-400">{{ \App\Models\User::ROLES[$l->user_role] ?? $l->user_role }}</span>@endif</td>
          <td class="whitespace-nowrap">
            <span class="badge-{{ ['created' => 'green', 'deleted' => 'red', 'login' => 'ink', 'login_failed' => 'red', 'restored' => 'warn'][$l->action] ?? 'gray' }}">{{ str_replace('_', ' ', $l->action) }}</span>
            @if($l->status === 'failure')<span class="badge-red">failed</span>@endif
          </td>
          <td>{{ $l->module ?? $l->subject_type }} @if($l->subject_label)<span class="text-neutral-500">· {{ $l->subject_label }}</span>@endif @if($l->subject_id)<span class="block font-mono text-[11px] text-neutral-400">{{ $l->subject_id }}</span>@endif</td>
          <td class="text-neutral-500">{{ $l->description }}</td>
          <td class="text-xs text-neutral-400">{{ $l->ip }}</td>
        </tr>
      @empty
        <tr><td colspan="6" class="py-14 text-center text-neutral-500">No activity matches these filters.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
<div class="mt-4">{{ $logs->links('admin.partials.pagination') }}</div>
@endsection
