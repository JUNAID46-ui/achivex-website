@extends('admin.layouts.app')
@section('title', 'Settings')
@section('heading', 'General Settings')
@section('subheading', 'Business details used in the header, footer, contact page and structured data.')
@section('content')
<form method="POST" class="grid gap-6 xl:grid-cols-2">
  @csrf
  <div class="card">
    <div class="card-header"><h2 class="card-title">Business details</h2></div>
    <div class="card-body space-y-4">
      @foreach(['siteName' => 'Site name', 'tagline' => 'Tagline', 'phone' => 'Phone number', 'email' => 'Contact email', 'location' => 'Location', 'googleProfile' => 'Google Business Profile link'] as $k => $l)
        <div>
          <label for="g-{{ $k }}" class="label">{{ $l }}</label>
          <input id="g-{{ $k }}" name="general[{{ $k }}]" value="{{ old('general.'.$k, $general[$k] ?? '') }}" class="input">
        </div>
      @endforeach
    </div>
  </div>
  <div class="space-y-6">
    <div class="card">
      <div class="card-header"><h2 class="card-title">Social profiles</h2></div>
      <div class="card-body space-y-4">
        @foreach(['facebook' => 'Facebook', 'x' => 'X (Twitter)', 'linkedin' => 'LinkedIn', 'instagram' => 'Instagram'] as $k => $l)
          <div>
            <label for="s-{{ $k }}" class="label">{{ $l }}</label>
            <input id="s-{{ $k }}" name="social[{{ $k }}]" value="{{ old('social.'.$k, $social[$k] ?? '') }}" class="input" placeholder="https://">
          </div>
        @endforeach
      </div>
    </div>
    <div class="card card-body flex items-center justify-between">
      <p class="text-[13px] text-slate-500">SEO defaults live under <a href="{{ route('admin.seo.appearance') }}" class="font-semibold text-ink underline">SEO, Search Appearance</a>.</p>
      <button class="btn-primary">Save settings</button>
    </div>
  </div>
</form>

<div class="mt-6 grid gap-6 xl:grid-cols-2">
  {{-- Image storage --}}
  <form id="storage" method="POST" action="{{ route('admin.settings.storage') }}" class="card scroll-mt-20">
    @csrf
    <div class="card-header"><h2 class="card-title">Image storage</h2>
      <span class="badge-{{ $storageReady ? 'green' : 'warn' }}">{{ $storageReady ? 'Connected' : 'Not connected' }}</span>
    </div>
    <div class="card-body space-y-4 text-[13px]">
      <p class="text-slate-600">Images you upload are stored in Supabase so the live website can show them. This needs your Supabase <strong>secret key</strong>, entered once.</p>
      <ol class="list-decimal space-y-1 pl-5 text-slate-600">
        <li>Open your project on supabase.com.</li>
        <li>Click <strong>Project Settings</strong>, then <strong>API Keys</strong>.</li>
        <li>Under <strong>Secret keys</strong>, click the eye or copy button next to the key (it starts with <code>sb_secret_</code>).</li>
        <li>Paste it below and click Connect.</li>
      </ol>
      @if($storageKeyHint)<p class="text-slate-500">Saved key: <code>{{ $storageKeyHint }}</code></p>@endif
      <div>
        <label for="secretKey" class="label">Supabase secret key</label>
        <input id="secretKey" name="secretKey" type="password" autocomplete="off" class="input" placeholder="sb_secret_...">
        @error('secretKey')<p class="error">{{ $message }}</p>@enderror
        <p class="help">Keep this key private. It is stored in the private admin area of the database and is never sent to the website.</p>
      </div>
    </div>
    <div class="flex justify-end pt-2"><button class="btn-primary">{{ $storageReady ? 'Update key' : 'Connect' }}</button></div>
  </form>

  {{-- Email --}}
  <div id="email" class="card scroll-mt-20">
    <div class="card-header"><h2 class="card-title">Email (SMTP)</h2>
      <span class="badge-{{ config('mail.default') === 'smtp' && config('mail.mailers.smtp.password') ? 'green' : 'warn' }}">{{ config('mail.default') === 'smtp' && config('mail.mailers.smtp.password') ? 'Connected' : 'Not set up' }}</span>
    </div>
    <form method="POST" action="{{ route('admin.settings.mail') }}">
      @csrf
      <div class="card-body grid gap-4 text-[13px] sm:grid-cols-2">
        <div><label for="m-host" class="label">SMTP server</label><input id="m-host" name="host" value="{{ old('host', $mail['host'] ?? 'smtp.gmail.com') }}" class="input"></div>
        <div><label for="m-port" class="label">Port</label><input id="m-port" name="port" value="{{ old('port', $mail['port'] ?? 587) }}" class="input"></div>
        <div><label for="m-user" class="label">Email account</label><input id="m-user" name="username" value="{{ old('username', $mail['username'] ?? '') }}" class="input" placeholder="you@gmail.com"></div>
        <div><label for="m-pass" class="label">App password</label><input id="m-pass" name="password" type="password" autocomplete="off" class="input" placeholder="{{ config('mail.mailers.smtp.password') ? 'Saved. Leave empty to keep it.' : '16 letter Gmail App Password' }}"></div>
        <div><label for="m-from" class="label">Send from address</label><input id="m-from" name="from_address" value="{{ old('from_address', $mail['from_address'] ?? '') }}" class="input" placeholder="Same as the email account"></div>
        <div><label for="m-name" class="label">Send from name</label><input id="m-name" name="from_name" value="{{ old('from_name', $mail['from_name'] ?? 'AchiveX LLC') }}" class="input"></div>
        <div><label for="m-sig-name" class="label">Signature name</label><input id="m-sig-name" name="signature_name" value="{{ old('signature_name', $mail['signature_name'] ?? '') }}" class="input" placeholder="AchiveX LLC"><p class="help">Closing name under every reply. Leave empty to sign as the company.</p></div>
        <div><label for="m-sig-title" class="label">Signature title</label><input id="m-sig-title" name="signature_title" value="{{ old('signature_title', $mail['signature_title'] ?? '') }}" class="input" placeholder="Customer Support"></div>
        <div class="sm:col-span-2"><label for="m-notify" class="label">Send new website messages to</label><input id="m-notify" name="notify_to" value="{{ old('notify_to', $mail['notify_to'] ?? '') }}" class="input" placeholder="you@gmail.com"><p class="help">Every new contact message and guide request is emailed here. Separate several addresses with commas. Empty sends to all administrators.</p></div>
        <p class="help sm:col-span-2">For Gmail use smtp.gmail.com, port 587, and a Gmail App Password (not your normal password).</p>
        @if($notifyError)
          <div class="border-l-4 border-red-600 bg-red-50 px-3 py-2 text-red-700 sm:col-span-2"><strong>The last new-message email failed</strong> ({{ \Illuminate\Support\Carbon::parse($notifyError['at'])->diffForHumans() }}): {{ $notifyError['message'] }}</div>
        @endif
      </div>
      <div class="flex flex-wrap items-center justify-between gap-2 pt-2">
        <button type="submit" form="test-email" class="btn-secondary">@include('admin.partials.icon', ['name' => 'mail']) Send test email</button>
        <button class="btn-primary">Save email settings</button>
      </div>
    </form>
    <form id="test-email" method="POST" action="{{ route('admin.settings.test-email') }}" class="hidden">@csrf</form>
  </div>
</div>
@endsection
