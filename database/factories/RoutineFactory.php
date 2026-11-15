<?php

namespace Database\Factories;

use App\Models\Routine;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Routine>
 */
class RoutineFactory extends Factory
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
            'frequency' => 'daily',
            'target_per_week' => 7,
            'estimated_minutes' => 20,
            'status' => 'active',
            //
        ];
    }
}
