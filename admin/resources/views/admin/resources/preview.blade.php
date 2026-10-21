<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title>Preview · {{ $resource->titleOf($record) }}</title>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body class="bg-white">
  <div class="sticky top-0 z-10 flex flex-wrap items-center justify-between gap-2 bg-navy px-4 py-2 text-[13px] text-white">
    <span>Preview of the saved version{{ $state ? ' · '.\App\Services\ContentWorkflow::STATES[$state] : '' }}. The website layout differs slightly.</span>
    <a href="{{ route('admin.resources.edit', [$resource->slug, $record->getKey()]) }}" class="font-semibold text-amber hover:underline">Back to editing</a>
  </div>
  @php
    $fields = collect($resource->allFields())->reject(fn ($f) => in_array($f['type'], ['toggle', 'slug', 'password'], true) || in_array($f['name'], [$resource->titleField], true));
    $image = $fields->first(fn ($f) => $f['type'] === 'image' && ($f['accept'] ?? null) !== 'file');
  @endphp
  <article class="mx-auto max-w-3xl px-5 py-10">
    <h1 class="text-3xl font-extrabold leading-tight text-navy">{{ $resource->titleOf($record) }}</h1>
    @if($image && $record->{$image['name']})
      <img src="{{ $record->{$image['name']} }}" alt="" class="mt-6 w-full rounded-lg object-cover">
    @endif
    <div class="mt-6 space-y-5 text-[16px] leading-relaxed text-slate-800">
      @foreach($fields as $f)
        @php $v = $record->{$f['name']}; @endphp
        @continue(blank($v) || $f['type'] === 'image')
        @if($f['type'] === 'sections')
          @foreach((array) $v as $sec)
            @if(! empty($sec['heading']))<h2 class="pt-2 text-xl font-bold text-navy">{{ $sec['heading'] }}</h2>@endif
            @foreach((array) ($sec['paragraphs'] ?? []) as $para)<p>{{ $para }}</p>@endforeach
            @if(! empty($sec['list']))<ul class="list-disc space-y-1 pl-6">@foreach((array) $sec['list'] as $li)<li>{{ $li }}</li>@endforeach</ul>@endif
          @endforeach
        @elseif(is_array($v))
          <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $f['label'] }}</p>
            <ul class="mt-1 list-disc space-y-1 pl-6">
              @foreach($v as $item)<li>{{ is_array($item) ? collect($item)->filter(fn ($x) => is_scalar($x))->implode(' · ') : $item }}</li>@endforeach
            </ul>
          </div>
        @elseif($f['type'] === 'date')
          <p class="text-sm text-slate-500">{{ $f['label'] }}: {{ \Illuminate\Support\Carbon::parse($v)->format('M j, Y') }}</p>
        @else
          <div>
            @if(! in_array($f['type'], ['textarea', 'paragraphs'], true))<p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $f['label'] }}</p>@endif
            <p class="whitespace-pre-line">{{ $v }}</p>
          </div>
        @endif
      @endforeach
    </div>
  </article>
</body>
</html>
