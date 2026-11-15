<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Services\EmailVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AuthController extends Controller
{
    // berfungsi untuk menampilkan halaman register
    public function showRegister(): View
    {
        return view('auth.register');
    }

    // untuk proses register
    public function register(RegisterRequest $request, EmailVerificationService $emailVerification): RedirectResponse
    {
        $user = DB::transaction(function () use ($request) {
            return User::create($request->validated());
        });

        $request->session()->regenerate();
        $request->session()->put('pending_email_verification_user_id', $user->id);

        try {
            $code = $emailVerification->issue($user);

            if (config('app.e2e_isolated')) {
                $request->session()->put('e2e_email_verification_code', $code);
            }
        } catch (\Throwable) {
            return redirect()->route('verification.notice')->withErrors([
                'email' => 'Akun sudah dibuat, tetapi kode belum terkirim. Coba kirim ulang kode verifikasi.',
            ]);
        }

        return redirect()->route('verification.notice')->with('status', 'Kode verifikasi telah dikirim ke email Anda.');
    }

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        // memanggil method authenticated dari LoginRequest
        $request->authenticated();

        $request->session()->regenerate();

        return redirect()->intended('/dashboard')->with('success', 'Selamat datang kembali!');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
