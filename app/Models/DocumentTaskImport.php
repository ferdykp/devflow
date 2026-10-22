<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentTaskImport extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['draft_tasks' => 'array', 'committed_at' => 'datetime'];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(PrdVersion::class, 'prd_version_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
