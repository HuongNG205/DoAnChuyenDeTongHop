@extends('layouts.guest')
@section('title', 'Đăng ký')
@section('content')
<form method="POST" action="{{ route('register') }}" class="auth-form">
    @csrf
    <input name="name" value="{{ old('name') }}" placeholder="Tên người dùng" aria-label="Tên người dùng" class="input input-auth" required>
    <input type="email" name="email" value="{{ old('email') }}" placeholder="Email" aria-label="Email" class="input input-auth" required>
    <input type="password" name="password" placeholder="Mật khẩu" aria-label="Mật khẩu" class="input input-auth" required>
    <div class="center">
        <button class="btn btn-auth">Đăng ký</button>
    </div>
</form>

<div class="auth-foot">
    <a href="{{ route('login') }}" class="auth-link">Đăng nhập</a>
</div>
@endsection
