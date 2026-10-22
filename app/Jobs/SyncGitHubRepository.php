<?php

namespace App\Jobs;

use App\Models\GithubDelivery;
use App\Models\GithubRepository;
use App\Services\GitHub\GitHubActivitySync;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncGitHubRepository implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 45;

    public function __construct(public int $repositoryId, public ?string $deliveryId = null) {}

    public function backoff(): array
    {
        return [30, 120, 300];
    }

    public function handle(GitHubActivitySync $service): void
    {
        $repo = GithubRepository::with('project')->find($this->repositoryId);
        if ($repo) {
            $service->sync($repo);
        }

        // A deleted repository is no longer actionable; it still counts as resolved.
        $this->recordResult(true);
    }

    private function recordResult(bool $success): void
    {
        if (! $this->deliveryId) {
            return;
        }

        DB::transaction(function () use ($success) {
            $delivery = GithubDelivery::where('delivery_id', $this->deliveryId)
                ->lockForUpdate()->first();
            if (! $delivery) {
                return;
            }
            // Both a recovered job and the original may finish. Only the first
            // terminal transition may update counters.
            $changed = DB::table('github_delivery_jobs')
                ->where('delivery_id', $this->deliveryId)
                ->where('repository_key', $this->repositoryId)
                ->where('status', 'queued')
                ->update(['status' => $success ? 'completed' : 'failed', 'updated_at' => now()]);
            if ($changed === 0) {
                return;
            }
            $delivery->pending_jobs = max(0, $delivery->pending_jobs - 1);
            if ($success) {
                $delivery->succeeded_jobs++;
            } else {
                $delivery->failed_jobs++;
            }
            $delivery->status = $delivery->pending_jobs > 0
                ? 'queued'
                : ($delivery->failed_jobs > 0 ? 'failed' : 'completed');
            $delivery->save();
        });
    }

    public function failed(?Throwable $exception): void
    {
        $this->recordResult(false);
        Log::warning('DevFlow GitHub sync failed', [
            'repository_id' => $this->repositoryId,
            'exception_type' => $exception ? $exception::class : null,
        ]);
    }
}
