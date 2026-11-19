<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\EmailVerificationService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class GoogleAuthController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        $clientId = config('services.google.client_id');
        $clientSecret = config('services.google.client_secret');

        if (! is_string($clientId) || $clientId === '' || ! is_string($clientSecret) || $clientSecret === '') {
            return back()->withErrors(['email' => 'Login Google belum dikonfigurasi. Isi GOOGLE_CLIENT_ID dan GOOGLE_CLIENT_SECRET terlebih dahulu.']);
        }

        $intent = $request->query('intent') === 'register' ? 'register' : 'login';
        $state = Str::random(48);
        $request->session()->put('google_oauth_state', $state);
        $request->session()->put('google_oauth_intent', $intent);

        $query = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $this->redirectUri(),
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'prompt' => 'select_account',
        ]);

        return redirect()->away('https://accounts.google.com/o/oauth2/v2/auth?'.$query);
    }

    public function callback(Request $request, EmailVerificationService $emailVerification): RedirectResponse
    {
        $expectedState = $request->session()->pull('google_oauth_state');
        $intent = $request->session()->pull('google_oauth_intent', 'login');
        $request->attributes->set('google_oauth_intent', $intent);
        $state = $request->query('state');

        if (! is_string($expectedState) || ! is_string($state) || ! hash_equals($expectedState, $state)) {
            return $this->authError('Sesi Google tidak valid atau sudah kedaluwarsa. Silakan coba lagi.', $intent);
        }

        if ($request->filled('error') || ! $request->filled('code')) {
            return $this->authError('Proses Google dibatalkan atau tidak dapat dilanjutkan.', $intent);
        }

        try {
            $tokenResponse = Http::asForm()
                ->acceptJson()
                ->timeout(10)
                ->post('https://oauth2.googleapis.com/token', [
                    'code' => $request->query('code'),
                    'client_id' => config('services.google.client_id'),
                    'client_secret' => config('services.google.client_secret'),
                    'redirect_uri' => $this->redirectUri(),
                    'grant_type' => 'authorization_code',
                ])
                ->throw();

            $accessToken = $tokenResponse->json('access_token');

            if (! is_string($accessToken) || $accessToken === '') {
                return $this->authError('Google tidak mengembalikan token login yang valid.', $intent);
            }

            $profile = Http::withToken($accessToken)
                ->acceptJson()
                ->timeout(10)
                ->get('https://openidconnect.googleapis.com/v1/userinfo')
                ->throw()
                ->json();
        } catch (ConnectionException|RequestException $exception) {
            return $this->authError('Tidak dapat menghubungi Google saat ini. Silakan coba beberapa saat lagi.', $intent);
        }

        if (! is_array($profile)
            || ! is_string($profile['sub'] ?? null)
            || ! is_string($profile['email'] ?? null)
            || ! filter_var($profile['email'], FILTER_VALIDATE_EMAIL)
            || ($profile['email_verified'] ?? false) !== true) {
            return $this->authError('Akun Google harus memiliki alamat email terverifikasi untuk digunakan di DevFlow.', $intent);
        }

        try {
            $result = DB::transaction(function () use ($profile, $intent): array {
                $user = User::query()->where('google_id', $profile['sub'])->lockForUpdate()->first();

                if ($user instanceof User) {
                    if ($intent === 'register') {
                        return ['action' => 'already_registered'];
                    }

                    if ($user->email_verified_at !== null) {
                        return ['action' => 'login', 'user' => $user];
                    }

                    return ['action' => 'verify', 'is_new_account' => false, 'user' => $user];
                }

                $email = Str::lower($profile['email']);
                $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->lockForUpdate()->first();

                if ($user instanceof User) {
                    if ($intent === 'register') {
                        return ['action' => 'already_registered'];
                    }

                    if ($user->google_id !== null && $user->google_id !== $profile['sub']) {
                        throw new \DomainException('Akun Google ini sudah terhubung ke akun DevFlow lain.');
                    }

                    if ($user->google_id === null) {
                        $user->forceFill(['google_id' => $profile['sub']])->save();
                    }

                    if ($user->email_verified_at !== null) {
                        return ['action' => 'login', 'user' => $user];
                    }

                    return ['action' => 'verify', 'is_new_account' => false, 'user' => $user];
                }

                if ($intent === 'login') {
                    return ['action' => 'not_registered'];
                }

                $user = User::query()->create([
                    'name' => is_string($profile['name'] ?? null) && trim($profile['name']) !== ''
                        ? $profile['name']
                        : Str::before($email, '@'),
                    'email' => $email,
                    'password' => Str::random(64),
                    'google_id' => $profile['sub'],
                ]);

                return ['action' => 'verify', 'is_new_account' => true, 'user' => $user];
            });
        } catch (Throwable $exception) {
            report($exception);

            $message = $exception instanceof \DomainException
                ? $exception->getMessage()
                : 'Akun Google tidak dapat dihubungkan. Pastikan email tersebut belum dipakai akun lain.';

            return $this->authError($message, $intent);
        }

        if ($result['action'] === 'already_registered') {
            return redirect()->route('login')->withErrors([
                'email' => 'Akun dengan email Google ini sudah terdaftar. Silakan masuk.',
            ]);
        }

        if ($result['action'] === 'not_registered') {
            return redirect()->route('register')->withErrors([
                'email' => 'Akun Google belum terdaftar. Silakan daftar terlebih dahulu.',
            ]);
        }

        /** @var User $user */
        $user = $result['user'];

        if ($result['action'] === 'login') {
            auth()->login($user, true);
            $request->session()->regenerate();

            return redirect()->intended('/dashboard')->with('success', 'Selamat datang kembali!');
        }

        $request->session()->regenerate();
        $request->session()->put('pending_email_verification_user_id', $user->id);
        $request->session()->put('pending_email_verification_purpose', ($result['is_new_account'] ?? false) ? 'registration' : 'google_login');
        $request->session()->put('pending_email_verification_can_cancel', (bool) ($result['is_new_account'] ?? false));

        try {
            $code = $emailVerification->issue($user);

            if (config('app.e2e_isolated')) {
                $request->session()->put('e2e_email_verification_code', $code);
            }
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('verification.notice')->withErrors([
                'email' => 'Kode OTP belum dapat dikirim. Coba kirim ulang dari halaman verifikasi.',
            ]);
        }

        $status = ($result['is_new_account'] ?? false)
            ? 'Kode OTP telah dikirim untuk memverifikasi akun Google Anda.'
            : 'Kode OTP telah dikirim untuk mengonfirmasi login Google Anda.';

        return redirect()->route('verification.notice')->with('status', $status);
    }

    private function redirectUri(): string
    {
        $configuredRedirect = config('services.google.redirect');

        return is_string($configuredRedirect) && $configuredRedirect !== ''
            ? $configuredRedirect
            : route('google.callback');
    }

    private function authError(string $message, string $intent): RedirectResponse
    {
        return redirect()->route($intent === 'register' ? 'register' : 'login')->withErrors(['email' => $message]);
    }
}
