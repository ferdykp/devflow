<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\WeeklyReview;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WeeklyReview>
 */
class WeeklyReviewFactory extends Factory
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
            'week_starts_on' => today()->startOfWeek()->toDateString(),
            //
        ];
    }
}
