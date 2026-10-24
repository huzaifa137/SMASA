<div class="d-flex flex-wrap justify-content-between align-items-center" style="gap:.6rem;padding:.8rem 1rem;">
    <div class="bl-muted">
        @if ($page->total() === 0)
            0 schools
        @else
            Showing {{ $page->firstItem() }}–{{ $page->lastItem() }} of {{ number_format($page->total()) }}
            @if ($search !== '') matching school(s) <span>(of {{ number_format($schoolsTotal) }})</span>@else school(s)@endif
        @endif
    </div>
    @if ($page->hasPages())
        <div style="display:flex;gap:.4rem;align-items:center;">
            <button type="button" class="bl-btn sm ghost pg-btn" data-page="{{ $page->currentPage() - 1 }}" @disabled($page->onFirstPage())><i class="fas fa-chevron-left"></i> Prev</button>
            <span class="bl-muted">Page {{ $page->currentPage() }} of {{ $page->lastPage() }}</span>
            <button type="button" class="bl-btn sm ghost pg-btn" data-page="{{ $page->currentPage() + 1 }}" @disabled(!$page->hasMorePages())>Next <i class="fas fa-chevron-right"></i></button>
        </div>
    @endif
</div>
