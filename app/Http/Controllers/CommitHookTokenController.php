<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CommitHookTokenController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $request->validate(['consent' => ['accepted']]);

        $plainToken = 'dfc_'.Str::random(60);
        $request->user()->commitHookToken()->updateOrCreate([], [
            'token_hash' => hash('sha256', $plainToken),
            'token_prefix' => Str::substr($plainToken, 0, 12),
            'encrypted_token' => $plainToken,
            'last_used_at' => null,
        ]);

        return redirect()->route('settings.integrations')
            ->with('generatedCommitHookToken', $plainToken)
            ->with('success', 'Token hook commit dibuat. Kamu bisa menampilkannya lagi dari halaman Integrasi.');
    }

    public function reveal(Request $request): JsonResponse
    {
        $token = $request->user()->commitHookToken;

        if (! $token) {
            return response()->json(['message' => 'Token hook tidak ditemukan.'], JsonResponse::HTTP_NOT_FOUND)
                ->header('Cache-Control', 'no-store, private, max-age=0');
        }

        if (! $token->encrypted_token) {
            return response()->json([
                'message' => 'Token ini dibuat sebelum fitur tampilkan token tersedia. Buat token baru untuk menampilkannya.',
            ], JsonResponse::HTTP_CONFLICT)->header('Cache-Control', 'no-store, private, max-age=0');
        }

        return response()->json(['token' => $token->encrypted_token])
            ->header('Cache-Control', 'no-store, private, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->user()->commitHookToken()->delete();

        return redirect()->route('settings.integrations')->with('success', 'Token hook commit dicabut. Hook lokal tidak dapat meminta saran sampai token baru dipasang.');
    }
}
