<?php

namespace App\Http\Controllers;

use App\Models\Apar;
use App\Models\AparCadangan;
use App\Models\Inspeksi;
use App\Models\Lokasi;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function getDashboardData(Request $request)
    {
        $now = Carbon::now();

        // 0. Filter Parameters
        $selectedYear = (int) $request->input('year', $now->year);
        $selectedMonth = (int) $request->input('month', $now->month);

        if ($selectedMonth < 1 || $selectedMonth > 12) {
            $selectedMonth = (int) $now->month;
        }

        // 1. Summary Cards Data
        $totalApar = Apar::count();

        $startOfSelectedMonth = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->startOfMonth();
        $endOfSelectedMonth = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->endOfMonth();

        $apars = Apar::with('jenis')->get();

        // Ambil data inspeksi yang benar-benar tercatat pada bulan & tahun terpilih
        $inspeksisBulanIni = Inspeksi::whereYear('created_at', $selectedYear)
            ->whereMonth('created_at', $selectedMonth)
            ->latest()
            ->get();

        // 1 APAR hanya dihitung 1 kali berdasarkan inspeksi terakhir di bulan tersebut
        $latestInspeksisByApar = $inspeksisBulanIni->unique('apar_id')->keyBy('apar_id');

        $kondisiBaik = 0;     // Sesuai
        $rusakServis = 0;     // Tidak Sesuai
        $perluRefill = 0;     // Perlu Refill

        foreach ($latestInspeksisByApar as $inspeksi) {
            if (in_array($inspeksi->status, ['sesuai', 'layak', 'baik'])) {
                $kondisiBaik++;
            } elseif (in_array($inspeksi->status, ['tidak_sesuai', 'rusak', 'perbaikan', 'service', 'tidak_layak'])) {
                $rusakServis++;
            } elseif (in_array($inspeksi->status, ['perlu_refill', 'isi_ulang'])) {
                $perluRefill++;
            }
        }

        // Expiry check pada batas bulan terpilih (hanya yang terjadi di bulan tersebut)
        $thirtyDaysFromSelected = $endOfSelectedMonth->copy()->addDays(30);
        $akanKedaluwarsa = 0;
        $sudahKedaluwarsa = 0;

        foreach ($apars as $apar) {
            if ($apar->tgl_kedaluwarsa) {
                // APAR yang kedaluwarsa pada bulan & tahun terpilih
                if ($apar->tgl_kedaluwarsa->year == $selectedYear && $apar->tgl_kedaluwarsa->month == $selectedMonth) {
                    $sudahKedaluwarsa++;
                }
                // APAR yang akan kedaluwarsa pada rentang 30 hari ke depan setelah akhir bulan terpilih
                elseif ($apar->tgl_kedaluwarsa->between($endOfSelectedMonth, $thirtyDaysFromSelected)) {
                    $akanKedaluwarsa++;
                }
            }
        }

        $aparKosong = $perluRefill;

        // 2. Chart Data: Jenis APAR
        $jenisData = $apars->groupBy(function ($item) {
            return $item->jenis->nama ?? 'Unknown';
        })->map->count();

        // 3. Chart Data: Yearly Inspections (Sudah vs Belum)
        $months = collect(range(1, 12))->map(function ($month) use ($selectedYear) {
            return Carbon::createFromDate($selectedYear, $month, 1);
        });

        $yearlyInspections = $months->map(function ($date) use ($totalApar) {
            // Batasi chart agar tidak menampilkan data di masa depan dalam tahun yg sama
            if ($date->isFuture()) {
                return [
                    'month' => $date->translatedFormat('M Y'),
                    'sudah' => 0,
                    'belum' => 0,
                ];
            }

            $inspectedCount = Inspeksi::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->distinct('apar_id')
                ->count('apar_id');

            return [
                'month' => $date->translatedFormat('M Y'),
                'sudah' => $inspectedCount,
                'belum' => max(0, $totalApar - $inspectedCount),
            ];
        });

        // 4. Recent Activities (Latest 5 Inspections)
        $recentInspections = Inspeksi::with(['apar.lokasi', 'user'])
            ->latest()
            ->take(5)
            ->get();

        // 5. Progress Inspeksi Bulan Ini
        $inspeksiBulanIniAparIds = Inspeksi::whereMonth('created_at', $selectedMonth)
            ->whereYear('created_at', $selectedYear)
            ->pluck('apar_id')
            ->unique();

        $sudahDiinspeksiBulanIni = $inspeksiBulanIniAparIds->count();
        $belumDiinspeksiBulanIni = max(0, $totalApar - $sudahDiinspeksiBulanIni);
        $persentaseInspeksi = $totalApar > 0 ? round(($sudahDiinspeksiBulanIni / $totalApar) * 100) : 0;

        $totalStock = AparCadangan::sum('total');

        $sudahDiinspeksiApars = Apar::with('lokasi.gedung')
            ->whereIn('id', $inspeksiBulanIniAparIds)
            ->get();

        $belumDiinspeksiApars = Apar::with('lokasi.gedung')
            ->whereNotIn('id', $inspeksiBulanIniAparIds)
            ->get();

        return compact(
            'totalApar',
            'kondisiBaik',
            'rusakServis',
            'perluRefill',
            'akanKedaluwarsa',
            'sudahKedaluwarsa',
            'aparKosong',
            'totalStock',
            'jenisData',
            'yearlyInspections',
            'recentInspections',
            'sudahDiinspeksiBulanIni',
            'belumDiinspeksiBulanIni',
            'persentaseInspeksi',
            'sudahDiinspeksiApars',
            'belumDiinspeksiApars',
            'selectedMonth',
            'selectedYear'
        );
    }

    public function index(Request $request)
    {
        $data = $this->getDashboardData($request);

        if ($request->ajax()) {
            return response()->json([
                'stats' => [
                    'totalApar' => $data['totalApar'],
                    'kondisiBaik' => $data['kondisiBaik'],
                    'kondisiBaikPercentage' => $data['totalApar'] > 0 ? round(($data['kondisiBaik'] / $data['totalApar']) * 100) : 0,
                    'rusakServis' => $data['rusakServis'],
                    'perluRefill' => $data['perluRefill'],
                    'akanKedaluwarsa' => $data['akanKedaluwarsa'],
                    'sudahKedaluwarsa' => $data['sudahKedaluwarsa'],
                    'aparKosong' => $data['aparKosong'],
                    'totalStock' => $data['totalStock'],
                    'sudahDiinspeksiBulanIni' => $data['sudahDiinspeksiBulanIni'],
                    'belumDiinspeksiBulanIni' => $data['belumDiinspeksiBulanIni'],
                ],
                'charts' => [
                    'yearlyInspections' => $data['yearlyInspections'],
                    'jenisData' => $data['jenisData'],
                ],
                'html' => [
                    'recentInspections' => view('dashboard.partials.recent-inspections', ['recentInspections' => $data['recentInspections']])->render(),
                    'uninspectedTable' => view('dashboard.partials.uninspected-table', ['belumDiinspeksiApars' => $data['belumDiinspeksiApars']])->render(),
                    'inspectedTable' => view('dashboard.partials.inspected-table', ['sudahDiinspeksiApars' => $data['sudahDiinspeksiApars']])->render(),
                ],
            ]);
        }

        return view('dashboard.index', $data);
    }

    public function displayReport(Request $request)
    {
        if (! auth()->check()) {
            abort(404);
        }

        $data = $this->getDashboardData($request);

        return view('dashboard.display-report', $data);
    }

    public function quickSearch(Request $request)
    {
        $q = trim($request->input('q', ''));
        if (strlen($q) < 1) {
            return response()->json([
                'apars' => [],
                'lokasis' => [],
            ]);
        }

        $apars = Apar::with(['lokasi.gedung', 'jenis'])
            ->where(function ($query) use ($q) {
                $query->where('kode', 'like', "%{$q}%")
                    ->orWhere('no_seri', 'like', "%{$q}%");
            })
            ->take(5)
            ->get()
            ->map(function ($a) {
                return [
                    'id' => $a->id,
                    'kode' => $a->kode,
                    'lokasi' => ($a->lokasi->gedung->nama ?? '').' - '.($a->lokasi->nomor_ruangan ? '['.$a->lokasi->nomor_ruangan.'] ' : '').($a->lokasi->nama ?? ''),
                    'jenis' => $a->jenis->nama ?? 'APAR',
                    'url' => route('master-data.index', ['search' => $a->kode]),
                ];
            });

        $lokasis = Lokasi::with('gedung')
            ->where(function ($query) use ($q) {
                $query->where('nama', 'like', "%{$q}%")
                    ->orWhere('nomor_ruangan', 'like', "%{$q}%");
            })
            ->take(5)
            ->get()
            ->map(function ($l) {
                return [
                    'id' => $l->id,
                    'nama' => ($l->nomor_ruangan ? '['.$l->nomor_ruangan.'] ' : '').$l->nama,
                    'gedung' => $l->gedung->nama ?? '',
                    'url' => route('master-data.index', ['tab' => 'referensi', 'search' => $l->nomor_ruangan ?: $l->nama]),
                ];
            });

        return response()->json([
            'apars' => $apars,
            'lokasis' => $lokasis,
        ]);
    }
}
