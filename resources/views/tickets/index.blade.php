@extends('layouts.app')
@section('title', 'Danh sách Ticket')
@section('content')
<h1 class="page-title">Danh sách Ticket</h1>
<div class="toolbar">
    @include('partial.search_box', ['action' => route('tickets.index')])
</div>
@include('partial.ticket_table', ['tickets' => $tickets, 'mode' => 'customer'])
@endsection
