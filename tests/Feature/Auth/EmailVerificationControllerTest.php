<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\EmailVerificationCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EmailVerificationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_otp_verifies_email_authenticates_user_and_creates_personal_workspace(): void
    {
        Notification::fake();

        $this->post(route('register'), [
            'name' => 'OTP User',
            'email' => 'otp-user@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('verification.notice'));

        $user = User::query()->where('email', 'otp-user@example.test')->firstOrFail();
        $code = null;
        Notification::assertSentTo($user, EmailVerificationCode::class, function (EmailVerificationCode $notification) use (&$code): bool {
            $code = $notification->code;

            return true;
        });

        $this->get(route('verification.notice'))
            ->assertOk()
            ->assertSee('otp-user@example.test');

        $this->post(route('verification.verify'), ['code' => $code])
            ->assertRedirect(route('onboarding.welcome'));

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertDatabaseHas('workspaces', [
            'user_id' => $user->id,
            'name' => 'Personal',
        ]);
        $this->assertDatabaseMissing('email_verification_codes', ['user_id' => $user->id]);
    }

    public function test_invalid_otp_does_not_verify_or_authenticate_user(): void
    {
        Notification::fake();
        $user = $this->createPendingUser();
        $codes = [];
        Notification::assertSentTo($user, EmailVerificationCode::class, function (EmailVerificationCode $notification) use (&$codes): bool {
            $codes[] = $notification->code;

            return true;
        });
        $wrongCode = $codes[0] === '000000' ? '000001' : '000000';

        $response = $this->from(route('verification.notice'))
            ->post(route('verification.verify'), ['code' => $wrongCode]);

        $response->assertRedirect(route('verification.notice'));
        $response->assertSessionHasErrors('code');
        $this->assertGuest();
        $this->assertNull($user->fresh()->email_verified_at);
        $this->assertDatabaseHas('email_verification_codes', [
            'user_id' => $user->id,
            'attempts' => 1,
        ]);
    }

    public function test_otp_cannot_be_used_after_five_failed_attempts(): void
    {
        Notification::fake();
        $user = $this->createPendingUser();
        $code = null;
        Notification::assertSentTo($user, EmailVerificationCode::class, function (EmailVerificationCode $notification) use (&$code): bool {
            $code = $notification->code;

            return true;
        });

        DB::table('email_verification_codes')->where('user_id', $user->id)->update(['attempts' => 5]);

        $response = $this->from(route('verification.notice'))
            ->post(route('verification.verify'), ['code' => $code]);

        $response->assertSessionHasErrors('code');
        $this->assertGuest();
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_expired_otp_is_rejected(): void
    {
        Notification::fake();
        $user = $this->createPendingUser();
        $code = null;
        Notification::assertSentTo($user, EmailVerificationCode::class, function (EmailVerificationCode $notification) use (&$code): bool {
            $code = $notification->code;

            return true;
        });

        DB::table('email_verification_codes')->where('user_id', $user->id)->update(['expires_at' => now()->subMinute()]);

        $response = $this->from(route('verification.notice'))
            ->post(route('verification.verify'), ['code' => $code]);

        $response->assertSessionHasErrors('code');
        $this->assertGuest();
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_resending_otp_sends_a_fresh_code(): void
    {
        Notification::fake();
        $user = $this->createPendingUser();

        $this->post(route('verification.send'))->assertRedirect();

        $codes = [];
        Notification::assertSentTo($user, EmailVerificationCode::class, function (EmailVerificationCode $notification) use (&$codes): bool {
            $codes[] = $notification->code;

            return true;
        });

        $this->assertCount(2, $codes);
        $this->assertNotSame($codes[0], $codes[1]);
        $this->post(route('verification.verify'), ['code' => $codes[1]])
            ->assertRedirect(route('onboarding.welcome'));
        $this->assertAuthenticatedAs($user);
    }

    private function createPendingUser(): User
    {
        $this->post(route('register'), [
            'name' => 'OTP User',
            'email' => 'otp-user@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('verification.notice'));

        return User::query()->where('email', 'otp-user@example.test')->firstOrFail();
    }
}
