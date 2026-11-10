{{-- Dùng: {{ $items->links('partial.pagination') }} --}}
@if ($paginator->hasPages())
    <nav class="mt-4 flex flex-wrap items-center justify-center gap-2" aria-label="Phân trang">
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="page font-bold">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="page page-active">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="page hover:bg-muted">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach
    </nav>
@endif
