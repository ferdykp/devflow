<?php

namespace App\Models;

use Database\Factories\CoachRecommendationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoachRecommendation extends Model
{
    /** @use HasFactory<CoachRecommendationFactory> */
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'proposed_action' => 'array',
            'confirmed_at' => 'datetime',
            'dismissed_at' => 'datetime',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
