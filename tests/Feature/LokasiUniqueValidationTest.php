<?php

namespace Tests\Feature;

use App\Models\Gedung;
use App\Models\Lokasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LokasiUniqueValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_allows_same_location_name_with_different_room_numbers_in_same_building(): void
    {
        $admin = User::factory()->create(['role' => 'Super Admin']);
        $gedung = Gedung::create(['nama' => 'BU-S']);

        Lokasi::create([
            'nama' => 'STAGING AREA',
            'gedung_id' => $gedung->id,
            'nomor_ruangan' => 'S.1.NC.003',
            'lantai' => '1',
            'klasifikasi' => 'NC',
        ]);

        $response = $this->actingAs($admin)->post('/master-data/lokasi', [
            'nama' => 'STAGING AREA',
            'gedung_id' => $gedung->id,
            'nomor_ruangan' => 'S.1.NC.020',
            'lantai' => '1',
            'klasifikasi' => 'NC',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseCount('lokasi', 2);
    }

    public function test_prevents_duplicate_room_sequence_number_in_same_building_even_if_overall_id_differs(): void
    {
        $admin = User::factory()->create(['role' => 'Super Admin']);
        $gedung = Gedung::create(['nama' => 'BU-S']);

        Lokasi::create([
            'nama' => 'STAGING AREA',
            'gedung_id' => $gedung->id,
            'nomor_ruangan' => 'S.1.NC.020',
            'lantai' => '1',
            'klasifikasi' => 'NC',
        ]);

        // Same building, different floor and classification and name, but same sequence number 20 / 020
        $response = $this->actingAs($admin)->post('/master-data/lokasi', [
            'nama' => 'PRODUCTION ROOM',
            'gedung_id' => $gedung->id,
            'nomor_ruangan' => 'S.2.C.020',
            'lantai' => '2',
            'klasifikasi' => 'C',
        ]);

        $response->assertSessionHasErrors('nomor_ruangan');
        $this->assertDatabaseCount('lokasi', 1);
    }

    public function test_allows_same_sequence_number_in_different_buildings(): void
    {
        $admin = User::factory()->create(['role' => 'Super Admin']);
        $gedungA = Gedung::create(['nama' => 'BU-S']);
        $gedungB = Gedung::create(['nama' => 'BU-P']);

        Lokasi::create([
            'nama' => 'STAGING AREA',
            'gedung_id' => $gedungA->id,
            'nomor_ruangan' => 'S.1.NC.020',
            'lantai' => '1',
            'klasifikasi' => 'NC',
        ]);

        $response = $this->actingAs($admin)->post('/master-data/lokasi', [
            'nama' => 'STORAGE',
            'gedung_id' => $gedungB->id,
            'nomor_ruangan' => 'P.1.NC.020',
            'lantai' => '1',
            'klasifikasi' => 'NC',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseCount('lokasi', 2);
    }
}
