<?php

namespace App\Console\Commands;

use App\Jobs\SyncGitHubRepository;
use App\Models\GithubRepository;
use Illuminate\Console\Command;

class SyncGitHubRepositories extends Command
{
    protected $signature = 'devflow:github-sync {--stale=60 : Minutes since last successful sync}';
    protected $description = 'Queue refresh of linked public GitHub repositories';

    public function handle(): int
    {
        $minutes = max(5, (int) $this->option('stale'));
        $count = 0;
        GithubRepository::query()
            ->where(fn ($q) => $q->whereNull('last_synced_at')
                ->orWhere('last_synced_at', '<', now()->subMinutes($minutes)))
            ->orderBy('id')->chunkById(100, function ($repos) use (&$count) {
                foreach ($repos as $repo) {
                    SyncGitHubRepository::dispatch($repo->id);
                    $count++;
                }
            });
        $this->info("Queued {$count} repositories.");
        return self::SUCCESS;
    }
}
