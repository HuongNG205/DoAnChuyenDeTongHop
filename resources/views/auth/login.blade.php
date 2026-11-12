@extends('layouts.guest')
@section('title', 'Đăng nhập')
@section('content')
<form method="POST" action="{{ route('login') }}" class="auth-form">
    @csrf
    <input type="email" name="email" value="{{ old('email') }}" placeholder="Email" aria-label="Email" class="input input-auth" required>
    <input type="password" name="password" placeholder="Mật khẩu" aria-label="Mật khẩu" class="input input-auth" required>
    <div class="center">
        <button class="btn btn-auth">Đăng nhập</button>
    </div>
</form>

<div class="auth-foot">
    <a href="{{ route('register') }}" class="auth-link">Đăng ký</a>
</div>
@endsection
