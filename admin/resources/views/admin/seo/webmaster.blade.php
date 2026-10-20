@extends('admin.layouts.app')
@section('title', 'Webmaster Tools')
@section('heading', 'Webmaster Tools')
@section('subheading', 'Verify the website with Google Search Console and Bing Webmaster Tools.')
@section('content')
<form method="POST" class="grid max-w-5xl gap-6 lg:grid-cols-2">
  @csrf
  <div class="card">
    <div class="card-header"><h2 class="card-title">Google Search Console</h2>@if(!empty($seo['googleVerification']))<span class="badge-green">Code added</span>@endif</div>
    <div class="card-body space-y-3">
      <label for="googleVerification" class="label">Verification code</label>
      <input id="googleVerification" name="googleVerification" value="{{ old('googleVerification', $seo['googleVerification'] ?? '') }}" class="input" placeholder="Paste the code or the full meta tag">
      <ol class="list-decimal space-y-1 pl-5 text-[13px] text-slate-600">
        <li>Open <a href="https://search.google.com/search-console" target="_blank" class="text-navy underline">Google Search Console</a> and add your website.</li>
        <li>Choose the "HTML tag" method and copy the tag.</li>
        <li>Paste it here and save.</li>
        <li>The code goes into the website's HTML when the website is next published, so publish (redeploy) the website before you click Verify.</li>
        <li>Use the same address in Search Console as the live website, for example {{ rtrim(config('admin.site_url'), '/') }}.</li>
      </ol>
    </div>
  </div>
  <div class="card">
    <div class="card-header"><h2 class="card-title">Bing Webmaster Tools</h2>@if(!empty($seo['bingVerification']))<span class="badge-green">Code added</span>@endif</div>
    <div class="card-body space-y-3">
      <label for="bingVerification" class="label">Verification code</label>
      <input id="bingVerification" name="bingVerification" value="{{ old('bingVerification', $seo['bingVerification'] ?? '') }}" class="input" placeholder="Paste the code or the full meta tag">
      <p class="text-[13px] text-slate-600">In <a href="https://www.bing.com/webmasters" target="_blank" class="text-navy underline">Bing Webmaster Tools</a>, choose the meta tag option and paste it here.</p>
    </div>
  </div>
  <div class="lg:col-span-2"><button class="btn-primary">Save verification codes</button></div>
</form>
@endsection
