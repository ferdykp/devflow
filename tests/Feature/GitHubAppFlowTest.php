<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GitHubAppFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_authorize_github_app(): void
    {
        $this->get('/projects/1/github/authorize')->assertRedirect(route('login'));
    }

    public function test_github_app_callback_requires_valid_session_state(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/github/callback?code=bogus&state=bogus')
            ->assertForbidden();
    }

    public function test_github_app_connect_rejects_missing_verified_selection(): void
    {
        $user = User::factory()->create();
        $workspace = $user->workspaces()->create(['name' => 'My workspace']);
        $project = $workspace->projects()->create(['name' => 'Private source']);

        $this->actingAs($user)->post(route('github.app.connect', $project), ['repository_id' => 123])
            ->assertSessionHasErrors('repository_id');
        $this->assertDatabaseCount('github_repositories', 0);
    }
}
