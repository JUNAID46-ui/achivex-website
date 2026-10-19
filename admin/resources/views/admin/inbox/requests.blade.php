@extends('admin.layouts.app')
@section('title', 'Guide Requests')
@section('heading', 'Guide Requests')
@section('subheading', 'People who asked for a whitepaper or eBook. Send them the file, then mark the request as sent.')
@section('actions')
  <a href="{{ route('admin.inbox.requests.export') }}" class="btn-secondary">@include('admin.partials.icon', ['name' => 'download']) Export CSV</a>
@endsection
@section('content')
<div class="mb-4 flex gap-1 text-[13px]">
  <a href="{{ route('admin.inbox.requests') }}" class="rounded-md px-3 py-1.5 {{ request('status') ? 'text-slate-600 hover:bg-white' : 'bg-navy font-semibold text-white' }}">All</a>
  <a href="{{ route('admin.inbox.requests', ['status' => 'pending']) }}" class="rounded-md px-3 py-1.5 {{ request('status') ? 'bg-navy font-semibold text-white' : 'text-slate-600 hover:bg-white' }}">Waiting <span class="opacity-70">({{ $pending }})</span></a>
</div>
<div class="card overflow-x-auto">
  <table class="table">
    <thead><tr><th>Date</th><th>Guide</th><th>Name</th><th>Email</th><th>Company</th><th>Job title</th><th>Status</th></tr></thead>
    <tbody>
      @forelse($requests as $r)
        <tr>
          <td class="whitespace-nowrap text-slate-500">{{ $r->created_at?->format('M j, Y') }}</td>
          <td class="font-semibold text-navy">{{ $r->whitepaper?->title ?? $r->whitepaper_slug }}</td>
          <td>{{ $r->name }}</td>
          <td><a class="text-navy underline" href="mailto:{{ $r->email }}?subject={{ rawurlencode('Your copy of '.($r->whitepaper?->title ?? 'our guide')) }}{{ $r->whitepaper?->file_url ? '&body='.rawurlencode("Hello {$r->name},\n\nHere is your copy: ".$r->whitepaper->file_url) : '' }}">{{ $r->email }}</a></td>
          <td>{{ $r->company }}</td>
          <td>{{ $r->job_title }}</td>
          <td>
            <form method="POST" action="{{ route('admin.inbox.requests.handled', $r) }}">@csrf
              <button class="{{ $r->handled_at ? 'badge-green' : 'badge-amber' }}">{{ $r->handled_at ? 'Sent' : 'Mark as sent' }}</button>
            </form>
          </td>
        </tr>
      @empty
        <tr><td colspan="7" class="py-14 text-center text-slate-500">No guide requests yet.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
<div class="mt-4">{{ $requests->links('admin.partials.pagination') }}</div>
@endsection
