<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScopeReview extends Model
{
    protected $guarded = [];

    public function decisions()
    {
        return $this->hasMany(ScopeDecision::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function prdVersion(): BelongsTo
    {
        return $this->belongsTo(PrdVersion::class);
    }

    public function changeRequests(): HasMany
    {
        return $this->hasMany(ChangeRequest::class);
    }
}
