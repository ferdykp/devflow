<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Workspace extends Model
{
    protected $guarded = [];
    public function projects() { return $this->hasMany(Project::class); }
    public function user() { return $this->belongsTo(User::class); }
}
