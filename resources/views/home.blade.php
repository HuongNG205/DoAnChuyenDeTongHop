@extends('layouts.app')
@section('title', 'Guidance')
{{-- Trang chủ cho người dùng chưa đăng nhập. Biến: $tickets (paginator, có rating, replies_count) --}}
@section('content')
<div class="card-list">
    @foreach ($tickets as $ticket)
        @include('partial.ticket_card', ['ticket' => $ticket])
    @endforeach
</div>
{{ $tickets->links('partial.pagination') }}
@endsection
