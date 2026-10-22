<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Resume\ProjectResumeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LaunchSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_callback_uses_user_authorized_repository_scope_and_rejects_unlisted_repository(): void
    {
        Http::preventStrayRequests();
        config(['services.github.app_id' => '42']);
        Http::fake([
            'https://github.com/login/oauth/access_token' => Http::response(['access_token' => 'user-token']),
            'https://api.github.com/user/installations*' => function ($request) {
                if (str_contains($request->url(), '/99/repositories')) {
                    return Http::response(['repositories' => [['id' => 7, 'full_name' => 'team/allowed', 'default_branch' => 'main']]]);
                }

                return Http::response(['installations' => [['id' => 99, 'app_id' => 42]]]);
            },
        ]);
        $user = User::factory()->create();
        $project = $user->workspaces()->create(['name' => 'Private'])->projects()->create(['name' => 'OAuth']);

        $this->actingAs($user)->withSession(['github_app_flow' => [
            'state' => hash('sha256', 'verified-state'), 'project_id' => $project->id,
            'user_id' => $user->id, 'created_at' => now()->timestamp,
        ]])->get('/github/callback?code=code&state=verified-state')->assertOk()->assertSee('team/allowed');

        Http::assertSent(fn ($request) => str_contains($request->url(), '/user/installations/99/repositories')
            && $request->hasHeader('Authorization', 'Bearer user-token'));
        Http::assertSentCount(3);
        $this->post(route('github.app.connect', $project), ['repository_id' => 999])->assertSessionHasErrors('repository_id');
        $this->assertDatabaseCount('github_repositories', 0);
        $this->post(route('github.app.connect', $project), ['repository_id' => 7])->assertRedirect();
        $this->assertDatabaseHas('github_repositories', ['project_id' => $project->id, 'github_id' => 7, 'installation_id' => 99]);
    }

    public function test_login_blocks_sixth_attempt_for_same_account_without_blocking_other_account(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post('/login', ['email' => 'blocked@example.test', 'password' => 'wrong'])->assertSessionHasErrors('email');
        }
        $this->post('/login', ['email' => 'blocked@example.test', 'password' => 'wrong'])->assertTooManyRequests();
        $this->post('/login', ['email' => 'other@example.test', 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_cross_project_task_and_document_mutations_are_rejected(): void
    {
        $user = User::factory()->create();
        $workspace = $user->workspaces()->create(['name' => 'Mine']);
        $first = $workspace->projects()->create(['name' => 'One']);
        $second = $workspace->projects()->create(['name' => 'Two']);
        $task = $second->tasks()->create(['title' => 'Untouched']);
        $document = $second->documents()->create(['title' => 'Private PRD']);
        $version = $document->versions()->create(['version_number' => 1, 'content_markdown' => 'Private']);

        $this->actingAs($user)->patchJson(route('tasks.update', [$first, $task]), ['status' => 'done'])->assertNotFound();
        $this->delete(route('tasks.destroy', [$first, $task]))->assertNotFound();
        $this->post(route('prd.approve', [$first, $document, $version]))->assertNotFound();
        $this->assertSame('todo', $task->fresh()->status);
        $this->assertSame('draft', $version->fresh()->status);
    }

    public function test_task_fields_are_escaped_and_unvalidated_ownership_is_ignored(): void
    {
        $user = User::factory()->create();
        $project = $user->workspaces()->create(['name' => 'Mine'])->projects()->create(['name' => 'Safe']);
        $title = '<script>alert(1)</script>';
        $this->actingAs($user)->post(route('tasks.store', $project), [
            'title' => $title, 'project_id' => 999, 'completed_at' => now()->toDateTimeString(),
        ])->assertSessionHasNoErrors();
        $task = $project->tasks()->firstOrFail();
        $this->assertNull($task->completed_at);
        $this->get(route('projects.show', ['project' => $project, 'section' => 'tasks']))
            ->assertOk()->assertSee($title)->assertDontSee($title, false);
    }

    public function test_security_headers_are_present_on_authentication_pages(): void
    {
        $this->get('/login')->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_malformed_login_email_is_validation_error_not_server_error(): void
    {
        $this->post('/login', ['email' => ['invalid'], 'password' => 'wrong'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_disconnect_is_project_scoped_and_preserves_activity_history(): void
    {
        $user = User::factory()->create();
        $workspace = $user->workspaces()->create(['name' => 'Mine']);
        $first = $workspace->projects()->create(['name' => 'One']);
        $second = $workspace->projects()->create(['name' => 'Two']);
        $repository = $second->repositories()->create(['full_name' => 'team/repo', 'github_id' => 123]);
        $second->activities()->create(['type' => 'commit', 'title' => 'History', 'source' => 'github', 'occurred_at' => now()]);

        $this->actingAs($user)->delete(route('github.disconnect', [$first, $repository]))->assertNotFound();
        $this->assertDatabaseCount('github_repositories', 1);
        $this->delete(route('github.disconnect', [$second, $repository]))->assertSessionHas('success');
        $this->assertDatabaseCount('github_repositories', 0);
        $this->assertDatabaseCount('activities', 1);
    }

    public function test_provider_failure_and_malformed_output_do_not_mutate_project(): void
    {
        Http::preventStrayRequests();
        config(['devflow_ai.enabled' => true, 'devflow_ai.api_key' => 'test-key']);
        $user = User::factory()->create();
        $project = $user->workspaces()->create(['name' => 'Mine'])->projects()->create(['name' => 'Advisory only']);
        Http::fake(['https://api.openai.com/*' => Http::response(['error' => 'Unavailable'], 503)]);
        $this->actingAs($user)->post(route('projects.insights.analyze', $project), ['mode' => 'progress', 'consent' => '1'])->assertSessionHasErrors('ai');
        Http::fake(['https://api.openai.com/*' => Http::response(['choices' => [['message' => ['content' => 'invalid-json']]]])]);
        $this->post(route('projects.insights.analyze', $project), ['mode' => 'progress', 'consent' => '1'])->assertSessionHasErrors('ai');
        $this->assertSame('Advisory only', $project->fresh()->name);
        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_resume_uses_bounded_queries_with_one_thousand_tasks(): void
    {
        $user = User::factory()->create();
        $project = $user->workspaces()->create(['name' => 'Mine'])->projects()->create(['name' => 'Scale']);
        $rows = [];
        for ($number = 0; $number < 1000; $number++) {
            $rows[] = ['project_id' => $project->id, 'title' => 'Task '.$number, 'status' => $number < 500 ? 'done' : 'todo', 'weight' => 1, 'created_at' => now(), 'updated_at' => now()];
        }
        DB::table('tasks')->insert($rows);
        DB::enableQueryLog();
        $summary = app(ProjectResumeService::class)->build($project);
        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();
        $this->assertSame(1000, $summary['task_count']);
        $this->assertSame(50.0, $summary['progress']);
        $this->assertCount(6, $summary['open_tasks']);
        $this->assertLessThanOrEqual(15, $queryCount);
    }

    public function test_task_editor_rejects_cross_project_and_malformed_identifiers(): void
    {
        $user = User::factory()->create();
        $workspace = $user->workspaces()->create(['name' => 'Mine']);
        $first = $workspace->projects()->create(['name' => 'One']);
        $second = $workspace->projects()->create(['name' => 'Two']);
        $task = $second->tasks()->create(['title' => 'Only second project']);
        $this->actingAs($user)->get(route('projects.show', ['project' => $first, 'section' => 'tasks', 'edit' => $task->id]))->assertNotFound();
        $this->getJson(route('projects.show', ['project' => $first, 'section' => 'tasks', 'edit' => ['invalid']]))->assertUnprocessable()->assertJsonValidationErrors('edit');
        $this->get(route('projects.show', ['project' => $second, 'section' => 'tasks', 'view' => 'list', 'edit' => $task->id]))->assertOk()->assertSee('value="Only second project"', false);
    }
}
