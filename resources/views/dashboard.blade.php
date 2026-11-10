@extends('layouts.app')
@section('title', 'Dashboard')
{{-- Biến: $total, $resolved, $inProgress, $waiting, $recent (ticket gần đây) --}}
@section('content')
<h1 class="text-center text-3xl">Dashboard</h1>

<div class="mt-8 grid grid-cols-2 gap-5 lg:grid-cols-4">
    @foreach ([
        ['Tổng số Ticket', $total],
        ['Đã giải quyết', $resolved],
        ['Đang giải quyết', $inProgress],
        ['Chờ giải quyết', $waiting],
    ] as [$label, $value])
        <div class="card px-3 pb-6 pt-4 text-center">
            <div class="text-xl">{{ $label }}</div>
            <div class="mt-8 text-7xl font-light">{{ $value }}</div>
        </div>
    @endforeach
</div>
@endsection

@section('aside')
<div class="rounded-3xl bg-muted p-4">
    <h2 class="mb-3 text-xl">Gần đây</h2>
    <div class="space-y-3">
        @foreach ($recent as $ticket)
            @include('partial.ticket_card', ['ticket' => $ticket])
        @endforeach
    </div>
</div>
@endsection
