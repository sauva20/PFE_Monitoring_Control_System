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

    public function test_sesi_inspeksi_tetap_aktif_saat_menscan_apar_berikutnya()
    {
        $user = User::factory()->create([
            'name' => 'Petugas Lapangan',
            'pin' => Hash::make('1234'),
            'role' => 'Staff',
        ]);

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
            'tgl_kedaluwarsa' => now()->addYear(),
        ]);

        $apar2 = Apar::create([
            'kode' => 'PFE-R-A2',
            'gedung_id' => 1,
            'lokasi_id' => 1,
            'jenis_id' => 1,
            'kapasitas_id' => 1,
            'tgl_kedaluwarsa' => now()->addYear(),
        ]);

        // 1. User verifikasi PIN di APAR 1
        $this->post(route('scan.verify', ['kode' => $apar1->kode]), [
            'pin' => '1234',
        ]);
        $this->assertAuthenticatedAs($user);

        // 2. User mulai pedoman APAR 1 (mengaktifkan form)
        $this->get(route('inspeksi.pedoman', ['apar' => $apar1->id]));
        $this->assertTrue(session('active_inspection_'.$apar1->id));

        // 3. User menyelesaikan inspeksi APAR 1
        $checklist = [];
        for ($i = 0; $i < 25; $i++) {
            $checklist[$i] = [
                'jawaban' => 'ya',
                'keterangan' => null,
            ];
        }

        $resStore = $this->post(route('inspeksi.store', ['apar' => $apar1->id]), [
            'checklist' => $checklist,
            'status' => 'layak',
            'foto_base64' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=',
            'qty' => 1,
            'tgl_kedaluwarsa' => now()->addYear()->format('Y-m-d'),
            'jenis_id' => 1,
            'kapasitas_id' => 1,
        ]);

        // Selesai inspeksi: user TIDAK di-logout paksa
        $this->assertAuthenticatedAs($user);
        $resStore->assertRedirect(route('inspeksi.sukses', ['apar' => $apar1->id]));

        // 4. Petugas menscan APAR 2
        $resScan2 = $this->get(route('scan.apar', ['kode' => $apar2->kode]));

        // Petugas tetap login dan diarahkan ke link inspeksi langsung tanpa modal PIN
        $this->assertAuthenticatedAs($user);
        $resScan2->assertStatus(200);
        $resScan2->assertSee(route('inspeksi.pedoman', $apar2->id));
        $resScan2->assertSee('Petugas Lapangan');
    }
}
