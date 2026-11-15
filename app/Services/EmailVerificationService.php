<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\EmailVerificationCode as EmailVerificationNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmailVerificationService
{
    public function issue(User $user): string
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $now = now();

        DB::table('email_verification_codes')->updateOrInsert(
            ['user_id' => $user->id],
            [
                'code_hash' => Hash::make($code),
                'expires_at' => $now->copy()->addMinutes(10),
                'attempts' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        );

        $user->notify(new EmailVerificationNotification($code));

        return $code;
    }

    public function verify(User $user, string $code): bool
    {
        return DB::transaction(function () use ($user, $code): bool {
            $challenge = DB::table('email_verification_codes')
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if ($challenge === null || now()->greaterThanOrEqualTo($challenge->expires_at) || $challenge->attempts >= 5) {
                return false;
            }

            DB::table('email_verification_codes')
                ->where('user_id', $user->id)
                ->increment('attempts');

            if (! Hash::check($code, $challenge->code_hash)) {
                return false;
            }

            $user->forceFill(['email_verified_at' => now()])->save();

            if (! $user->workspaces()->where('name', 'Personal')->exists()) {
                $user->workspaces()->create(['name' => 'Personal']);
            }

            DB::table('email_verification_codes')->where('user_id', $user->id)->delete();

            return true;
        });
    }
}
