<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScopeWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function projectFor(User $user)
    {
        return $user->workspaces()->create(['name' => 'Personal'])
            ->projects()->create(['name' => 'Demo']);
    }

    public function test_approval_records_decision_without_mutating_scope_baseline(): void
    {
        $user = User::factory()->create();
        $project = $this->projectFor($user);
        $project->baseline()->create(['in_scope' => 'Only read operations']);
        $review = $project->reviews()->create(['title' => 'Allow exports']);

        $this->actingAs($user)->post(route('scope.decide', [$project, $review]), [
            'decision' => 'approve_proposal',
            'classification' => 'out_of_scope',
            'reason' => 'Approved for next planning cycle',
            'evidence' => 'Original scope only permits reading',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame('Only read operations', $project->baseline->fresh()->in_scope);
        $this->assertSame('resolved', $review->fresh()->status);
        $this->assertDatabaseHas('scope_decisions', [
            'scope_review_id' => $review->id, 'decision' => 'approve_proposal',
        ]);
        $this->assertSame(0, $project->backlogItems()->count());
    }

    public function test_backlog_decision_is_atomic_and_cannot_run_twice(): void
    {
        $user = User::factory()->create();
        $project = $this->projectFor($user);
        $review = $project->reviews()->create(['title' => 'Try another UI']);
        $route = route('scope.decide', [$project, $review]);
        $data = ['decision' => 'move_to_backlog', 'classification' => 'uncertain',
            'bucket' => 'research', 'reason' => 'Needs validation'];

        $this->actingAs($user)->post($route, $data)->assertRedirect();
        $this->actingAs($user)->post($route, $data)->assertStatus(409);
        $this->assertSame(1, $project->backlogItems()->count());
        $this->assertSame(1, $review->decisions()->count());
    }

    public function test_other_users_cannot_decide_scope_reviews(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $project = $this->projectFor($owner);
        $review = $project->reviews()->create(['title' => 'Sensitive request']);

        $this->actingAs($other)->post(route('scope.decide', [$project, $review]), [
            'decision' => 'reject', 'classification' => 'uncertain', 'reason' => 'No',
        ])->assertNotFound();
        $this->assertSame('pending', $review->fresh()->status);
    }

    public function test_decision_requires_a_reason(): void
    {
        $user = User::factory()->create();
        $project = $this->projectFor($user);
        $review = $project->reviews()->create(['title' => 'Change']);
        $this->actingAs($user)->post(route('scope.decide', [$project, $review]), [
            'decision' => 'approve_proposal', 'classification' => 'in_scope',
        ])->assertSessionHasErrors('reason');
        $this->assertSame('pending', $review->fresh()->status);
    }
}
