<?php

namespace App\Models;

use Database\Factories\DailyReflectionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyReflection extends Model
{
    /** @use HasFactory<DailyReflectionFactory> */
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['reflection_date' => 'date', 'incomplete_task_ids' => 'array'];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
