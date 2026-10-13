<?php

namespace App\Console\Commands;

use App\Jobs\SyncGitHubRepository;
use App\Models\GithubDelivery;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReconcileGitHubDeliveries extends Command
{
    protected $signature = 'devflow:github-reconcile {--stale=30 : Minutes before a queued job is considered stuck} {--limit=100 : Maximum jobs to inspect}';
    protected $description = 'Recover interrupted GitHub webhook dispatches, once per repository';

    public function handle(): int
    {
        $minutes = max(15, (int) $this->option('stale'));
        $limit = min(500, max(1, (int) $this->option('limit')));
        $cutoff = now()->subMinutes($minutes);
        $candidates = DB::table('github_delivery_jobs')
            ->where('status', 'queued')
            ->where('last_dispatched_at', '<', $cutoff)
            ->orderBy('id')->limit($limit)->pluck('id');
        $recovered = 0;
        $expired = 0;

        foreach ($candidates as $id) {
            $result = DB::transaction(function () use ($id, $cutoff) {
                $job = DB::table('github_delivery_jobs')->where('id', $id)->first();
                if (! $job) {
                    return null;
                }
                $delivery = GithubDelivery::where('delivery_id', $job->delivery_id)->lockForUpdate()->first();
                if (! $delivery) {
                    return null;
                }
                $job = DB::table('github_delivery_jobs')->where('id', $id)->lockForUpdate()->first();
                if (! $job || $job->status !== 'queued' || $job->last_dispatched_at >= $cutoff) {
                    return null;
                }
                if ($job->recovery_attempts >= 1 || $job->repository_id === null) {
                    DB::table('github_delivery_jobs')->where('id', $id)->update(['status' => 'failed', 'updated_at' => now()]);
                    $delivery->pending_jobs = max(0, $delivery->pending_jobs - 1);
                    $delivery->failed_jobs++;
                    $delivery->status = $delivery->pending_jobs > 0 ? 'queued' : 'failed';
                    $delivery->save();
                    return ['expired', null, null];
                }
                DB::table('github_delivery_jobs')->where('id', $id)->update([
                    'recovery_attempts' => $job->recovery_attempts + 1,
                    'last_dispatched_at' => now(),
                    'updated_at' => now(),
                ]);
                return ['recovered', (int) $job->repository_id, $job->delivery_id];
            });
            if (! $result) {
                continue;
            }
            if ($result[0] === 'expired') {
                $expired++;
                continue;
            }
            SyncGitHubRepository::dispatch($result[1], $result[2]);
            $recovered++;
        }

        $this->info("Re-dispatched {$recovered}; marked failed {$expired}.");
        return self::SUCCESS;
    }
}
