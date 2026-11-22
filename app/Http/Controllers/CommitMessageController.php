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

        if (strlen($data['diff']) > 40960) {
            return response()->json(['message' => 'Diff staged melebihi batas 40 KiB.'], 413);
        }

        if (! $suggestion->ready()) {
            return response()->json(['message' => 'Asisten commit belum diaktifkan di server DevFlow.'], JsonResponse::HTTP_SERVICE_UNAVAILABLE);
        }

        try {
            $message = $suggestion->suggest($data['files'], $data['diff'], $data['language'] ?? 'en');
        } catch (Throwable $exception) {
            report($exception);

            return response()->json(['message' => 'Saran commit belum tersedia. Periksa koneksi AI server atau lanjutkan dengan pesan manual.'], JsonResponse::HTTP_SERVICE_UNAVAILABLE);
        }

        return response()->json(['message' => $message]);
    }
}
