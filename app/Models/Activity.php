<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $guarded = [];
    protected function casts(): array { return ['metadata'=>'array','occurred_at'=>'datetime']; }
    public function project() { return $this->belongsTo(Project::class); }
}
