<?php

namespace App\Models;

use Database\Factories\GoalProgressEntryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoalProgressEntry extends Model
{
    /** @use HasFactory<GoalProgressEntryFactory> */
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['value' => 'decimal:3', 'recorded_on' => 'date'];
    }

    public function goal(): BelongsTo
    {
        return $this->belongsTo(PersonalGoal::class, 'personal_goal_id');
    }
}
