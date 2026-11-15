{{--
  Ô tiêu đề cột có nút sắp xếp (tam giác lên = tăng dần, tam giác xuống = giảm dần).
  Tham số: $key (khóa sort, controller kiểm tra allowlist), $label.
  Tùy chọn: $sortDefault = [khóa, 'asc'|'desc'] để tô sáng cột đang sắp xếp mặc định (kế thừa từ view cha).
  Giữ nguyên search / filter hiện tại, chỉ đổi sort, dir và đưa về trang 1.
--}}
@php
    $explicit = is_string(request('sort'));
    $curKey = $explicit ? request('sort') : ($sortDefault[0] ?? null);
    $curDir = $explicit ? \App\Support\ListParams::dir(request('dir')) : ($sortDefault[1] ?? null);
    $activeHere = $curKey === $key;
    $sortUrl = fn (string $dir) => request()->fullUrlWithQuery(['sort' => $key, 'dir' => $dir, 'page' => null]);
@endphp
<th @if ($activeHere) aria-sort="{{ $curDir === 'desc' ? 'descending' : 'ascending' }}" @endif>
    <span class="th-sort">
        <span>{{ $label }}</span>
        <span class="sort-btns">
            <a href="{{ $sortUrl('asc') }}" class="sort-btn {{ $activeHere && $curDir === 'asc' ? 'is-active' : '' }}"
               title="Sắp xếp {{ $label }} tăng dần" aria-label="Sắp xếp {{ $label }} tăng dần">
                <i class="fa-solid fa-caret-up"></i>
            </a>
            <a href="{{ $sortUrl('desc') }}" class="sort-btn {{ $activeHere && $curDir === 'desc' ? 'is-active' : '' }}"
               title="Sắp xếp {{ $label }} giảm dần" aria-label="Sắp xếp {{ $label }} giảm dần">
                <i class="fa-solid fa-caret-down"></i>
            </a>
        </span>
    </span>
</th>
