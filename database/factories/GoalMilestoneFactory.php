<?php

namespace Database\Factories;

use App\Models\GoalMilestone;
use App\Models\PersonalGoal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GoalMilestone>
 */
class GoalMilestoneFactory extends Factory
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
            'title' => fake()->sentence(3),
            //
        ];
    }
}
