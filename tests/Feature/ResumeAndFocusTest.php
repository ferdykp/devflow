<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResumeAndFocusTest extends TestCase
{
    use RefreshDatabase;

    public function test_resume_works_without_prd_or_github(): void
    {
        $user = User::factory()->create();
        $project = $user->workspaces()->create(['name' => 'Personal'])
            ->projects()->create(['name' => 'Prototype']);
        $project->tasks()->create(['title' => 'Design first page', 'status' => 'todo']);

        $this->actingAs($user)->get(route('projects.resume', $project))
            ->assertOk()
            ->assertSee('Terakhir sampai mana?')
            ->assertSee('aria-current="page"', false)
            ->assertSee('Design first page')
            ->assertSee('Belum terhubung')
            ->assertSee('0%');
    }

    public function test_resume_is_not_accessible_to_another_user(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $project = $owner->workspaces()->create(['name' => 'Private'])
            ->projects()->create(['name' => 'Secret']);

        $this->actingAs($stranger)->get(route('projects.resume', $project))
            ->assertNotFound();
    }

    public function test_focus_excludes_paused_projects_and_enforces_three_task_limit(): void
    {
        $user = User::factory()->create();
        $workspace = $user->workspaces()->create(['name' => 'Personal']);
        $active = $workspace->projects()->create(['name' => 'Active', 'status' => 'active']);
        $paused = $workspace->projects()->create(['name' => 'Paused', 'status' => 'paused']);
        $pausedTask = $paused->tasks()->create(['title' => 'Do not suggest me']);
        $tasks = collect(range(1, 4))->map(fn ($n) => $active->tasks()->create(['title' => "Task {$n}"]));

        $this->actingAs($user)->get(route('focus.index'))
            ->assertOk()->assertDontSee('Do not suggest me');
        $this->actingAs($user)->post(route('focus.toggle', $pausedTask))->assertNotFound();

        foreach ($tasks->take(3) as $task) {
            $this->actingAs($user)->post(route('focus.toggle', $task))
                ->assertSessionHas('success');
        }
        $this->actingAs($user)->post(route('focus.toggle', $tasks[3]))
            ->assertSessionHas('error');
        $this->assertDatabaseCount('focus_items', 3);
    }
}
