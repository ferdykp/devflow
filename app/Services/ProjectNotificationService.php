<?php

namespace App\Services;

use App\Models\Project;
use App\Notifications\WorkspaceActivityNotification;

class ProjectNotificationService
{
    public function notifyParticipants(Project $project, int $actorId, string $event, string $title, string $message, string $url): void
    {
        $project->loadMissing('workspace');
        $recipients = $project->workspace->participants()->reject(fn ($user): bool => $user->id === $actorId);
        $notification = new WorkspaceActivityNotification($event, $title, $message, $url);

        $recipients->each(fn ($user) => $user->notify(clone $notification));
    }
}
