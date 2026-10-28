<?php

namespace Tests\Feature;

use App\Models\Gedung;
use App\Models\JadwalInspeksi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DateValidationRestrictionTest extends TestCase
{
    use RefreshDatabase;

    public function test_reports_clamp_future_year_and_month_to_current_date(): void
    {
        $admin = User::factory()->create([
            'role' => 'EHSS Admin',
        ]);

        $this->actingAs($admin);

        // Requesting year 2099 and month 12
        $response = $this->get(route('reports.index', [
            'year' => 2099,
            'month' => 12,
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('selectedYear', now()->year);
        $response->assertViewHas('selectedMonth', now()->month);
    }

    public function test_reports_clamp_future_month_in_current_year(): void
    {
        $admin = User::factory()->create([
            'role' => 'EHSS Admin',
        ]);

        $this->actingAs($admin);

        // If current month is 10, requesting month 12 in current year should clamp to current month
        if (now()->month < 12) {
            $futureMonth = now()->month + 1;
            $response = $this->get(route('reports.index', [
                'year' => now()->year,
                'month' => $futureMonth,
            ]));

            $response->assertStatus(200);
            $response->assertViewHas('selectedYear', now()->year);
            $response->assertViewHas('selectedMonth', now()->month);
        } else {
            $this->assertTrue(true);
        }
    }

    public function test_cannot_create_inspection_schedule_with_past_date(): void
    {
        $admin = User::factory()->create([
            'role' => 'EHSS Admin',
        ]);
        $staff = User::factory()->create([
            'role' => 'Staff',
        ]);
        $gedung = Gedung::create(['nama' => 'Gedung A']);

        $this->actingAs($admin);

        $yesterday = Carbon::yesterday()->format('Y-m-d');

        $response = $this->post('/inspection-schedule', [
            'jenis_jadwal' => 'Rutin Bulanan',
            'tanggal' => $yesterday,
            'areas' => ['g_'.$gedung->id],
            'user_id' => $staff->id,
        ]);

        $response->assertSessionHasErrors(['tanggal']);
        $this->assertDatabaseMissing('jadwal_inspeksis', [
            'tanggal_inspeksi' => $yesterday,
        ]);
    }

    public function test_can_create_inspection_schedule_with_today_or_future_date(): void
    {
        $admin = User::factory()->create([
            'role' => 'EHSS Admin',
        ]);
        $staff = User::factory()->create([
            'role' => 'Staff',
        ]);
        $gedung = Gedung::create(['nama' => 'Gedung A']);

        $this->actingAs($admin);

        $today = Carbon::today()->format('Y-m-d');

        $response = $this->post('/inspection-schedule', [
            'jenis_jadwal' => 'Rutin Bulanan',
            'tanggal' => $today,
            'areas' => ['g_'.$gedung->id],
            'user_id' => $staff->id,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('jadwal_inspeksis', [
            'tanggal_inspeksi' => $today,
            'gedung_id' => $gedung->id,
            'user_id' => $staff->id,
        ]);
    }

    public function test_cannot_update_inspection_schedule_with_past_date(): void
    {
        $admin = User::factory()->create([
            'role' => 'EHSS Admin',
        ]);
        $staff = User::factory()->create([
            'role' => 'Staff',
        ]);
        $gedung = Gedung::create(['nama' => 'Gedung A']);

        $jadwal = JadwalInspeksi::create([
            'jenis_jadwal' => 'Rutin Bulanan',
            'tanggal_inspeksi' => Carbon::today()->format('Y-m-d'),
            'tipe_area' => 'gedung',
            'gedung_id' => $gedung->id,
            'user_id' => $staff->id,
            'status' => 'menunggu',
        ]);

        $this->actingAs($admin);

        $yesterday = Carbon::yesterday()->format('Y-m-d');

        $response = $this->put("/inspection-schedule/{$jadwal->id}", [
            'jenis_jadwal' => 'Rutin Bulanan',
            'tanggal' => $yesterday,
            'tipe_area' => 'gedung',
            'gedung_id' => $gedung->id,
            'user_id' => $staff->id,
        ]);

        $response->assertSessionHasErrors(['tanggal']);
        $this->assertDatabaseMissing('jadwal_inspeksis', [
            'id' => $jadwal->id,
            'tanggal_inspeksi' => $yesterday,
        ]);
    }
}
