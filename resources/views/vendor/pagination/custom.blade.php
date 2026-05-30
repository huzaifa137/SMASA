@if ($paginator->hasPages())
    <div class="custom-pagination">
        {{-- Pagination Elements --}}
        <div class="pagination-links">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span class="pagination-link disabled" aria-disabled="true">
                    <i class="fas fa-chevron-left"></i>
                    <span class="pagination-text">Prev</span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="pagination-link" rel="prev">
                    <i class="fas fa-chevron-left"></i>
                    <span class="pagination-text">Prev</span>
                </a>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span class="pagination-dots">{{ $element }}</span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="pagination-link active" aria-current="page">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="pagination-link">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="pagination-link" rel="next">
                    <span class="pagination-text">Next</span>
                    <i class="fas fa-chevron-right"></i>
                </a>
            @else
                <span class="pagination-link disabled" aria-disabled="true">
                    <span class="pagination-text">Next</span>
                    <i class="fas fa-chevron-right"></i>
                </span>
            @endif
        </div>
    </div>
@endif