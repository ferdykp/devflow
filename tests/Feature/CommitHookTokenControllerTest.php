<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommitHookTokenControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_generate_a_token_that_is_only_shown_once_and_stored_as_a_hash(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('settings.commit-hook-token.store'), [
            'consent' => '1',
        ]);

        $response->assertRedirect(route('settings.integrations'));
        $token = session('generatedCommitHookToken');

        $this->assertIsString($token);
        $this->assertStringStartsWith('dfc_', $token);
        $this->assertDatabaseHas('commit_hook_tokens', [
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $token),
        ]);
        $this->assertDatabaseMissing('commit_hook_tokens', ['token_hash' => $token]);

        $this->get(route('settings.integrations'))->assertOk()->assertSeeText($token);
        $this->get(route('settings.integrations'))->assertOk()->assertDontSeeText($token);
    }

    public function test_token_creation_requires_privacy_consent(): void
    {
        $this->actingAs(User::factory()->create())
            ->from(route('settings.integrations'))
            ->post(route('settings.commit-hook-token.store'))
            ->assertRedirect(route('settings.integrations'))
            ->assertSessionHasErrors('consent');

        $this->assertDatabaseCount('commit_hook_tokens', 0);
    }

    public function test_user_can_revoke_their_token(): void
    {
        $user = User::factory()->create();
        $user->commitHookToken()->create([
            'token_hash' => hash('sha256', 'dfc_test-token'),
            'token_prefix' => 'dfc_test-tok',
        ]);

        $this->actingAs($user)
            ->delete(route('settings.commit-hook-token.destroy'))
            ->assertRedirect(route('settings.integrations'));

        $this->assertDatabaseCount('commit_hook_tokens', 0);
    }
}
