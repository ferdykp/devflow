<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DevelopmentContextFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_task_context_has_a_preview_with_source_links_and_secret_redaction(): void
    {
        $project = Project::factory()->create(['goal' => 'Rilis situs yang mudah diakses']);
        $task = $project->tasks()->create([
            'title' => 'Perbaiki navigasi keyboard',
            'description' => "Pastikan semua aksi dapat dipakai dengan keyboard.\nAPI_TOKEN=ghp_abcdefghijklmnopqrstuvwxyz1234567890",
            'checklist' => [['title' => 'Uji Tab dan Enter', 'done' => false]],
        ]);

        $response = $this->actingAs($project->workspace->user)
            ->get(route('projects.tasks.development-context', [$project, $task]));

        $response->assertOk()
            ->assertSee('Siapkan Konteks Pengembangan')
            ->assertSee('Perbaiki navigasi keyboard')
            ->assertSee('Uji Tab dan Enter')
            ->assertSee('Rilis situs yang mudah diakses')
            ->assertSee('[REDACTED TOKEN]')
            ->assertDontSee('ghp_abcdefghijklmnopqrstuvwxyz1234567890');
    }

    public function test_project_task_context_is_scoped_to_the_accessible_project(): void
    {
        $project = Project::factory()->create();
        $otherProject = Project::factory()->create();
        $task = $otherProject->tasks()->create(['title' => 'Tugas privat']);

        $this->actingAs($project->workspace->user)
            ->get(route('projects.tasks.development-context', [$project, $task]))
            ->assertNotFound();

        $projectTask = $project->tasks()->create(['title' => 'Tugas proyek']);

        $this->actingAs(User::factory()->create())
            ->get(route('projects.tasks.development-context', [$project, $projectTask]))
            ->assertNotFound();
    }
}
