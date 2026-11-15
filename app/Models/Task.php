<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class);
    }

    public function taskImport(): BelongsTo
    {
        return $this->belongsTo(DocumentTaskImport::class, 'document_task_import_id');
    }

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'completed_at' => 'datetime',
            'checklist' => 'array',
            'tags' => 'array',
            'recurrence_rule' => 'array',
            'scheduled_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function personalGoal(): BelongsTo
    {
        return $this->belongsTo(PersonalGoal::class, 'personal_goal_id');
    }

    public function learningLesson(): BelongsTo
    {
        return $this->belongsTo(LearningLesson::class, 'learning_lesson_id');
    }

    public function calendarEvents(): HasMany
    {
        return $this->hasMany(CalendarEvent::class);
    }

    public function history(): HasMany
    {
        return $this->hasMany(TaskHistoryEntry::class)->latest();
    }

    public function milestone(): BelongsTo
    {
        return $this->belongsTo(Milestone::class);
    }
}
