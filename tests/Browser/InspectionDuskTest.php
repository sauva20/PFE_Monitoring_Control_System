<?php

namespace Tests\Browser;

use App\Models\Apar;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class InspectionDuskTest extends DuskTestCase
{
    public function test_user_dapat_login_menggunakan_pin_saat_scan_apar_dusk(): void
    {
        // 1. Ambil data APAR pertama dari database yang sudah ada
        $apar = Apar::first();

        if (! $apar) {
            $this->markTestSkipped('Tidak ada data APAR di database untuk diuji.');
        }

        // 2. Buat User sementara untuk testing login
        $user = User::factory()->create([
            'pin' => Hash::make('1234'),
            'role' => 'EHSS',
        ]);

        // 3. Aksi: Browser otomatis mengunjungi URL Scan
        $this->browse(function (Browser $browser) use ($apar) {
            $browser->visit('/scan/'.$apar->kode)
                ->assertSee('PIN Petugas')
                ->type('pin', '1234')
                ->press('Verify & Continue')
                ->assertPathIs('/inspeksi/pedoman/'.$apar->id);
        });

        // Bersihkan data user dummy
        $user->delete();
    }
}
