<?php

namespace App\Services\GitHub;

use App\Models\GithubRepository;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GitHubActivitySync
{
    public function sync(GithubRepository $repository, GitHubAppClient $app = new GitHubAppClient): int
    {
        $request = Http::acceptJson()->withHeaders(['User-Agent' => 'DevFlow']);
        if ($repository->installation_id) {
            $request = $request->withToken($app->installationToken((int) $repository->installation_id));
        }
        $response = $request
            ->connectTimeout(5)->timeout(15)
            ->get('https://api.github.com/repositories/'.$repository->github_id.'/commits', [
                'per_page' => 50,
            ]);

        if (! $response->successful() || ! is_array($response->json())) {
            $repository->update(['last_error' => 'GitHub sync failed (HTTP '.$response->status().').']);
            throw new RuntimeException('GitHub sync failed.');
        }

        $count = 0;
        foreach ($response->json() as $entry) {
            if (! is_array($entry) || ! is_string($entry['sha'] ?? null)) {
                continue;
            }
            $sha = $entry['sha'];
            if (! preg_match('/^[a-f0-9]{40}$/i', $sha)) {
                continue;
            }
            $commit = $entry['commit'] ?? [];
            $date = $commit['committer']['date'] ?? $commit['author']['date'] ?? null;
            $activity = $repository->project->activities()->firstOrCreate(
                ['source' => 'github', 'source_key' => $repository->github_id.':'.$sha],
                [
                    'type' => 'commit',
                    'title' => mb_substr($commit['message'] ?? 'Commit', 0, 255),
                    'description' => $entry['html_url'] ?? null,
                    'occurred_at' => $date ?: now(),
                    'metadata' => ['sha' => $sha, 'repo' => $repository->full_name],
                ],
            );
            $count += $activity->wasRecentlyCreated ? 1 : 0;
        }
        $repository->update(['last_synced_at' => now(), 'last_error' => null]);
        return $count;
    }
}
