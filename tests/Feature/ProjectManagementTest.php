<?php

namespace Tests\Feature;

use App\Models\FocusItem;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_creation_forms_have_separate_pages_and_index_only_lists_projects(): void
    {
        $project = Project::factory()->create();
        $this->actingAs($project->workspace->user)->get(route('projects.index'))
            ->assertOk()->assertSee($project->name)->assertSee(route('projects.create'))
            ->assertDontSee('name="workspace_id"', false)->assertDontSee('id="workspace_name"', false);
        $this->get(route('projects.create', ['workspace' => $project->workspace_id]))->assertOk()
            ->assertSee('Mulai proyek baru')->assertViewHas('selectedWorkspace', $project->workspace_id);
        $this->get(route('workspaces.create'))->assertOk()->assertSee('id="workspace_name"', false);
    }

    public function test_project_search_rejects_arrays_and_overlong_queries_without_a_server_error(): void
    {
        $project = Project::factory()->create();
        $this->actingAs($project->workspace->user)->from(route('projects.index'))
            ->get(route('projects.index', ['search' => ['invalid']]))
            ->assertRedirect(route('projects.index'))->assertSessionHasErrors('search');
        $this->get(route('projects.index', ['search' => str_repeat('a', 201)]))
            ->assertRedirect(route('projects.index'))->assertSessionHasErrors('search');
        $this->get(route('projects.index', ['search' => $project->name]))->assertOk()->assertSee($project->name);
    }

    public function test_guests_cannot_open_management_pages_or_delete_projects(): void
    {
        $project = Project::factory()->create();
        foreach ([route('projects.create'), route('workspaces.create'), route('projects.edit', $project)] as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }
        $this->delete(route('projects.destroy', $project))->assertRedirect(route('login'));
        $this->assertDatabaseHas('projects', ['id' => $project->id]);
    }

    public function test_outsiders_cannot_edit_delete_or_preselect_a_private_workspace(): void
    {
        $project = Project::factory()->create();
        $this->actingAs(User::factory()->create())->get(route('projects.edit', $project))->assertNotFound();
        $this->get(route('projects.create', ['workspace' => $project->workspace_id]))->assertNotFound();
        $this->put(route('projects.update', $project), ['name' => 'Injected'])->assertNotFound();
        $this->delete(route('projects.destroy', $project))->assertNotFound();
        $this->assertDatabaseHas('projects', ['id' => $project->id, 'name' => $project->name]);
    }

    public function test_members_can_edit_but_only_the_workspace_owner_can_delete(): void
    {
        $project = Project::factory()->create();
        $member = User::factory()->create();
        $project->workspace->members()->attach($member);
        $this->actingAs($member)->get(route('projects.edit', $project))->assertOk()->assertDontSee('data-confirm="Hapus proyek', false);
        $this->from(route('projects.edit', $project))->put(route('projects.update', $project), [
            'name' => 'Nama baru', 'description' => 'Rencana tim', 'status' => 'paused', 'priority' => 'high', 'target_date' => '2026-12-01',
        ])->assertSessionHasNoErrors()->assertRedirect(route('projects.edit', $project));
        $this->assertDatabaseHas('projects', ['id' => $project->id, 'name' => 'Nama baru', 'status' => 'paused', 'target_date' => '2026-12-01']);
        $this->delete(route('projects.destroy', $project))->assertForbidden();
        $this->assertDatabaseHas('projects', ['id' => $project->id]);
        $project->workspace->members()->detach($member);
        $this->get(route('projects.edit', $project))->assertNotFound();
        $this->delete(route('projects.destroy', $project))->assertNotFound();
    }

    public function test_invalid_edits_preserve_the_original_project(): void
    {
        $project = Project::factory()->create();
        $this->actingAs($project->workspace->user)->from(route('projects.edit', $project))
            ->put(route('projects.update', $project), ['name' => '', 'status' => 'unknown', 'priority' => 'unknown', 'target_date' => 'invalid'])
            ->assertSessionHasErrors(['name', 'status', 'priority', 'target_date'])->assertRedirect(route('projects.edit', $project));
        $this->assertDatabaseHas('projects', ['id' => $project->id, 'name' => $project->name, 'status' => 'active']);
    }

    public function test_owner_deletion_removes_project_data_and_preserves_other_projects_and_workspace(): void
    {
        $project = Project::factory()->create();
        $owner = $project->workspace->user;
        $otherProject = Project::factory()->create(['workspace_id' => $project->workspace_id]);
        $otherTask = Task::factory()->for($otherProject)->create();
        $task = Task::factory()->for($project)->create();
        $task->comments()->create(['user_id' => $owner->id, 'body' => 'Catatan pekerjaan']);
        FocusItem::create(['user_id' => $owner->id, 'task_id' => $task->id, 'focus_date' => today()]);
        $this->actingAs($owner)->post(route('task-imports.store', $project), ['mode' => 'automatic', 'content_markdown' => "## Tahap 1: Persiapan\n- Buat fondasi\n  - Uji hasilnya"])->assertSessionHasNoErrors();
        $this->delete(route('projects.destroy', $project))->assertRedirect(route('projects.index'))->assertSessionHas('success', 'Proyek berhasil dihapus.');
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
        $this->assertDatabaseMissing('tasks', ['project_id' => $project->id]);
        foreach (['task_comments', 'focus_items', 'document_task_imports', 'project_documents', 'prd_versions', 'milestones'] as $table) {
            $this->assertDatabaseCount($table, 0);
        }
        $this->assertDatabaseHas('tasks', ['id' => $otherTask->id]);
        $this->assertDatabaseHas('projects', ['id' => $otherProject->id]);
        $this->assertDatabaseHas('workspaces', ['id' => $project->workspace_id]);
        $this->get(route('projects.show', $project))->assertNotFound();
    }
}
