@extends('layouts.app')
@section('title', 'Dashboard')
{{-- Biến: $total, $resolved, $inProgress, $waiting, $recent (ticket gần đây) --}}
@section('content')
<h1 class="page-title center">Dashboard</h1>

<div class="stat-grid">
    @foreach ([
        ['Tổng số Ticket', $total],
        ['Đã giải quyết', $resolved],
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
        @foreach ($recent as $ticket)
            @include('partial.ticket_card', ['ticket' => $ticket])
        @endforeach
    </div>
</div>
@endsection
