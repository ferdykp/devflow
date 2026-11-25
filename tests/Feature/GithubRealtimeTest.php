<?php

namespace Tests\Feature;

use App\Events\GitHubActivityUpdated;
use App\Jobs\SyncGitHubRepository;
use App\Models\User;
use App\Services\GitHub\GitHubActivitySync;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GithubRealtimeTest extends TestCase
{
    use RefreshDatabase;

    public function test_github_page_subscribes_to_private_realtime_activity_channel(): void
    {
        $user = User::factory()->create();
        $project = $user->workspaces()->create(['name' => 'Personal'])
            ->projects()->create(['name' => 'Demo']);
        $project->repositories()->create(['github_id' => 123, 'full_name' => 'owner/repo']);

        $this->actingAs($user)
            ->get(route('projects.show', ['project' => $project, 'section' => 'github']))
            ->assertOk()
            ->assertSee('data-github-channel="projects.'.$project->id.'.github"', false)
            ->assertSee('tambahkan webhook push');
    }

    public function test_sync_broadcasts_only_when_new_github_activities_are_saved(): void
    {
        $user = User::factory()->create();
        $project = $user->workspaces()->create(['name' => 'Personal'])
            ->projects()->create(['name' => 'Demo']);
        $repository = $project->repositories()->create(['github_id' => 123, 'full_name' => 'owner/repo']);
        Event::fake([GitHubActivityUpdated::class]);
        Http::preventStrayRequests();
        Http::fake(['api.github.com/repositories/123/commits*' => Http::response([
            [
                'sha' => str_repeat('a', 40),
                'html_url' => 'https://github.com/owner/repo/commit/'.str_repeat('a', 40),
                'commit' => ['message' => 'Add feature', 'committer' => ['date' => now()->toIso8601String()]],
            ],
        ], 200)]);

        (new SyncGitHubRepository($repository->id))->handle(app(GitHubActivitySync::class));

        $this->assertDatabaseHas('activities', ['project_id' => $project->id, 'source' => 'github']);
        Event::assertDispatched(GitHubActivityUpdated::class, fn (GitHubActivityUpdated $event): bool => $event->projectId === $project->id);
    }

    public function test_sync_does_not_broadcast_when_github_has_no_new_activities(): void
    {
        $user = User::factory()->create();
        $project = $user->workspaces()->create(['name' => 'Personal'])
            ->projects()->create(['name' => 'Demo']);
        $repository = $project->repositories()->create(['github_id' => 123, 'full_name' => 'owner/repo']);
        Event::fake([GitHubActivityUpdated::class]);
        Http::preventStrayRequests();
        Http::fake(['api.github.com/repositories/123/commits*' => Http::response([], 200)]);

        (new SyncGitHubRepository($repository->id))->handle(app(GitHubActivitySync::class));

        $this->assertDatabaseCount('activities', 0);
        Event::assertNotDispatched(GitHubActivityUpdated::class);
    }

    public function test_private_github_channel_is_limited_to_project_members(): void
    {
        $owner = User::factory()->create();
        $project = $owner->workspaces()->create(['name' => 'Private'])
            ->projects()->create(['name' => 'Demo']);
        $otherUser = User::factory()->create();
        $authorizeChannel = Broadcast::getChannels()->get('projects.{projectId}.github');

        $this->assertFalse($authorizeChannel($otherUser, $project->id));
        $this->assertTrue($authorizeChannel($owner, $project->id));
    }
}
