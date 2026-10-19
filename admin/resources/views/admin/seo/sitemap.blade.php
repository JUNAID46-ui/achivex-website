@extends('admin.layouts.app')
@section('title', 'Sitemap')
@section('heading', 'XML Sitemap')
@section('subheading', count($urls).' URLs included. Pages marked noindex are left out automatically.')
@section('actions')
  <a href="{{ $publicUrl }}" target="_blank" class="btn-secondary">@include('admin.partials.icon', ['name' => 'external']) Open sitemap.xml</a>
  <a href="{{ route('admin.seo.sitemap.download') }}" class="btn-primary">@include('admin.partials.icon', ['name' => 'download']) Download</a>
@endsection
@section('content')
<div class="card card-body mb-6 text-[13.5px] leading-relaxed text-slate-600">
  <p class="font-semibold text-navy">How to use it</p>
  <ol class="mt-2 list-decimal space-y-1 pl-5">
    <li>Download sitemap.xml and place it in the website's <code>public</code> folder, or point <code>{{ rtrim(config('admin.site_public_url'), '/') }}/sitemap.xml</code> to <code>{{ $publicUrl }}</code> on your host.</li>
    <li>Submit the sitemap address in Google Search Console and Bing Webmaster Tools.</li>
    <li>The sitemap updates itself when you publish or hide content here.</li>
  </ol>
</div>
<div class="card overflow-x-auto">
  <table class="table">
    <thead><tr><th>URL</th><th>Type</th><th>Last modified</th><th>Priority</th></tr></thead>
    <tbody>
      @foreach($urls as $u)
        <tr><td><a href="{{ $u['loc'] }}" target="_blank" class="text-navy hover:underline">{{ $u['loc'] }}</a></td><td class="text-slate-500">{{ $u['type'] }}</td><td class="text-slate-500">{{ $u['lastmod'] }}</td><td>{{ $u['priority'] }}</td></tr>
      @endforeach
    </tbody>
  </table>
</div>
@endsection
