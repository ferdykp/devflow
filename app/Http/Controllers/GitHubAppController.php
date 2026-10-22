<?php

namespace App\Http\Controllers;

use App\Services\GitHub\GitHubAppClient;
use App\Support\ProjectAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class GitHubAppController extends Controller
{
    public function authorizeApp(int $project, Request $request, GitHubAppClient $client)
    {
        ProjectAccess::find($project);
        abort_unless($client->configured(), 503, 'Integrasi GitHub App belum dikonfigurasi.');
        $state = Str::random(48);
        $request->session()->put('github_app_flow', [
            'state' => hash('sha256', $state), 'project_id' => $project,
            'user_id' => $request->user()->id, 'created_at' => now()->timestamp,
        ]);

        return redirect()->away('https://github.com/login/oauth/authorize?'.http_build_query([
            'client_id' => config('services.github.client_id'),
            'redirect_uri' => route('github.app.callback'), 'state' => $state,
        ]));
    }

    public function callback(Request $request, GitHubAppClient $client)
    {
        $flow = $request->session()->pull('github_app_flow');
        $state = $request->query('state');
        if (! is_array($flow) || ! is_string($state) ||
            ! hash_equals($flow['state'] ?? '', hash('sha256', $state)) ||
            ($flow['user_id'] ?? null) !== $request->user()->id ||
            now()->timestamp - ($flow['created_at'] ?? 0) > 600) {
            abort(403, 'Otorisasi GitHub kedaluwarsa. Hubungkan kembali.');
        }
        $project = ProjectAccess::find((int) $flow['project_id']);
        $code = $request->query('code');
        if (! is_string($code) || strlen($code) > 500) {
            return redirect()->route('projects.show', $project)->with('error', 'Otorisasi GitHub dibatalkan.');
        }
        try {
            $token = $client->userAccessToken($code);
            $installations = $client->installationsForUser($token);
            $choices = [];
            foreach ($installations as $installation) {
                $id = $installation['id'] ?? null;
                if (! is_int($id) || (string) ($installation['app_id'] ?? '') !== (string) config('services.github.app_id')) {
                    continue;
                }
                foreach ($client->repositoriesForUserInstallation($token, $id) as $repo) {
                    $choices[] = [...$repo, 'installation_id' => $id];
                }
            }
            // Only allow selecting repositories verified during this authenticated OAuth callback.
            $request->session()->put('github_app_choices', [
                'project_id' => $project->id, 'user_id' => $request->user()->id,
                'created_at' => now()->timestamp, 'repositories' => $choices,
            ]);

            return view('github.repositories', ['project' => $project, 'repositories' => $choices]);
        } catch (Throwable $e) {
            report($e);

            return redirect()->route('projects.show', $project)->with('error', 'Daftar repositori GitHub gagal dimuat. Periksa instalasi dan izin aplikasi.');
        }
    }

    public function connect(int $project, Request $request)
    {
        $project = ProjectAccess::find($project);
        $data = $request->validate(['repository_id' => ['required', 'integer', 'min:1']]);
        $choices = $request->session()->get('github_app_choices');
        if (! is_array($choices) || ($choices['project_id'] ?? null) !== $project->id ||
            ($choices['user_id'] ?? null) !== $request->user()->id ||
            now()->timestamp - ($choices['created_at'] ?? 0) > 600) {
            throw ValidationException::withMessages(['repository_id' => 'Pilihan GitHub kedaluwarsa. Hubungkan kembali.']);
        }
        $repo = collect($choices['repositories'])->first(fn ($item) => $item['id'] === $data['repository_id']);
        if (! $repo) {
            throw ValidationException::withMessages(['repository_id' => 'Repositori tidak diizinkan.']);
        }
        $project->repositories()->updateOrCreate(['full_name' => $repo['full_name']], [
            'github_id' => $repo['id'],
            'default_branch' => $repo['default_branch'],
            'installation_id' => $repo['installation_id'],
        ]);
        $request->session()->forget('github_app_choices');

        return redirect()->route('projects.show', $project)->with('success', 'Repositori GitHub berhasil dihubungkan.');
    }
}
