<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\TaskComment;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<TaskComment> */
class TaskCommentFactory extends Factory
{
    public function definition(): array
    {
        return ['task_id' => Task::factory(), 'user_id' => fn (array $attributes) => Task::findOrFail($attributes['task_id'])->project->workspace->user_id, 'body' => fake()->paragraph()];
    }
}
