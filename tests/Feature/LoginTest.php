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
            'email' => 'qisty.sauva@bbraun.com',
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
}
