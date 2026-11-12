<?php

namespace Database\Factories;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Activity>
 */
class ActivityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ticket_id' => null,
            'action' => fake()->randomElement(['login', 'logout']),
            'message' => null,
            'notify_user_id' => null,
            'is_read' => false,
            'created_at' => fake()->dateTimeBetween('-60 days', 'now'),
        ];
    }
}
