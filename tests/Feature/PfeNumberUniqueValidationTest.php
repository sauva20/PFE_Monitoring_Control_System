<?php

namespace Tests\Feature;

use App\Models\Apar;
use App\Models\Gedung;
use App\Models\JenisApar;
use App\Models\KapasitasApar;
use App\Models\Lokasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PfeNumberUniqueValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_prevents_duplicate_pfe_number_globally_on_store(): void
    {
        $admin = User::factory()->create(['role' => 'Super Admin']);
        $gedung1 = Gedung::create(['nama' => 'BU-W']);
        $lokasi1 = Lokasi::create([
            'nama' => 'WATER TANK 1',
            'gedung_id' => $gedung1->id,
            'nomor_ruangan' => 'W.0.NC.001',
        ]);
        $jenis = JenisApar::create(['nama' => 'Dry Chemical Powder']);
        $kapasitas = KapasitasApar::create(['ukuran' => '5 Kg']);

        Apar::create([
            'kode' => 'PFE-W-A1',
            'lokasi_id' => $lokasi1->id,
            'jenis_id' => $jenis->id,
            'kapasitas_id' => $kapasitas->id,
            'pic_id' => $admin->id,
            'nomor_ruangan' => 'W.0.NC.001',
            'tgl_pengadaan' => now(),
            'tgl_kedaluwarsa' => now()->addYear(),
            'tgl_isi_ulang' => now(),
            'qty' => 1,
            'vendor' => 'N/A',
        ]);

        $gedung2 = Gedung::create(['nama' => 'BU-S']);
        $lokasi2 = Lokasi::create([
            'nama' => 'STAGING AREA',
            'gedung_id' => $gedung2->id,
            'nomor_ruangan' => 'S.1.NC.002',
        ]);

        // Attempt to create another APAR with same number 1 in a different building
        $response = $this->actingAs($admin)->post('/master-data/apar', [
            'gedung_id' => $gedung2->id,
            'lokasi_id' => $lokasi2->id,
            'lokasi' => $lokasi2->nama,
            'nomor_ruangan' => $lokasi2->nomor_ruangan,
            'jenis_id' => $jenis->id,
            'kapasitas_id' => $kapasitas->id,
            'nomor_apar' => '1',
            'qty' => 1,
            'tgl_kedaluwarsa' => now()->addYear()->format('Y-m-d'),
            'tgl_isi_ulang' => now()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors(['nomor_apar']);
        $this->assertDatabaseCount('apar', 1);
    }

    public function test_allows_unique_pfe_number_on_store(): void
    {
        $admin = User::factory()->create(['role' => 'Super Admin']);
        $gedung = Gedung::create(['nama' => 'BU-W']);
        $lokasi = Lokasi::create([
            'nama' => 'WATER TANK 1',
            'gedung_id' => $gedung->id,
            'nomor_ruangan' => 'W.0.NC.001',
        ]);
        $jenis = JenisApar::create(['nama' => 'Dry Chemical Powder']);
        $kapasitas = KapasitasApar::create(['ukuran' => '5 Kg']);

        Apar::create([
            'kode' => 'PFE-W-A1',
            'lokasi_id' => $lokasi->id,
            'jenis_id' => $jenis->id,
            'kapasitas_id' => $kapasitas->id,
            'pic_id' => $admin->id,
            'nomor_ruangan' => 'W.0.NC.001',
            'tgl_pengadaan' => now(),
            'tgl_kedaluwarsa' => now()->addYear(),
            'tgl_isi_ulang' => now(),
            'qty' => 1,
            'vendor' => 'N/A',
        ]);

        // Create with number 2
        $response = $this->actingAs($admin)->post('/master-data/apar', [
            'gedung_id' => $gedung->id,
            'lokasi_id' => $lokasi->id,
            'lokasi' => $lokasi->nama,
            'nomor_ruangan' => $lokasi->nomor_ruangan,
            'jenis_id' => $jenis->id,
            'kapasitas_id' => $kapasitas->id,
            'nomor_apar' => '2',
            'qty' => 1,
            'tgl_kedaluwarsa' => now()->addYear()->format('Y-m-d'),
            'tgl_isi_ulang' => now()->format('Y-m-d'),
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseCount('apar', 2);
    }

    public function test_prevents_duplicate_pfe_number_on_update(): void
    {
        $admin = User::factory()->create(['role' => 'Super Admin']);
        $gedung = Gedung::create(['nama' => 'BU-W']);
        $lokasi = Lokasi::create([
            'nama' => 'WATER TANK 1',
            'gedung_id' => $gedung->id,
            'nomor_ruangan' => 'W.0.NC.001',
        ]);
        $jenis = JenisApar::create(['nama' => 'Dry Chemical Powder']);
        $kapasitas = KapasitasApar::create(['ukuran' => '5 Kg']);

        $apar1 = Apar::create([
            'kode' => 'PFE-W-A1',
            'lokasi_id' => $lokasi->id,
            'jenis_id' => $jenis->id,
            'kapasitas_id' => $kapasitas->id,
            'pic_id' => $admin->id,
            'nomor_ruangan' => 'W.0.NC.001',
            'tgl_pengadaan' => now(),
            'tgl_kedaluwarsa' => now()->addYear(),
            'tgl_isi_ulang' => now(),
            'qty' => 1,
            'vendor' => 'N/A',
        ]);

        $apar2 = Apar::create([
            'kode' => 'PFE-W-A2',
            'lokasi_id' => $lokasi->id,
            'jenis_id' => $jenis->id,
            'kapasitas_id' => $kapasitas->id,
            'pic_id' => $admin->id,
            'nomor_ruangan' => 'W.0.NC.001',
            'tgl_pengadaan' => now(),
            'tgl_kedaluwarsa' => now()->addYear(),
            'tgl_isi_ulang' => now(),
            'qty' => 1,
            'vendor' => 'N/A',
        ]);

        // Attempt to update $apar2 to number 1 which is already held by $apar1
        $response = $this->actingAs($admin)->put('/master-data/apar/'.$apar2->id, [
            'gedung_id' => $gedung->id,
            'lokasi_id' => $lokasi->id,
            'lokasi' => $lokasi->nama,
            'nomor_ruangan' => $lokasi->nomor_ruangan,
            'jenis_id' => $jenis->id,
            'kapasitas_id' => $kapasitas->id,
            'nomor_apar' => '1',
            'qty' => 1,
            'tgl_kedaluwarsa' => now()->addYear()->format('Y-m-d'),
            'tgl_isi_ulang' => now()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors(['nomor_apar']);
        $this->assertEquals('PFE-W-A2', $apar2->fresh()->kode);
    }
}
