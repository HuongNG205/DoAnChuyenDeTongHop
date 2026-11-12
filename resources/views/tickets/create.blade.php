@extends('layouts.app')
@section('title', 'Tạo Ticket')
@section('content')
<h1 class="page-title center mb-5">Tạo Ticket</h1>
@include('partial.form.ticket', ['ticket' => null])
@endsection
