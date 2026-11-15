<?php

namespace App\Models;

use Database\Factories\LearningModuleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearningModule extends Model
{
    /** @use HasFactory<LearningModuleFactory> */
    use HasFactory;

    protected $guarded = [];

    public function path(): BelongsTo
    {
        return $this->belongsTo(LearningPath::class, 'learning_path_id');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(LearningLesson::class)->orderBy('id');
    }
}
