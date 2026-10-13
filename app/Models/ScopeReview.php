<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScopeReview extends Model
{
    protected $guarded = [];
    public function decisions() { return $this->hasMany(ScopeDecision::class); }
    public function project() { return $this->belongsTo(Project::class); }
}
