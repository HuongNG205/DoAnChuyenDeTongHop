<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Ticket;
use App\Models\User;

/**
 * Quy ước bảng activities: message != null => thông báo hiện ở chuông trên header của notify_user_id;
 * message = null => chỉ là dòng lịch sử hoạt động.
 */
class ActivityService
{
    /** Ghi một dòng vào bảng activities (message = null: chỉ là lịch sử, không phải thông báo). */
    public function record(string $action, ?Ticket $ticket, User $user, ?string $message = null): Activity
    {
        return Activity::create([
            'ticket_id' => $ticket?->id,
            'action' => $action,
            'message' => $message,
            'notify_user_id' => $user->id,
        ]);
    }

    /** Tạo thông báo (hiện ở chuông trên header) cho customer. Bỏ qua nếu customer không còn tồn tại / đã bị xóa. */
    public function notifyCustomer(?User $customer, string $action, Ticket $ticket, string $message): void
    {
        if ($customer && ! $customer->trashed()) {
            $this->record($action, $ticket, $customer, $message);
        }
    }

    /** Đánh dấu đã đọc toàn bộ thông báo chưa đọc của user (gọi khi user bấm vào biểu tượng chuông). */
    public function markAllRead(User $user): int
    {
        return Activity::where('notify_user_id', $user->id)
            ->whereNotNull('message')
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }
}
