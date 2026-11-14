<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;

/**
 * Lớp cha của các email hệ thống. Email được đưa vào hàng đợi (ShouldQueue)
 * để request của người dùng không phải chờ SMTP của Gmail (thường 1-3 giây).
 */
abstract class HelpdeskMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /** Thử lại tối đa 3 lần nếu SMTP lỗi tạm thời. */
    public int $tries = 3;

    /** Chờ 30 giây rồi 2 phút giữa các lần thử lại. */
    public array $backoff = [30, 120];

    /**
     * SUPPORT_EMAIL hợp lệ, hoặc null nếu chưa cấu hình.
     * (Giá trị mặc định "*@gmail.com" trong config là placeholder nên bị loại.)
     */
    public static function supportEmail(): ?string
    {
        $email = trim((string) config('helpdesk.support_email'));

        return $email !== '' && ! str_contains($email, '*') && filter_var($email, FILTER_VALIDATE_EMAIL)
            ? $email
            : null;
    }

    /**
     * Reply-To = SUPPORT_EMAIL (nếu có), để khách bấm "Trả lời" trong Gmail
     * thì thư về hộp thư hỗ trợ.
     *
     * @return array<int, Address>
     */
    protected function supportReplyTo(): array
    {
        $email = static::supportEmail();

        return $email ? [new Address($email, config('app.name'))] : [];
    }
}
