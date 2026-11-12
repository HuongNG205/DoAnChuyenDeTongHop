@extends('layouts.app')
@section('title', 'Tài khoản')
@section('content')
<div class="two-col">
    <section>
        <h2 class="section-title">Tài khoản</h2>
        @include('partial.form.user', ['user' => auth()->user(), 'action' => route('account.update'), 'admin' => false])
    </section>
    <section>
        <h2 class="section-title">Mật khẩu</h2>
        @include('partial.form.password')
    </section>
</div>
@endsection
