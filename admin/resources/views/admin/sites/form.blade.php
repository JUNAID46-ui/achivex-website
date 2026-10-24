@extends('admin.layouts.app')
@section('title', $site->exists ? 'Edit '.$site->name : 'Add website')
@section('heading', $site->exists ? 'Edit '.$site->name : 'Add website')
@section('subheading', 'Connect a website built on the same stack. The connection is tested when you save.')
@section('actions')
  <a href="{{ route('admin.sites.index') }}" class="btn-secondary">&larr; All sites</a>
@endsection

@section('content')
<form method="POST" action="{{ $site->exists ? route('admin.sites.update', $site) : route('admin.sites.store') }}" class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
  @csrf
  @if($site->exists) @method('PUT') @endif
  <div class="space-y-6">
    <div class="card card-body space-y-4">
      <h2 class="card-title">Website</h2>
      <div><label for="name" class="label">Name</label><input id="name" name="name" value="{{ old('name', $site->name) }}" required class="input" placeholder="e.g. Client Co"></div>
      <div><label for="site_url" class="label">Website address used by the admin</label><input id="site_url" name="site_url" value="{{ old('site_url', $site->site_url) }}" required class="input" placeholder="https://client-site.vercel.app"><p class="help">Used for previews, the page list and "Visit site".</p></div>
      <div><label for="public_url" class="label">Public domain (optional)</label><input id="public_url" name="public_url" value="{{ old('public_url', $site->public_url) }}" class="input" placeholder="https://www.client.com"><p class="help">Used in the sitemap and Google previews. Leave empty to use the address above.</p></div>
    </div>
    <div class="card card-body space-y-4">
      <h2 class="card-title">Database</h2>
      <p class="help mt-0">In Supabase open Project Settings, Database, then copy the values from "Connection parameters" (the Session pooler works well).</p>
      <div class="grid gap-4 sm:grid-cols-[1fr_120px]">
        <div><label for="db_host" class="label">Host</label><input id="db_host" name="db_host" value="{{ old('db_host', $site->db_host) }}" required class="input" placeholder="aws-0-us-west-1.pooler.supabase.com"></div>
        <div><label for="db_port" class="label">Port</label><input id="db_port" name="db_port" type="number" value="{{ old('db_port', $site->db_port) }}" required class="input"></div>
      </div>
      <div class="grid gap-4 sm:grid-cols-2">
        <div><label for="db_database" class="label">Database name</label><input id="db_database" name="db_database" value="{{ old('db_database', $site->db_database) }}" required class="input"></div>
        <div><label for="db_username" class="label">User</label><input id="db_username" name="db_username" value="{{ old('db_username', $site->db_username) }}" required class="input" placeholder="postgres.abcdefghij"></div>
      </div>
      <div class="grid gap-4 sm:grid-cols-2">
        <div><label for="db_password" class="label">Password</label><input id="db_password" name="db_password" type="password" autocomplete="new-password" @required(! $site->exists) class="input" placeholder="{{ $site->exists ? 'Leave empty to keep the saved password' : '' }}"></div>
        <div><label for="db_sslmode" class="label">SSL</label>
          <select id="db_sslmode" name="db_sslmode" class="input">
            @foreach(['require' => 'Require (Supabase)', 'verify-full' => 'Verify full', 'prefer' => 'Prefer', 'disable' => 'Disable (local only)'] as $v => $l)
              <option value="{{ $v }}" @selected(old('db_sslmode', $site->db_sslmode) === $v)>{{ $l }}</option>
            @endforeach
          </select>
        </div>
      </div>
    </div>
    <div class="card card-body"><label for="notes" class="label">Notes (optional)</label><textarea id="notes" name="notes" rows="3" class="input" placeholder="Hosting, contacts, anything useful">{{ old('notes', $site->notes) }}</textarea></div>
  </div>
  <aside>
    <div class="card">
      <div class="card-header"><h2 class="card-title">Save</h2></div>
      <div class="card-body text-[13px] text-neutral-600">The password is stored encrypted. After saving, the admin connects to the database and tells you whether it is ready or needs to be prepared.</div>
      <div class="flex justify-end px-5 pb-5"><button class="btn-primary">{{ $site->exists ? 'Save changes' : 'Add website' }}</button></div>
    </div>
  </aside>
</form>
@endsection
