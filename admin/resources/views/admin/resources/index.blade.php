@extends('admin.layouts.app')
@section('title', $resource->label)
@section('heading', $resource->label)
@section('subheading', $resource->description)
@section('actions')
  @if($resource->slug === 'blog-posts' && auth()->user()->canAccess('content'))<a href="{{ route('admin.blog.categories') }}" class="btn-secondary">Categories</a>@endif
  @if($resource->slug !== 'users' && $deleted)<a href="{{ route('admin.resources.deleted', $resource->slug) }}" class="btn-secondary">@include('admin.partials.icon', ['name' => 'history']) Recently deleted ({{ $deleted }})</a>@endif
  <a href="{{ route('admin.resources.create', $resource->slug) }}" class="btn-primary">@include('admin.partials.icon', ['name' => 'plus']) Add {{ $resource->singular }}</a>
@endsection

@section('content')
<div x-data="{ selected: [], all: false }" class="space-y-4">
  <div class="flex flex-wrap items-center justify-between gap-3">
    @if($counts)
      <div class="flex flex-wrap gap-1 text-[13px]">
        @foreach(['all' => 'All'] + \App\Services\ContentWorkflow::STATES as $key => $label)
          @continue($key !== 'all' && ! $counts[$key] && request('status') !== $key)
          @php $active = (request('status') ?: 'all') === $key; @endphp
          <a href="{{ request()->fullUrlWithQuery(['status' => $key === 'all' ? null : $key, 'page' => null]) }}"
             class="px-3 py-1.5 {{ $active ? 'bg-ink font-semibold text-white' : 'text-neutral-600 hover:bg-white' }}">
            {{ $label }} <span class="{{ $active ? 'text-white/70' : 'text-neutral-400' }}">({{ $counts[$key] }})</span>
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
        onsubmit="if (this.action.value === 'delete') return confirm('Delete the selected items?{{ $resource->slug === 'users' ? ' This cannot be undone.' : ' Copies are kept under Recently deleted.' }}'); if (this.action.value === 'publish') return confirm('Publish the selected items on the website now?'); return true;">
    @csrf
    <div class="flex items-center gap-2 border-b border-neutral-200 bg-neutral-50 px-4 py-2.5">
      <label for="bulk-action" class="sr-only">Bulk action</label>
      <select id="bulk-action" name="action" class="input w-44 py-1.5 text-[13px]">
        <option value="">Bulk actions</option>
        @if($resource->hasPublished)
          @if($canPublish)<option value="publish">Publish</option>@endif
          <option value="draft">Move to draft</option><option value="review">Send for review</option><option value="archive">Archive</option>
        @endif
        <option value="delete">Delete</option>
      </select>
      <button class="btn-secondary btn-sm" :disabled="!selected.length">Apply</button>
      <span class="ml-auto text-xs text-neutral-500">{{ $records->total() }} {{ \Illuminate\Support\Str::plural('item', $records->total()) }}</span>
    </div>
    <div class="overflow-x-auto">
      <table class="table">
        <thead>
          <tr>
            <th class="w-10"><input type="checkbox" class="border-neutral-300 text-ink" x-model="all"
                 @change="selected = all ? [...$el.closest('table').querySelectorAll('tbody input[type=checkbox]')].map(i => i.value) : []" aria-label="Select all"></th>
            @foreach($resource->columns() as $col)<th>{{ $col['label'] }}</th>@endforeach
            @if($resource->slug !== 'users')<th>Checks</th>@endif
          </tr>
        </thead>
        <tbody>
          @forelse($records as $record)
            @php $editUrl = route('admin.resources.edit', [$resource->slug, $record->getKey()]); $path = $resource->sitePath($record); @endphp
            <tr class="group">
              <td>@if($resource->slug !== 'users' || ! $resource->deleteBlocker($record))<input type="checkbox" name="ids[]" value="{{ $record->getKey() }}" x-model="selected" class="border-neutral-300 text-ink" aria-label="Select">@endif</td>
              @foreach($resource->columns() as $col)
                @php $v = $record->{$col['name']} ?? null; @endphp
                <td>
                  @switch($col['type'])
                    @case('title')
                      <a href="{{ $editUrl }}" class="font-semibold text-ink hover:underline">{{ $v ?: '(no title)' }}</a>
                      <div class="mt-1 flex gap-2 text-xs text-neutral-500 opacity-0 transition group-hover:opacity-100">
                        <a href="{{ $editUrl }}" class="hover:text-ink">Edit</a>
                        @if($resource->slug !== 'users')<span>|</span><a href="{{ route('admin.resources.preview', [$resource->slug, $record->getKey()]) }}" target="_blank" class="hover:text-ink">Preview</a>@endif
                        @if($path && in_array($states[$record->getKey()] ?? 'published', ['published'], true))<span>|</span><a href="{{ rtrim(config('admin.site_url'), '/').$path }}" target="_blank" class="hover:text-ink">View</a>@endif
                        @unless($resource->deleteBlocker($record))
                          <span>|</span>
                          <button type="submit" form="del-{{ md5($record->getKey()) }}" class="text-red-600 hover:underline">Delete</button>
                        @endunless
                      </div>
                      @break
                    @case('image')
                      @if($v)<img src="{{ $v }}" alt="" class="h-11 w-16  object-cover ring-1 ring-neutral-200" loading="lazy">@else<div class="h-11 w-16  bg-neutral-100"></div>@endif
                      @break
                    @case('status')
                      @php $st = $states[$record->getKey()] ?? ($v ? 'published' : 'draft'); @endphp
                      <span class="badge-{{ ['published' => 'green', 'scheduled' => 'blue', 'review' => 'warn'][$st] ?? 'gray' }}">{{ \App\Services\ContentWorkflow::STATES[$st] }}</span>
                      @if($st === 'scheduled')<span class="block text-[11px] text-neutral-500">{{ $record->publish_at?->format('M j, H:i') }} UTC</span>@endif
                      @break
                    @case('bool')
                      {!! $v ? '<span class="badge-green">Yes</span>' : '<span class="badge-gray">No</span>' !!}
                      @break
                    @case('date')
                      <span class="whitespace-nowrap text-neutral-600">{{ $v ? \Illuminate\Support\Carbon::parse($v)->format('M j, Y') : '' }}</span>
                      @break
                    @case('datetime')
                      <span class="whitespace-nowrap text-neutral-600">{{ $v ? \Illuminate\Support\Carbon::parse($v)->diffForHumans() : 'Never' }}</span>
                      @break
                    @case('seo')
                      @php $s = $scores[$record->getKey()] ?? 0; @endphp
                      <span class="score {{ \App\Services\SeoAnalyzer::color($s) }}" title="SEO score">{{ $s }}</span>
                      @break
                    @default
                      <span class="text-neutral-700">{{ is_array($v) ? implode(', ', $v) : \Illuminate\Support\Str::limit((string) $v, 60) }}</span>
                  @endswitch
                </td>
              @endforeach
              @if($resource->slug !== 'users')
                @php $hw = collect($health[$record->getKey()] ?? []); $warn = $hw->where('level', 'warning'); @endphp
                <td>
                  @if($warn->count())
                    <a href="{{ $editUrl }}" class="badge-warn" title="{{ $warn->pluck('label')->implode("\n") }}">@include('admin.partials.icon', ['name' => 'alert', 'class' => 'h-3 w-3']) {{ $warn->count() }}</a>
                  @else
                    <span class="badge-green" title="{{ $hw->count() ? $hw->pluck('label')->implode("\n") : 'No problems found' }}">@include('admin.partials.icon', ['name' => 'check', 'class' => 'h-3 w-3']) OK</span>
                  @endif
                </td>
              @endif
            </tr>
          @empty
            <tr><td colspan="{{ count($resource->columns()) + 2 }}" class="py-14 text-center text-neutral-500">
              Nothing here yet. <a href="{{ route('admin.resources.create', $resource->slug) }}" class="font-semibold text-ink underline">Add the first {{ strtolower($resource->singular) }}</a>.
            </td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </form>

  {{-- Row delete forms live outside the bulk form --}}
  @foreach($records as $record)
    <form id="del-{{ md5($record->getKey()) }}" method="POST" action="{{ route('admin.resources.destroy', [$resource->slug, $record->getKey()]) }}" class="hidden"
          onsubmit="return confirm('Delete this {{ strtolower($resource->singular) }}?{{ $resource->slug === 'users' ? ' This cannot be undone.' : ' A copy is kept under Recently deleted.' }}')">
      @csrf @method('DELETE')
    </form>
  @endforeach

  <div>{{ $records->links('admin.partials.pagination') }}</div>
</div>
@endsection
