<?php

namespace Database\Factories;

use App\Models\GoalProgressEntry;
use App\Models\PersonalGoal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GoalProgressEntry>
 */
class GoalProgressEntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'personal_goal_id' => PersonalGoal::factory(),
            'value' => fake()->randomFloat(2, 0, 100),
            'recorded_on' => today()->toDateString(),
            //
        ];
    }
}
