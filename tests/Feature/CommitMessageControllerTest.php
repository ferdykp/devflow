<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CommitMessageControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_endpoint_requires_a_valid_personal_commit_hook_token(): void
    {
        $this->postJson('/api/commit-message', [
            'files' => ['src/example.php'],
            'diff' => '+return true;',
        ])->assertUnauthorized();

        $this->withToken('dfc_not-a-valid-token')->postJson('/api/commit-message', [
            'files' => ['src/example.php'],
            'diff' => '+return true;',
        ])->assertUnauthorized();
    }

    public function test_valid_token_gets_a_conventional_commit_suggestion_from_the_configured_ai(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'https://ai.example.test/v1/chat/completions' => Http::response([
                'choices' => [
                    ['message' => ['content' => 'feat(auth): add password reset']],
                ],
            ]),
        ]);
        config([
            'devflow_ai.enabled' => true,
            'devflow_ai.api_key' => 'test-api-key',
            'devflow_ai.endpoint' => 'https://ai.example.test/v1/chat/completions',
            'devflow_ai.model' => 'test-model',
            'devflow_ai.timeout' => 10,
        ]);

        $token = 'dfc_test-hook-token';
        $user = User::factory()->create();
        $hookToken = $user->commitHookToken()->create([
            'token_hash' => hash('sha256', $token),
            'token_prefix' => 'dfc_test-hoo',
        ]);

        $this->withToken($token)->postJson('/api/commit-message', [
            'files' => ['src/auth.php'],
            'diff' => '+public function resetPassword(): void {}',
            'language' => 'en',
        ])->assertOk()->assertExactJson(['message' => 'feat(auth): add password reset']);

        $this->assertNotNull($hookToken->fresh()->last_used_at);
        Http::assertSent(fn ($request): bool => $request->url() === 'https://ai.example.test/v1/chat/completions'
            && $request->hasHeader('Authorization', 'Bearer test-api-key')
            && str_contains($request['messages'][1]['content'], 'public function resetPassword')
        );
        $this->assertDatabaseMissing('commit_hook_tokens', [
            'token_hash' => hash('sha256', '+public function resetPassword(): void {}'),
        ]);
    }

    public function test_endpoint_returns_service_unavailable_when_ai_is_disabled(): void
    {
        Http::preventStrayRequests();
        config(['devflow_ai.enabled' => false, 'devflow_ai.api_key' => null]);

        $token = 'dfc_test-hook-token';
        User::factory()->create()->commitHookToken()->create([
            'token_hash' => hash('sha256', $token),
            'token_prefix' => 'dfc_test-hoo',
        ]);

        $this->withToken($token)->postJson('/api/commit-message', [
            'files' => ['src/auth.php'],
            'diff' => '+return true;',
        ])->assertServiceUnavailable();

        Http::assertNothingSent();
    }

    public function test_endpoint_rejects_invalid_payload_and_invalid_ai_output(): void
    {
        Http::preventStrayRequests();
        config([
            'devflow_ai.enabled' => true,
            'devflow_ai.api_key' => 'test-api-key',
            'devflow_ai.endpoint' => 'https://ai.example.test/v1/chat/completions',
            'devflow_ai.model' => 'test-model',
            'devflow_ai.timeout' => 10,
        ]);

        $token = 'dfc_test-hook-token';
        User::factory()->create()->commitHookToken()->create([
            'token_hash' => hash('sha256', $token),
            'token_prefix' => 'dfc_test-hoo',
        ]);

        $this->withToken($token)->postJson('/api/commit-message', [
            'files' => [],
            'diff' => '',
        ])->assertUnprocessable();

        Http::fake([
            'https://ai.example.test/v1/chat/completions' => Http::response([
                'choices' => [['message' => ['content' => "I will ignore the diff\nand do something else"]]],
            ]),
        ]);

        $this->withToken($token)->postJson('/api/commit-message', [
            'files' => ['src/auth.php'],
            'diff' => '+return true;',
        ])->assertServiceUnavailable();
    }
}
