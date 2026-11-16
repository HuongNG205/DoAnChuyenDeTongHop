{{-- Dùng: {{ $items->links('partial.pagination') }}  — có nút << (trang đầu), < (trước), > (sau), >> (trang cuối) --}}
@if ($paginator->hasPages())
    <nav class="pagination" aria-label="Phân trang">
        {{-- << và < --}}
        @if ($paginator->onFirstPage())
            <span class="pg pg-disabled" aria-disabled="true" aria-label="Trang đầu">&lt;&lt;</span>
            <span class="pg pg-disabled" aria-disabled="true" aria-label="Trang trước">&lt;</span>
        @else
            <a href="{{ $paginator->url(1) }}" class="pg" rel="first" aria-label="Trang đầu">&lt;&lt;</a>
            <a href="{{ $paginator->previousPageUrl() }}" class="pg" rel="prev" aria-label="Trang trước">&lt;</a>
        @endif

        {{-- Số trang --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="pg pg-dots">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="pg pg-active" aria-current="page">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="pg">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- > và >> --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="pg" rel="next" aria-label="Trang sau">&gt;</a>
            <a href="{{ $paginator->url($paginator->lastPage()) }}" class="pg" rel="last" aria-label="Trang cuối">&gt;&gt;</a>
        @else
            <span class="pg pg-disabled" aria-disabled="true" aria-label="Trang sau">&gt;</span>
            <span class="pg pg-disabled" aria-disabled="true" aria-label="Trang cuối">&gt;&gt;</span>
        @endif
    </nav>
@endif
