{{--
    SMASA standard pagination.

    Registered as the application-wide default in AppServiceProvider
    (Paginator::defaultView), so a plain {{ $items->links() }} renders this
    design everywhere — same look as /students/all-students: a
    "Showing x–y of z" label on the left, compact rounded page buttons on
    the right. It deliberately uses its own .smasa-pager* classes (not
    Bootstrap's .pagination/.page-link) so per-page legacy overrides
    can't change how it looks. Query-string filters are kept by the
    caller with ->appends(...), exactly as before.
--}}
@if ($paginator->hasPages())
    @include('pagination.smasa-styles')

    @php
        $current = $paginator->currentPage();
        $last = $paginator->lastPage();

        // First two, last two and one either side of the current page;
        // everything else collapses into an ellipsis.
        $shown = collect([1, 2, $last - 1, $last, $current - 1, $current, $current + 1])
            ->filter(fn ($p) => $p >= 1 && $p <= $last)
            ->unique()
            ->sort()
            ->values();
    @endphp

    <nav class="smasa-pager" role="navigation" aria-label="Pagination">
        <span class="smasa-pager__info">
            Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }}
        </span>

        <ul class="smasa-pager__list">
            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <li class="is-disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                    <span class="smasa-pager__link" aria-hidden="true">&lsaquo;</span>
                </li>
            @else
                <li>
                    <a class="smasa-pager__link" href="{{ $paginator->previousPageUrl() }}" rel="prev"
                        aria-label="@lang('pagination.previous')">&lsaquo;</a>
                </li>
            @endif

            {{-- Page numbers --}}
            @foreach ($shown as $i => $page)
                @if ($i > 0 && $page - $shown[$i - 1] > 1)
                    <li aria-disabled="true"><span class="smasa-pager__gap">&hellip;</span></li>
                @endif

                @if ($page == $current)
                    <li class="is-active" aria-current="page">
                        <span class="smasa-pager__link">{{ $page }}</span>
                    </li>
                @else
                    <li>
                        <a class="smasa-pager__link" href="{{ $paginator->url($page) }}"
                            aria-label="Go to page {{ $page }}">{{ $page }}</a>
                    </li>
                @endif
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <li>
                    <a class="smasa-pager__link" href="{{ $paginator->nextPageUrl() }}" rel="next"
                        aria-label="@lang('pagination.next')">&rsaquo;</a>
                </li>
            @else
                <li class="is-disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                    <span class="smasa-pager__link" aria-hidden="true">&rsaquo;</span>
                </li>
            @endif
        </ul>
    </nav>
@endif
