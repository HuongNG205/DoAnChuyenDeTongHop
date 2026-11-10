@extends('layouts.app')
@section('title', $user->name)
{{-- Biến: $user, $tickets (paginator ticket của user, đã load rating) --}}
@section('content')
<div class="mb-5 flex items-center gap-3">
    <a href="{{ route('admin.users.index') }}" aria-label="Quay lại"><i class="fa-solid fa-arrow-left"></i></a>
    <h1 class="text-2xl">{{ $user->name }}</h1>
</div>

<div class="max-w-4xl">
    @include('partial.form.user', ['user' => $user, 'action' => route('admin.users.update', $user), 'admin' => true])
</div>

<hr class="my-6 border-black">

<h2 class="mb-3 text-2xl">Tổng số Ticket: {{ $tickets->total() }}</h2>
@include('partial.ticket_table', ['tickets' => $tickets, 'mode' => 'customer'])
@endsection
