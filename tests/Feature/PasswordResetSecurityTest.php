<?php

namespace Tests\Feature;

use App\Mail\NewUserRegistered;
use App\Mail\ResetPassword;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class PasswordResetSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_user_can_request_password_reset_link_and_token_is_created(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('oldpassword123'),
        ]);

        $response = $this->post(route('password.email'), [
            'email' => 'user@example.com',
        ]);

        $response->assertSessionHas('status');
        Mail::assertSent(ResetPassword::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });

        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => 'user@example.com',
        ]);
    }

    public function test_password_reset_is_limited_to_3_attempts_per_email_in_a_month(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'limit@example.com',
        ]);

        for ($i = 1; $i <= 3; $i++) {
            $response = $this->post(route('password.email'), [
                'email' => 'limit@example.com',
            ]);
            $response->assertSessionHas('status');
        }

        // 4th attempt in the same month should fail with generic error
        $response = $this->post(route('password.email'), [
            'email' => 'limit@example.com',
        ]);

        $response->assertSessionHasErrors(['email' => 'Terlalu banyak percobaan.']);
    }

    public function test_password_can_be_reset_once_and_link_becomes_invalid_immediately(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'resetme@example.com',
            'password' => Hash::make('oldpassword123'),
        ]);

        $this->post(route('password.email'), [
            'email' => 'resetme@example.com',
        ]);

        $tokenRecord = DB::table('password_reset_tokens')->where('email', $user->email)->first();
        $this->assertNotNull($tokenRecord);

        // Retrieve the generated URL from the sent mailable
        $sentMail = null;
        Mail::assertSent(ResetPassword::class, function ($mail) use (&$sentMail) {
            $sentMail = $mail;

            return true;
        });

        $this->assertNotNull($sentMail);
        $resetUrl = $sentMail->resetUrl;

        // 1. First visit to show form should succeed (200 OK)
        $response = $this->get($resetUrl);
        $response->assertStatus(200);

        // Parse token from query parameter
        parse_str(parse_url($resetUrl, PHP_URL_QUERY), $queryParams);
        $token = $queryParams['token'] ?? null;
        $this->assertNotEmpty($token);

        // 2. Submit new password
        $submitResponse = $this->post($resetUrl, [
            'token' => $token,
            'password' => 'NewSecretPassword123!',
            'password_confirmation' => 'NewSecretPassword123!',
        ]);

        $submitResponse->assertRedirect(route('login'));
        $submitResponse->assertSessionHas('success');

        // Verify password was updated
        $this->assertTrue(Hash::check('NewSecretPassword123!', $user->fresh()->password));

        // Verify token in password_reset_tokens table was deleted
        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => $user->email,
        ]);

        // 3. Reopening the reset password link should be rejected immediately
        $reopenResponse = $this->get($resetUrl);
        $reopenResponse->assertRedirect(route('password.request'));
        $reopenResponse->assertSessionHasErrors(['email']);

        // 4. Trying to submit again with the used token should also be rejected
        $resubmitResponse = $this->post($resetUrl, [
            'token' => $token,
            'password' => 'AnotherPassword123!',
            'password_confirmation' => 'AnotherPassword123!',
        ]);
        $resubmitResponse->assertRedirect(route('password.request'));
        $resubmitResponse->assertSessionHasErrors(['email']);
    }

    public function test_new_user_registration_sends_plain_pin_not_bcrypt_hash(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['role' => 'EHSS Admin']);
        $this->actingAs($admin);

        $response = $this->post(route('users.store'), [
            'employee_id' => 'EMP12345',
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'role' => 'Staff',
            'pin' => '7890',
        ]);

        $response->assertSessionHasNoErrors();

        Mail::assertSent(NewUserRegistered::class, function ($mail) {
            return $mail->hasTo('budi@example.com')
                && $mail->pin === '7890'
                && ! str_starts_with($mail->pin, '$2y$');
        });
    }
}
