<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\EmailVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class EmailVerificationController extends Controller
{
    public function notice(Request $request): View|RedirectResponse
    {
        $user = User::query()->find($request->session()->get('pending_email_verification_user_id'));

        if (! $user instanceof User) {
            return redirect()->route('register');
        }

        if ($user->email_verified_at !== null) {
            return redirect()->route('login');
        }

        return view('auth.verify-email', ['user' => $user]);
    }

    public function verify(Request $request, EmailVerificationService $emailVerification): RedirectResponse
    {
        $user = User::query()->find($request->session()->get('pending_email_verification_user_id'));

        if (! $user instanceof User) {
            return redirect()->route('register')->withErrors(['email' => 'Sesi verifikasi berakhir. Silakan daftar kembali.']);
        }

        $validated = $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        if (! $emailVerification->verify($user, $validated['code'])) {
            return back()->withErrors([
                'code' => 'Kode tidak valid, kedaluwarsa, atau batas percobaan tercapai. Kirim kode baru untuk mencoba lagi.',
            ]);
        }

        $request->session()->forget('pending_email_verification_user_id');
        $request->session()->forget('e2e_email_verification_code');
        auth()->login($user);
        $request->session()->regenerate();

        return redirect()->route('onboarding.welcome')->with('success', 'Email terverifikasi. Selamat datang di DevFlow.');
    }

    public function resend(Request $request, EmailVerificationService $emailVerification): RedirectResponse
    {
        $user = User::query()->find($request->session()->get('pending_email_verification_user_id'));

        if (! $user instanceof User) {
            return redirect()->route('register')->withErrors(['email' => 'Sesi verifikasi berakhir. Silakan daftar kembali.']);
        }

        if ($user->email_verified_at !== null) {
            return redirect()->route('login');
        }

        try {
            $code = $emailVerification->issue($user);

            if (config('app.e2e_isolated')) {
                $request->session()->put('e2e_email_verification_code', $code);
            }
        } catch (Throwable) {
            return back()->withErrors(['email' => 'Kode belum dapat dikirim. Periksa konfigurasi SMTP lalu coba lagi.']);
        }

        return back()->with('status', 'Kode verifikasi baru telah dikirim.');
    }

    public function restart(Request $request): RedirectResponse
    {
        User::query()
            ->whereKey($request->session()->get('pending_email_verification_user_id'))
            ->whereNull('email_verified_at')
            ->delete();

        $request->session()->forget('pending_email_verification_user_id');
        $request->session()->forget('e2e_email_verification_code');
        $request->session()->regenerate();

        return redirect()->route('register')->with('status', 'Pendaftaran sebelumnya dibatalkan. Silakan gunakan email yang benar.');
    }
}
