@extends('emails.layout')
@section('title', 'Đã nhận ticket ' . $ticket->track_id)
{{-- Biến: $ticket (Mailable truyền vào) --}}
@section('body')
<p style="margin:0 0 12px;">Xin chào <b>{{ $ticket->customer->name }}</b>,</p>
<p style="margin:0 0 16px;">Chúng tôi đã nhận được yêu cầu hỗ trợ của bạn. Nhân viên sẽ phản hồi sớm nhất có thể.</p>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 16px;font-size:14px;">
    <tr>
        <td style="padding:4px 0;color:#6b7280;width:140px;">Mã ticket</td>
        <td style="padding:4px 0;"><b>{{ $ticket->track_id }}</b></td>
    </tr>
    <tr>
        <td style="padding:4px 0;color:#6b7280;">Tiêu đề</td>
        <td style="padding:4px 0;">{{ $ticket->title }}</td>
    </tr>
    <tr>
        <td style="padding:4px 0;color:#6b7280;">Mức độ ưu tiên</td>
        <td style="padding:4px 0;">{{ config('helpdesk.priority.' . $ticket->priority) }}</td>
    </tr>
    <tr>
        <td style="padding:4px 0;color:#6b7280;">Trạng thái</td>
        <td style="padding:4px 0;">{{ config('helpdesk.status.' . $ticket->status) }}</td>
    </tr>
    <tr>
        <td style="padding:4px 0;color:#6b7280;">Thời gian gửi</td>
        <td style="padding:4px 0;">{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
    </tr>
</table>

<p style="margin:0 0 6px;"><b>Nội dung bạn đã gửi:</b></p>
{{-- HTML đã được lọc bằng HtmlSanitizer khi lưu --}}
<div class="rich" style="padding:12px 16px;background:#F3F5F7;border-radius:8px;">{!! $ticket->message !!}</div>

<p style="margin:24px 0 0;">
    <a href="{{ route('tickets.show', $ticket) }}"
       style="display:inline-block;padding:10px 22px;background:#F4D35E;color:#002D13;text-decoration:none;border-radius:6px;font-weight:bold;">Xem ticket</a>
</p>
@endsection
