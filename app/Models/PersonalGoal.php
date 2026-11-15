<?php

namespace App\Models;

use Database\Factories\PersonalGoalFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PersonalGoal extends Model
{
    /** @use HasFactory<PersonalGoalFactory> */
    use HasFactory;

    protected $guarded = [];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(GoalMilestone::class)->orderBy('sort_order')->orderBy('id');
    }

    public function progressEntries(): HasMany
    {
        return $this->hasMany(GoalProgressEntry::class)->orderByDesc('recorded_on');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function routines(): HasMany
    {
        return $this->hasMany(Routine::class);
    }

    public function learningPaths(): HasMany
    {
        return $this->hasMany(LearningPath::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(PersonalNote::class);
    }

    public function calendarEvents(): HasMany
    {
        return $this->hasMany(CalendarEvent::class);
    }

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'target_date' => 'date',
            'next_review_on' => 'date',
            'current_value' => 'decimal:3',
            'target_value' => 'decimal:3',
        ];
    }
}
