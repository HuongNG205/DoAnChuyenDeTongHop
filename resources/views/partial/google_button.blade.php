{{-- Nút đăng nhập / đăng ký bằng Google. Tham số: $label --}}
<div class="or">hoặc</div>
<a href="{{ route('auth.google.redirect') }}" class="btn btn-google">
    <i class="fa-brands fa-google"></i>
    {{ $label }}
</a>
