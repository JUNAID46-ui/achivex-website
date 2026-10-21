@extends('admin.layouts.app')
@section('title', 'AI Visibility')
@section('heading', 'AI Visibility')
@section('subheading', 'How ready the website is for AI search and answer engines, based on what can be checked here.')
@section('actions')
  <form method="POST" action="{{ route('admin.seo.audits.run', 'ai') }}">@csrf<button class="btn-primary" data-loading>@include('admin.partials.icon', ['name' => 'refresh']) Run audit now</button></form>
@endsection
@section('content')
@php $sum = $run?->summary ?? []; @endphp
<div class="mb-6 rounded-lg border border-slate-200 bg-white px-5 py-4 text-[13px] text-slate-600">
  <p><span class="font-semibold text-navy">What this measures.</span> Things the admin can observe: what the website serves to crawlers, robots.txt rules for AI crawlers, llms.txt, organization data, indexing settings and the structure of your content.</p>
  <p class="mt-1"><span class="font-semibold text-navy">What it does not measure.</span> AI citations, AI rankings, crawler visits and competitor data. No data source for these is connected, so none are shown. No tool can guarantee that a website will appear in ChatGPT, Google AI Overviews or other AI products.</p>
</div>

<div class="mb-6 grid gap-6 lg:grid-cols-2">
  <div class="card">
    <div class="card-header"><h2 class="card-title">AI crawler access</h2>
      @if($run)<span class="text-xs text-slate-500">From the {{ $sum['robots_source'] ?? 'robots.txt' }}</span>@endif
    </div>
    @if($run && ! empty($sum['access']))
      <table class="table">
        <tbody>
          @foreach($sum['access'] as $bot => $a)
            <tr>
              <td class="font-semibold text-navy">{{ $bot }}<span class="block text-xs font-normal text-slate-500">{{ $a['purpose'] }}</span></td>
              <td class="text-right">
                @if($a['allowed'])<span class="badge-green">Allowed</span>@else<span class="badge-red">Blocked</span>@endif
                @if($a['rule'])<span class="block font-mono text-[11px] text-slate-400">{{ $a['rule'] }}</span>@endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @else
      <p class="card-body text-[13px] text-slate-500">Run the audit to check robots.txt for {{ count($crawlers) }} documented AI and search crawlers.</p>
    @endif
  </div>

  <div class="card" id="llms" x-data="{ text: @js(old('content', $llms['content'] ?? '')), loading: false,
        async draft() { this.loading = true; try { const r = await fetch('{{ route('admin.seo.llms.draft') }}', { headers: { Accept: 'application/json' } }); const j = await r.json(); if (! this.text.trim() || confirm('Replace the text in the box with a generated draft?')) this.text = j.content } finally { this.loading = false } } }">
    <div class="card-header"><h2 class="card-title">llms.txt</h2>@if(filled($llms['content'] ?? null))<span class="badge-green">Saved</span>@endif</div>
    <form method="POST" action="{{ route('admin.seo.llms') }}" class="card-body space-y-3" data-unsaved-warning>
      @csrf
      <p class="text-[13px] text-slate-600">A plain text guide to the site for AI tools, in Markdown. It is a proposed convention; support by AI services varies. The website includes it on its next publish.</p>
      <textarea name="content" x-model="text" rows="12" class="input font-mono text-[12px]" spellcheck="false" placeholder="# AchiveX LLC&#10;&#10;> One line about the company&#10;&#10;## Main pages&#10;- [Services](https://www.achivex.com/services/): what we do"></textarea>
      <div class="flex flex-wrap gap-2">
        <button type="button" class="btn-secondary" @click="draft()" :disabled="loading">@include('admin.partials.icon', ['name' => 'sparkle']) <span x-text="loading ? 'Building…' : 'Build a draft from site content'"></span></button>
        <button class="btn-primary" data-loading>Save llms.txt</button>
      </div>
    </form>
  </div>
</div>

<h2 class="mb-3 text-[15px] font-semibold text-navy">Findings</h2>
@include('admin.seo.partials.audit-findings')
@endsection
