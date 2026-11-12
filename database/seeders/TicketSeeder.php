<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Ticket;
use App\Models\User;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customerIds = User::where('role', 'customer')->pluck('id');

        $scenarios = [
            'new' => 15,
            'in_progress' => 15,
            'solved' => 10,
            'closed' => 10,
        ];

        foreach ($scenarios as $status => $count) {
            Ticket::factory()->count($count)->status($status)->create(['customer_id' => fn () => $customerIds->random()]);
        }
    }
}
