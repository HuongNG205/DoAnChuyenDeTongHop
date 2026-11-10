@extends('layouts.guest')
@section('title', 'Đăng ký')
@section('content')
<form method="POST" action="{{ route('register') }}" class="mt-6 space-y-6">
    @csrf
    <input name="name" value="{{ old('name') }}" placeholder="Tên người dùng" aria-label="Tên người dùng" class="input !rounded-none py-3 text-lg" required>
    <input type="email" name="email" value="{{ old('email') }}" placeholder="Email" aria-label="Email" class="input !rounded-none py-3 text-lg" required>
    <input type="password" name="password" placeholder="Mật khẩu" aria-label="Mật khẩu" class="input !rounded-none py-3 text-lg" required>
    <div class="text-center">
        <button class="btn w-2/3 !rounded-xl py-2 text-lg">Đăng ký</button>
    </div>
</form>

@include('partial.google_button', ['label' => 'Đăng ký bằng Google'])

<div class="mt-6 text-right">
    <a href="{{ route('login') }}" class="text-lg text-link underline">Đăng nhập</a>
</div>
@endsection
