<?php

namespace Tests\Feature;

use App\Jobs\SyncGitHubRepository;
use App\Models\GithubDelivery;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Http;
use App\Services\GitHub\GitHubActivitySync;
use Tests\TestCase;

class GitHubWebhookTest extends TestCase
{
    use RefreshDatabase;

    private function payload(int $githubId): array
    {
        return ['repository' => ['id' => $githubId, 'private' => false]];
    }

    private function signedRequest(array $payload, string $delivery = '01234567-89ab-cdef-0123-456789abcdef')
    {
        $body = json_encode($payload, JSON_THROW_ON_ERROR);
        return $this->call('POST', '/api/webhooks/github', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_HUB_SIGNATURE_256' => 'sha256='.hash_hmac('sha256', $body, 'test-secret'),
            'HTTP_X_GITHUB_DELIVERY' => $delivery,
            'HTTP_X_GITHUB_EVENT' => 'push',
        ], $body);
    }

    public function test_webhook_rejects_requests_without_valid_signature(): void
    {
        config()->set('services.github.webhook_secret', 'test-secret');
        $this->postJson('/api/webhooks/github', $this->payload(123))->assertUnauthorized();
        $this->assertDatabaseCount('github_deliveries', 0);
    }

    public function test_signed_push_queues_sync_and_rejects_duplicate_delivery(): void
    {
        config()->set('services.github.webhook_secret', 'test-secret');
        Queue::fake();
        $owner = User::factory()->create();
        $project = $owner->workspaces()->create(['name' => 'Personal'])->projects()->create(['name' => 'Demo']);
        $project->repositories()->create(['github_id' => 123, 'full_name' => 'owner/repo']);

        $this->signedRequest($this->payload(123))->assertAccepted();
        $this->signedRequest($this->payload(123))->assertAccepted();

        $this->assertDatabaseCount('github_deliveries', 1);
        Queue::assertPushed(SyncGitHubRepository::class, 1);
        $this->assertSame('queued', GithubDelivery::firstOrFail()->status);
        $this->assertSame(1, GithubDelivery::firstOrFail()->pending_jobs);
    }


    public function test_signed_webhook_rejects_non_json_content_type(): void
    {
        config()->set('services.github.webhook_secret', 'test-secret');
        $body = json_encode($this->payload(123), JSON_THROW_ON_ERROR);
        $this->call('POST', '/api/webhooks/github', [], [], [], [
            'CONTENT_TYPE' => 'text/plain',
            'HTTP_X_HUB_SIGNATURE_256' => 'sha256='.hash_hmac('sha256', $body, 'test-secret'),
            'HTTP_X_GITHUB_DELIVERY' => '01234567-89ab-cdef-0123-456789abcdef',
            'HTTP_X_GITHUB_EVENT' => 'push',
        ], $body)->assertStatus(415);
    }

    public function test_signed_webhook_limits_payload_size(): void
    {
        config()->set('services.github.webhook_secret', 'test-secret');
        $body = json_encode(['repository' => ['id' => 123, 'private' => false], 'filler' => str_repeat('X', 1024 * 1024)], JSON_THROW_ON_ERROR);
        $this->call('POST', '/api/webhooks/github', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_HUB_SIGNATURE_256' => 'sha256='.hash_hmac('sha256', $body, 'test-secret'),
            'HTTP_X_GITHUB_DELIVERY' => '01234567-89ab-cdef-0123-456789abcdef',
            'HTTP_X_GITHUB_EVENT' => 'push',
        ], $body)->assertStatus(413);
    }

    public function test_delivery_completes_after_all_repositories_finish(): void
    {
        config()->set('services.github.webhook_secret', 'test-secret');
        Queue::fake();
        $user = User::factory()->create();
        $workspace = $user->workspaces()->create(['name' => 'Personal']);
        foreach (['First', 'Second'] as $name) {
            $project = $workspace->projects()->create(['name' => $name]);
            $project->repositories()->create(['github_id' => 123, 'full_name' => 'owner/repo']);
        }
        $this->signedRequest($this->payload(123))->assertAccepted();
        $delivery = GithubDelivery::firstOrFail();
        $this->assertSame(2, $delivery->pending_jobs);

        Http::fake(['api.github.com/*' => Http::response([], 200)]);
        $jobs = Queue::pushed(SyncGitHubRepository::class);
        $this->assertCount(2, $jobs);
        $jobs[0]->handle(app(GitHubActivitySync::class));
        $this->assertSame('queued', $delivery->fresh()->status);
        $this->assertSame(1, $delivery->fresh()->pending_jobs);
        $jobs[1]->handle(app(GitHubActivitySync::class));
        $this->assertSame('completed', $delivery->fresh()->status);
        $this->assertSame(0, $delivery->fresh()->pending_jobs);
        $this->assertSame(2, $delivery->fresh()->succeeded_jobs);
    }

    public function test_delivery_records_failure_after_other_jobs_complete(): void
    {
        config()->set('services.github.webhook_secret', 'test-secret');
        Queue::fake();
        $user = User::factory()->create();
        $project = $user->workspaces()->create(['name' => 'Personal'])
            ->projects()->create(['name' => 'Demo']);
        $project->repositories()->create(['github_id' => 123, 'full_name' => 'owner/repo']);
        $this->signedRequest($this->payload(123))->assertAccepted();
        $job = Queue::pushed(SyncGitHubRepository::class)[0];
        $job->failed(new \RuntimeException('Test error'));
        $delivery = GithubDelivery::firstOrFail();
        $this->assertSame('failed', $delivery->status);
        $this->assertSame(1, $delivery->failed_jobs);
        $this->assertSame(0, $delivery->pending_jobs);
    }

    public function test_webhook_is_disabled_without_secret(): void
    {
        config()->set('services.github.webhook_secret', null);
        $this->postJson('/api/webhooks/github', $this->payload(123))->assertServiceUnavailable();
    }
    public function test_duplicate_job_completion_is_counted_once(): void
    {
        config()->set('services.github.webhook_secret', 'test-secret');
        Queue::fake();
        $owner = User::factory()->create();
        $project = $owner->workspaces()->create(['name' => 'Personal'])
            ->projects()->create(['name' => 'Demo']);
        $project->repositories()->create(['github_id' => 123, 'full_name' => 'owner/repo']);
        $this->signedRequest($this->payload(123))->assertAccepted();
        Http::fake(['api.github.com/*' => Http::response([], 200)]);
        $job = Queue::pushed(SyncGitHubRepository::class)[0];
        $job->handle(app(GitHubActivitySync::class));
        $job->handle(app(GitHubActivitySync::class));
        $delivery = GithubDelivery::firstOrFail();
        $this->assertSame('completed', $delivery->status);
        $this->assertSame(1, $delivery->succeeded_jobs);
        $this->assertSame(0, $delivery->pending_jobs);
    }

    public function test_stale_queued_jobs_recover_only_once_then_fail_closed(): void
    {
        config()->set('services.github.webhook_secret', 'test-secret');
        Queue::fake();
        $owner = User::factory()->create();
        $project = $owner->workspaces()->create(['name' => 'Personal'])
            ->projects()->create(['name' => 'Demo']);
        $project->repositories()->create(['github_id' => 123, 'full_name' => 'owner/repo']);
        $this->signedRequest($this->payload(123))->assertAccepted();
        $rowId = \Illuminate\Support\Facades\DB::table('github_delivery_jobs')->value('id');
        \Illuminate\Support\Facades\DB::table('github_delivery_jobs')->where('id', $rowId)
            ->update(['last_dispatched_at' => now()->subHour()]);
        $this->artisan('devflow:github-reconcile')->assertSuccessful();
        Queue::assertPushed(SyncGitHubRepository::class, 2);
        $this->assertSame(1, (int) \Illuminate\Support\Facades\DB::table('github_delivery_jobs')->value('recovery_attempts'));
        \Illuminate\Support\Facades\DB::table('github_delivery_jobs')->where('id', $rowId)
            ->update(['last_dispatched_at' => now()->subHour()]);
        $this->artisan('devflow:github-reconcile')->assertSuccessful();
        Queue::assertPushed(SyncGitHubRepository::class, 2);
        $this->assertSame('failed', GithubDelivery::firstOrFail()->status);
        $this->assertSame(0, GithubDelivery::firstOrFail()->pending_jobs);
    }

}
