<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectDocument extends Model
{
    protected $guarded = [];
    public function project() { return $this->belongsTo(Project::class); }
    public function versions() { return $this->hasMany(PrdVersion::class); }
}
