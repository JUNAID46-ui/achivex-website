@extends('emails.layout')
@section('body')
  <div style="white-space:pre-line">{{ $body }}</div>
  <div style="margin-top:24px;padding:12px 16px;border-left:3px solid #F0A500;background:#EEF1F3;font-size:13px;color:#334756">
    <strong>Your message on {{ $original->created_at?->format('F j, Y') }}:</strong>
    <div style="white-space:pre-line;margin-top:6px">{{ $original->message }}</div>
  </div>
@endsection
