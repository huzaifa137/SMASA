{{-- SMASA standard pagination — simplePaginate() variant (Previous / Next only). --}}
@if ($paginator->hasPages())
    @include('pagination.smasa-styles')

    <nav class="smasa-pager" role="navigation" aria-label="Pagination">
        <span class="smasa-pager__info">Page {{ $paginator->currentPage() }}</span>

        <ul class="smasa-pager__list">
            @if ($paginator->onFirstPage())
                <li class="is-disabled" aria-disabled="true"><span class="smasa-pager__link">&lsaquo; Previous</span></li>
            @else
                <li><a class="smasa-pager__link" href="{{ $paginator->previousPageUrl() }}" rel="prev">&lsaquo; Previous</a></li>
            @endif

            @if ($paginator->hasMorePages())
                <li><a class="smasa-pager__link" href="{{ $paginator->nextPageUrl() }}" rel="next">Next &rsaquo;</a></li>
            @else
                <li class="is-disabled" aria-disabled="true"><span class="smasa-pager__link">Next &rsaquo;</span></li>
            @endif
        </ul>
    </nav>
@endif
