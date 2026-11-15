<?php

namespace Database\Factories;

use App\Models\CalendarEvent;
use App\Models\CalendarEventException;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CalendarEventException>
 */
class CalendarEventExceptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'calendar_event_id' => CalendarEvent::factory(),
            'original_starts_at' => now()->addDay()->startOfHour()->utc(),
            'action' => 'cancelled',
            //
        ];
    }
}
