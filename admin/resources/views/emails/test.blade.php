@extends('emails.layout')
@section('preheader', 'Your AchiveX email settings work.')
@section('body')
  <p style="margin:0 0 12px;font-size:18px;font-weight:bold;color:#0B0D12">Your email settings work.</p>
  <p style="margin:0">This message was sent from the AchiveX admin panel. Replies to contact messages, new message alerts and password reset links are delivered the same way, with the signature below.</p>
@endsection
@section('signature')
  @include('emails.partials.signature', ['sender' => auth()->user()])
@endsection
