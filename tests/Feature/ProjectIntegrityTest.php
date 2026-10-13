<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_and_workspace_name_fields_have_distinct_html_ids(): void
    {
        $user = \App\Models\User::factory()->create();

        $this->actingAs($user)->get(route('projects.index'))
            ->assertOk()
            ->assertSee('id="workspace_name"', false)
            ->assertSee('for="workspace_name"', false);
    }

    public function test_creating_workspace_redirects_to_projects_and_populates_project_selector(): void
    {
        $user = User::factory()->create();
        $workspaceName = 'E2E Workspace Example';

        $this->actingAs($user)
            ->from(route('projects.index'))
            ->post(route('workspaces.store'), ['name' => $workspaceName])
            ->assertRedirect(route('projects.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('workspaces', [
            'user_id' => $user->id,
            'name' => $workspaceName,
        ]);

        $this->actingAs($user)->get(route('projects.index'))
            ->assertOk()
            ->assertSee($workspaceName);
    }

    public function test_project_can_be_created_without_prd_or_github(): void
    {
        $user = User::factory()->create();
        $workspace = $user->workspaces()->create(['name' => 'Personal']);

        $this->actingAs($user)->post(route('projects.store'), [
            'workspace_id' => $workspace->id,
            'name' => 'My experiment',
        ])->assertRedirect();

        $project = Project::query()->where('name', 'My experiment')->firstOrFail();
        $this->assertSame($workspace->id, $project->workspace_id);
        $this->assertSame(0, $project->documents()->count());
        $this->assertSame(0, $project->repositories()->count());
    }

    public function test_users_cannot_access_or_create_projects_inside_others_workspaces(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $workspace = $owner->workspaces()->create(['name' => 'Private']);
        $project = $workspace->projects()->create(['name' => 'Private project']);

        $this->actingAs($attacker)
            ->post(route('projects.store'), ['workspace_id' => $workspace->id, 'name' => 'Injected'])
            ->assertSessionHasErrors('workspace_id');

        $this->actingAs($attacker)->get(route('projects.show', $project))->assertNotFound();
        $this->actingAs($attacker)->post(route('tasks.store', $project), ['title' => 'Injected task'])->assertNotFound();
        $this->assertSame(0, $project->tasks()->count());
    }

    public function test_updating_task_status_does_not_require_resubmitting_title(): void
    {
        $user = User::factory()->create();
        $project = $user->workspaces()->create(['name' => 'Personal'])->projects()->create(['name' => 'Demo']);
        $task = $project->tasks()->create(['title' => 'Existing task']);

        $this->actingAs($user)->patch(route('tasks.update', [$project, $task]), [
            'status' => 'done',
        ])->assertSessionHasNoErrors();

        $task->refresh();
        $this->assertSame('Existing task', $task->title);
        $this->assertSame('done', $task->status);
        $this->assertNotNull($task->completed_at);

        $this->actingAs($user)->patch(route('tasks.update', [$project, $task]), [
            'status' => 'in_progress',
        ])->assertSessionHasNoErrors();
        $this->assertNull($task->fresh()->completed_at);
    }

    public function test_ajax_task_move_returns_json_without_redirect(): void
    {
        $user = User::factory()->create();
        $project = $user->workspaces()->create(['name' => 'Workspace'])
            ->projects()->create(['name' => 'Project']);
        $task = $project->tasks()->create(['title' => 'Move me', 'status' => 'todo']);

        $this->actingAs($user)
            ->patchJson(route('tasks.update', [$project, $task]), ['status' => 'in_review'])
            ->assertOk()
            ->assertJsonPath('task.id', $task->id)
            ->assertJsonPath('task.status', 'in_review');

        $this->assertSame('in_review', $task->fresh()->status);
    }

    public function test_ajax_move_cannot_update_another_users_task(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $project = $owner->workspaces()->create(['name' => 'Private'])
            ->projects()->create(['name' => 'Secret']);
        $task = $project->tasks()->create(['title' => 'Private task', 'status' => 'todo']);

        $this->actingAs($other)
            ->patchJson(route('tasks.update', [$project, $task]), ['status' => 'done'])
            ->assertNotFound();

        $this->assertSame('todo', $task->fresh()->status);
    }

    public function test_task_cannot_be_assigned_to_milestone_in_another_project(): void
    {
        $user = User::factory()->create();
        $workspace = $user->workspaces()->create(['name' => 'Personal']);
        $first = $workspace->projects()->create(['name' => 'First']);
        $second = $workspace->projects()->create(['name' => 'Second']);
        $milestone = $second->milestones()->create(['title' => 'Other project milestone']);

        $this->actingAs($user)->post(route('tasks.store', $first), [
            'title' => 'Invalid reference', 'milestone_id' => $milestone->id,
        ])->assertSessionHasErrors('milestone_id');
        $this->assertSame(0, $first->tasks()->count());
    }
}
