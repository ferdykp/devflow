<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
        ];
    }

    public function authenticated(): void
    {
        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $user = Auth::user();

        if ($user !== null && $user->email_verified_at === null) {
            $this->session()->put('pending_email_verification_user_id', $user->getAuthIdentifier());
            $this->session()->put('pending_email_verification_purpose', 'email_verification');
            $this->session()->put('pending_email_verification_can_cancel', false);
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => 'Verifikasi email terlebih dahulu sebelum masuk ke DevFlow.',
            ]);
        }
    }
}
