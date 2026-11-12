{{-- Hiển thị sao. Tham số: $stars (0 - 5, hỗ trợ nửa sao) --}}
@php $s = (float) ($stars ?? 0); @endphp
<span class="stars" title="{{ $s }}/5">
    @for ($i = 1; $i <= 5; $i++)
        @if ($s >= $i)
            <i class="fa-solid fa-star star-on"></i>
        @elseif ($s >= $i - 0.5)
            <i class="fa-solid fa-star-half-stroke star-on"></i>
        @else
            <i class="fa-solid fa-star star-off"></i>
        @endif
    @endfor
</span>
