<?php

namespace App\Models;

use Database\Factories\LearningPathFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearningPath extends Model
{
    /** @use HasFactory<LearningPathFactory> */
    use HasFactory;

    protected $guarded = [];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function goal(): BelongsTo
    {
        return $this->belongsTo(PersonalGoal::class, 'personal_goal_id');
    }

    public function modules(): HasMany
    {
        return $this->hasMany(LearningModule::class)->orderBy('sort_order');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(LearningSession::class)->latest('occurred_on');
    }
}
