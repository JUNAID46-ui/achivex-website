@extends('admin.layouts.app')
@section('title', 'Robots.txt')
@section('heading', 'Robots.txt Editor')
@section('subheading', 'Tells search engines which parts of the site they may crawl.')
@section('actions')
  <a href="{{ route('robots') }}" target="_blank" class="btn-secondary">@include('admin.partials.icon', ['name' => 'external']) View robots.txt</a>
@endsection
@section('content')
<form method="POST" class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_340px]" x-data="{ content: @js(old('content', $content)) }">
  @csrf
  <div class="card card-body">
    <label for="content" class="label">robots.txt</label>
    <textarea id="content" name="content" x-model="content" rows="16" class="input font-mono text-[13px]"></textarea>
    <button class="btn-primary mt-4">Save robots.txt</button>
  </div>
  <div class="card card-body space-y-3 self-start text-[13px] text-slate-600">
    <p class="font-semibold text-ink">Quick inserts</p>
    <button type="button" class="btn-secondary btn-sm w-full justify-start" @click="content = 'User-agent: *\nAllow: /\n\nSitemap: {{ rtrim(config('admin.site_public_url'), '/') }}/sitemap.xml'">Allow everything (recommended)</button>
    <button type="button" class="btn-secondary btn-sm w-full justify-start" @click="content += '\nUser-agent: GPTBot\nDisallow: /'">Block OpenAI crawler</button>
    <button type="button" class="btn-secondary btn-sm w-full justify-start" @click="content += '\nDisallow: /admin/'">Block /admin/</button>
    <p class="border-t border-slate-100 pt-3">Copy the saved file into the website's <code>public</code> folder, or serve it from this admin at <code>{{ route('robots') }}</code>.</p>
  </div>
</form>
@endsection
