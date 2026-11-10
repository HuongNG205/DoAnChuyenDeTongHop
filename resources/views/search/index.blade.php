@extends('layouts.app')
@section('title', 'Tìm kiếm')
{{-- Biến: $tickets (paginator, có rating, replies_count) --}}
@section('content')
<div class="space-y-3">
    @forelse ($tickets as $ticket)
        @include('partial.ticket_card', ['ticket' => $ticket])
    @empty
        <p class="py-6 text-center text-gray-500">Không tìm thấy kết quả.</p>
    @endforelse
</div>
{{ $tickets->withQueryString()->links('partial.pagination') }}
@endsection
