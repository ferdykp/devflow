<?php

namespace App\Models;

use Database\Factories\ChangeRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChangeRequest extends Model
{
    /** @use HasFactory<ChangeRequestFactory> */
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['decided_at' => 'datetime'];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function scopeReview(): BelongsTo
    {
        return $this->belongsTo(ScopeReview::class);
    }

    public function sourcePrdVersion(): BelongsTo
    {
        return $this->belongsTo(PrdVersion::class, 'source_prd_version_id');
    }

    public function draftPrdVersion(): BelongsTo
    {
        return $this->belongsTo(PrdVersion::class, 'draft_prd_version_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function decider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}
