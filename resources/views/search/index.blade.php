@extends('layouts.app')
@section('title', 'Tìm kiếm')
{{-- Biến: $tickets (paginator, có rating, replies_count) --}}
@section('content')
<div class="card-list">
    @forelse ($tickets as $ticket)
        @include('partial.ticket_card', ['ticket' => $ticket])
    @empty
        <p class="empty-text">Không tìm thấy kết quả.</p>
    @endforelse
</div>
{{ $tickets->withQueryString()->links('partial.pagination') }}
@endsection
