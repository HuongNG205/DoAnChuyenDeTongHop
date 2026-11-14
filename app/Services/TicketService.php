<?php

namespace App\Services;

use App\Mail\TicketCreatedMail;
use App\Mail\TicketReplyMail;
use App\Models\Activity;
use App\Models\Reply;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

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

            // Lịch sử hoạt động của khách hàng (không có message => không hiện ở chuông).
            $this->record('create_ticket', $ticket, $customer);

            // Thông báo trên chuông cho staff/admin.
            $this->notify(
                $this->activeStaff()->get(),
                'create_ticket',
                $ticket,
                "Khách hàng {$customer->name} vừa tạo ticket {$ticket->track_id}: " . Str::limit($ticket->title, 60)
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

            $this->record('reply_ticket', $ticket, $author);

            if ($isCustomerReply) {
                $this->notify(
                    $this->staffFor($ticket),
                    'reply_ticket',
                    $ticket,
                    "Khách hàng {$author->name} đã phản hồi ticket {$ticket->track_id}."
                );
            } else {
                $this->notify(
                    collect([$ticket->customer])->filter(),   // customer có thể đã bị xóa mềm
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

    // ---------- Nội bộ ----------

    /** Staff + admin chưa bị khóa. */
    private function activeStaff()
    {
        return User::whereIn('role', ['staff', 'admin'])->where('is_blocked', false);
    }

    /**
     * Staff cần được báo khi khách hàng phản hồi: những người đã tham gia ticket;
     * nếu chưa ai tham gia (hoặc họ đã bị khóa/xóa) thì báo cho tất cả staff/admin.
     */
    private function staffFor(Ticket $ticket): Collection
    {
        $participants = $this->activeStaff()
            ->whereIn('id', Reply::where('ticket_id', $ticket->id)->select('user_id'))
            ->get();

        return $participants->isNotEmpty() ? $participants : $this->activeStaff()->get();
    }

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

    /** Tạo thông báo (hiện ở chuông trên header) cho từng người nhận. */
    private function notify(Collection $users, string $action, Ticket $ticket, string $message): void
    {
        foreach ($users as $user) {
            $this->record($action, $ticket, $user, $message);
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
