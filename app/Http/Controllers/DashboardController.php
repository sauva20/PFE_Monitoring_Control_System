<?php

namespace App\Http\Controllers;

use App\Models\Apar;
use App\Models\AparCadangan;
use App\Models\Inspeksi;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    private function getDashboardData(Request $request)
    {
        $now = Carbon::now();

        // 0. Filter Parameters
        $requestedYear = $request->input('year', $now->year);
        $requestedMonth = $request->input('month', $now->month);

        // Validasi: tidak boleh melihat masa depan (bulan/tahun lebih dari saat ini)
        if ($requestedYear > $now->year) {
            $selectedYear = $now->year;
            $selectedMonth = $now->month;
        } elseif ($requestedYear == $now->year && $requestedMonth > $now->month) {
            $selectedYear = $now->year;
            $selectedMonth = $now->month;
        } else {
            $selectedYear = $requestedYear;
            $selectedMonth = $requestedMonth;
        }
        // 1. Summary Cards Data
        $totalApar = Apar::count();

        $endOfSelectedMonth = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->endOfMonth();

        $apars = Apar::with(['inspeksis' => function ($q) use ($endOfSelectedMonth) {
            $q->where('created_at', '<=', $endOfSelectedMonth)->latest();
        }, 'jenis'])->get();

        $kondisiBaik = 0;
        $rusakServis = 0;
        $akanKedaluwarsa = 0;
        $sudahKedaluwarsa = 0;
        $aparKosong = $apars->where('qty', '<', 1)->count();

        $thirtyDaysFromSelected = $endOfSelectedMonth->copy()->addDays(30);

        foreach ($apars as $apar) {
            // Status based on latest inspection up to selected month
            $latestInspeksi = $apar->inspeksis->first();
            if ($latestInspeksi) {
                if ($latestInspeksi->status === 'layak') {
                    $kondisiBaik++;
                } elseif (in_array($latestInspeksi->status, ['rusak', 'perbaikan', 'service'])) {
                    $rusakServis++;
                }
            }

            // Expiry Check relative to selected month
            if ($apar->tgl_kedaluwarsa) {
                if ($apar->tgl_kedaluwarsa->isBefore($endOfSelectedMonth)) {
                    $sudahKedaluwarsa++;
                } elseif ($apar->tgl_kedaluwarsa->between($endOfSelectedMonth, $thirtyDaysFromSelected)) {
                    $akanKedaluwarsa++;
                }
            }
        }

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
        $data = $this->getDashboardData($request);

        return view('dashboard.display-report', $data);
    }
}
