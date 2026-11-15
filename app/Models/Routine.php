<?php

namespace App\Models;

use Database\Factories\RoutineFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Routine extends Model
{
    /** @use HasFactory<RoutineFactory> */
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['days_of_week' => 'array'];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function goal(): BelongsTo
    {
        return $this->belongsTo(PersonalGoal::class, 'personal_goal_id');
    }

    public function learningPath(): BelongsTo
    {
        return $this->belongsTo(LearningPath::class);
    }

    public function checkIns(): HasMany
    {
        return $this->hasMany(RoutineCheckIn::class);
    }

    public function calendarEvents(): HasMany
    {
        return $this->hasMany(CalendarEvent::class);
    }
}
