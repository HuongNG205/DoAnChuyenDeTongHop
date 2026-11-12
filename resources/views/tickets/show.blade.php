@extends('layouts.app')
@section('title', $ticket->title)
{{-- Biến: $ticket (đã load customer, replies.user, rating) --}}
@php
    $isOwner = auth()->id() === $ticket->customer_id;
@endphp

@section('content')
<div class="show-head">
    <a href="{{ url()->previous() }}" aria-label="Quay lại"><i class="fa-solid fa-arrow-left"></i></a>
    <h1 class="page-title">{{ $ticket->title }}</h1>
    <div class="show-actions">
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
<div class="message message-first rich">{!! $ticket->message !!}</div>

@foreach ($ticket->replies as $reply)
    <div class="message">
        <div>Reply by <b>{{ $reply->user->name }}</b> - {{ $reply->created_at->format('d/m/Y') }}</div>
        <div class="rich">{!! $reply->message !!}</div>
    </div>
@endforeach

@if ($ticket->status !== 'closed')
    <div class="reply-box">
        @include('partial.form.reply', ['ticket' => $ticket])
    </div>
@endif

@if ($isOwner)
    <div class="rating-box">
        @include('partial.form.rating', ['ticket' => $ticket, 'rating' => $ticket->rating])
    </div>
@endif
@endsection

@section('aside')
<h2 class="info-title">Thông tin Ticket</h2>
<ul class="info-list">
    <li>Mã Ticket: {{ $ticket->track_id }}</li>
    <li>Trạng thái: {{ config('helpdesk.status.' . $ticket->status) }}</li>
    <li>Ngày tạo: {{ $ticket->created_at->format('d/m/Y') }}</li>
    <li>Mức độ ưu tiên: {{ config('helpdesk.priority.' . $ticket->priority) }}</li>
    <li>Người tạo: {{ $ticket->customer->name }}</li>
    <li>Đánh giá: @include('partial.stars', ['stars' => $ticket->rating->stars ?? 0])</li>
</ul>
@endsection
