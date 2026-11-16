{{--
  Ô chọn nhanh để cập nhật một trường ngay trên danh sách: đổi giá trị là tự gửi form (PUT).
  Tham số: $action, $name, $value, $options [giá trị => nhãn], $label (nhãn trợ năng), $badge (tùy chọn: tô màu như nhãn trạng thái ticket)
  _inline = 1 để controller quay lại đúng trang danh sách (giữ nguyên trang / bộ lọc / sắp xếp).
--}}
<form method="POST" action="{{ $action }}" class="inline-select-form">
    @csrf
    @method('PUT')
    <input type="hidden" name="_inline" value="1">
    <select name="{{ $name }}" aria-label="{{ $label }}" onchange="this.form.submit()"
            class="inline-select {{ ! empty($badge) ? 'badge badge-' . $value : '' }}">
        @foreach ($options as $key => $text)
            <option value="{{ $key }}" @selected((string) $value === (string) $key)>{{ $text }}</option>
        @endforeach
    </select>
    <noscript><button class="btn">Lưu</button></noscript>
</form>
