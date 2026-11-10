{{-- Hiển thị sao. Tham số: $stars (0 - 5, hỗ trợ nửa sao) --}}
@php $s = (float) ($stars ?? 0); @endphp
<span class="inline-flex gap-0.5 text-sm" title="{{ $s }}/5">
    @for ($i = 1; $i <= 5; $i++)
        @if ($s >= $i)
            <i class="fa-solid fa-star text-gold"></i>
        @elseif ($s >= $i - 0.5)
            <i class="fa-solid fa-star-half-stroke text-gold"></i>
        @else
            <i class="fa-solid fa-star text-gray-400"></i>
        @endif
    @endfor
</span>
