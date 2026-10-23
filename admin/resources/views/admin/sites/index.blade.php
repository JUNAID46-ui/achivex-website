@extends('admin.layouts.app')
@section('title', 'Sites')
@section('heading', 'Sites')
@section('subheading', 'Manage more than one website from this admin. Each website keeps its own database and hosting.')
@section('actions')
  <a href="{{ route('admin.sites.create') }}" class="btn-primary">@include('admin.partials.icon', ['name' => 'plus']) Add website</a>
@endsection

@section('content')
<div class="space-y-6">
  <div class="card card-body flex flex-wrap items-center justify-between gap-4">
    <div>
      <p class="text-[15px] font-semibold text-ink">Main website</p>
      <p class="mt-1 text-[13px] text-neutral-500">{{ config('admin.site_public_url') }} · the database set in this admin's environment. Users and sign in always live here.</p>
    </div>
    @if($currentSite ?? null)
      <form method="POST" action="{{ route('admin.sites.main') }}">@csrf<button class="btn-secondary">Switch to main website</button></form>
    @else
      <span class="bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-700">You are managing this website</span>
    @endif
  </div>

  @forelse($sites as $site)
    @php($badge = ['ready' => ['Ready', 'bg-green-50 text-green-700'], 'needs_setup' => ['Needs setup', 'bg-amber-50 text-amber-700'], 'error' => ['Cannot connect', 'bg-red-50 text-red-700'], 'new' => ['Not checked', 'bg-neutral-100 text-neutral-600']][$site->status] ?? ['Unknown', 'bg-neutral-100 text-neutral-600'])
    <div class="card">
      <div class="card-body flex flex-wrap items-start justify-between gap-4">
        <div class="min-w-0">
          <p class="flex flex-wrap items-center gap-2 text-[15px] font-semibold text-ink">
            {{ $site->name }}
            <span class="px-2 py-0.5 text-[11px] font-semibold {{ $badge[1] }}">{{ $badge[0] }}</span>
            @if(($currentSite ?? null)?->id === $site->id)<span class="bg-blue-50 px-2 py-0.5 text-[11px] font-semibold text-blue-700">Managing now</span>@endif
          </p>
          <p class="mt-1 text-[13px] text-neutral-500"><a href="{{ $site->public_url ?: $site->site_url }}" target="_blank" rel="noopener" class="hover:text-ink">{{ $site->public_url ?: $site->site_url }}</a> · database {{ $site->db_host }}</p>
          @if($site->status_message)<p class="mt-2 text-[13px] text-neutral-600">{{ $site->status_message }} @if($site->checked_at)<span class="text-neutral-400">({{ $site->checked_at->diffForHumans() }})</span>@endif</p>@endif
        </div>
        <div class="flex flex-wrap gap-2">
          @if($site->status === 'ready' && ($currentSite ?? null)?->id !== $site->id)
            <form method="POST" action="{{ route('admin.sites.switch', $site) }}">@csrf<button class="btn-primary btn-sm">Manage this website</button></form>
          @endif
          @if(in_array($site->status, ['needs_setup']))
            <form method="POST" action="{{ route('admin.sites.prepare', $site) }}" onsubmit="return confirm('Add the admin and website tables to this database? Existing tables are not changed.')">@csrf<button class="btn-primary btn-sm">Prepare database</button></form>
          @endif
          <form method="POST" action="{{ route('admin.sites.check', $site) }}">@csrf<button class="btn-secondary btn-sm">Test connection</button></form>
          <a href="{{ route('admin.sites.edit', $site) }}" class="btn-secondary btn-sm">Edit</a>
          <form method="POST" action="{{ route('admin.sites.destroy', $site) }}" onsubmit="return confirm('Remove {{ addslashes($site->name) }} from this admin? Its database and website are not deleted.')">@csrf @method('DELETE')<button class="btn-ghost btn-sm text-red-600">Remove</button></form>
        </div>
      </div>
    </div>
  @empty
    <div class="card card-body text-[13.5px] text-neutral-600">
      <p class="font-semibold text-ink">No other websites yet.</p>
      <p class="mt-1">Add a website built on the same stack (React website, Supabase database). You need its database host, user and password, which you find in Supabase under Project Settings, Database.</p>
    </div>
  @endforelse

  <div class="card card-body text-[13px] leading-relaxed text-neutral-600">
    <p class="font-semibold text-ink">How it works</p>
    <ul class="mt-2 list-disc space-y-1 pl-5">
      <li>Every website has its own database and its own hosting. This admin only stores how to reach the database, with the password encrypted.</li>
      <li>Use the switcher at the top left to choose which website you are managing. Pages, content, media, SEO, analytics, settings and logs then all belong to that website.</li>
      <li>Users and sign in stay on the main website, so the same accounts can manage every website.</li>
      <li>A new, empty database can be set up with Prepare database. It adds the admin and website tables and leaves anything already there alone.</li>
      <li>Each website must be built from the same codebase (or follow the same database structure) for its pages and content to work here.</li>
    </ul>
  </div>
</div>
@endsection
