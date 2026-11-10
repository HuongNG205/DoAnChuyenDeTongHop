{{--
  Ô tìm kiếm dùng chung (header và đầu trang danh sách).
  Tham số: $action, $variant ('header' | 'page'), $name (mặc định q), $placeholder
--}}
@php
    $name = $name ?? 'q';
    $header = ($variant ?? 'page') === 'header';
@endphp
<form method="GET" action="{{ $action }}" class="relative {{ $header ? '' : 'max-w-xl' }}">
    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2"></i>
    <input type="text" name="{{ $name }}" value="{{ request($name) }}"
           placeholder="{{ $placeholder ?? 'Tìm kiếm' }}" aria-label="Tìm kiếm"
           class="w-full bg-white py-2 pl-10 pr-10 outline-none {{ $header ? 'rounded' : 'rounded-lg border-2 border-black focus:border-gold' }}">
    <a href="{{ $action }}" class="absolute right-3 top-1/2 -translate-y-1/2" aria-label="Xóa tìm kiếm">
        <i class="fa-regular fa-circle-xmark"></i>
    </a>
</form>
