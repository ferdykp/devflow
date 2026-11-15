<?php

namespace App\Models;

use Database\Factories\LearningLessonFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearningLesson extends Model
{
    /** @use HasFactory<LearningLessonFactory> */
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['status_updated_at' => 'datetime', 'next_review_at' => 'datetime', 'knowledge_gap' => 'boolean'];
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(LearningModule::class, 'learning_module_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(LearningSession::class, 'learning_lesson_id')->latest('occurred_on');
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(LearningAssessment::class, 'learning_lesson_id')->latest('assessed_on');
    }

    public function materials(): HasMany
    {
        return $this->hasMany(LearningMaterial::class, 'learning_lesson_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'learning_lesson_id');
    }

    public function calendarEvents(): HasMany
    {
        return $this->hasMany(CalendarEvent::class, 'learning_lesson_id');
    }
}
