<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $guarded = [];
    protected function casts(): array { return ['due_date'=>'date','completed_at'=>'datetime']; }
    public function project() { return $this->belongsTo(Project::class); }
    public function milestone() { return $this->belongsTo(Milestone::class); }
}
