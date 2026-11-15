<?php

namespace Database\Factories;

use App\Models\PersonalGoal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PersonalGoal>
 */
class PersonalGoalFactory extends Factory
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
            'title' => fake()->sentence(4),
            'description' => fake()->sentence(),
            'horizon' => fake()->randomElement(['short', 'medium', 'long']),
            'progress_mode' => 'milestones',
            'status' => 'active',
            //
        ];
    }
}
