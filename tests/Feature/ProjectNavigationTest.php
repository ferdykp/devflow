<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_has_one_contextual_navigation_and_all_sections_remain_accessible(): void
    {
        $user = User::factory()->create();
        $workspace = $user->workspaces()->create(['name' => 'Test workspace']);
        $project = $workspace->projects()->create(['name' => 'Test project']);

        $this->actingAs($user)
            ->get(route('projects.show', $project))
            ->assertOk()
            ->assertSee('Bagian proyek')
            ->assertSee('Menu proyek lainnya')
            ->assertSee('Tugas')
            ->assertSee('Ide nanti')
            ->assertSee('Ringkasan');

        $this->actingAs($user)
            ->get(route('projects.show', ['project' => $project, 'section' => 'scope']))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('projects.resume', $project))
            ->assertOk()
            ->assertSee('Terakhir sampai mana?')
            ->assertSee('aria-current="page"', false);
    }
    public function test_each_project_section_renders_without_missing_view_variables(): void
    {
        $user = User::factory()->create();
        $workspace = $user->workspaces()->create(['name' => 'Demo workspace']);
        $project = $workspace->projects()->create(['name' => 'Demo project']);

        foreach (['overview', 'tasks', 'activity', 'notes', 'backlog', 'milestones', 'scope', 'prd', 'github', 'settings'] as $section) {
            $this->actingAs($user)
                ->get(route('projects.show', ['project' => $project, 'section' => $section]))
                ->assertOk();
        }

        $this->actingAs($user)
            ->get(route('projects.resume', $project))
            ->assertOk()
            ->assertSee('Bagian proyek')
            ->assertSee('Demo project');
    }

}
