<?php

namespace Tests\Feature;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskBoardWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_and_personal_boards_group_task_statuses_into_three_columns(): void
    {
        $project = Project::factory()->create();
        foreach (['backlog', 'todo', 'in_progress', 'in_review', 'blocked', 'done'] as $status) {
            $project->tasks()->create(['title' => 'Tugas '.ucfirst($status), 'status' => $status]);
        }

        $projectBoard = $this->actingAs($project->workspace->user)
            ->get(route('projects.show', ['project' => $project, 'section' => 'tasks']));

        $projectBoard->assertOk()
            ->assertSee('data-task-column="not_started"', false)
            ->assertSee('data-task-column="in_progress"', false)
            ->assertSee('data-task-column="done"', false)
            ->assertDontSee('data-task-column="blocked"', false)
            ->assertSee('Backlog')
            ->assertSee('Ditinjau')
            ->assertSee('Terhambat');

        $this->get(route('tasks.index'))
            ->assertOk()
            ->assertSee('data-task-column="not_started"', false)
            ->assertSee('data-task-column="in_progress"', false)
            ->assertSee('data-task-column="done"', false)
            ->assertDontSee('data-task-column="blocked"', false)
            ->assertSee('Backlog')
            ->assertSee('Ditinjau')
            ->assertSee('Terhambat');

        $this->get(route('tasks.index', ['view' => 'list']))
            ->assertOk()
            ->assertSee('<optgroup label="Belum dikerjakan">', false)
            ->assertSee('<optgroup label="Sedang dikerjakan">', false)
            ->assertSee('<optgroup label="Selesai">', false);

        $blockedTask = $project->tasks()->where('status', 'blocked')->firstOrFail();
        $this->get(route('tasks.show', [$project, $blockedTask]))
            ->assertOk()
            ->assertSee('<optgroup label="Belum dikerjakan">', false)
            ->assertSee('<optgroup label="Sedang dikerjakan">', false)
            ->assertSee('<optgroup label="Selesai">', false);
    }
}
