@extends('emails.layout')
@section('preheader', $kind === 'message' ? 'New website message from '.$item->name : 'New guide request from '.$item->name)
@section('body')
  @if ($kind === 'message')
    <p style="margin:0 0 16px"><strong>New message from the website contact form.</strong> Reply to this email to answer {{ $item->name }} directly.</p>
    <table cellpadding="0" cellspacing="0" style="font-size:14px;margin-bottom:16px">
      <tr><td style="padding:2px 12px 2px 0;color:#4B5563">Name</td><td>{{ $item->name }}</td></tr>
      <tr><td style="padding:2px 12px 2px 0;color:#4B5563">Email</td><td>{{ $item->email }}</td></tr>
      @if ($item->phone)<tr><td style="padding:2px 12px 2px 0;color:#4B5563">Phone</td><td>{{ $item->phone }}</td></tr>@endif
      @if ($item->service)<tr><td style="padding:2px 12px 2px 0;color:#4B5563">Service</td><td>{{ $item->service }}</td></tr>@endif
      <tr><td style="padding:2px 12px 2px 0;color:#4B5563">Received</td><td>{{ $item->created_at?->format('F j, Y g:i A') }} UTC</td></tr>
    </table>
    <div style="white-space:pre-line;padding:12px 16px;border-left:3px solid #0F2C9C;background:#F3F5F8;border-radius:0 8px 8px 0">{{ $item->message }}</div>
    <p style="margin:16px 0 0"><a href="{{ route('admin.inbox.messages.show', $item->id) }}" style="display:inline-block;background:#B6F500;color:#0B0D12;text-decoration:none;font-weight:bold;padding:11px 22px;border-radius:999px">Open in the admin inbox</a></p>
  @else
    <p style="margin:0 0 16px"><strong>New guide request from the website.</strong></p>
    <table cellpadding="0" cellspacing="0" style="font-size:14px">
      <tr><td style="padding:2px 12px 2px 0;color:#4B5563">Guide</td><td>{{ $item->whitepaper?->title ?? $item->whitepaper_slug }}</td></tr>
      <tr><td style="padding:2px 12px 2px 0;color:#4B5563">Name</td><td>{{ $item->name }}</td></tr>
      <tr><td style="padding:2px 12px 2px 0;color:#4B5563">Email</td><td>{{ $item->email }}</td></tr>
      @if ($item->company)<tr><td style="padding:2px 12px 2px 0;color:#4B5563">Company</td><td>{{ $item->company }}</td></tr>@endif
      @if ($item->job_title)<tr><td style="padding:2px 12px 2px 0;color:#4B5563">Job title</td><td>{{ $item->job_title }}</td></tr>@endif
      <tr><td style="padding:2px 12px 2px 0;color:#4B5563">Received</td><td>{{ $item->created_at?->format('F j, Y g:i A') }} UTC</td></tr>
    </table>
    <p style="margin:16px 0 0"><a href="{{ route('admin.inbox.requests') }}" style="display:inline-block;background:#B6F500;color:#0B0D12;text-decoration:none;font-weight:bold;padding:11px 22px;border-radius:999px">Open guide requests in the admin</a></p>
  @endif
@endsection
