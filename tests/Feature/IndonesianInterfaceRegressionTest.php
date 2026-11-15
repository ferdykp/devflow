<?php

namespace Tests\Feature;

use App\Models\FocusItem;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndonesianInterfaceRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_and_scope_render_in_indonesian_without_missing_variables(): void
    {
        $user = User::factory()->create();
        $project = $user->workspaces()->create(['name' => 'Ruang kerja uji'])
            ->projects()->create(['name' => 'Proyek uji']);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Prioritas hari ini')
            ->assertSee('Belajar &amp; berkembang', false);

        $this->actingAs($user)
            ->get(route('projects.show', ['project' => $project, 'section' => 'scope']))
            ->assertOk()
            ->assertSee('Penjaga Cakupan')
            ->assertSee('Belum ada peninjauan cakupan.');
    }

    public function test_dashboard_links_personal_and_project_tasks_to_their_matching_detail_routes(): void
    {
        $user = User::factory()->create();
        $project = $user->workspaces()->create(['name' => 'Ruang kerja uji'])
            ->projects()->create(['name' => 'Proyek uji']);
        $personalTask = Task::query()->create([
            'owner_id' => $user->id,
            'project_id' => null,
            'title' => 'Tugas pribadi fokus',
            'status' => 'todo',
            'priority' => 'high',
            'due_date' => now('Asia/Jakarta')->toDateString(),
        ]);
        FocusItem::query()->create([
            'user_id' => $user->id,
            'task_id' => $personalTask->id,
            'focus_date' => now('Asia/Jakarta')->toDateString(),
        ]);
        $projectTask = $project->tasks()->create([
            'title' => 'Tugas proyek mendatang',
            'status' => 'todo',
            'priority' => 'normal',
            'due_date' => now('Asia/Jakarta')->addDays(2)->toDateString(),
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('personal-tasks.show', $personalTask), false)
            ->assertSee(route('tasks.show', [$project, $projectTask]), false);

        $this->actingAs($user)->get(route('personal-tasks.show', $personalTask))->assertOk();
        $this->actingAs($user)->get(route('tasks.show', [$project, $projectTask]))->assertOk();
    }
}
