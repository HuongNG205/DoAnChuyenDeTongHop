<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    private const TITLES = [
        'Không đăng nhập được vào hệ thống',
        'Quên mật khẩu tài khoản sinh viên',
        'Lịch học bị trùng giữa hai môn',
        'Không xem được điểm học kỳ vừa rồi',
        'Lỗi khi đăng ký học phần',
        'Xin cấp lại thẻ sinh viên',
        'Wifi trong ký túc xá rất chậm',
        'Email trường không nhận được thư',
        'Cần xác nhận sinh viên để làm hồ sơ',
        'Máy chiếu phòng học bị hỏng',
        'Không tải được tài liệu môn học',
        'Sai thông tin cá nhân trên hệ thống',
    ];

    private const PARAGRAPHS = [
        'Em đã thử nhiều lần nhưng vẫn không được, mong thầy cô hỗ trợ giúp em.',
        'Sự cố bắt đầu xảy ra từ sáng hôm qua và đến giờ vẫn chưa khắc phục được.',
        'Em dùng trình duyệt Chrome trên Windows 11, thông báo lỗi hiện ra như hình đính kèm.',
        'Mong bên hỗ trợ phản hồi sớm vì em cần xử lý trước hạn nộp hồ sơ.',
        'Em đã làm theo hướng dẫn trên trang chủ nhưng kết quả vẫn không thay đổi.',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $createdAt = fake()->dateTimeBetween('-60 days', '-1 day');

        return [
            'track_id' => Ticket::generateTrackId(),
            'customer_id' => User::factory()->customer(),
            'title' => fake()->randomElement(self::TITLES),
            'message' => $this->html(fake()->numberBetween(1, 3)),
            'priority' => fake()->randomElement(['low', 'medium', 'high']),
            'status' => 'new',
            'closed_at' => null,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ];
    }

    /** Sinh nội dung HTML (giống dữ liệu rich editor Quill). */
    public function html(int $paragraphs = 2): string
    {
        $items = fake()->randomElements(self::PARAGRAPHS, $paragraphs);

        return collect($items)->map(fn ($p) => "<p>{$p}</p>")->implode('');
    }

    public function status(string $status): static
    {
        return $this->state(fn () => [
            'status' => $status,
            'closed_at' => $status === 'closed' ? now() : null,
        ]);
    }
}
