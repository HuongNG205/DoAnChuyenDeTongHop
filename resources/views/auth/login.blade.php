@extends('layouts.guest')
@section('title', 'Đăng nhập')
@section('content')
<form method="POST" action="{{ route('login') }}" class="mt-6 space-y-6">
    @csrf
    <input type="email" name="email" value="{{ old('email') }}" placeholder="Email" aria-label="Email" class="input !rounded-none py-3 text-lg" required>
    <input type="password" name="password" placeholder="Mật khẩu" aria-label="Mật khẩu" class="input !rounded-none py-3 text-lg" required>
    <div class="text-center">
        <button class="btn w-2/3 !rounded-xl py-2 text-lg">Đăng nhập</button>
    </div>
</form>

@include('partial.google_button', ['label' => 'Đăng nhập bằng Google'])

<div class="mt-6 text-right">
    <a href="{{ route('register') }}" class="text-lg text-link underline">Đăng ký</a>
</div>
@endsection
