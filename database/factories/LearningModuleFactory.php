<?php

namespace Database\Factories;

use App\Models\LearningModule;
use App\Models\LearningPath;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LearningModule>
 */
class LearningModuleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'learning_path_id' => LearningPath::factory(),
            'title' => fake()->sentence(3),
            'sort_order' => 0,
            //
        ];
    }
}
