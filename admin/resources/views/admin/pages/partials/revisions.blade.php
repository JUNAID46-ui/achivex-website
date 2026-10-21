{{-- Earlier saved copies of this page's content, with restore. --}}
<div class="card mt-6 max-w-4xl" x-data="{ open: false }">
  <button type="button" class="card-header w-full text-left" @click="open = !open" :aria-expanded="open">
    <h2 class="card-title">Revisions</h2>
    <span class="text-[13px] text-slate-500">{{ $revisions->count() ? $revisions->count().' saved '.\Illuminate\Support\Str::plural('copy', $revisions->count()) : 'None yet' }}</span>
  </button>
  <div x-show="open" x-cloak class="divide-y divide-slate-100">
    @forelse($revisions as $rev)
      <form method="POST" action="{{ route('admin.pages.restore-revision', $rev->id) }}" class="flex flex-wrap items-center justify-between gap-3 px-5 py-3 text-[13px]"
            onsubmit="return confirm('Replace the current page content with the copy from {{ $rev->created_at?->format('M j, Y H:i') }}? The current content is kept as a revision.')">
        @csrf <input type="hidden" name="confirm" value="1">
        <span><span class="font-semibold text-ink">{{ $rev->created_at?->format('M j, Y H:i') }} UTC</span> <span class="text-slate-500">· saved before a change by {{ $rev->user_name ?? 'System' }}</span></span>
        <button class="btn-secondary btn-sm">Restore</button>
      </form>
    @empty
      <p class="px-5 py-6 text-[13px] text-slate-500">A copy is saved every time this page changes.</p>
    @endforelse
  </div>
</div>
