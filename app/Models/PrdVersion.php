<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrdVersion extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['approved_at' => 'datetime'];
    }

    public function document()
    {
        return $this->belongsTo(ProjectDocument::class, 'project_document_id');
    }
}
