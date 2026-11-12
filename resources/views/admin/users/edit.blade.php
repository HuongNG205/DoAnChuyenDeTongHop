@extends('layouts.app')
@section('title', $user->name)
{{-- Biến: $user, $tickets (paginator ticket của user, đã load rating) --}}
@section('content')
<div class="back-head back-head-left">
    <a href="{{ route('admin.users.index') }}" aria-label="Quay lại"><i class="fa-solid fa-arrow-left"></i></a>
    <h1 class="title-sm">{{ $user->name }}</h1>
</div>

<div class="narrow">
    @include('partial.form.user', ['user' => $user, 'action' => route('admin.users.update', $user), 'admin' => true])
</div>

<hr class="divider">

<h2 class="title-sm mb-5">Tổng số Ticket: {{ $tickets->total() }}</h2>
@include('partial.ticket_table', ['tickets' => $tickets, 'mode' => 'customer'])
@endsection
