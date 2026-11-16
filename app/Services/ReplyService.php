<?php

namespace App\Services;

use App\Mail\TicketReplyMail;
use App\Models\Reply;
use App\Models\Ticket;
use App\Models\User;
use App\Services\Concerns\QueuesMail;
use Illuminate\Support\Facades\DB;

/** Trả lời ticket (dùng chung cho customer và staff/admin). Tách riêng khỏi TicketService. */
class ReplyService
{
    use QueuesMail;

    public function __construct(private ActivityService $activities)
    {
    }

    public function reply(Ticket $ticket, User $author, string $message): Reply
    {
        $isCustomerReply = $author->id === $ticket->customer_id;

        $reply = DB::transaction(function () use ($ticket, $author, $message, $isCustomerReply) {
            $reply = $ticket->replies()->create([
                'user_id' => $author->id,
                'message' => $message,
            ]);

            // Staff trả lời ticket mới => chuyển sang "Đang giải quyết"
            // (ticket "Chờ giải quyết" chỉ được phép có reply của khách hàng).
            if (! $isCustomerReply && $ticket->status === 'new') {
                $ticket->status = 'in_progress';
            }
            // save() cập nhật updated_at; nếu không có gì đổi thì touch() để ticket nổi lên đầu danh sách staff.
            $ticket->isDirty() ? $ticket->save() : $ticket->touch();

            if ($isCustomerReply) {
                $this->activities->notifyCustomer($author, 'reply_ticket', $ticket, "Bạn đã phản hồi ticket {$ticket->track_id}.");
            } else {
                $this->activities->record('reply_ticket', $ticket, $author); // lịch sử của staff
                $this->activities->notifyCustomer(
                    $ticket->customer,
                    'reply_ticket',
                    $ticket,
                    "{$author->name} đã phản hồi ticket {$ticket->track_id} của bạn."
                );
            }

            return $reply;
        });

        // Staff trả lời => gửi email cho khách hàng (không gửi nếu tài khoản đã bị xóa).
        if (! $isCustomerReply && $ticket->customer && ! $ticket->customer->trashed()) {
            $this->sendMail($ticket->customer, new TicketReplyMail($reply));
        }

        return $reply;
    }
}
