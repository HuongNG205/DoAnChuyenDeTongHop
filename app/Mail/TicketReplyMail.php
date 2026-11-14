<?php

namespace App\Mail;

use App\Models\Reply;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Email gửi cho khách hàng khi staff phản hồi ticket. */
class TicketReplyMail extends HelpdeskMail
{
    public function __construct(public Reply $reply)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[" . config('app.name') . "] Ticket {$this->reply->ticket->track_id} có phản hồi mới",
            replyTo: $this->supportReplyTo(),
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.ticket-reply');
    }
}
