@extends('admin.layouts.app')
@section('title', '404 Monitor')
@section('heading', '404 Monitor')
@section('subheading', 'Addresses visitors tried that do not exist. Redirect them to a useful page.')
@section('actions')
  <form method="POST" action="{{ route('admin.seo.notfound.clear') }}" onsubmit="return confirm('Clear the whole 404 log?')">@csrf<button class="btn-danger">Clear log</button></form>
@endsection
@section('content')
<div class="card overflow-x-auto">
  <table class="table">
    <thead><tr><th>Missing URL</th><th>Hits</th><th>Last seen</th><th>Came from</th><th>Redirect to</th></tr></thead>
    <tbody>
      @forelse($rows as $r)
        @php $key = '/'.trim($r->path, '/').'/'; @endphp
        <tr>
          <td class="font-semibold text-navy">{{ $r->path }}</td>
          <td><span class="badge-red">{{ $r->hits }}</span></td>
          <td class="whitespace-nowrap text-slate-500">{{ \Illuminate\Support\Carbon::parse($r->last_seen)->diffForHumans() }}</td>
          <td class="max-w-[200px] truncate text-xs text-slate-500">{{ $r->referrer ?: 'Direct' }}</td>
          <td>
            @if(isset($redirected[$key]))
              <span class="badge-green">Redirects to {{ $redirected[$key] }}</span>
            @else
              <form method="POST" action="{{ route('admin.seo.notfound.redirect') }}" class="flex gap-2">@csrf
                <input type="hidden" name="from_path" value="{{ $r->path }}">
                <input name="to_path" class="input w-48 py-1 text-[13px]" placeholder="/services/" required aria-label="Redirect target">
                <button class="btn-primary btn-sm">Redirect</button>
              </form>
            @endif
          </td>
        </tr>
      @empty
        <tr><td colspan="5" class="py-14 text-center text-slate-500">No missing pages recorded. Good news.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
<div class="mt-4">{{ $rows->links('admin.partials.pagination') }}</div>
@endsection
