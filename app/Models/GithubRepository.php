<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GithubRepository extends Model
{
    protected $guarded = [];
    protected function casts(): array { return ['last_synced_at'=>'datetime']; }
    public function project() { return $this->belongsTo(Project::class); }
}
