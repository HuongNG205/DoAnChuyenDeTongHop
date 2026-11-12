@extends('layouts.app')
@section('title', $ticket->title)
{{-- Biến: $ticket (đã load customer, replies.user, rating) --}}
@php
    $isOwner = auth()->id() === $ticket->customer_id;
@endphp

@section('content')
<div class="flex flex-wrap items-center gap-3">
    <a href="{{ url()->previous() }}" aria-label="Quay lại"><i class="fa-solid fa-arrow-left"></i></a>
    <h1 class="text-3xl">{{ $ticket->title }}</h1>
    <div class="ml-auto flex gap-3">
        @can('update', $ticket)
            <a href="{{ route('tickets.edit', $ticket) }}" class="btn">Sửa</a>
        @endcan
        @can('delete', $ticket)
            <form method="POST" action="{{ route('tickets.destroy', $ticket) }}" onsubmit="return confirm('Xóa ticket này?')">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger">Xóa</button>
            </form>
        @endcan
    </div>
</div>

{{-- Nội dung ticket (HTML đã được lọc ở server) --}}
<div class="rich mt-5 border-b border-gray-500 pb-4">{!! $ticket->message !!}</div>

@foreach ($ticket->replies as $reply)
    <div class="border-b border-gray-500 py-4">
        <div>Reply by <b>{{ $reply->user->name }}</b> - {{ $reply->created_at->format('d/m/Y') }}</div>
        <div class="rich">{!! $reply->message !!}</div>
    </div>
@endforeach

@if ($ticket->status !== 'closed')
    <div class="mt-6">
        @include('partial.form.reply', ['ticket' => $ticket])
    </div>
@endif

@if ($isOwner)
    <div class="mt-6 border-t border-gray-500 pt-4">
        @include('partial.form.rating', ['ticket' => $ticket, 'rating' => $ticket->rating])
    </div>
@endif
@endsection

@section('aside')
<h2 class="mb-4 font-bold">Thông tin Ticket</h2>
<ul class="space-y-2">
    <li>Mã Ticket: {{ $ticket->track_id }}</li>
    <li>Trạng thái: {{ config('helpdesk.status.' . $ticket->status) }}</li>
    <li>Ngày tạo: {{ $ticket->created_at->format('d/m/Y') }}</li>
    <li>Mức độ ưu tiên: {{ config('helpdesk.priority.' . $ticket->priority) }}</li>
    <li>Người tạo: {{ $ticket->customer->name }}</li>
    <li>Đánh giá: @include('partial.stars', ['stars' => $ticket->rating->stars ?? 0])</li>
</ul>
@endsection
