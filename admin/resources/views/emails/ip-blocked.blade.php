@extends('emails.layout')
@section('preheader', 'IP address '.$block->ip.' was blocked from the admin')
@section('body')
  <p style="margin:0 0 16px"><strong>An IP address was blocked from the AchiveX admin</strong> after {{ $block->attempts }} failed sign ins within {{ \App\Services\Security\LoginGuard::WINDOW_MINUTES }} minutes.</p>
  <table cellpadding="0" cellspacing="0" style="font-size:14px;margin-bottom:16px">
    <tr><td style="padding:2px 12px 2px 0;color:#4B5563">IP address</td><td>{{ $block->ip }}</td></tr>
    <tr><td style="padding:2px 12px 2px 0;color:#4B5563">Emails tried</td><td>{{ implode(', ', $block->emails ?? []) ?: 'None' }}</td></tr>
    <tr><td style="padding:2px 12px 2px 0;color:#4B5563">Browser</td><td>{{ $block->user_agent ?: 'Unknown' }}</td></tr>
    <tr><td style="padding:2px 12px 2px 0;color:#4B5563">First attempt</td><td>{{ $block->first_attempt_at?->format('F j, Y g:i A') }} UTC</td></tr>
    <tr><td style="padding:2px 12px 2px 0;color:#4B5563">Blocked</td><td>{{ $block->blocked_at?->format('F j, Y g:i A') }} UTC</td></tr>
    <tr><td style="padding:2px 12px 2px 0;color:#4B5563">Blocked until</td><td>{{ $block->expires_at ? $block->expires_at->format('F j, Y g:i A').' UTC' : 'Until lifted' }}</td></tr>
  </table>
  <p style="margin:0 0 16px">No password is stored or shown. If this was you, another administrator can lift the block in the Security Center. If it was not you, consider changing the password of the emails listed above and turning on two-step login.</p>
  <p style="margin:16px 0 0"><a href="{{ route('admin.security') }}#blocked" style="display:inline-block;background:#B6F500;color:#0B0D12;text-decoration:none;font-weight:bold;padding:11px 22px;border-radius:999px">Open the Security Center</a></p>
@endsection
