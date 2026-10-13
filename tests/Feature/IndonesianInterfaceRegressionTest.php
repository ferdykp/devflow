<?php

namespace Tests\Feature;

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
            ->assertSee('Tugas belum selesai dari semua proyekmu');

        $this->actingAs($user)
            ->get(route('projects.show', ['project' => $project, 'section' => 'scope']))
            ->assertOk()
            ->assertSee('Penjaga Cakupan')
            ->assertSee('Belum ada peninjauan cakupan.');
    }
}
