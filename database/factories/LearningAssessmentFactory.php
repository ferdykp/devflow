<?php

namespace Database\Factories;

use App\Models\LearningAssessment;
use App\Models\LearningLesson;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LearningAssessment>
 */
class LearningAssessmentFactory extends Factory
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
            'learning_lesson_id' => LearningLesson::factory(),
            'title' => fake()->sentence(3),
            'assessed_on' => today()->toDateString(),
            //
        ];
    }
}
