@extends('emails.layout')
@section('body')
  <p style="margin:0 0 12px">Hello {{ $name }},</p>
  <p style="margin:0 0 20px">Someone asked to reset the password for your AchiveX admin account. Click the button below to choose a new one. The link works for 60 minutes.</p>
  <p style="margin:0 0 20px"><a href="{{ $url }}" style="display:inline-block;background:#B6F500;color:#0B0D12;text-decoration:none;font-weight:bold;padding:13px 26px;border-radius:999px">Reset password</a></p>
  <p style="margin:0;font-size:13px;color:#4B5563">If you did not ask for this, you can ignore this email. Your password stays the same.</p>
@endsection
