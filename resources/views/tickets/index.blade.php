@extends('layouts.app')
@section('title', 'Danh sách Ticket')
@section('content')
<h1 class="text-3xl">Danh sách Ticket</h1>
<div class="my-4">
    @include('partial.search_box', ['action' => route('tickets.index')])
</div>
@include('partial.ticket_table', ['tickets' => $tickets, 'mode' => 'customer'])
@endsection
