{{--
  Rich Text Editor (Quill) thay cho textarea. Nội dung lưu dạng HTML trong input ẩn.
  Tham số: $name (mặc định message), $value (nội dung HTML ban đầu)
  Lưu ý: cần lọc HTML ở server khi lưu (ví dụ HTMLPurifier) vì nội dung hiển thị bằng {!! !!}.
--}}
@php
    $name = $name ?? 'message';
    $uid = 'rte-' . $name;
@endphp
<div>
    <div id="{{ $uid }}" data-rte data-input="{{ $uid }}-input"></div>
    <input type="hidden" name="{{ $name }}" id="{{ $uid }}-input" value="{{ old($name, $value ?? '') }}">
</div>

@once
    @push('styles')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css">
    @endpush
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
        <script>
            document.querySelectorAll('[data-rte]').forEach(function (el) {
                var input = document.getElementById(el.dataset.input);
                var quill = new Quill(el, {
                    theme: 'snow',
                    modules: {
                        toolbar: [
                            [{ header: [1, 2, 3, false] }],
                            [{ align: [] }],
                            [{ color: [] }, { background: [] }],
                            ['bold', 'italic', 'underline', 'strike'],
                            ['code', 'blockquote', 'code-block'],
                            [{ list: 'bullet' }, { list: 'ordered' }],
                            ['link'],
                            ['clean']
                        ]
                    }
                });

                if (input.value) {
                    quill.clipboard.dangerouslyPasteHTML(input.value);
                }

                function sync() {
                    var html = quill.root.innerHTML;
                    input.value = html === '<p><br></p>' ? '' : html;
                }
                quill.on('text-change', sync);
                el.closest('form').addEventListener('submit', sync);
            });
        </script>
    @endpush
@endonce
