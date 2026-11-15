<?php

namespace App\Models;

use Database\Factories\CalendarEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CalendarEvent extends Model
{
    /** @use HasFactory<CalendarEventFactory> */
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'starts_at' => 'immutable_datetime',
            'ends_at' => 'immutable_datetime',
            'event_date' => 'date',
            'all_day' => 'boolean',
            'is_flexible' => 'boolean',
            'recurrence_rule' => 'array',
            'actual_duration_minutes' => 'integer',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function routine(): BelongsTo
    {
        return $this->belongsTo(Routine::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(LearningLesson::class, 'learning_lesson_id');
    }

    public function goal(): BelongsTo
    {
        return $this->belongsTo(PersonalGoal::class, 'personal_goal_id');
    }

    public function exceptions(): HasMany
    {
        return $this->hasMany(CalendarEventException::class);
    }
}
