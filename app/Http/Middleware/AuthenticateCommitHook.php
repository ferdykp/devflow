<?php

namespace App\Http\Middleware;

use App\Models\CommitHookToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateCommitHook
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $plainToken = $request->bearerToken();

        if (! is_string($plainToken) || ! str_starts_with($plainToken, 'dfc_')) {
            return response()->json(['message' => 'Token DevFlow tidak valid.'], Response::HTTP_UNAUTHORIZED);
        }

        $token = CommitHookToken::query()
            ->with('user')
            ->where('token_hash', hash('sha256', $plainToken))
            ->first();

        if (! $token) {
            return response()->json(['message' => 'Token DevFlow tidak valid atau sudah dicabut.'], Response::HTTP_UNAUTHORIZED);
        }

        $request->setUserResolver(fn () => $token->user);
        $token->forceFill(['last_used_at' => now()])->save();

        return $next($request);
    }
}
