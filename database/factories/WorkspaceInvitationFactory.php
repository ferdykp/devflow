<?php

namespace Database\Factories;

use App\Models\Workspace;
use App\Models\WorkspaceInvitation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<WorkspaceInvitation> */
class WorkspaceInvitationFactory extends Factory
{
    public function definition(): array
    {
        return ['workspace_id' => Workspace::factory(), 'email' => fake()->unique()->safeEmail(), 'token_hash' => hash('sha256', Str::random(64)), 'expires_at' => now()->addDays(7)];
    }
}
