{{-- Dùng: {{ $items->links('partial.pagination') }} --}}
@if ($paginator->hasPages())
    <nav class="pagination" aria-label="Phân trang">
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="pg pg-dots">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="pg pg-active">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="pg">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach
    </nav>
@endif
