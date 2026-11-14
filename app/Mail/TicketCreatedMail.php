<?php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Email xác nhận gửi cho khách hàng sau khi tạo ticket. */
class TicketCreatedMail extends HelpdeskMail
{
    public function __construct(public Ticket $ticket)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[" . config('app.name') . "] Đã nhận ticket {$this->ticket->track_id}",
            replyTo: $this->supportReplyTo(),
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.ticket-created');
    }
}
