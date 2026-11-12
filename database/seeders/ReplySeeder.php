<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Reply;
use App\Models\Ticket;
use App\Models\User;

class ReplySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $staffIds = User::where('role', 'staff')->pluck('id');

        foreach (Ticket::with('customer')->get() as $ticket) {
            // New: chỉ có thể không có reply hoặc có reply của customer.
            if ($ticket->status === 'new') {
                if (fake()->boolean(65)) {
                    Reply::factory()->create([
                        'ticket_id' => $ticket->id,
                        'user_id' => $ticket->customer_id,
                    ]);
                }
                continue;
            }

            // Các trạng thái còn lại có thể có phản hồi từ customer và/hoặc staff.
            if (fake()->boolean(75)) {
                Reply::factory()->create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $ticket->customer_id,
                ]);
            }

            if (fake()->boolean(85)) {
                Reply::factory()->create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $staffIds->random(),
                ]);
            }
        }
    }
}
