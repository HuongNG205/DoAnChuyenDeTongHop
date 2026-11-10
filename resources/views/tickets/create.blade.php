@extends('layouts.app')
@section('title', 'Tạo Ticket')
@section('content')
<h1 class="mb-5 text-center text-3xl">Tạo Ticket</h1>
@include('partial.form.ticket', ['ticket' => null])
@endsection
