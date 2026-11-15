<?php

namespace Database\Factories;

use App\Models\CoachRecommendation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CoachRecommendation>
 */
class CoachRecommendationFactory extends Factory
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
            'kind' => 'workload',
            'pending_key' => 'workload',
            'title' => fake()->sentence(4),
            'explanation' => fake()->sentence(),
            'status' => 'pending',
            //
        ];
    }
}
