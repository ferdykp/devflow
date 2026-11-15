<?php

namespace Database\Factories;

use App\Models\LearningPath;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LearningPath>
 */
class LearningPathFactory extends Factory
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
            'subject' => fake()->word(),
            'status' => 'active',
            //
        ];
    }
}
