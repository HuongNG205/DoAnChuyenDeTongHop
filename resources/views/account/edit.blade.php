@extends('layouts.app')
@section('title', 'Tài khoản')
@section('content')
<div class="grid gap-10 lg:grid-cols-2">
    <section>
        <h2 class="mb-4 text-2xl font-bold">Tài khoản</h2>
        @include('partial.form.user', ['user' => auth()->user(), 'action' => route('account.update'), 'admin' => false])
    </section>
    <section>
        <h2 class="mb-4 text-2xl font-bold">Mật khẩu</h2>
        @include('partial.form.password')
    </section>
</div>
@endsection
