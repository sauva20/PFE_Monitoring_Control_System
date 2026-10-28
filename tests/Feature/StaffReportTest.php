<?php

namespace Tests\Feature;

use App\Models\Apar;
use App\Models\Gedung;
use App\Models\Inspeksi;
use App\Models\JenisApar;
use App\Models\KapasitasApar;
use App\Models\Lokasi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffReportTest extends TestCase
{
    use RefreshDatabase;

    private User $staffA;

    private User $staffB;

    private User $admin;

    private Apar $apar1;

    private Apar $apar2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->staffA = User::factory()->create([
            'name' => 'Staff Alice',
            'role' => 'Staff',
        ]);

        $this->staffB = User::factory()->create([
            'name' => 'Staff Bob',
            'role' => 'Staff',
        ]);

        $this->admin = User::factory()->create([
            'name' => 'Admin EHSS',
            'role' => 'EHSS',
        ]);

        $gedung1 = Gedung::create(['nama' => 'Gedung A']);
        $gedung2 = Gedung::create(['nama' => 'Gedung B']);

        $lokasi1 = Lokasi::create(['nama' => 'Lantai 1', 'gedung_id' => $gedung1->id]);
        $lokasi2 = Lokasi::create(['nama' => 'Lantai 2', 'gedung_id' => $gedung2->id]);

        $jenis = JenisApar::create(['nama' => 'Powder']);
        $kapasitas = KapasitasApar::create(['ukuran' => '3 Kg']);

        $this->apar1 = Apar::create([
            'kode' => 'APAR-ALICE-01',
            'lokasi_id' => $lokasi1->id,
            'jenis_id' => $jenis->id,
            'kapasitas_id' => $kapasitas->id,
        ]);

        $this->apar2 = Apar::create([
            'kode' => 'APAR-BOB-02',
            'lokasi_id' => $lokasi2->id,
            'jenis_id' => $jenis->id,
            'kapasitas_id' => $kapasitas->id,
        ]);

        $this->staffA->gedungs()->attach($gedung1->id);

        $this->apar3 = Apar::create([
            'kode' => 'APAR-ALICE-UNINSPECTED-03',
            'lokasi_id' => $lokasi1->id,
            'jenis_id' => $jenis->id,
            'kapasitas_id' => $kapasitas->id,
        ]);

        $now = Carbon::now();

        // Alice menginspeksi APAR 1
        Inspeksi::create([
            'apar_id' => $this->apar1->id,
            'user_id' => $this->staffA->id,
            'checklist' => ['handle' => 'ok', 'pressure' => 'ok'],
            'status' => 'sesuai',
            'catatan_tambahan' => 'Inspeksi Alice OK',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Bob menginspeksi APAR 2
        Inspeksi::create([
            'apar_id' => $this->apar2->id,
            'user_id' => $this->staffB->id,
            'checklist' => ['handle' => 'rusak', 'pressure' => 'rusak'],
            'status' => 'tidak_sesuai',
            'catatan_tambahan' => 'Inspeksi Bob Rusak',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function test_staff_can_see_reports_link_in_sidebar_and_access_reports_page(): void
    {
        $response = $this->actingAs($this->staffA)->get(route('reports.index'));

        $response->assertOk();
        $response->assertSee('/reports');
        $response->assertSee(__('Inspection Reports'));
    }

    public function test_staff_all_statuses_filter_shows_both_inspected_and_uninspected_in_scope(): void
    {
        // Filter 'all' harus memunculkan yang sudah diinspeksi DAN yang belum diinspeksi milik Staff A
        $response = $this->actingAs($this->staffA)->get(route('reports.index', ['status' => 'all']));

        $response->assertOk();
        $response->assertSee('APAR-ALICE-01');
        $response->assertSee('APAR-ALICE-UNINSPECTED-03');
        $response->assertSee(__('Inspected'));
        $response->assertSee(__('Uninspected'));

        // Tetap tidak boleh melihat milik Staff B di Gedung B
        $response->assertDontSee('APAR-BOB-02');
    }

    public function test_staff_status_filter_sudah_and_belum(): void
    {
        // 1. Filter 'sudah' -> hanya APAR yang sudah diinspeksi
        $responseSudah = $this->actingAs($this->staffA)->get(route('reports.index', ['status' => 'sudah']));
        $responseSudah->assertOk();
        $responseSudah->assertSee('APAR-ALICE-01');
        $responseSudah->assertDontSee('APAR-ALICE-UNINSPECTED-03');
        $responseSudah->assertDontSee('APAR-BOB-02');

        // 2. Filter 'belum' -> hanya APAR yang belum diinspeksi
        $responseBelum = $this->actingAs($this->staffA)->get(route('reports.index', ['status' => 'belum']));
        $responseBelum->assertOk();
        $responseBelum->assertDontSee('APAR-ALICE-01');
        $responseBelum->assertSee('APAR-ALICE-UNINSPECTED-03');
        $responseBelum->assertDontSee('APAR-BOB-02');
    }

    public function test_staff_only_sees_their_own_inspection_records_in_reports(): void
    {
        $response = $this->actingAs($this->staffA)->get(route('reports.index'));

        $response->assertOk();
        // Alice melihat record miliknya
        $response->assertSee('APAR-ALICE-01');
        $response->assertSee('Staff Alice');

        // Alice TIDAK melihat record milik Bob
        $response->assertDontSee('APAR-BOB-02');
        $response->assertDontSee('Staff Bob');
    }

    public function test_staff_excel_preview_only_contains_their_own_records(): void
    {
        $response = $this->actingAs($this->staffA)->get(route('reports.export-excel-preview'));

        $response->assertOk();
        $json = $response->json();

        $rows = collect($json['rows'] ?? []);
        $aparCodes = $rows->pluck(1)->toArray();

        $this->assertContains('APAR-ALICE-01', $aparCodes);
        $this->assertContains('APAR-ALICE-UNINSPECTED-03', $aparCodes);
        $this->assertNotContains('APAR-BOB-02', $aparCodes);
    }

    public function test_staff_export_excel_stream_only_contains_their_records(): void
    {
        $response = $this->actingAs($this->staffA)->get(route('reports.export-excel'));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));

        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        $this->assertStringContainsString('APAR-ALICE-01', $content);
        $this->assertStringContainsString('APAR-ALICE-UNINSPECTED-03', $content);
        $this->assertStringNotContainsString('APAR-BOB-02', $content);
    }

    public function test_staff_export_pdf_returns_successful_stream(): void
    {
        $response = $this->actingAs($this->staffA)->get(route('reports.export-pdf'));

        $response->assertOk();
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
    }

    public function test_admin_sees_all_inspection_records(): void
    {
        $response = $this->actingAs($this->admin)->get(route('reports.index'));

        $response->assertOk();
        $response->assertSee('APAR-ALICE-01');
        $response->assertSee('Staff Alice');
        $response->assertSee('APAR-BOB-02');
        $response->assertSee('Staff Bob');
    }
}
