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

    public function test_allows_same_sequence_number_on_different_floors_in_same_building(): void
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

        // Same building (BU-S), but different floor (Lantai 2). Sequence number 20 / 020 IS ALLOWED.
        $response = $this->actingAs($admin)->post('/master-data/lokasi', [
            'nama' => 'PRODUCTION ROOM',
            'gedung_id' => $gedung->id,
            'nomor_ruangan' => 'S.2.NC.020',
            'lantai' => '2',
            'klasifikasi' => 'NC',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseCount('lokasi', 2);
    }

    public function test_prevents_duplicate_room_sequence_on_same_floor_and_building(): void
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

        // Same building (BU-S) AND same floor (1). Sequence number 20 / 020 MUST BE PREVENTED.
        $response = $this->actingAs($admin)->post('/master-data/lokasi', [
            'nama' => 'PACKAGING ROOM',
            'gedung_id' => $gedung->id,
            'nomor_ruangan' => 'S.1.C.020',
            'lantai' => '1',
            'klasifikasi' => 'C',
        ]);

        $response->assertSessionHasErrors('nomor_ruangan');
        $this->assertDatabaseCount('lokasi', 1);
    }

    public function test_supports_and_validates_letter_suffix_sequence(): void
    {
        $admin = User::factory()->create(['role' => 'Super Admin']);
        $gedung = Gedung::create(['nama' => 'BU-S']);

        // Create 006A
        Lokasi::create([
            'nama' => 'AIRLOCK ROOM',
            'gedung_id' => $gedung->id,
            'nomor_ruangan' => 'S.1.NC.006A',
            'lantai' => '1',
            'klasifikasi' => 'NC',
        ]);

        // 006B on same floor should be allowed
        $response1 = $this->actingAs($admin)->post('/master-data/lokasi', [
            'nama' => 'AIRLOCK ROOM 2',
            'gedung_id' => $gedung->id,
            'nomor_ruangan' => 'S.1.NC.006B',
            'lantai' => '1',
            'klasifikasi' => 'NC',
        ]);
        $response1->assertSessionHasNoErrors();

        // 006A on same floor should be prevented
        $response2 = $this->actingAs($admin)->post('/master-data/lokasi', [
            'nama' => 'AIRLOCK ROOM 3',
            'gedung_id' => $gedung->id,
            'nomor_ruangan' => 'S.1.C.006A',
            'lantai' => '1',
            'klasifikasi' => 'C',
        ]);
        $response2->assertSessionHasErrors('nomor_ruangan');
    }
}
