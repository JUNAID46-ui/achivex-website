@extends('admin.layouts.app')
@section('title', 'Custom Code')
@section('heading', 'Custom Code')
@section('subheading', 'Add HTML, CSS and JavaScript to the website header and footer, on every page or on the home page only.')
@section('content')
<form method="POST" class="max-w-5xl space-y-6">
  @csrf
  <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-[13px] text-amber-900">
    Code saved here runs in every visitor's browser. Paste code only from services you trust, such as Google or Meta. PHP cannot run here because the website is a static site; use HTML, CSS or JavaScript.
  </div>
  <div class="grid gap-6 lg:grid-cols-2">
    @foreach($fields as $key => [$label, $help])
      <div class="card">
        <div class="card-header"><h2 class="card-title">{{ $label }}</h2>@if(!empty($code[$key]))<span class="badge-green">Active</span>@endif</div>
        <div class="card-body space-y-2">
          <textarea id="{{ $key }}" name="{{ $key }}" rows="8" spellcheck="false" class="input font-mono text-[12px]" placeholder="{{ $key === 'css' ? '.my-class { color: #0F2C9C; }' : ($key === 'js' ? 'console.log(\'Hello\')' : '<!-- paste code here -->') }}">{{ old($key, $code[$key] ?? '') }}</textarea>
          <p class="text-[12px] text-slate-500">{{ $help }}</p>
          @error($key)<p class="text-[12px] text-red-600">{{ $message }}</p>@enderror
        </div>
      </div>
    @endforeach
  </div>
  <button class="btn-primary">Save custom code</button>
</form>
@endsection
