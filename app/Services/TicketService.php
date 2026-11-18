<?php

namespace App\Services;

use App\Mail\TicketCreatedMail;
use App\Models\Rating;
use App\Models\Ticket;
use App\Models\User;
use App\Services\Concerns\QueuesMail;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Quy ước thông báo (bảng activities, message != null => hiện ở chuông trên header):
 * tạo ticket, sửa ticket, xóa ticket và phản hồi luôn tạo thông báo gửi tới CUSTOMER (chủ ticket).
 * Hành động của staff/admin còn được ghi thêm một dòng lịch sử (message = null) cho chính họ.
 */
class TicketService
{
    use QueuesMail;

    public function __construct(private ActivityService $activities)
    {
    }

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

            $this->activities->notifyCustomer(
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

    // ---------- Cập nhật ticket (staff/admin) ----------

    /**
     * @param  array{title?: string, message?: string, priority?: string, status?: string}  $data
     *         Quyền sửa title/message do UpdateTicketRequest kiểm soát (staff bị cấm, chỉ admin được sửa).
     */
    public function update(Ticket $ticket, User $actor, array $data): Ticket
    {
        $ticket->fill(Arr::only($data, ['title', 'message', 'priority', 'status']));

        if (! $ticket->isDirty()) {
            return $ticket;
        }

        return DB::transaction(function () use ($ticket, $actor) {
            $statusChanged = $ticket->isDirty('status');

            if ($statusChanged) {
                // Đóng ticket thì ghi thời điểm đóng; mở lại thì xóa.
                $ticket->closed_at = $ticket->status === 'closed' ? now() : null;
            }

            $ticket->save();

            // Ticket mới / đang giải quyết không được có đánh giá (chỉ solved, closed mới có).
            if ($statusChanged && in_array($ticket->status, ['new', 'in_progress'], true)) {
                $ticket->rating?->delete();
            }

            $this->activities->record('update_ticket', $ticket, $actor); // lịch sử của staff

            $message = $statusChanged
                ? "{$actor->name} đã chuyển ticket {$ticket->track_id} của bạn sang trạng thái “" . config('helpdesk.status.' . $ticket->status) . '”.'
                : "{$actor->name} đã cập nhật ticket {$ticket->track_id} của bạn.";

            $this->activities->notifyCustomer($ticket->customer, 'update_ticket', $ticket, $message);

            return $ticket;
        });
    }

    // ---------- Xóa ticket ----------

    public function delete(Ticket $ticket, User $actor): void
    {
        DB::transaction(function () use ($ticket, $actor) {
            $customer = $ticket->customer;

            $ticket->delete(); // xóa mềm

            if ($actor->id === $ticket->customer_id) {
                $this->activities->notifyCustomer($actor, 'delete_ticket', $ticket, "Bạn đã xóa ticket {$ticket->track_id}.");

                return;
            }

            $this->activities->record('delete_ticket', $ticket, $actor); // lịch sử của staff
            $this->activities->notifyCustomer(
                $customer,
                'delete_ticket',
                $ticket,
                "{$actor->name} đã xóa ticket {$ticket->track_id} của bạn."
            );
        });
    }

    // ---------- Đánh giá ----------

    /**
     * Thêm hoặc cập nhật đánh giá (mỗi ticket một đánh giá).
     * Ticket đang "in_progress" mà customer đánh giá => tạo đánh giá và chuyển ticket sang "solved".
     * Ticket đã "solved" / "closed" => giữ nguyên trạng thái (không hạ "closed" về "solved").
     */
    public function rate(Ticket $ticket, User $customer, int $stars): Rating
    {
        return DB::transaction(function () use ($ticket, $customer, $stars) {
            // Khóa dòng ticket và đọc lại trạng thái mới nhất: tránh trường hợp staff vừa đổi trạng thái
            // (ví dụ về "new" / "in_progress" và xóa rating) trong lúc customer đang gửi đánh giá.
            $locked = Ticket::lockForUpdate()->findOrFail($ticket->id);

            abort_unless(in_array($locked->status, ['in_progress', 'solved', 'closed'], true), 403);

            if ($locked->status === 'in_progress') {
                $locked->status = 'solved';
                $locked->save();
                $ticket->status = 'solved'; // đồng bộ instance của controller
            }

            // ratings.ticket_id là UNIQUE nên đánh giá đã bị xóa mềm phải được khôi phục, không tạo dòng mới.
            $rating = Rating::withTrashed()->firstOrNew(['ticket_id' => $locked->id]);
            $rating->fill(['user_id' => $customer->id, 'stars' => $stars]);

            if ($rating->trashed()) {
                $rating->restore();
            }
            $rating->save();

            $this->activities->record('rate_ticket', $locked, $customer);

            return $rating;
        });
    }

    public function removeRating(Ticket $ticket, User $customer): void
    {
        DB::transaction(function () use ($ticket, $customer) {
            if ($ticket->rating?->delete()) {
                $this->activities->record('rate_ticket', $ticket, $customer);
            }
        });
    }
}
