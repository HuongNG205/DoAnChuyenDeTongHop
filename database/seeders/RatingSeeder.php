<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Ticket;
use App\Models\Rating;

class RatingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // New và in_progress tuyệt đối không được có rating.
        // Solved và closed chỉ có thể có tối đa một rating mỗi ticket.
        Ticket::whereIn('status', ['solved', 'closed'])->get()->each(function (Ticket $ticket) {
            if (fake()->boolean(70)) {
                Rating::factory()->create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $ticket->customer_id,
                ]);
            }
        });
    }
}
