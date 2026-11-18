<?php

namespace Tests\Feature;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChangeRequestFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_approved_change_request_creates_a_separate_prd_draft_without_mutating_approved_content(): void
    {
        $project = Project::factory()->create();
        $user = $project->workspace->user;
        $document = $project->documents()->create(['title' => 'Product requirements']);
        $approvedVersion = $document->versions()->create([
            'version_number' => 1,
            'status' => 'approved',
            'content_markdown' => '# Current scope',
            'content_hash' => hash('sha256', '# Current scope'),
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);
        $project->baseline()->create(['prd_version_id' => $approvedVersion->id, 'in_scope' => 'Current scope']);

        $this->actingAs($user)->post(route('change-requests.store', $project), [
            'title' => 'Add export support',
            'description' => 'Allow CSV exports for the reports page.',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $changeRequest = $project->changeRequests()->firstOrFail();
        $this->assertSame($approvedVersion->id, $changeRequest->source_prd_version_id);
        $this->assertSame('proposed', $changeRequest->status);

        $this->actingAs($user)->patch(route('change-requests.decide', [$project, $changeRequest]), [
            'status' => 'approved',
            'decision_reason' => 'Approved for the next planning cycle.',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->actingAs($user)->post(route('change-requests.prd-draft', [$project, $changeRequest]), [
            'content_markdown' => "# Revised scope\n\nCSV export is included.",
        ])->assertRedirect()->assertSessionHasNoErrors();

        $changeRequest->refresh();
        $this->assertSame('approved', $changeRequest->status);
        $this->assertNotNull($changeRequest->draft_prd_version_id);
        $this->assertSame('draft', $changeRequest->draftPrdVersion->status);
        $this->assertSame('# Current scope', $approvedVersion->fresh()->content_markdown);
        $this->assertSame('approved', $approvedVersion->fresh()->status);
    }

    public function test_change_request_and_decision_are_scoped_to_the_project(): void
    {
        $project = Project::factory()->create();
        $otherProject = Project::factory()->create();
        $requester = $project->workspace->user;
        $changeRequest = $otherProject->changeRequests()->create([
            'requested_by' => $otherProject->workspace->user->id,
            'title' => 'Private request',
        ]);

        $this->actingAs($requester)->patch(route('change-requests.decide', [$project, $changeRequest]), [
            'status' => 'approved', 'decision_reason' => 'Looks good',
        ])->assertNotFound();

        $this->assertSame('proposed', $changeRequest->fresh()->status);
    }

    public function test_change_request_must_have_an_explicit_decision_reason(): void
    {
        $project = Project::factory()->create();
        $user = $project->workspace->user;
        $changeRequest = $project->changeRequests()->create(['requested_by' => $user->id, 'title' => 'Review API']);

        $this->actingAs($user)->patch(route('change-requests.decide', [$project, $changeRequest]), [
            'status' => 'approved',
        ])->assertSessionHasErrors('decision_reason');

        $this->assertSame('proposed', $changeRequest->fresh()->status);
    }
}
