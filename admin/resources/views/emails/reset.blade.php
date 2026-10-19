@extends('emails.layout')
@section('body')
  <p style="margin:0 0 12px">Hello {{ $name }},</p>
  <p style="margin:0 0 20px">Someone asked to reset the password for your AchiveX admin account. Click the button below to choose a new one. The link works for 60 minutes.</p>
  <p style="margin:0 0 20px"><a href="{{ $url }}" style="display:inline-block;background:#F0A500;color:#000000;text-decoration:none;font-weight:bold;padding:12px 22px;border-radius:6px">Reset password</a></p>
  <p style="margin:0;font-size:13px;color:#334756">If you did not ask for this, you can ignore this email. Your password stays the same.</p>
@endsection
