<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders_with_recaptcha_when_configured(): void
    {
        Config::set('services.recaptcha.site_key', 'test_site_key');

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('g-recaptcha');
        $response->assertSee('data-sitekey="test_site_key"', false);
    }

    public function test_login_fails_if_recaptcha_is_missing(): void
    {
        Config::set('services.recaptcha.site_key', 'test_site_key');

        $user = User::factory()->create([
            'email' => 'test.user@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['g-recaptcha-response']);
        $this->assertGuest();
    }

    public function test_login_fails_if_recaptcha_verification_fails(): void
    {
        Config::set('services.recaptcha.site_key', 'test_site_key');
        Config::set('services.recaptcha.secret_key', 'test_secret_key');

        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response(['success' => false], 200),
        ]);

        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
            'g-recaptcha-response' => 'invalid-token',
        ]);

        $response->assertSessionHasErrors(['g-recaptcha-response']);
        $this->assertGuest();
    }

    public function test_login_succeeds_with_valid_recaptcha_and_credentials(): void
    {
        Config::set('services.recaptcha.site_key', 'test_site_key');
        Config::set('services.recaptcha.secret_key', 'test_secret_key');

        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response(['success' => true], 200),
        ]);

        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
            'g-recaptcha-response' => 'valid-token',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_locks_specific_account_after_5_failed_attempts(): void
    {
        Config::set('services.recaptcha.site_key', null);

        $user = User::factory()->create([
            'email' => 'user_test@example.com',
            'password' => Hash::make('correct_password'),
        ]);

        for ($i = 1; $i <= 4; $i++) {
            $response = $this->post('/login', [
                'email' => $user->email,
                'password' => 'wrong_password',
            ]);
            $response->assertSessionHasErrors(['email' => __('Email atau Password yang Anda masukkan salah.')]);
            $this->assertGuest();
        }

        // Percobaan ke-5 -> akun langsung terkunci
        $response5 = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong_password',
        ]);
        $response5->assertSessionHasErrors(['email' => __('Akun Anda diblokir sementara karena terlalu banyak percobaan masuk.')]);
        $this->assertGuest();
    }

    public function test_login_locks_ip_globally_after_10_failed_attempts_across_different_accounts(): void
    {
        Config::set('services.recaptcha.site_key', null);

        // Simulasi bot mencoba 10 akun berbeda dengan password salah dari IP yang sama
        for ($i = 1; $i <= 9; $i++) {
            $user = User::factory()->create([
                'email' => "target_{$i}@example.com",
                'password' => Hash::make('password123'),
            ]);

            $response = $this->post('/login', [
                'email' => $user->email,
                'password' => 'wrong_password',
            ]);
            $response->assertSessionHasErrors(['email' => __('Email atau Password yang Anda masukkan salah.')]);
            $this->assertGuest();
        }

        // Percobaan ke-10 (akun berbeda) -> IP global langsung terblokir
        $user10 = User::factory()->create([
            'email' => 'target_10@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response10 = $this->post('/login', [
            'email' => $user10->email,
            'password' => 'wrong_password',
        ]);
        $response10->assertSessionHasErrors(['email' => __('Akses masuk diblokir sementara karena terlalu banyak percobaan gagal dari perangkat ini.')]);
        $this->assertGuest();

        // Bahkan akun ke-11 yang sama sekali baru tetap ditolak karena IP sudah terblokir
        $user11 = User::factory()->create([
            'email' => 'target_11@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response11 = $this->post('/login', [
            'email' => $user11->email,
            'password' => 'password123',
        ]);
        $response11->assertSessionHasErrors(['email' => __('Akses masuk diblokir sementara karena terlalu banyak percobaan gagal dari perangkat ini.')]);
        $this->assertGuest();
    }
}
