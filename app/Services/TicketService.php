<?php

namespace App\Services;

use App\Mail\TicketCreatedMail;
use App\Mail\TicketReplyMail;
use App\Models\Activity;
use App\Models\Rating;
use App\Models\Reply;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Quy ước thông báo (bảng activities, message != null => hiện ở chuông trên header):
 * tạo ticket, xóa ticket và phản hồi luôn tạo thông báo gửi tới CUSTOMER (chủ ticket).
 * Hành động của staff/admin còn được ghi thêm một dòng lịch sử (message = null) cho chính họ.
 */
class TicketService
{
    // ---------- Tạo ticket ----------

    /**
     * @param  array{title: string, priority: string, message: string}  $data  (message đã lọc HTML)
     */
    public function create(User $customer, array $data): Ticket
    {
        $ticket = DB::transaction(function () use ($customer, $data) {
            $ticket = Ticket::create([
                'track_id' => Ticket::generateTrackId(),
                'customer_id' => $customer->id,
                'title' => $data['title'],
                'message' => $data['message'],
                'priority' => $data['priority'],
                'status' => 'new',
            ]);

            $this->notifyCustomer(
                $customer,
                'create_ticket',
                $ticket,
                "Bạn đã tạo ticket {$ticket->track_id}: " . Str::limit($ticket->title, 60)
            );

            return $ticket;
        });

        // Email xác nhận gửi cho khách hàng (sau khi đã commit).
        $this->sendMail($customer, new TicketCreatedMail($ticket));

        return $ticket;
    }

    // ---------- Trả lời ticket ----------

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
                $this->notifyCustomer($author, 'reply_ticket', $ticket, "Bạn đã phản hồi ticket {$ticket->track_id}.");
            } else {
                $this->record('reply_ticket', $ticket, $author); // lịch sử của staff
                $this->notifyCustomer(
                    $ticket->customer, // có thể null nếu customer đã bị xóa mềm
                    'reply_ticket',
                    $ticket,
                    "{$author->name} đã phản hồi ticket {$ticket->track_id} của bạn."
                );
            }

            return $reply;
        });

        // Staff trả lời => gửi email cho khách hàng.
        if (! $isCustomerReply && $ticket->customer) {
            $this->sendMail($ticket->customer, new TicketReplyMail($reply));
        }

        return $reply;
    }

    // ---------- Xóa ticket ----------

    public function delete(Ticket $ticket, User $actor): void
    {
        DB::transaction(function () use ($ticket, $actor) {
            $customer = $ticket->customer;

            $ticket->delete(); // xóa mềm

            if ($actor->id === $ticket->customer_id) {
                $this->notifyCustomer($actor, 'delete_ticket', $ticket, "Bạn đã xóa ticket {$ticket->track_id}.");

                return;
            }

            $this->record('delete_ticket', $ticket, $actor); // lịch sử của staff
            $this->notifyCustomer(
                $customer,
                'delete_ticket',
                $ticket,
                "{$actor->name} đã xóa ticket {$ticket->track_id} của bạn."
            );
        });
    }

    // ---------- Đánh giá ----------

    /** Thêm hoặc cập nhật đánh giá (mỗi ticket một đánh giá). */
    public function rate(Ticket $ticket, User $customer, int $stars): Rating
    {
        return DB::transaction(function () use ($ticket, $customer, $stars) {
            // ratings.ticket_id là UNIQUE nên đánh giá đã bị xóa mềm phải được khôi phục, không tạo dòng mới.
            $rating = Rating::withTrashed()->firstOrNew(['ticket_id' => $ticket->id]);
            $rating->fill(['user_id' => $customer->id, 'stars' => $stars]);

            if ($rating->trashed()) {
                $rating->restore();
            }
            $rating->save();

            $this->record('rate_ticket', $ticket, $customer);

            return $rating;
        });
    }

    public function removeRating(Ticket $ticket, User $customer): void
    {
        DB::transaction(function () use ($ticket, $customer) {
            if ($ticket->rating?->delete()) {
                $this->record('rate_ticket', $ticket, $customer);
            }
        });
    }

    // ---------- Nội bộ ----------

    /** Ghi một dòng vào bảng activities (message = null: chỉ là lịch sử, không phải thông báo). */
    private function record(string $action, Ticket $ticket, User $user, ?string $message = null): void
    {
        Activity::create([
            'ticket_id' => $ticket->id,
            'action' => $action,
            'message' => $message,
            'notify_user_id' => $user->id,
        ]);
    }

    /** Tạo thông báo (hiện ở chuông trên header) cho customer. Bỏ qua nếu customer không còn tồn tại. */
    private function notifyCustomer(?User $customer, string $action, Ticket $ticket, string $message): void
    {
        if ($customer) {
            $this->record($action, $ticket, $customer, $message);
        }
    }

    /**
     * Đưa email vào hàng đợi. Lỗi gửi mail không được làm hỏng thao tác chính
     * (ticket/reply đã lưu xong), nên chỉ ghi log lỗi.
     */
    private function sendMail(User $to, Mailable $mailable): void
    {
        try {
            Mail::to($to)->queue($mailable);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
