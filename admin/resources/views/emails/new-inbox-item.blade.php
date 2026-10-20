@extends('emails.layout')
@section('body')
  @if ($kind === 'message')
    <p style="margin:0 0 16px"><strong>New message from the website contact form.</strong> Reply to this email to answer {{ $item->name }} directly.</p>
    <table cellpadding="0" cellspacing="0" style="font-size:14px;margin-bottom:16px">
      <tr><td style="padding:2px 12px 2px 0;color:#334756">Name</td><td>{{ $item->name }}</td></tr>
      <tr><td style="padding:2px 12px 2px 0;color:#334756">Email</td><td>{{ $item->email }}</td></tr>
      @if ($item->phone)<tr><td style="padding:2px 12px 2px 0;color:#334756">Phone</td><td>{{ $item->phone }}</td></tr>@endif
      @if ($item->service)<tr><td style="padding:2px 12px 2px 0;color:#334756">Service</td><td>{{ $item->service }}</td></tr>@endif
      <tr><td style="padding:2px 12px 2px 0;color:#334756">Received</td><td>{{ $item->created_at?->format('F j, Y g:i A') }} UTC</td></tr>
    </table>
    <div style="white-space:pre-line;padding:12px 16px;border-left:3px solid #F0A500;background:#EEF1F3">{{ $item->message }}</div>
    <p style="margin:16px 0 0"><a href="{{ route('admin.inbox.messages.show', $item->id) }}" style="color:#082032">Open in the admin inbox</a></p>
  @else
    <p style="margin:0 0 16px"><strong>New guide request from the website.</strong></p>
    <table cellpadding="0" cellspacing="0" style="font-size:14px">
      <tr><td style="padding:2px 12px 2px 0;color:#334756">Guide</td><td>{{ $item->whitepaper?->title ?? $item->whitepaper_slug }}</td></tr>
      <tr><td style="padding:2px 12px 2px 0;color:#334756">Name</td><td>{{ $item->name }}</td></tr>
      <tr><td style="padding:2px 12px 2px 0;color:#334756">Email</td><td>{{ $item->email }}</td></tr>
      @if ($item->company)<tr><td style="padding:2px 12px 2px 0;color:#334756">Company</td><td>{{ $item->company }}</td></tr>@endif
      @if ($item->job_title)<tr><td style="padding:2px 12px 2px 0;color:#334756">Job title</td><td>{{ $item->job_title }}</td></tr>@endif
      <tr><td style="padding:2px 12px 2px 0;color:#334756">Received</td><td>{{ $item->created_at?->format('F j, Y g:i A') }} UTC</td></tr>
    </table>
    <p style="margin:16px 0 0"><a href="{{ route('admin.inbox.requests') }}" style="color:#082032">Open guide requests in the admin</a></p>
  @endif
@endsection
