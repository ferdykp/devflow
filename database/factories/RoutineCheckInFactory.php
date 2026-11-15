<?php

namespace Database\Factories;

use App\Models\Routine;
use App\Models\RoutineCheckIn;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoutineCheckIn>
 */
class RoutineCheckInFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'routine_id' => Routine::factory(),
            'occurred_on' => today()->toDateString(),
            'status' => 'completed',
            //
        ];
    }
}
