{{-- Nút đăng nhập / đăng ký bằng Google. Tham số: $label --}}
<div class="my-5 flex items-center gap-3 text-sm text-gray-500">
    <span class="h-px flex-1 bg-gray-300"></span>hoặc<span class="h-px flex-1 bg-gray-300"></span>
</div>
<a href="{{ route('auth.google.redirect') }}" class="btn flex items-center justify-center gap-2 !rounded-xl">
    <i class="fa-brands fa-google"></i>
    {{ $label }}
</a>
