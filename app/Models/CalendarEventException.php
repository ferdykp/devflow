<?php

namespace App\Models;

use Database\Factories\CalendarEventExceptionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalendarEventException extends Model
{
    /** @use HasFactory<CalendarEventExceptionFactory> */
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'original_starts_at' => 'immutable_datetime',
            'new_starts_at' => 'immutable_datetime',
            'new_ends_at' => 'immutable_datetime',
            'actual_duration_minutes' => 'integer',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(CalendarEvent::class, 'calendar_event_id');
    }
}
