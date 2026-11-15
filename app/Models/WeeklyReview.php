<?php

namespace App\Models;

use Database\Factories\WeeklyReviewFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeeklyReview extends Model
{
    /** @use HasFactory<WeeklyReviewFactory> */
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'week_starts_on' => 'date',
            'carry_forward_task_ids' => 'array',
            'planned_commitments' => 'array',
            'reviewed_at' => 'datetime',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
