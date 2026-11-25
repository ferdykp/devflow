<?php

namespace App\Http\Controllers;

use App\Http\Requests\SuggestCommitMessageRequest;
use App\Services\AI\CommitMessageSuggestion;
use Illuminate\Http\JsonResponse;
use Throwable;

class CommitMessageController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(SuggestCommitMessageRequest $request, CommitMessageSuggestion $suggestion): JsonResponse
    {
        $data = $request->validated();
        $maxDiffBytes = (int) config('devflow_ai.commit_message_max_diff_bytes', 256 * 1024);

        if (strlen($data['diff']) > $maxDiffBytes) {
            return response()->json(['message' => 'Diff staged melebihi batas '.intdiv($maxDiffBytes, 1024).' KiB.'], 413);
        }

        if (! $suggestion->ready()) {
            return response()->json(['message' => 'Asisten commit belum diaktifkan di server DevFlow.'], JsonResponse::HTTP_SERVICE_UNAVAILABLE);
        }

        try {
            $message = $suggestion->suggest($data['files'], $data['diff'], $data['language'] ?? 'en');
        } catch (Throwable $exception) {
            report($exception);

            return response()->json(['message' => 'Penyedia AI belum merespons setelah beberapa percobaan. Coba commit lagi sebentar.'], JsonResponse::HTTP_SERVICE_UNAVAILABLE);
        }

        return response()->json(['message' => $message]);
    }
}
