{{--
  Ô tìm kiếm dùng chung (header và đầu trang danh sách).
  Tham số: $action, $variant ('header' | 'page'), $name (mặc định q), $placeholder
--}}
@php
    $name = $name ?? 'q';
    $header = ($variant ?? 'page') === 'header';
@endphp
<form method="GET" action="{{ $action }}" class="search {{ $header ? '' : 'search-page' }}">
    <i class="fa-solid fa-magnifying-glass"></i>
    <input type="text" name="{{ $name }}" value="{{ request($name) }}"
           placeholder="{{ $placeholder ?? 'Tìm kiếm' }}" aria-label="Tìm kiếm">
    <a href="{{ $action }}" aria-label="Xóa tìm kiếm"><i class="fa-regular fa-circle-xmark"></i></a>
</form>
