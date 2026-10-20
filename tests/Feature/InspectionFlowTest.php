<?php

namespace Tests\Feature;

use App\Models\Apar;
use App\Models\Gedung;
use App\Models\JenisApar;
use App\Models\KapasitasApar;
use App\Models\Lokasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InspectionFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_dapat_login_menggunakan_pin_saat_scan_apar()
    {
        // 1. Persiapan Data (Simulasi Database)
        $user = User::factory()->create([
            'pin' => Hash::make('1234'), // PIN sudah di-hash sesuai aturan terbaru
            'role' => 'EHSS',
        ]);

        // Karena belum ada factory, buat manual relasinya
        Gedung::create(['id' => 1, 'nama' => 'Gedung A']);
        Lokasi::create(['id' => 1, 'nama' => 'Lantai 1', 'gedung_id' => 1]);
        JenisApar::create(['id' => 1, 'nama' => 'Powder']);
        KapasitasApar::create(['id' => 1, 'ukuran' => '3 Kg']);

        $apar = Apar::create([
            'kode' => 'PFE-R-A1',
            'gedung_id' => 1,
            'lokasi_id' => 1,
            'jenis_id' => 1,
            'kapasitas_id' => 1,
        ]);

        // 2. Aksi: User mensimulasikan hit endpoint Verify PIN dengan PIN 1234
        $response = $this->post(route('scan.verify', ['kode' => 'PFE-R-A1']), [
            'pin' => '1234',
        ]);

        // 3. Pengecekan Hasil:
        // - Harusnya sistem me-login-kan user ini
        $this->assertAuthenticatedAs($user);

        // - Harusnya user dialihkan/diperbolehkan masuk ke halaman Form (bukan balik error)
        // Note: verifyPin bisa redirect ke 'inspeksi.pedoman' atau kembali ke halaman scan index tergantung session,
        // tapi pastinya tidak ada session error 'PIN tidak valid'.
        $response->assertSessionHasNoErrors();
    }

    public function test_user_diblokir_jika_salah_memasukkan_pin_5_kali_tanpa_menampilkan_durasi_waktu()
    {
        Gedung::create(['id' => 1, 'nama' => 'Gedung A']);
        Lokasi::create(['id' => 1, 'nama' => 'Lantai 1', 'gedung_id' => 1]);
        JenisApar::create(['id' => 1, 'nama' => 'Powder']);
        KapasitasApar::create(['id' => 1, 'ukuran' => '3 Kg']);

        $apar1 = Apar::create([
            'kode' => 'PFE-R-A1',
            'gedung_id' => 1,
            'lokasi_id' => 1,
            'jenis_id' => 1,
            'kapasitas_id' => 1,
        ]);

        $apar2 = Apar::create([
            'kode' => 'PFE-R-A2',
            'gedung_id' => 1,
            'lokasi_id' => 1,
            'jenis_id' => 1,
            'kapasitas_id' => 1,
        ]);

        // Percobaan 1 sampai 4 dengan PIN salah
        for ($i = 1; $i <= 4; $i++) {
            $res = $this->post(route('scan.verify', ['kode' => $apar1->kode]), ['pin' => '9999']);
            $res->assertSessionHas('error', 'PIN tidak valid atau tidak ditemukan.');
            $this->assertGuest();
        }

        // Percobaan ke-5 dengan PIN salah -> langsung terblokir
        $res5 = $this->post(route('scan.verify', ['kode' => $apar1->kode]), ['pin' => '9999']);
        $res5->assertSessionHas('error', 'Akses verifikasi PIN diblokir sementara karena terlalu banyak percobaan salah.');
        $this->assertGuest();

        // Pastikan tidak ada bocoran durasi waktu (detik/menit) di pesan error
        $errorMessage = session('error');
        $this->assertStringNotContainsString('detik', strtolower($errorMessage));
        $this->assertStringNotContainsString('menit', strtolower($errorMessage));
        $this->assertStringNotContainsString('second', strtolower($errorMessage));
        $this->assertStringNotContainsString('minute', strtolower($errorMessage));

        // Percobaan ke-6 bahkan di APAR lain (apar2) tetap diblokir (blokir global per IP)
        $res6 = $this->post(route('scan.verify', ['kode' => $apar2->kode]), ['pin' => '9999']);
        $res6->assertSessionHas('error', 'Akses verifikasi PIN diblokir sementara karena terlalu banyak percobaan salah.');
        $this->assertGuest();
    }
}
