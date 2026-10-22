<?php

namespace App\Services\GitHub;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GitHubAppClient
{
    public function configured(): bool
    {
        return filled(config('services.github.app_id'))
            && filled(config('services.github.private_key'))
            && filled(config('services.github.client_id'))
            && filled(config('services.github.client_secret'));
    }

    private function jwt(): string
    {
        $key = config('services.github.private_key');
        if (str_starts_with((string) $key, 'file://')) {
            $key = @file_get_contents(substr($key, 7));
        } else {
            $key = str_replace('\\n', "\n", (string) $key);
        }
        if (! $key) {
            throw new RuntimeException('GitHub App private key not configured.');
        }
        $encode = fn (array $data): string => rtrim(strtr(base64_encode(json_encode($data, JSON_THROW_ON_ERROR)), '+/', '-_'), '=');
        $header = $encode(['alg' => 'RS256', 'typ' => 'JWT']);
        $payload = $encode(['iat' => time() - 60, 'exp' => time() + 540, 'iss' => (string) config('services.github.app_id')]);
        $input = "$header.$payload";
        if (! openssl_sign($input, $signature, $key, OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('Unable to sign GitHub App JWT.');
        }

        return $input.'.'.rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
    }

    public function installationToken(int $installationId): string
    {
        if (! $this->configured() || $installationId < 1) {
            throw new RuntimeException('GitHub App installation is not configured.');
        }
        $response = Http::withToken($this->jwt())->acceptJson()
            ->withHeaders(['User-Agent' => 'DevFlow', 'X-GitHub-Api-Version' => '2022-11-28'])
            ->connectTimeout(5)->timeout(15)
            ->post("https://api.github.com/app/installations/$installationId/access_tokens", [
                'permissions' => ['contents' => 'read', 'metadata' => 'read'],
            ]);
        if (! $response->successful() || ! is_string($response->json('token'))) {
            throw new RuntimeException('GitHub App installation token request failed (HTTP '.$response->status().').');
        }

        return $response->json('token');
    }

    public function userAccessToken(string $code): string
    {
        $response = Http::acceptJson()->asForm()->connectTimeout(5)->timeout(15)
            ->post('https://github.com/login/oauth/access_token', [
                'client_id' => config('services.github.client_id'),
                'client_secret' => config('services.github.client_secret'),
                'code' => $code,
                'redirect_uri' => route('github.app.callback'),
            ]);
        if (! $response->successful() || ! is_string($response->json('access_token'))) {
            throw new RuntimeException('GitHub authorization was unsuccessful.');
        }

        return $response->json('access_token');
    }

    public function installationsForUser(string $token): array
    {
        $response = Http::withToken($token)->acceptJson()
            ->withHeaders(['User-Agent' => 'DevFlow', 'X-GitHub-Api-Version' => '2022-11-28'])
            ->connectTimeout(5)->timeout(15)->get('https://api.github.com/user/installations', ['per_page' => 100]);
        if (! $response->successful()) {
            throw new RuntimeException('Could not read authorized GitHub installations.');
        }

        return $response->json('installations') ?? [];
    }

    public function repositoriesForUserInstallation(string $userToken, int $installationId): array
    {
        $result = [];
        for ($page = 1; $page <= 10; $page++) {
            $response = Http::withToken($userToken)->acceptJson()
                ->withHeaders(['User-Agent' => 'DevFlow', 'X-GitHub-Api-Version' => '2022-11-28'])
                ->connectTimeout(5)->timeout(15)
                ->get("https://api.github.com/user/installations/{$installationId}/repositories", ['per_page' => 100, 'page' => $page]);
            if (! $response->successful() || ! is_array($response->json('repositories'))) {
                throw new RuntimeException('Could not list repositories authorized for this GitHub user.');
            }
            $batch = $response->json('repositories');
            foreach ($batch as $repo) {
                if (is_int($repo['id'] ?? null) && is_string($repo['full_name'] ?? null)) {
                    $result[] = ['id' => $repo['id'], 'full_name' => $repo['full_name'], 'default_branch' => $repo['default_branch'] ?? 'main'];
                }
            }
            if (count($batch) < 100) {
                break;
            }
        }

        return $result;
    }

    public function repositoriesForInstallation(int $installationId): array
    {
        $result = [];
        $token = $this->installationToken($installationId);
        for ($page = 1; $page <= 10; $page++) {
            $response = Http::withToken($token)->acceptJson()
                ->withHeaders(['User-Agent' => 'DevFlow', 'X-GitHub-Api-Version' => '2022-11-28'])
                ->connectTimeout(5)->timeout(15)
                ->get('https://api.github.com/installation/repositories', ['per_page' => 100, 'page' => $page]);
            if (! $response->successful()) {
                throw new RuntimeException('Could not list GitHub App repositories.');
            }
            $batch = $response->json('repositories') ?? [];
            foreach ($batch as $repo) {
                if (is_int($repo['id'] ?? null) && is_string($repo['full_name'] ?? null)) {
                    $result[] = ['id' => $repo['id'], 'full_name' => $repo['full_name'], 'default_branch' => $repo['default_branch'] ?? 'main'];
                }
            }
            if (count($batch) < 100) {
                break;
            }
        }

        return $result;
    }
}
