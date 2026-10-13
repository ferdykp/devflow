<?php

namespace App\Http\Controllers;

use App\Jobs\SyncGitHubRepository;
use App\Models\GithubDelivery;
use App\Models\GithubRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GitHubWebhookController extends Controller
{
    public function __invoke(Request $request)
    {
        $secret = config('services.github.webhook_secret');
        if (! is_string($secret) || $secret === '') {
            return response()->json(['message' => 'Webhook unavailable'], 503);
        }

        $signature = $request->header('X-Hub-Signature-256', '');
        $expected = 'sha256='.hash_hmac('sha256', $request->getContent(), $secret);
        if (! is_string($signature) || ! hash_equals($expected, $signature)) {
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        $delivery = $request->header('X-GitHub-Delivery');
        if (! is_string($delivery) || ! preg_match('/^[a-zA-Z0-9-]{10,100}$/', $delivery)) {
            return response()->json(['message' => 'Missing delivery ID'], 422);
        }
        $event = $request->header('X-GitHub-Event', '');
        if (! in_array($event, ['push', 'ping'], true)) {
            return response()->json(['message' => 'Ignored'], 202);
        }
        if ($event === 'ping') {
            return response()->json(['message' => 'pong']);
        }
        // Bound webhook payloads before decoding to protect memory and queue throughput.
        if (strlen($request->getContent()) > 1024 * 1024) {
            return response()->json(['message' => 'Payload too large'], 413);
        }
        if (! $request->isJson()) {
            return response()->json(['message' => 'JSON required'], 415);
        }
        $data = $request->json()->all();
        $repoId = data_get($data, 'repository.id');
        if (! is_int($repoId)) {
            return response()->json(['message' => 'Unrecognized repository'], 202);
        }
        $installationId = data_get($data, 'installation.id');
        $isPrivate = data_get($data, 'repository.private') === true;
        $repos = GithubRepository::query()->where('github_id', $repoId)
            ->when($isPrivate, fn ($q) => $q->whereNotNull('installation_id')
                ->where('installation_id', $installationId))
            ->pluck('id');
        if ($repos->isEmpty()) {
            return response()->json(['message' => 'Not linked'], 202);
        }
        // Persist the delivery and its per-repository work before dispatching.
        // Out-of-band reconciliation recovers an interrupted dispatch.
        $created = DB::transaction(function () use ($delivery, $event, $repos) {
            $inserted = DB::table('github_deliveries')->insertOrIgnore([
                'delivery_id' => $delivery,
                'event' => $event,
                'status' => 'queued',
                'pending_jobs' => $repos->count(),
                'succeeded_jobs' => 0,
                'failed_jobs' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            if (! $inserted) {
                return false;
            }
            foreach ($repos as $id) {
                DB::table('github_delivery_jobs')->insert([
                    'delivery_id' => $delivery,
                    'repository_id' => (int) $id,
                    'repository_key' => (int) $id,
                    'status' => 'queued',
                    'last_dispatched_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            return true;
        });
        if (! $created) {
            return response()->json(['message' => 'Duplicate delivery'], 202);
        }
        foreach ($repos as $id) {
            SyncGitHubRepository::dispatch((int) $id, $delivery);
        }
        return response()->json(['message' => 'Accepted'], 202);
    }
}
