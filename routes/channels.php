<?php

use App\Models\Project;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('projects.{projectId}.github', function ($user, int $projectId): bool {
    return Project::query()
        ->whereKey($projectId)
        ->whereHas('workspace', fn ($query) => $query->accessibleTo($user->id))
        ->exists();
});
