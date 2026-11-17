<?php

namespace Tests\Feature;

use App\Http\Controllers\DashboardController;
use App\Models\Apar;
use App\Models\Gedung;
use App\Models\JenisApar;
use App\Models\KapasitasApar;
use App\Models\Lokasi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class DashboardExpiryHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_expired_counts_reflect_current_dates_for_any_selected_month(): void
    {
        $this->travelTo('2026-11-09 12:00:00');

        $gedung = Gedung::create(['nama' => 'Gedung Uji']);
        $lokasi = Lokasi::create(['nama' => 'Lokasi Uji', 'gedung_id' => $gedung->id]);
        $jenis = JenisApar::create(['nama' => 'Powder']);
        $kapasitas = KapasitasApar::create(['ukuran' => '3 Kg']);
        foreach (['2026-04-30', '2026-03-06', '2026-12-09', '2026-12-10'] as $index => $expiryDate) {
            Apar::create([
                'kode' => 'APAR-UJI-'.$index,
                'lokasi_id' => $lokasi->id,
                'jenis_id' => $jenis->id,
                'kapasitas_id' => $kapasitas->id,
                'tgl_kedaluwarsa' => $expiryDate,
            ]);
        }

        $controller = app(DashboardController::class);
        $october = $controller->getDashboardData(new Request(['year' => 2026, 'month' => 10]));
        $november = $controller->getDashboardData(new Request(['year' => 2026, 'month' => 11]));

        $this->assertSame(2, $october['sudahKedaluwarsa']);
        $this->assertSame(2, $november['sudahKedaluwarsa']);
        $this->assertSame(1, $october['akanKedaluwarsa']);
        $this->assertSame(1, $november['akanKedaluwarsa']);
    }
}
