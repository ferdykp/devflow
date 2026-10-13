<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $guarded = [];
    public function events() { return $this->hasMany(ScopeEvent::class); }
    public function workspace() { return $this->belongsTo(Workspace::class); }
    public function tasks() { return $this->hasMany(Task::class); }
    public function milestones() { return $this->hasMany(Milestone::class); }
    public function notes() { return $this->hasMany(ProjectNote::class); }
    public function backlogItems() { return $this->hasMany(BacklogItem::class); }
    public function documents() { return $this->hasMany(ProjectDocument::class); }
    public function activities() { return $this->hasMany(Activity::class); }
    public function repositories() { return $this->hasMany(GithubRepository::class); }
    public function reviews() { return $this->hasMany(ScopeReview::class); }
    public function baseline() { return $this->hasOne(ScopeBaseline::class); }
}
