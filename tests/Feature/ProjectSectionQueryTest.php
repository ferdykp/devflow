<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProjectSectionQueryTest extends TestCase
{
    use RefreshDatabase;

    public function test_overview_does_not_load_unrelated_project_modules(): void
    {
        $user = User::factory()->create();
        $workspace = $user->workspaces()->create(['name' => 'Engineering']);
        $project = $workspace->projects()->create(['name' => 'DevFlow']);
        $project->tasks()->create(['title' => 'Tugas berikutnya', 'status' => 'todo']);

        $queries = [];
        DB::listen(function ($query) use (&$queries): void {
            $queries[] = strtolower($query->sql);
        });

        $this->actingAs($user)
            ->get(route('projects.show', ['project' => $project, 'section' => 'overview']))
            ->assertOk()
            ->assertSee('Tugas berikutnya');

        $sql = implode("\n", $queries);
        foreach (['prd_versions', 'scope_reviews', 'github_repositories', 'backlog_items', 'milestones'] as $table) {
            // Eloquent quotes identifiers differently on SQLite and MySQL.
            $this->assertDoesNotMatchRegularExpression(
                '/\b(?:from|join)\s+[\"`]?'.preg_quote($table, '/').'[\"`]?(?![a-z0-9_])/i',
                $sql,
                "Overview should not fetch {$table}."
            );
        }
    }

    public function test_every_project_section_renders_with_section_specific_data(): void
    {
        $user = User::factory()->create();
        $project = $user->workspaces()->create(['name' => 'Engineering'])
            ->projects()->create(['name' => 'DevFlow']);
        $project->tasks()->create(['title' => 'Task screen smoke test', 'status' => 'todo']);
        $project->notes()->create(['title' => 'Notes screen smoke test', 'user_id' => $user->id]);

        foreach (['overview', 'tasks', 'activity', 'notes', 'backlog', 'milestones', 'scope', 'prd', 'github', 'settings'] as $section) {
            $this->actingAs($user)
                ->get(route('projects.show', ['project' => $project, 'section' => $section]))
                ->assertOk();
        }

        $this->actingAs($user)
            ->get(route('projects.show', ['project' => $project, 'section' => 'tasks']))
            ->assertSee('Task screen smoke test');

        $this->actingAs($user)
            ->get(route('projects.show', ['project' => $project, 'section' => 'notes']))
            ->assertSee('Notes screen smoke test');
    }
}
