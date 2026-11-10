@extends('layouts.app')
@section('title', 'Sửa Ticket ' . $ticket->track_id)
@section('content')
<div class="mb-5 flex items-center justify-center gap-3">
    <a href="{{ route('tickets.show', $ticket) }}" aria-label="Quay lại"><i class="fa-solid fa-arrow-left"></i></a>
    <h1 class="text-3xl">Sửa Ticket {{ $ticket->track_id }}</h1>
</div>
@include('partial.form.ticket', ['ticket' => $ticket])
@endsection
