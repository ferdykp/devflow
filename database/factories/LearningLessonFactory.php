<?php

namespace Database\Factories;

use App\Models\LearningLesson;
use App\Models\LearningModule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LearningLesson>
 */
class LearningLessonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'learning_module_id' => LearningModule::factory(),
            'title' => fake()->sentence(4),
            'status' => 'not_started',
            //
        ];
    }
}
