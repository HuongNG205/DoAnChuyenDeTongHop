<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Activity;
use App\Models\Ticket;
use App\Models\User;

class ActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::pluck('id');
        $tickets = Ticket::pluck('id');

        if ($users->isEmpty()) {
            return;
        }

        // Hoạt động hệ thống không gắn ticket và một số hoạt động gắn ticket.
        Activity::factory()->count(30)->create([
            'notify_user_id' => fn () => $users->random(),
        ]);

        if ($tickets->isNotEmpty()) {
            Activity::factory()->count(50)->create([
                'ticket_id' => fn () => $tickets->random(),
                'notify_user_id' => fn () => $users->random(),
                'action' => 'reply_ticket',
                'message' => 'Có phản hồi mới trong ticket.',
            ]);
        }
    }
}
