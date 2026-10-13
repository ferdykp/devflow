<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AuthController extends Controller
{
    # berfungsi untuk menampilkan halaman register
    public function showRegister(): View
    {
        return view('auth.register');
    }

    # untuk proses register
    public function register(RegisterRequest $request): RedirectResponse
    {
        # berfungsi untuk mengambil data yang sudah lolos validasi
        $user = DB::transaction(function () use ($request) {
            $user = User::create($request->validated());
            $user->workspaces()->create(['name' => 'Personal']);

            return $user;
        });

        Auth::login($user);

        # regenerasi sesion untuk mencegah session fixation attack
        $request->session()->regenerate();

        return redirect()->intended('/dashboard')->with('success', 'Registrasi Berhasil');
    }

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        # memanggil method authenticated dari LoginRequest
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
