<?php

namespace Database\Factories;

use App\Models\LearningPath;
use App\Models\LearningSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LearningSession>
 */
class LearningSessionFactory extends Factory
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
            'learning_path_id' => LearningPath::factory(),
            'session_type' => 'study',
            'duration_minutes' => fake()->numberBetween(10, 60),
            'occurred_on' => today()->toDateString(),
            //
        ];
    }
}
