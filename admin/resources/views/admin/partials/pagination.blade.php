@if ($paginator->hasPages())
<nav class="flex items-center justify-between text-[13px]" aria-label="Pagination">
  <p class="text-slate-500">Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</p>
  <div class="flex gap-1">
    @if ($paginator->onFirstPage())
      <span class="btn-secondary btn-sm opacity-50">&lsaquo; Previous</span>
    @else
      <a href="{{ $paginator->previousPageUrl() }}" class="btn-secondary btn-sm">&lsaquo; Previous</a>
    @endif
    @if ($paginator->hasMorePages())
      <a href="{{ $paginator->nextPageUrl() }}" class="btn-secondary btn-sm">Next &rsaquo;</a>
    @else
      <span class="btn-secondary btn-sm opacity-50">Next &rsaquo;</span>
    @endif
  </div>
</nav>
@endif
