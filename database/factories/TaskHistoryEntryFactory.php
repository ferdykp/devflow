<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\TaskHistoryEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TaskHistoryEntry>
 */
class TaskHistoryEntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'task_id' => Task::factory(),
            'user_id' => User::factory(),
            'action' => 'updated',
            //
        ];
    }
}
