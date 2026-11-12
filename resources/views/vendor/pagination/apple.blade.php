@if ($paginator->hasPages())
    <nav aria-label="Pagination">
        <div class="apple-pagination">
            @if ($paginator->onFirstPage())
                <span class="apple-glass-btn apple-btn-outline is-disabled">Sebelumnya</span>
            @else
                <a class="apple-glass-btn apple-btn-outline text-decoration-none" href="{{ $paginator->previousPageUrl() }}" rel="prev">Sebelumnya</a>
            @endif

            <div class="apple-pagination__pages">
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="apple-pagination__ellipsis">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="apple-glass-btn apple-btn-primary apple-pagination__page">{{ $page }}</span>
                            @else
                                <a class="apple-glass-btn apple-btn-outline apple-pagination__page text-decoration-none" href="{{ $url }}">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>

            @if ($paginator->hasMorePages())
                <a class="apple-glass-btn apple-btn-outline text-decoration-none" href="{{ $paginator->nextPageUrl() }}" rel="next">Berikutnya</a>
            @else
                <span class="apple-glass-btn apple-btn-outline is-disabled">Berikutnya</span>
            @endif
        </div>
    </nav>
@endif
