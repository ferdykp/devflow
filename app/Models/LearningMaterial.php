<?php

namespace App\Models;

use Database\Factories\LearningMaterialFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearningMaterial extends Model
{
    /** @use HasFactory<LearningMaterialFactory> */
    use HasFactory;

    protected $guarded = [];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(LearningLesson::class, 'learning_lesson_id');
    }
}
