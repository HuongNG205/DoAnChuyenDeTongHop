<?php

namespace App\Services\Concerns;

use App\Models\User;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Support\Facades\Mail;

trait QueuesMail
{
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
