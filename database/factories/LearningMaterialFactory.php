<?php

namespace Database\Factories;

use App\Models\LearningLesson;
use App\Models\LearningMaterial;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LearningMaterial>
 */
class LearningMaterialFactory extends Factory
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
            'original_name' => 'example.pdf',
            'storage_path' => 'private/learning/example.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 1024,
            //
        ];
    }
}
