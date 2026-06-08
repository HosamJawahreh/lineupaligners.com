<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['mail.default' => 'smtp']);
    }

    public function test_forgot_password_sends_branded_reset_notification(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'doctor@example.com',
            'role' => User::ROLE_ADMIN,
        ]);

        $response = $this->post(route('pages.forgot-password'), [
            'email' => $user->email,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        Notification::assertSentTo(
            $user,
            ResetPasswordNotification::class,
            function (ResetPasswordNotification $notification) use ($user): bool {
                $mail = $notification->toMail($user);
                $rendered = $mail->render();

                return str_contains($mail->subject, 'Reset your password')
                    && str_contains($mail->markdown, 'mail.reset-password')
                    && str_contains($rendered, route('password.reset', [
                        'token' => $notification->token,
                        'email' => $user->email,
                    ], false))
                    && str_contains($rendered, 'email='.$user->email);
            }
        );
    }

    public function test_forgot_password_does_not_reveal_unknown_accounts(): void
    {
        Notification::fake();

        $response = $this->post(route('pages.forgot-password'), [
            'email' => 'missing@example.com',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        Notification::assertNothingSent();
    }

    public function test_user_can_reset_password_with_valid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'doctor@example.com',
            'role' => User::ROLE_ADMIN,
        ]);

        $token = Password::createToken($user);

        $response = $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');

        $user->refresh();

        $this->assertTrue(Hash::check('new-secure-password', $user->password));
    }

    public function test_reset_password_page_is_accessible_with_token(): void
    {
        $response = $this->get(route('password.reset', [
            'token' => 'sample-token',
            'email' => 'doctor@example.com',
        ]));

        $response->assertOk();
        $response->assertSee('Choose a new password');
    }
}
