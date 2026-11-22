<?php

namespace App\Http\Controllers;

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
            'last_used_at' => null,
        ]);

        return redirect()->route('settings.integrations')
            ->with('generatedCommitHookToken', $plainToken)
            ->with('success', 'Token hook commit dibuat. Salin sekarang; token lengkap tidak akan ditampilkan lagi.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->user()->commitHookToken()->delete();

        return redirect()->route('settings.integrations')->with('success', 'Token hook commit dicabut. Hook lokal tidak dapat meminta saran sampai token baru dipasang.');
    }
}
