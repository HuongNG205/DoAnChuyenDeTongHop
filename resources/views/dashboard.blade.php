@extends('layouts.app')
@section('title', 'Dashboard')
{{-- Biến: $total, $solved, $inProgress, $waiting, $closed, $recent (gợi ý: ticket đã đóng có đánh giá từ 3 sao trở lên) --}}
@section('content')
<h1 class="page-title center">Dashboard</h1>

<div class="stat-grid">
    @foreach ([
        ['Tổng số Ticket', $total],
        ['Đã giải quyết', $solved + $closed],
        ['Đang giải quyết', $inProgress],
        ['Chờ giải quyết', $waiting],
    ] as [$label, $value])
        <div class="stat-card">
            <div class="stat-label">{{ $label }}</div>
            <div class="stat-value">{{ $value }}</div>
        </div>
    @endforeach
</div>
@endsection

@section('aside')
<div class="recent-box">
    <h2>Gần đây</h2>
    <div class="card-list">
        @forelse ($recent as $ticket)
            @include('partial.ticket_card', ['ticket' => $ticket])
        @empty
            <p class="empty-text">Chưa có ticket nào để gợi ý.</p>
        @endforelse
    </div>
</div>
@endsection
