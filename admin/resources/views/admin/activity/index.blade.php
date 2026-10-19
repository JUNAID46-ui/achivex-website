@extends('admin.layouts.app')
@section('title', 'Activity Log')
@section('heading', 'Activity Log')
@section('subheading', 'Every change made in the admin, newest first.')
@section('content')
<form method="GET" class="mb-4 flex flex-wrap gap-2">
  <select name="user" class="input w-48 py-1.5" aria-label="User"><option value="">All users</option>
    @foreach($users as $id => $name)<option value="{{ $id }}" @selected(request('user') == $id)>{{ $name }}</option>@endforeach</select>
  <select name="action" class="input w-48 py-1.5" aria-label="Action"><option value="">All actions</option>
    @foreach($actions as $a)<option value="{{ $a }}" @selected(request('action') === $a)>{{ ucfirst($a) }}</option>@endforeach</select>
  <button class="btn-secondary">Filter</button>
</form>
<div class="card overflow-x-auto">
  <table class="table">
    <thead><tr><th>When</th><th>User</th><th>Action</th><th>Item</th><th>Details</th><th>IP</th></tr></thead>
    <tbody>
      @forelse($logs as $l)
        <tr>
          <td class="whitespace-nowrap text-slate-500" title="{{ $l->created_at }}">{{ $l->created_at?->diffForHumans() }}</td>
          <td class="font-semibold text-navy">{{ $l->user?->name ?? 'System' }}</td>
          <td><span class="badge-{{ ['created' => 'green', 'deleted' => 'red', 'login' => 'navy'][$l->action] ?? 'gray' }}">{{ $l->action }}</span></td>
          <td>{{ $l->subject_type }} @if($l->subject_label)<span class="text-slate-500">· {{ $l->subject_label }}</span>@endif</td>
          <td class="text-slate-500">{{ $l->description }}</td>
          <td class="text-xs text-slate-400">{{ $l->ip }}</td>
        </tr>
      @empty
        <tr><td colspan="6" class="py-14 text-center text-slate-500">No activity recorded yet.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
<div class="mt-4">{{ $logs->links('admin.partials.pagination') }}</div>
@endsection
