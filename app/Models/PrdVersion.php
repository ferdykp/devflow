<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;

class PrdVersion extends Model
{
    protected $guarded = [];

    protected static function booted(): void
    {
        static::updating(function (PrdVersion $version): void {
            if (in_array($version->getOriginal('status'), ['approved', 'superseded'], true)
                && $version->isDirty(['content_markdown', 'content_hash', 'version_number', 'project_document_id', 'approved_by', 'approved_at'])) {
                throw new LogicException('Approved PRD version records are immutable. Create a new draft revision.');
            }
        });

        static::deleting(function (PrdVersion $version): void {
            if (in_array($version->status, ['approved', 'superseded'], true)) {
                throw new LogicException('Approved PRD versions cannot be deleted.');
            }
        });
    }

    protected function casts(): array
    {
        return ['approved_at' => 'datetime'];
    }

    public function document()
    {
        return $this->belongsTo(ProjectDocument::class, 'project_document_id');
    }

    public function changeRequests(): HasMany
    {
        return $this->hasMany(ChangeRequest::class, 'source_prd_version_id');
    }
}
