@extends('admin.layouts.app')
@section('title', 'Local SEO')
@section('heading', 'Local SEO and Schema')
@section('subheading', 'Business details added to every page as structured data, so Google understands who AchiveX is and where it is based.')
@section('content')
<form method="POST" class="grid max-w-5xl gap-6 lg:grid-cols-2">
  @csrf
  <div class="card">
    <div class="card-header"><h2 class="card-title">Business</h2></div>
    <div class="card-body space-y-4">
      <div><label for="type" class="label">Business type</label>
        <select id="type" name="type" class="input">@foreach(['Organization' => 'Organization', 'LocalBusiness' => 'Local business', 'ProfessionalService' => 'Professional service', 'Corporation' => 'Corporation'] as $k => $l)<option value="{{ $k }}" @selected(($schema['type'] ?? '') === $k)>{{ $l }}</option>@endforeach</select></div>
      @foreach(['name' => 'Business name', 'url' => 'Website URL', 'phone' => 'Phone', 'email' => 'Email', 'openingHours' => 'Opening hours (e.g. Mo-Fr 09:00-17:00)', 'priceRange' => 'Price range (e.g. $$)'] as $k => $l)
        <div><label for="{{ $k }}" class="label">{{ $l }}</label><input id="{{ $k }}" name="{{ $k }}" value="{{ old($k, $schema[$k] ?? '') }}" class="input"></div>
      @endforeach
      @include('admin.partials.field', ['field' => ['name' => 'logo', 'label' => 'Logo', 'type' => 'image'], 'value' => $schema['logo'] ?? '', 'record' => new \App\Models\Content\SeoMeta])
    </div>
  </div>
  <div class="space-y-6">
    <div class="card">
      <div class="card-header"><h2 class="card-title">Address</h2></div>
      <div class="card-body space-y-4">
        @foreach(['street' => 'Street address', 'city' => 'City', 'region' => 'State (e.g. WY)', 'postalCode' => 'ZIP code', 'country' => 'Country code (e.g. US)'] as $k => $l)
          <div><label for="{{ $k }}" class="label">{{ $l }}</label><input id="{{ $k }}" name="{{ $k }}" value="{{ old($k, $schema[$k] ?? '') }}" class="input"></div>
        @endforeach
      </div>
    </div>
    <div class="card card-body"><p class="text-[13px] text-slate-500">Keep these details identical to your Google Business Profile. Matching name, address and phone helps local rankings.</p><button class="btn-primary mt-4">Save local SEO</button></div>
  </div>
</form>
@endsection
