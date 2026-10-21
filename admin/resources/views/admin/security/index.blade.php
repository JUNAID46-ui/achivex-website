@extends('admin.layouts.app')
@section('title', 'Security Center')
@section('heading', 'Security Center')
@section('subheading', 'Sign ins, accounts, sensitive actions and security checks for this admin. Passwords, keys, tokens and session ids are never shown.')
@section('actions')
  <form method="POST" action="{{ route('admin.security.run') }}">@csrf<button class="btn-primary" data-loading>@include('admin.partials.icon', ['name' => 'shield']) Run security check</button></form>
@endsection
@section('content')
@php $sev = ['critical' => 'red', 'high' => 'red', 'medium' => 'amber', 'low' => 'gray', 'info' => 'gray']; $sum = $run?->summary ?? []; @endphp
<div class="mb-10 grid gap-6 border-b border-slate-100 pb-8 sm:grid-cols-2 lg:grid-cols-4">
  <div class="py-1"><p class="stat-label">Failed sign ins, 24 h</p><p class="stat-value {{ $failed24 >= 10 ? 'text-neon-red' : 'text-neon-purple' }}">{{ $failed24 }}</p><p class="text-xs text-slate-500">{{ $failed7 }} in 7 days</p></div>
  <div class="py-1"><p class="stat-label">Accounts with two-step login</p><p class="stat-value {{ $users->filter->hasTwoFactor()->count() === $users->count() ? 'text-neon-green' : 'text-neon-red' }}">{{ $users->filter->hasTwoFactor()->count() }} <span class="text-base text-slate-400">/ {{ $users->count() }}</span></p></div>
  <div class="py-1"><p class="stat-label">High or critical findings</p><p class="stat-value text-neon-red">{{ $run ? ($sum['by_severity']['critical'] ?? 0) + ($sum['by_severity']['high'] ?? 0) : '–' }}</p><p class="text-xs text-slate-500">{{ $run ? 'Last check '.$run->started_at?->diffForHumans() : 'Not checked yet' }}</p></div>
  <div class="py-1"><p class="stat-label">Administrators</p><p class="stat-value text-neon-blue">{{ $users->where('role', 'administrator')->count() }}</p></div>
</div>

<div class="card mb-10">
  <div class="card-header"><h2 class="card-title">Security check findings</h2>
    @if($runs->count())
      <form method="GET" class="flex items-center gap-2 text-[13px]"><label for="run" class="text-slate-500">Run</label>
        <select id="run" name="run" class="input w-56 py-1" onchange="this.form.submit()">@foreach($runs as $r)<option value="{{ $r->id }}" @selected($run?->id === $r->id)>{{ $r->started_at?->format('M j, H:i') }} · {{ $r->status === 'completed' ? ($r->summary['total'] ?? 0).' findings' : $r->status }}</option>@endforeach</select></form>
    @endif
  </div>
  @if($run)
    <div class="overflow-x-auto">
      <table class="table">
        <thead><tr><th>Severity</th><th>Finding</th><th>Evidence</th><th>What to do</th></tr></thead>
        <tbody>
          @forelse($run->findings ?? [] as $f)
            <tr>
              <td class="whitespace-nowrap"><span class="badge-{{ $sev[$f['severity']] }}">{{ ucfirst($f['severity']) }}</span><span class="block text-[11px] {{ $f['kind'] === 'verified' ? 'text-emerald-700' : 'text-slate-500' }}">{{ $f['kind'] === 'verified' ? 'Verified' : 'Recommendation' }}</span></td>
              <td><span class="font-semibold text-ink">{{ $f['check'] }}</span><span class="block text-xs text-slate-500">{{ $f['category'] }}</span></td>
              <td class="max-w-md break-words text-[12.5px] text-slate-600">{{ $f['evidence'] }}</td>
              <td class="max-w-sm text-[12.5px]">{{ $f['fix'] }}</td>
            </tr>
          @empty
            <tr><td colspan="4" class="py-10 text-center text-slate-500">No problems found in this check.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <p class="border-t border-slate-100 px-5 py-3 text-xs text-slate-500">Checks: sign in patterns, accounts and two-step login, route authorization, configuration, credentials written in code files (shown masked), and known package vulnerabilities from OSV.dev. It does not test the live site from outside.</p>
  @else
    <p class="card-body text-[13px] text-slate-500">No check has been run yet. It is read only and takes a few seconds.</p>
  @endif
</div>

<div class="mb-10 grid gap-x-10 gap-y-10 xl:grid-cols-2">
  <div class="card overflow-x-auto">
    <div class="card-header"><h2 class="card-title">Users and access</h2><a href="{{ route('admin.resources.index', 'users') }}" class="text-[13px] font-semibold text-ink hover:underline">Manage users</a></div>
    <table class="table">
      <thead><tr><th>User</th><th>Two-step</th><th>Last sign in</th><th>Sessions</th><th></th></tr></thead>
      <tbody>
        @foreach($users as $u)
          <tr>
            <td><span class="font-semibold text-ink">{{ $u->name }}</span><span class="block text-xs text-slate-500">{{ \App\Models\User::ROLES[$u->role] ?? $u->role }} · {{ $u->email }}</span></td>
            <td>@if($u->hasTwoFactor())<span class="badge-green">On</span>@else<span class="badge-{{ $u->isAdmin() ? 'red' : 'amber' }}">Off</span>@endif</td>
            <td class="whitespace-nowrap text-slate-500">{{ $u->last_login_at?->diffForHumans() ?? 'Never' }}</td>
            <td>{{ $sessionsEnabled ? ($sessionCounts[$u->id] ?? 0) : 'n/a' }}</td>
            <td class="text-right" x-data="{ open: false }">
              <button type="button" class="btn-ghost btn-sm" @click="open = !open">Sign out everywhere</button>
              <form x-show="open" x-cloak method="POST" action="{{ route('admin.security.sessions', $u->id) }}" class="mt-2 flex gap-2" onsubmit="return confirm('Sign {{ $u->name }} out on every other device?')">
                @csrf <input type="password" name="password" class="input py-1 text-xs" placeholder="Your password" required aria-label="Your password"><button class="btn-danger btn-sm">Confirm</button>
              </form>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
    <p class="px-5 py-3 text-xs text-slate-500">Roles: Administrator has full access. Editor manages content, pages, media, SEO, inbox and analytics. Author writes blog posts and can send them for review.</p>
  </div>

  <div class="card overflow-x-auto">
    <div class="card-header"><h2 class="card-title">Recent sign in activity</h2><a href="{{ route('admin.activity', ['module' => 'Security']) }}" class="text-[13px] font-semibold text-ink hover:underline">All</a></div>
    <table class="table">
      <tbody>
        @foreach($failures->concat($logins)->sortByDesc('id')->take(20) as $l)
          <tr>
            <td class="whitespace-nowrap text-slate-500">{{ $l->created_at?->format('M j, H:i') }}</td>
            <td>@if($l->action === 'login')<span class="badge-green">Signed in</span>@else<span class="badge-red">Failed</span>@endif</td>
            <td><span class="font-semibold text-ink">{{ $l->user_name ?? $l->subject_label }}</span><span class="block text-xs text-slate-500">{{ $l->description }}</span></td>
            <td class="text-xs text-slate-400">{{ $l->ip }}</td>
          </tr>
        @endforeach
        @if($failures->isEmpty() && $logins->isEmpty())<tr><td class="py-8 text-center text-slate-500">No sign ins recorded yet.</td></tr>@endif
      </tbody>
    </table>
  </div>
</div>

<div class="card overflow-x-auto">
  <div class="card-header"><h2 class="card-title">Recent security sensitive actions</h2><a href="{{ route('admin.activity', ['sensitive' => 1]) }}" class="text-[13px] font-semibold text-ink hover:underline">All</a></div>
  <table class="table">
    <tbody>
      @forelse($sensitive as $l)
        <tr>
          <td class="whitespace-nowrap text-slate-500">{{ $l->created_at?->format('M j, H:i') }}</td>
          <td class="font-semibold text-ink">{{ $l->user_name ?? 'System' }}</td>
          <td><span class="badge-gray">{{ str_replace('_', ' ', $l->action) }}</span></td>
          <td>{{ $l->module ?? $l->subject_type }} · {{ $l->subject_label }} <span class="block text-xs text-slate-500">{{ $l->description }}</span></td>
        </tr>
      @empty
        <tr><td class="py-8 text-center text-slate-500">Nothing recorded yet.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
