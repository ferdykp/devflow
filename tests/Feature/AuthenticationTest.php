<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\EmailVerificationCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_auth_pages(): void
    {
        $this->get('/login')->assertOk();
        $this->get('/register')->assertOk();
    }

    public function test_guest_cannot_access_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
    }

    public function test_user_registration_sends_an_otp_and_waits_for_email_verification(): void
    {
        Notification::fake();

        $this->post('/register', [
            'name' => 'DevFlow User',
            'email' => 'user@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('verification.notice'));

        $this->assertGuest();
        $user = User::query()->where('email', 'user@example.test')->firstOrFail();
        $this->assertNull($user->email_verified_at);
        Notification::assertSentTo($user, EmailVerificationCode::class);
    }

    public function test_user_can_login_and_logout_using_post(): void
    {
        $user = User::factory()->create(['password' => 'password123']);
        $this->post('/login', ['email' => $user->email, 'password' => 'password123'])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
        $this->get('/logout')->assertMethodNotAllowed();
    }

    public function test_invalid_login_has_validation_errors(): void
    {
        $this->post('/login', ['email' => 'missing@example.test', 'password' => 'wrong'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_unverified_user_cannot_login_with_a_password(): void
    {
        $user = User::factory()->unverified()->create(['password' => 'password123']);

        $response = $this->post('/login', ['email' => $user->email, 'password' => 'password123']);

        $response->assertSessionHasErrors('email');
        $response->assertSessionHas('pending_email_verification_user_id', $user->id);
        $this->assertGuest();
    }
}
