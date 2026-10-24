@extends('admin.layouts.app')
@section('title', 'Message from '.$message->name)
@section('heading', 'Message from '.$message->name)
@section('subheading', 'Received '.$message->created_at?->format('F j, Y \a\t g:i A'))
@section('actions')
  <a href="{{ route('admin.inbox.messages') }}" class="btn-secondary">&larr; Inbox</a>
  <a href="#reply" class="btn-primary">@include('admin.partials.icon', ['name' => 'mail']) Reply</a>
@endsection
@section('content')
<div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_300px]">
  <div class="min-w-0 space-y-6">
    <div class="card card-body">
      <p class="whitespace-pre-line text-[15px] leading-relaxed text-neutral-800">{{ $message->message }}</p>
    </div>
    <form id="reply" method="POST" action="{{ route('admin.inbox.messages.reply', $message) }}" class="card">
      @csrf
      <div class="card-header"><h2 class="card-title">Reply to {{ $message->name }}</h2><span class="text-xs text-neutral-400">Sent from {{ config('mail.from.address') }}</span></div>
      <div class="card-body space-y-4">
        <div>
          <label for="subject" class="label">Subject</label>
          <input id="subject" name="subject" value="{{ old('subject', 'Re: your message to AchiveX LLC') }}" required class="input">
        </div>
        <div>
          <label for="body" class="label">Message</label>
          <textarea id="body" name="body" rows="7" required class="input">{{ old('body', "Hi ".$message->name.",\n\nThank you for contacting AchiveX LLC.\n\n") }}</textarea>
          <p class="help">Their original message is added below your reply automatically.</p>
        </div>
        <button class="btn-primary">@include('admin.partials.icon', ['name' => 'mail']) Send reply</button>
      </div>
    </form>
  </div>
  <aside class="space-y-6">
    <div class="card card-body space-y-3 text-[13px]">
      <div><p class="text-neutral-500">Name</p><p class="font-semibold">{{ $message->name }}</p></div>
      <div><p class="text-neutral-500">Email</p><p class="flex items-center gap-2 font-semibold">{{ $message->email }} <button type="button" class="text-xs text-ink underline" onclick="copyText('{{ $message->email }}', this)">Copy</button></p></div>
      @if($message->phone)<div><p class="text-neutral-500">Phone</p><p class="font-semibold">{{ $message->phone }}</p></div>@endif
      @if($message->service)<div><p class="text-neutral-500">Service</p><p class="font-semibold">{{ $message->service }}</p></div>@endif
      <div class="flex flex-wrap gap-2 border-t border-neutral-100 pt-3">
        <form method="POST" action="{{ route('admin.inbox.messages.star', $message) }}">@csrf<button class="btn-secondary btn-sm">{{ $message->starred ? 'Unstar' : 'Star' }}</button></form>
        <form method="POST" action="{{ route('admin.inbox.messages.unread', $message) }}">@csrf<button class="btn-secondary btn-sm">Mark unread</button></form>
        <form method="POST" action="{{ route('admin.inbox.messages.destroy', $message) }}" onsubmit="return confirm('Delete this message?')">@csrf @method('DELETE')<button class="btn-danger btn-sm">Delete</button></form>
      </div>
    </div>
    @if($previous->count())
      <div class="card">
        <div class="card-header"><h2 class="card-title">Earlier messages from this person</h2></div>
        <ul class="divide-y divide-neutral-100">
          @foreach($previous as $p)
            <li><a href="{{ route('admin.inbox.messages.show', $p) }}" class="block px-5 py-2.5 text-[13px] hover:bg-neutral-50"><span class="text-xs text-neutral-400">{{ $p->created_at?->format('M j, Y') }}</span><span class="block truncate">{{ $p->message }}</span></a></li>
          @endforeach
        </ul>
      </div>
    @endif
  </aside>
</div>
@endsection
