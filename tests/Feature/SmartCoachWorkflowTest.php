<?php

namespace Tests\Feature;

use App\Models\CoachRecommendation;
use App\Models\User;
use App\Services\SmartCoachService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmartCoachWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_multiple_recommendations_of_the_same_kind_can_be_accepted(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        for ($iteration = 0; $iteration < 2; $iteration++) {
            $recommendation = CoachRecommendation::factory()->for($user, 'owner')->create([
                'kind' => 'weekly_review',
                'pending_key' => 'weekly_review',
                'proposed_action' => ['type' => 'open_weekly_review'],
            ]);

            $this->post(route('smart-coach.confirm', $recommendation))
                ->assertRedirect(route('weekly-review.index'));
        }

        $this->assertSame(2, $user->coachRecommendations()->where('kind', 'weekly_review')->where('status', 'accepted')->count());
    }

    public function test_refresh_reuses_the_pending_recommendation_for_each_kind(): void
    {
        $user = User::factory()->create();
        $coach = app(SmartCoachService::class);

        $coach->refresh($user);
        $coach->refresh($user);

        $this->assertSame(1, $user->coachRecommendations()->where('kind', 'weekly_review')->where('status', 'pending')->count());
    }
}
