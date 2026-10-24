@extends('emails.layout')
@section('preheader', \Illuminate\Support\Str::limit(preg_replace('/\s+/', ' ', $body), 110))
@section('body')
  <div style="white-space:pre-line">{{ $body }}</div>
  <div style="margin-top:26px;padding:14px 18px;border-left:3px solid #0F2C9C;background:#F3F5F8;border-radius:0 8px 8px 0;font-size:13px;line-height:1.6;color:#4B5563">
    <strong style="color:#0B0D12">Your message on {{ $original->created_at?->format('F j, Y') }}:</strong>
    <div style="white-space:pre-line;margin-top:6px">{{ $original->message }}</div>
  </div>
@endsection
@section('signature')
  @include('emails.partials.signature', ['sender' => $sender ?? auth()->user()])
@endsection
