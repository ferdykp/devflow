<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GitHubActivityUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public int $projectId) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('projects.'.$this->projectId.'.github')];
    }

    public function broadcastAs(): string
    {
        return 'github.activity.updated';
    }

    public function broadcastWith(): array
    {
        return ['project_id' => $this->projectId];
    }
}
