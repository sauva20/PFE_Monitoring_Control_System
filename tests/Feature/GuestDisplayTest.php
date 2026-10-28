<?php

namespace Tests\Feature;

use App\Mail\GuestDisplayAccessMail;
use App\Models\GuestDisplay;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class GuestDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_ehss_can_view_guest_display_index(): void
    {
        $admin = User::factory()->create([
            'role' => 'EHSS',
            'pin' => '1234',
        ]);

        $response = $this->actingAs($admin)->get(route('display-access.index'));

        $response->assertStatus(200);
        $response->assertSee(__('Viewer Display Access'));
    }

    public function test_staff_can_access_guest_display_management(): void
    {
        $staff = User::factory()->create([
            'role' => 'Staff',
            'pin' => '1234',
        ]);

        $response = $this->actingAs($staff)->get(route('display-access.index'));

        $response->assertStatus(200);
        $response->assertSee(__('Viewer Display Access'));
    }

    public function test_admin_can_create_guest_display_and_email_is_sent(): void
    {
        Mail::fake();

        $admin = User::factory()->create([
            'role' => 'EHSS',
            'pin' => '1234',
        ]);

        $data = [
            'name' => 'Bpk. Ahmad Sujarwo',
            'institution' => 'PT HSE Global Indonesia',
            'email' => 'ahmad@hse-global.com',
            'pin' => '6543',
            'valid_until' => Carbon::today()->addDays(5)->format('Y-m-d'),
        ];

        $response = $this->actingAs($admin)->post(route('display-access.store'), $data);

        $response->assertRedirect(route('display-access.index'));
        $this->assertDatabaseHas('guest_displays', [
            'name' => 'Bpk. Ahmad Sujarwo',
            'institution' => 'PT HSE Global Indonesia',
            'email' => 'ahmad@hse-global.com',
            'pin' => '6543',
        ]);

        Mail::assertSent(GuestDisplayAccessMail::class, function ($mail) use ($data) {
            return $mail->hasTo($data['email']) && $mail->guestDisplay->pin === $data['pin'];
        });
    }

    public function test_guest_can_access_pin_verification_screen_with_valid_link(): void
    {
        $guest = GuestDisplay::create([
            'name' => 'Dr. Rina',
            'institution' => 'Kementerian Kesehatan',
            'email' => 'rina@kemenkes.go.id',
            'pin' => '1122',
            'access_token' => 'valid-access-token-xyz',
            'valid_until' => Carbon::today()->addDays(2),
            'is_active' => true,
        ]);

        $response = $this->get(route('display.access', ['token' => $guest->access_token]));

        $response->assertStatus(200);
        $response->assertSee('Dr. Rina');
        $response->assertSee('Kementerian Kesehatan');
        $response->assertSee(__('Viewer Access Verification'));
    }

    public function test_guest_gets_expired_page_when_valid_until_date_has_passed(): void
    {
        $guest = GuestDisplay::create([
            'name' => 'Pak Joko',
            'institution' => 'PT Mitra Sejati',
            'email' => 'joko@mitra.com',
            'pin' => '9988',
            'access_token' => 'expired-token-123',
            'valid_until' => Carbon::yesterday(),
            'is_active' => true,
        ]);

        $response = $this->get(route('display.access', ['token' => $guest->access_token]));

        $response->assertStatus(200);
        $response->assertSee(__('Access Validity Period Has Ended'));
    }

    public function test_guest_cannot_verify_with_wrong_pin(): void
    {
        $guest = GuestDisplay::create([
            'name' => 'Ibu Siti',
            'institution' => 'Auditor Internal',
            'email' => 'siti@auditor.com',
            'pin' => '1234',
            'access_token' => 'test-token-siti',
            'valid_until' => Carbon::today()->addDays(3),
            'is_active' => true,
        ]);

        $response = $this->from(route('display.access', ['token' => $guest->access_token]))
            ->post(route('display.access.verify', ['token' => $guest->access_token]), [
                'pin' => '0000', // wrong PIN
            ]);

        $response->assertRedirect(route('display.access', ['token' => $guest->access_token]));
        $response->assertSessionHasErrors(['pin']);
        $this->assertFalse(session()->has("guest_display_auth_{$guest->access_token}"));
    }

    public function test_viewer_ip_is_blocked_for_30_minutes_after_3_failed_pin_attempts(): void
    {
        $guest = GuestDisplay::create([
            'name' => 'Auditor Security',
            'institution' => 'Security Test Lab',
            'email' => 'sec@test.com',
            'pin' => '4321',
            'access_token' => 'sec-token-123',
            'valid_until' => Carbon::today()->addDays(3),
            'is_active' => true,
        ]);

        $url = route('display.access.verify', ['token' => $guest->access_token]);

        // Attempt 1: fail
        $response1 = $this->post($url, ['pin' => '0001']);
        $response1->assertSessionHasErrors(['pin']);
        $this->assertStringContainsString('Sisa kesempatan mencoba: 2 kali', session('errors')->first('pin'));

        // Attempt 2: fail
        $response2 = $this->post($url, ['pin' => '0002']);
        $response2->assertSessionHasErrors(['pin']);
        $this->assertStringContainsString('Sisa kesempatan mencoba: 1 kali', session('errors')->first('pin'));

        // Attempt 3: fail -> blocked for 30 minutes
        $response3 = $this->post($url, ['pin' => '0003']);
        $response3->assertSessionHasErrors(['pin']);
        $this->assertStringContainsString('diblokir', session('errors')->first('pin'));

        // Attempt 4: even if PIN is correct, still blocked!
        $response4 = $this->post($url, ['pin' => '4321']);
        $response4->assertSessionHasErrors(['pin']);
        $this->assertStringContainsString('diblokir', session('errors')->first('pin'));
        $this->assertFalse(session()->has("guest_display_auth_{$guest->access_token}"));
    }

    public function test_guest_can_verify_correct_pin_and_view_report(): void
    {
        $guest = GuestDisplay::create([
            'name' => 'Bpk. Ridwan',
            'institution' => 'Dinas Tenaga Kerja',
            'email' => 'ridwan@disnaker.go.id',
            'pin' => '7891',
            'access_token' => 'token-ridwan-correct',
            'valid_until' => Carbon::today()->addDays(10),
            'is_active' => true,
        ]);

        // Submit correct PIN
        $response = $this->post(route('display.access.verify', ['token' => $guest->access_token]), [
            'pin' => '7891',
        ]);

        $response->assertRedirect(route('display.access.view', ['token' => $guest->access_token]));
        $this->assertTrue(session("guest_display_auth_{$guest->access_token}"));

        // Follow redirect to report view
        $reportResponse = $this->get(route('display.access.view', ['token' => $guest->access_token]));
        $reportResponse->assertStatus(200);
        $reportResponse->assertSee('Bpk. Ridwan');
        $reportResponse->assertSee('Dinas Tenaga Kerja');
    }

    public function test_guest_can_logout_from_display_session(): void
    {
        $guest = GuestDisplay::create([
            'name' => 'Viewer Logout',
            'institution' => 'PT Viewer',
            'email' => 'viewer@viewer.com',
            'pin' => '5555',
            'access_token' => 'token-logout-test',
            'valid_until' => Carbon::today()->addDays(5),
            'is_active' => true,
        ]);

        session(["guest_display_auth_{$guest->access_token}" => true]);

        $response = $this->post(route('display.access.logout', ['token' => $guest->access_token]));

        $response->assertRedirect(route('display.access', ['token' => $guest->access_token]));
        $this->assertFalse(session()->has("guest_display_auth_{$guest->access_token}"));
        $this->assertTrue($guest->fresh()->is_active);

        $followResponse = $this->get(route('display.access', ['token' => $guest->access_token]));
        $followResponse->assertStatus(200);
        $followResponse->assertSee(__('Viewer Access Verification'));
        $followResponse->assertSee(__('You have successfully logged out of the Display Report session.'));
    }

    public function test_unauthenticated_user_accessing_display_report_gets_404(): void
    {
        $response = $this->get(route('display-report'));

        $response->assertStatus(404);
    }

    public function test_authenticated_user_can_access_display_report(): void
    {
        $user = User::factory()->create([
            'role' => 'EHSS',
            'pin' => '1234',
        ]);

        $response = $this->actingAs($user)->get(route('display-report'));

        $response->assertStatus(200);
    }

    public function test_admin_can_update_guest_display(): void
    {
        $admin = User::factory()->create([
            'role' => 'EHSS',
            'pin' => '1234',
        ]);

        $guest = GuestDisplay::create([
            'name' => 'Initial Name',
            'institution' => 'Initial Company',
            'email' => 'initial@company.com',
            'pin' => '1234',
            'access_token' => 'token-update-test',
            'valid_until' => Carbon::today()->addDays(3),
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->put(route('display-access.update', $guest), [
            'name' => 'Updated Name',
            'institution' => 'Updated Company',
            'email' => 'updated@company.com',
            'pin' => '9999',
            'valid_until' => Carbon::today()->addDays(7)->format('Y-m-d'),
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('display-access.index'));
        $this->assertDatabaseHas('guest_displays', [
            'id' => $guest->id,
            'name' => 'Updated Name',
            'pin' => '9999',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_toggle_guest_display_active_status(): void
    {
        $admin = User::factory()->create([
            'role' => 'EHSS',
            'pin' => '1234',
        ]);

        $guest = GuestDisplay::create([
            'name' => 'Toggle Test',
            'institution' => 'Toggle Inc',
            'email' => 'toggle@inc.com',
            'pin' => '4444',
            'access_token' => 'token-toggle-test',
            'valid_until' => Carbon::today()->addDays(5),
            'is_active' => true,
        ]);

        // Toggle from active to inactive
        $response = $this->actingAs($admin)->patch(route('display-access.toggle', $guest));
        $response->assertRedirect();
        $this->assertFalse($guest->fresh()->is_active);

        // Toggle back to active
        $response2 = $this->actingAs($admin)->patch(route('display-access.toggle', $guest));
        $response2->assertRedirect();
        $this->assertTrue($guest->fresh()->is_active);
    }

    public function test_staff_only_sees_guest_displays_they_created(): void
    {
        $staffA = User::factory()->create([
            'name' => 'Staff Alice',
            'role' => 'Staff',
        ]);

        $staffB = User::factory()->create([
            'name' => 'Staff Bob',
            'role' => 'Staff',
        ]);

        $admin = User::factory()->create([
            'name' => 'Admin EHSS',
            'role' => 'EHSS',
        ]);

        $guestAlice = GuestDisplay::create([
            'name' => 'Alice Guest',
            'institution' => 'Alice Corp',
            'email' => 'aliceguest@example.com',
            'pin' => '1111',
            'access_token' => 'token-alice-guest',
            'valid_until' => Carbon::today()->addDays(5),
            'is_active' => true,
            'created_by' => $staffA->id,
        ]);

        $guestBob = GuestDisplay::create([
            'name' => 'Bob Guest',
            'institution' => 'Bob Corp',
            'email' => 'bobguest@example.com',
            'pin' => '2222',
            'access_token' => 'token-bob-guest',
            'valid_until' => Carbon::today()->addDays(5),
            'is_active' => true,
            'created_by' => $staffB->id,
        ]);

        // Staff Alice melihat record miliknya dan kolom Created By
        $responseAlice = $this->actingAs($staffA)->get(route('display-access.index'));
        $responseAlice->assertOk();
        $responseAlice->assertSee('Alice Guest');
        $responseAlice->assertSee('Staff Alice');
        $responseAlice->assertSee(__('Created By'));
        $responseAlice->assertDontSee('Bob Guest');

        // Admin EHSS dapat melihat seluruh data display
        $responseAdmin = $this->actingAs($admin)->get(route('display-access.index'));
        $responseAdmin->assertOk();
        $responseAdmin->assertSee('Alice Guest');
        $responseAdmin->assertSee('Bob Guest');
    }

    public function test_staff_cannot_modify_or_delete_guest_displays_created_by_others(): void
    {
        $staffA = User::factory()->create([
            'name' => 'Staff Alice',
            'role' => 'Staff',
        ]);

        $staffB = User::factory()->create([
            'name' => 'Staff Bob',
            'role' => 'Staff',
        ]);

        $guestBob = GuestDisplay::create([
            'name' => 'Bob Guest',
            'institution' => 'Bob Corp',
            'email' => 'bobguest@example.com',
            'pin' => '2222',
            'access_token' => 'token-bob-guest-2',
            'valid_until' => Carbon::today()->addDays(5),
            'is_active' => true,
            'created_by' => $staffB->id,
        ]);

        // Staff Alice mencoba update display milik Bob -> 403
        $responseUpdate = $this->actingAs($staffA)->put(route('display-access.update', $guestBob), [
            'name' => 'Hacked Name',
            'institution' => 'Hacked',
            'email' => 'hacked@example.com',
            'pin' => '9999',
            'valid_until' => Carbon::today()->addDays(5)->format('Y-m-d'),
        ]);
        $responseUpdate->assertStatus(403);

        // Staff Alice mencoba toggle display milik Bob -> 403
        $responseToggle = $this->actingAs($staffA)->patch(route('display-access.toggle', $guestBob));
        $responseToggle->assertStatus(403);

        // Staff Alice mencoba delete display milik Bob -> 403
        $responseDelete = $this->actingAs($staffA)->delete(route('display-access.destroy', $guestBob));
        $responseDelete->assertStatus(403);

        // Staff Alice mencoba resend email display milik Bob -> 403
        $responseResend = $this->actingAs($staffA)->post(route('display-access.resend', $guestBob));
        $responseResend->assertStatus(403);
    }
}
