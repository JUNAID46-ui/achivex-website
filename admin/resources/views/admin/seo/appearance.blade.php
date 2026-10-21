@extends('admin.layouts.app')
@section('title', 'Search Appearance')
@section('heading', 'Search Appearance')
@section('subheading', 'How AchiveX pages look in Google and when shared on social media.')
@section('content')
<form method="POST" class="grid max-w-5xl gap-6 lg:grid-cols-2"
      x-data="{ template: @js(old('titleTemplate', $seo['titleTemplate'] ?? '%title% | AchiveX LLC')), home: @js(old('homeTitle', $seo['homeTitle'] ?? '')), desc: @js(old('defaultDescription', $seo['defaultDescription'] ?? '')) }">
  @csrf
  <div class="card">
    <div class="card-header"><h2 class="card-title">Titles and descriptions</h2></div>
    <div class="card-body space-y-4">
      <div><label for="titleTemplate" class="label">Title template</label><input id="titleTemplate" name="titleTemplate" x-model="template" class="input">
        <p class="help">%title% is replaced by the page title. Example: <span x-text="template.replace('%title%', 'Cloud Services')"></span></p>
        <div class="mt-2 flex flex-wrap gap-1.5">@foreach(['%title% | AchiveX LLC', '%title% - AchiveX', '%title% · AchiveX LLC'] as $t)<button type="button" class="badge-gray hover:bg-neutral-200" @click="template = @js($t)">{{ $t }}</button>@endforeach</div></div>
      <div><label for="homeTitle" class="label">Home page title <span class="font-normal text-neutral-400" x-text="'(' + home.length + ')'"></span></label><input id="homeTitle" name="homeTitle" x-model="home" class="input"></div>
      <div><label for="defaultDescription" class="label">Default meta description <span class="font-normal text-neutral-400" x-text="'(' + desc.length + ' of 160)'"></span></label><textarea id="defaultDescription" name="defaultDescription" x-model="desc" rows="3" class="input"></textarea>
        <p class="help">Used when a page has no description of its own.</p></div>
    </div>
  </div>
  <div class="space-y-6">
    <div class="card">
      <div class="card-header"><h2 class="card-title">Social sharing</h2></div>
      <div class="card-body space-y-4">
        @include('admin.partials.field', ['field' => ['name' => 'ogImage', 'label' => 'Default share image (1200 x 630 works best)', 'type' => 'image'], 'value' => $seo['ogImage'] ?? '', 'record' => new \App\Models\Content\SeoMeta])
        <div><label for="twitterHandle" class="label">X (Twitter) username</label><input id="twitterHandle" name="twitterHandle" value="{{ old('twitterHandle', $seo['twitterHandle'] ?? '') }}" class="input" placeholder="@achivexllc"></div>
      </div>
    </div>
    <div class="card card-body">
      <p class="label">Home page in Google</p>
      <div class="border border-neutral-200 p-4">
        <p class="text-xs text-neutral-600">{{ parse_url(config('admin.site_public_url'), PHP_URL_HOST) }}</p>
        <p class="mt-1 text-[18px] text-[#1a0dab]" x-text="home"></p>
        <p class="mt-1 text-[13px] text-neutral-600" x-text="desc"></p>
      </div>
      <button class="btn-primary mt-4">Save changes</button>
    </div>
  </div>
</form>
@endsection
