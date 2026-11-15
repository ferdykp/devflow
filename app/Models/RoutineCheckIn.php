<?php

namespace App\Models;

use Database\Factories\RoutineCheckInFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoutineCheckIn extends Model
{
    /** @use HasFactory<RoutineCheckInFactory> */
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['occurred_on' => 'date'];
    }

    public function routine(): BelongsTo
    {
        return $this->belongsTo(Routine::class);
    }
}
