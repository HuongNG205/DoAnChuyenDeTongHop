{{--
  Bộ lọc cho các trang danh sách (GET). Giữ lại q (tìm kiếm) và sort/dir hiện tại.
  Tham số: $action, $fields = [['name' => ..., 'label' => ..., 'type' => 'select'|'date', 'options' => [giá trị => nhãn]]]
  Controller/model chỉ chấp nhận giá trị nằm trong danh sách cho phép, giá trị lạ bị bỏ qua.
--}}
@php
    $keep = collect(request()->only(['q', 'sort', 'dir']))->filter(fn ($v) => is_string($v) && $v !== '');
    $current = fn (string $name) => is_scalar(request($name)) ? (string) request($name) : '';
    $hasFilter = collect($fields)->contains(fn ($f) => $current($f['name']) !== '');
@endphp
<form method="GET" action="{{ $action }}" class="filter-box">
    @foreach ($keep as $k => $v)
        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
    @endforeach

    @foreach ($fields as $f)
        <label class="filter-field">
            <span>{{ $f['label'] }}</span>
            @if (($f['type'] ?? 'select') === 'date')
                <input type="date" name="{{ $f['name'] }}" value="{{ $current($f['name']) }}">
            @else
                <select name="{{ $f['name'] }}">
                    <option value="">Tất cả</option>
                    @foreach ($f['options'] as $value => $text)
                        <option value="{{ $value }}" @selected($current($f['name']) === (string) $value)>{{ $text }}</option>
                    @endforeach
                </select>
            @endif
        </label>
    @endforeach

    <button type="submit" class="btn btn-accent">Lọc</button>
    @if ($hasFilter)
        <a href="{{ $action . ($keep->isNotEmpty() ? '?' . http_build_query($keep->all()) : '') }}" class="btn">Xóa bộ lọc</a>
    @endif
</form>
