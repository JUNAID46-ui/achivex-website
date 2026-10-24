@extends('admin.layouts.app')
@section('title', 'SEO Audit')
@section('heading', 'SEO Audit')
@section('subheading', 'Technical checks for titles, descriptions, links, redirects, canonicals, sitemap, robots.txt and images. Nothing is changed automatically.')
@section('actions')
  <form method="POST" action="{{ route('admin.seo.audits.run', 'seo') }}">@csrf<button class="btn-primary" data-loading>@include('admin.partials.icon', ['name' => 'refresh']) Run audit now</button></form>
@endsection
@section('content')
@include('admin.seo.partials.audit-findings')
@endsection
