<?php

namespace App\Models;

use Database\Factories\LearningAssessmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearningAssessment extends Model
{
    /** @use HasFactory<LearningAssessmentFactory> */
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['assessed_on' => 'date', 'score' => 'decimal:2', 'maximum_score' => 'decimal:2'];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(LearningLesson::class, 'learning_lesson_id');
    }
}
