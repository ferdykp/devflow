<?php

namespace App\Models;

use Database\Factories\GoalMilestoneFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoalMilestone extends Model
{
    /** @use HasFactory<GoalMilestoneFactory> */
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['target_date' => 'date', 'completed_at' => 'datetime'];
    }

    public function goal(): BelongsTo
    {
        return $this->belongsTo(PersonalGoal::class, 'personal_goal_id');
    }
}
