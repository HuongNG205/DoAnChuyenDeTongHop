<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;
// use Illuminate\Auth\Access\Response;

class TicketPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Xem chi tiết ticket. $user có thể null (khách chưa đăng nhập).
     * - Ticket đã đóng: công khai, ai cũng xem được (chỉ xem, không tương tác).
     * - Ticket chưa đóng: chỉ chủ ticket và staff/admin.
     */
    public function view(?User $user, Ticket $ticket): bool
    {
        if ($ticket->status === 'closed') {
            return true;
        }

        return $user !== null && ($user->isStaff() || $user->id === $ticket->customer_id);
    }

    /**
     * Chỉ khách hàng tạo ticket (staff/admin xử lý ticket, không tạo).
     */
    public function create(User $user): bool
    {
        return $user->role === 'customer';
    }

    /**
     * Trả lời ticket: chủ ticket hoặc staff/admin, và ticket chưa đóng.
     */
    public function reply(User $user, Ticket $ticket): bool
    {
        return $ticket->status !== 'closed'
            && ($user->isStaff() || $user->id === $ticket->customer_id);
    }

    /**
     * Cập nhật ticket: chỉ staff/admin (customer không được sửa ticket).
     */
    public function update(User $user, Ticket $ticket): bool
    {
        return $user->isStaff();
    }

    /**
     * Sửa tiêu đề / nội dung ticket: nhân viên (staff) bị cấm vì đó là nội dung của khách hàng;
     * chỉ admin được sửa.
     */
    public function updateContent(User $user, Ticket $ticket): bool
    {
        return $user->isAdmin();
    }

    /**
     * Đánh giá ticket (thêm / sửa / xóa): chỉ khách hàng là chủ ticket,
     * và ticket đang giải quyết, đã giải quyết hoặc đã đóng (ticket mới chưa được đánh giá).
     * Đánh giá khi ticket đang giải quyết sẽ chuyển ticket sang "đã giải quyết" (xem TicketService::rate).
     */
    public function rate(User $user, Ticket $ticket): bool
    {
        return $user->role === 'customer'
            && $user->id === $ticket->customer_id
            && in_array($ticket->status, ['in_progress', 'solved', 'closed'], true);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Ticket $ticket): bool
    {
        return $user->isStaff() || $user->id === $ticket->customer_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Ticket $ticket): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Ticket $ticket): bool
    {
        return false;
    }
}
