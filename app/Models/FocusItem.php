<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FocusItem extends Model
{
    protected $guarded = [];
    protected function casts(): array { return ['focus_date'=>'date']; }
    public function task() { return $this->belongsTo(Task::class); }
}
