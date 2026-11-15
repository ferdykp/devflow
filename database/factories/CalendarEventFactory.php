<?php

namespace Database\Factories;

use App\Models\CalendarEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CalendarEvent>
 */
class CalendarEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'owner_id' => User::factory(),
            'title' => fake()->sentence(3),
            'starts_at' => now()->addDay()->startOfHour()->utc(),
            'ends_at' => now()->addDay()->startOfHour()->addHour()->utc(),
            'timezone' => 'Asia/Jakarta',
            'status' => 'planned',
            //
        ];
    }
}
