<?php

namespace Database\Factories;

use App\Models\Reply;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reply>
 */
class ReplyFactory extends Factory
{
    protected $model = Reply::class;

    private const STAFF_LINES = [
        'Chào bạn, bên mình đã tiếp nhận và đang kiểm tra sự cố này.',
        'Bạn vui lòng thử đăng xuất rồi đăng nhập lại, sau đó báo kết quả giúp mình nhé.',
        'Sự cố đã được khắc phục, bạn kiểm tra lại giúp mình.',
        'Bạn có thể gửi thêm thông tin về thiết bị và trình duyệt đang dùng không?',
    ];

    private const CUSTOMER_LINES = [
        'Em đã thử theo hướng dẫn nhưng vẫn bị lỗi như cũ ạ.',
        'Em cảm ơn, em đã dùng được rồi ạ.',
        'Em dùng điện thoại Android, trình duyệt Chrome ạ.',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ticket_id' => Ticket::factory(),
            'user_id' => User::factory()->staff(),
            'message' => '<p>' . fake()->randomElement(self::STAFF_LINES) . '</p>',
            'created_at' => now(),
        ];
    }

    public function byStaff(?int $staffId = null): static
    {
        return $this->state(fn () => [
            'user_id' => $staffId ?? User::whereIn('role', ['staff', 'admin'])->inRandomOrder()->value('id'),
            'message' => '<p>' . fake()->randomElement(self::STAFF_LINES) . '</p>',
        ]);
    }

    public function byCustomer(): static
    {
        return $this->state(function (array $attributes) {
            $ticket = Ticket::findOrFail($attributes['ticket_id']);

            return [
                'user_id' => $ticket->customer_id,
                'message' => '<p>' . fake()->randomElement(self::CUSTOMER_LINES) . '</p>',
            ];
        });
    }
}
