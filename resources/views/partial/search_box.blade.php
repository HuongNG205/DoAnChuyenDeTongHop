{{--
  Ô tìm kiếm dùng chung (header và đầu trang danh sách).
  Tham số: $action, $variant ('header' | 'page'), $name (mặc định q), $placeholder
--}}
@php
    $name = $name ?? 'q';
    $header = ($variant ?? 'page') === 'header';
    // Trang danh sách: giữ lại bộ lọc và sắp xếp hiện tại khi tìm kiếm (bỏ q và page)
    $keep = $header ? collect() : collect(request()->except([$name, 'page']))->filter(fn ($v) => is_string($v) && $v !== '');
@endphp
<form method="GET" action="{{ $action }}" class="search {{ $header ? '' : 'search-page' }}">
    @foreach ($keep as $k => $v)
        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
    @endforeach
    <i class="fa-solid fa-magnifying-glass"></i>
    <input type="text" name="{{ $name }}" value="{{ request($name) }}"
           placeholder="{{ $placeholder ?? 'Tìm kiếm' }}" aria-label="Tìm kiếm">
    <a href="{{ $action . ($keep->isNotEmpty() ? '?' . http_build_query($keep->all()) : '') }}" aria-label="Xóa tìm kiếm"><i class="fa-regular fa-circle-xmark"></i></a>
</form>
