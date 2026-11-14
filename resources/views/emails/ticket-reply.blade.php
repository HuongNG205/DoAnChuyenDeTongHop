@extends('emails.layout')
@php $ticket = $reply->ticket; @endphp
@section('title', 'Ticket ' . $ticket->track_id . ' có phản hồi mới')
{{-- Biến: $reply (Mailable truyền vào) --}}
@section('body')
<p style="margin:0 0 12px;">Xin chào <b>{{ $ticket->customer->name }}</b>,</p>
<p style="margin:0 0 16px;">
    <b>{{ $reply->user->name }}</b> vừa phản hồi ticket
    <b>{{ $ticket->track_id }}</b> – {{ $ticket->title }}.
</p>

{{-- HTML đã được lọc bằng HtmlSanitizer khi lưu --}}
<div class="rich" style="padding:12px 16px;background:#F3F5F7;border-radius:8px;">{!! $reply->message !!}</div>
<p style="margin:8px 0 0;color:#6b7280;font-size:13px;">
    {{ $reply->created_at->format('d/m/Y H:i') }} · Trạng thái hiện tại: {{ config('helpdesk.status.' . $ticket->status) }}
</p>

<p style="margin:24px 0 0;">
    <a href="{{ route('tickets.show', $ticket) }}"
       style="display:inline-block;padding:10px 22px;background:#F4D35E;color:#002D13;text-decoration:none;border-radius:6px;font-weight:bold;">Xem và trả lời</a>
</p>
@endsection
