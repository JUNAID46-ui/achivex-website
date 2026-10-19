@extends('admin.layouts.app')
@section('title', $resource->label)
@section('heading', $resource->label)
@section('subheading', $resource->description)
@section('actions')
  <a href="{{ route('admin.resources.create', $resource->slug) }}" class="btn-primary">@include('admin.partials.icon', ['name' => 'plus']) Add {{ $resource->singular }}</a>
@endsection

@section('content')
<div x-data="{ selected: [], all: false }" class="space-y-4">
  <div class="flex flex-wrap items-center justify-between gap-3">
    @if($counts)
      <div class="flex gap-1 text-[13px]">
        @foreach(['all' => 'All', 'published' => 'Published', 'draft' => 'Draft'] as $key => $label)
          @php $active = (request('status') ?: 'all') === $key; @endphp
          <a href="{{ request()->fullUrlWithQuery(['status' => $key === 'all' ? null : $key, 'page' => null]) }}"
             class="rounded-md px-3 py-1.5 {{ $active ? 'bg-navy font-semibold text-white' : 'text-slate-600 hover:bg-white' }}">
            {{ $label }} <span class="{{ $active ? 'text-white/70' : 'text-slate-400' }}">({{ $counts[$key] }})</span>
          </a>
        @endforeach
      </div>
    @else
      <div></div>
    @endif
    <form method="GET" class="flex gap-2">
      @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
      <label for="q" class="sr-only">Search</label>
      <input id="q" name="q" value="{{ request('q') }}" placeholder="Search {{ strtolower($resource->label) }}" class="input w-64 py-1.5">
      <button class="btn-secondary">Search</button>
    </form>
  </div>

  <form method="POST" action="{{ route('admin.resources.bulk', $resource->slug) }}" class="card overflow-hidden"
        onsubmit="return this.action.value !== 'delete' || confirm('Delete the selected items? This cannot be undone.')">
    @csrf
    <div class="flex items-center gap-2 border-b border-slate-200 bg-slate-50 px-4 py-2.5">
      <label for="bulk-action" class="sr-only">Bulk action</label>
      <select id="bulk-action" name="action" class="input w-44 py-1.5 text-[13px]">
        <option value="">Bulk actions</option>
        @if($resource->hasPublished)<option value="publish">Publish</option><option value="unpublish">Move to draft</option>@endif
        <option value="delete">Delete</option>
      </select>
      <button class="btn-secondary btn-sm" :disabled="!selected.length">Apply</button>
      <span class="ml-auto text-xs text-slate-500">{{ $records->total() }} {{ \Illuminate\Support\Str::plural('item', $records->total()) }}</span>
    </div>
    <div class="overflow-x-auto">
      <table class="table">
        <thead>
          <tr>
            <th class="w-10"><input type="checkbox" class="rounded border-slate-300 text-navy" x-model="all"
                 @change="selected = all ? [...$el.closest('table').querySelectorAll('tbody input[type=checkbox]')].map(i => i.value) : []" aria-label="Select all"></th>
            @foreach($resource->columns() as $col)<th>{{ $col['label'] }}</th>@endforeach
          </tr>
        </thead>
        <tbody>
          @forelse($records as $record)
            @php $editUrl = route('admin.resources.edit', [$resource->slug, $record->getKey()]); $path = $resource->sitePath($record); @endphp
            <tr class="group">
              <td><input type="checkbox" name="ids[]" value="{{ $record->getKey() }}" x-model="selected" class="rounded border-slate-300 text-navy" aria-label="Select"></td>
              @foreach($resource->columns() as $col)
                @php $v = $record->{$col['name']} ?? null; @endphp
                <td>
                  @switch($col['type'])
                    @case('title')
                      <a href="{{ $editUrl }}" class="font-semibold text-navy hover:text-amber-600">{{ $v ?: '(no title)' }}</a>
                      <div class="mt-1 flex gap-2 text-xs text-slate-500 opacity-0 transition group-hover:opacity-100">
                        <a href="{{ $editUrl }}" class="hover:text-navy">Edit</a>
                        @if($path)<span>|</span><a href="{{ rtrim(config('admin.site_url'), '/').$path }}" target="_blank" class="hover:text-navy">View</a>@endif
                        <span>|</span>
                        <button type="submit" form="del-{{ md5($record->getKey()) }}" class="text-red-600 hover:underline">Delete</button>
                      </div>
                      @break
                    @case('image')
                      @if($v)<img src="{{ $v }}" alt="" class="h-11 w-16 rounded object-cover ring-1 ring-slate-200" loading="lazy">@else<div class="h-11 w-16 rounded bg-slate-100"></div>@endif
                      @break
                    @case('status')
                      {!! $v ? '<span class="badge-green">Published</span>' : '<span class="badge-gray">Draft</span>' !!}
                      @break
                    @case('bool')
                      {!! $v ? '<span class="badge-green">Yes</span>' : '<span class="badge-gray">No</span>' !!}
                      @break
                    @case('date')
                      <span class="whitespace-nowrap text-slate-600">{{ $v ? \Illuminate\Support\Carbon::parse($v)->format('M j, Y') : '' }}</span>
                      @break
                    @case('datetime')
                      <span class="whitespace-nowrap text-slate-600">{{ $v ? \Illuminate\Support\Carbon::parse($v)->diffForHumans() : 'Never' }}</span>
                      @break
                    @case('seo')
                      @php $s = $scores[$record->getKey()] ?? 0; @endphp
                      <span class="score {{ \App\Services\SeoAnalyzer::color($s) }}" title="SEO score">{{ $s }}</span>
                      @break
                    @default
                      <span class="text-slate-700">{{ is_array($v) ? implode(', ', $v) : \Illuminate\Support\Str::limit((string) $v, 60) }}</span>
                  @endswitch
                </td>
              @endforeach
            </tr>
          @empty
            <tr><td colspan="{{ count($resource->columns()) + 1 }}" class="py-14 text-center text-slate-500">
              Nothing here yet. <a href="{{ route('admin.resources.create', $resource->slug) }}" class="font-semibold text-navy underline">Add the first {{ strtolower($resource->singular) }}</a>.
            </td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </form>

  {{-- Row delete forms live outside the bulk form --}}
  @foreach($records as $record)
    <form id="del-{{ md5($record->getKey()) }}" method="POST" action="{{ route('admin.resources.destroy', [$resource->slug, $record->getKey()]) }}" class="hidden"
          onsubmit="return confirm('Delete this {{ strtolower($resource->singular) }}? This cannot be undone.')">
      @csrf @method('DELETE')
    </form>
  @endforeach

  <div>{{ $records->links('admin.partials.pagination') }}</div>
</div>
@endsection
