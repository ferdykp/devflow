<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\EmailVerificationCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class GoogleAuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_registration_requires_otp_before_onboarding(): void
    {
        Notification::fake();

        Http::preventStrayRequests();
        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response(['access_token' => 'google-access-token']),
            'https://openidconnect.googleapis.com/v1/userinfo' => Http::response([
                'sub' => 'google-user-100',
                'email' => 'New.User@example.test',
                'email_verified' => true,
                'name' => 'New Google User',
            ]),
        ]);

        $response = $this->withSession([
            'google_oauth_state' => 'expected-state',
            'google_oauth_intent' => 'register',
        ])
            ->get('/auth/google/callback?code=authorization-code&state=expected-state');

        $response->assertRedirect(route('verification.notice'));
        $this->assertGuest();

        $user = User::query()->where('email', 'new.user@example.test')->firstOrFail();
        $this->assertNull($user->email_verified_at);
        $this->assertSame('google-user-100', $user->google_id);
        $this->assertDatabaseMissing('workspaces', [
            'user_id' => $user->id,
            'name' => 'Personal',
        ]);
        $this->assertSame($user->id, session('pending_email_verification_user_id'));

        $code = null;
        Notification::assertSentTo($user, EmailVerificationCode::class, function (EmailVerificationCode $notification) use (&$code): bool {
            $code = $notification->code;

            return true;
        });

        $this->post(route('verification.verify'), ['code' => $code])
            ->assertRedirect(route('onboarding.welcome'));

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertDatabaseHas('workspaces', [
            'user_id' => $user->id,
            'name' => 'Personal',
        ]);
    }

    public function test_google_login_for_an_unregistered_user_starts_registration_with_otp(): void
    {
        Notification::fake();

        Http::preventStrayRequests();
        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response(['access_token' => 'google-access-token']),
            'https://openidconnect.googleapis.com/v1/userinfo' => Http::response([
                'sub' => 'google-user-101',
                'email' => 'first.login@example.test',
                'email_verified' => true,
                'name' => 'First Login',
            ]),
        ]);

        $response = $this->withSession(['google_oauth_state' => 'expected-state'])
            ->get('/auth/google/callback?code=authorization-code&state=expected-state');

        $response->assertRedirect(route('verification.notice'));
        $response->assertSessionHas('status', 'Akun Google belum terdaftar. Kode OTP telah dikirim ke email Anda untuk menyelesaikan pendaftaran.');
        $this->assertGuest();

        $user = User::query()->where('email', 'first.login@example.test')->firstOrFail();
        $this->assertNull($user->email_verified_at);
        $this->assertSame($user->id, session('pending_email_verification_user_id'));
        Notification::assertSentTo($user, EmailVerificationCode::class);
    }

    public function test_google_login_links_a_verified_email_to_an_existing_account(): void
    {
        $user = User::factory()->create(['email' => 'existing@example.test']);

        Http::preventStrayRequests();
        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response(['access_token' => 'google-access-token']),
            'https://openidconnect.googleapis.com/v1/userinfo' => Http::response([
                'sub' => 'google-user-200',
                'email' => 'existing@example.test',
                'email_verified' => true,
            ]),
        ]);

        $response = $this->withSession(['google_oauth_state' => 'expected-state'])
            ->get('/auth/google/callback?code=authorization-code&state=expected-state');

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'google_id' => 'google-user-200',
        ]);
    }

    public function test_google_login_requires_otp_for_an_existing_unverified_account(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create(['email' => 'existing@example.test']);

        Http::preventStrayRequests();
        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response(['access_token' => 'google-access-token']),
            'https://openidconnect.googleapis.com/v1/userinfo' => Http::response([
                'sub' => 'google-user-201',
                'email' => 'existing@example.test',
                'email_verified' => true,
            ]),
        ]);

        $response = $this->withSession(['google_oauth_state' => 'expected-state'])
            ->get('/auth/google/callback?code=authorization-code&state=expected-state');

        $response->assertRedirect(route('verification.notice'));
        $this->assertGuest();
        $this->assertNull($user->fresh()->email_verified_at);
        $this->assertDatabaseMissing('workspaces', [
            'user_id' => $user->id,
            'name' => 'Personal',
        ]);
        Notification::assertSentTo($user, EmailVerificationCode::class);
    }

    public function test_google_login_does_not_take_over_an_account_linked_to_another_google_identity(): void
    {
        $user = User::factory()->create([
            'email' => 'protected@example.test',
            'google_id' => 'google-user-original',
        ]);

        Http::preventStrayRequests();
        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response(['access_token' => 'google-access-token']),
            'https://openidconnect.googleapis.com/v1/userinfo' => Http::response([
                'sub' => 'google-user-attacker',
                'email' => 'protected@example.test',
                'email_verified' => true,
            ]),
        ]);

        $response = $this->withSession(['google_oauth_state' => 'expected-state'])
            ->get('/auth/google/callback?code=authorization-code&state=expected-state');

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'google_id' => 'google-user-original',
        ]);
    }

    public function test_google_callback_rejects_an_unverified_email_without_creating_an_account(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response(['access_token' => 'google-access-token']),
            'https://openidconnect.googleapis.com/v1/userinfo' => Http::response([
                'sub' => 'google-user-300',
                'email' => 'unverified@example.test',
                'email_verified' => false,
            ]),
        ]);

        $response = $this->withSession(['google_oauth_state' => 'expected-state'])
            ->get('/auth/google/callback?code=authorization-code&state=expected-state');

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'unverified@example.test']);
    }

    public function test_google_callback_rejects_a_state_mismatch_before_contacting_google(): void
    {
        Http::preventStrayRequests();

        $response = $this->withSession(['google_oauth_state' => 'expected-state'])
            ->get('/auth/google/callback?code=authorization-code&state=attacker-state');

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_google_redirect_sends_the_configured_callback_and_session_state(): void
    {
        config([
            'services.google.client_id' => 'client-id.apps.googleusercontent.com',
            'services.google.client_secret' => 'client-secret',
            'services.google.redirect' => 'http://localhost/auth/google/callback',
        ]);

        $response = $this->get(route('google.redirect'));

        $response->assertRedirect();
        $this->assertStringStartsWith('https://accounts.google.com/o/oauth2/v2/auth?', $response->headers->get('Location'));
        $response->assertSessionHas('google_oauth_state');
    }

    public function test_google_redirect_explains_when_credentials_are_missing(): void
    {
        config([
            'services.google.client_id' => null,
            'services.google.client_secret' => null,
        ]);

        $response = $this->from(route('login'))->get(route('google.redirect'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['email' => 'Login Google belum dikonfigurasi. Isi GOOGLE_CLIENT_ID dan GOOGLE_CLIENT_SECRET terlebih dahulu.']);
    }
}
