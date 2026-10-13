<?php

namespace Tests\Feature;

use App\Models\{Project, User, Workspace};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ProjectInsightsTest extends TestCase
{
    use RefreshDatabase;

    public function test_ai_disabled_does_not_contact_provider(): void
    {
        config(['devflow_ai.enabled' => false, 'devflow_ai.api_key' => null]);
        Http::fake();
        $user = User::factory()->create();
        $workspace = Workspace::create(['user_id' => $user->id, 'name' => 'Mine']);
        $project = Project::create(['workspace_id' => $workspace->id, 'name' => 'Sample']);
        $this->actingAs($user)->post(route('projects.insights.analyze', $project), ['mode' => 'progress', 'consent' => '1'])->assertSessionHasErrors('ai');
        Http::assertNothingSent();
    }

    public function test_other_user_cannot_run_ai_on_private_project(): void
    {
        config(['devflow_ai.enabled' => true, 'devflow_ai.api_key' => 'test']);
        Http::fake();
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $workspace = Workspace::create(['user_id' => $owner->id, 'name' => 'Private']);
        $project = Project::create(['workspace_id' => $workspace->id, 'name' => 'Private project']);
        $this->actingAs($other)->post(route('projects.insights.analyze', $project), ['mode' => 'progress', 'consent' => '1'])->assertNotFound();
        Http::assertNothingSent();
    }

    public function test_consent_is_required_before_sending_project_data(): void
    {
        config(['devflow_ai.enabled' => true, 'devflow_ai.api_key' => 'test']);
        Http::fake();
        $user = User::factory()->create();
        $workspace = Workspace::create(['user_id' => $user->id, 'name' => 'Mine']);
        $project = Project::create(['workspace_id' => $workspace->id, 'name' => 'Sample']);
        $this->actingAs($user)->post(route('projects.insights.analyze', $project), ['mode' => 'progress'])->assertSessionHasErrors('consent');
        Http::assertNothingSent();
    }
}
