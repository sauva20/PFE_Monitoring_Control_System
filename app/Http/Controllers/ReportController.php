<?php

namespace App\Http\Controllers;

use App\Models\Apar;
use App\Models\Gedung;
use App\Models\Inspeksi;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class ReportController extends Controller
{
    private function getReportData(Request $request)
    {
        $now = Carbon::now();
        $selectedYear = $request->input('year', $now->year);
        $selectedMonth = $request->input('month', $now->month);
        $selectedGedung = $request->input('gedung_id');
        $status = $request->input('status', 'all');

        $query = Apar::with(['lokasi.gedung', 'jenis', 'kapasitas']);

        if ($selectedGedung) {
            $query->whereHas('lokasi', function ($q) use ($selectedGedung) {
                $q->where('gedung_id', $selectedGedung);
            });
        }

        $apars = $query->orderBy('kode', 'asc')->get();

        $inspeksiBulanIniAparIds = Inspeksi::whereMonth('created_at', $selectedMonth)
            ->whereYear('created_at', $selectedYear)
            ->pluck('apar_id')
            ->unique()
            ->toArray();

        $reportData = collect();

        foreach ($apars as $apar) {
            $isInspected = in_array($apar->id, $inspeksiBulanIniAparIds);

            if ($status === 'sudah' && ! $isInspected) {
                continue;
            }
            if ($status === 'belum' && $isInspected) {
                continue;
            }

            $inspeksi = null;
            if ($isInspected) {
                $inspeksi = Inspeksi::with('user')->where('apar_id', $apar->id)
                    ->whereMonth('created_at', $selectedMonth)
                    ->whereYear('created_at', $selectedYear)
                    ->latest()
                    ->first();
            }

            $reportData->push([
                'apar' => $apar,
                'status' => $isInspected ? 'Sudah Diinspeksi' : 'Belum Diinspeksi',
                'inspeksi' => $inspeksi,
            ]);
        }

        return [
            'data' => $reportData,
            'selectedYear' => $selectedYear,
            'selectedMonth' => $selectedMonth,
            'selectedGedung' => $selectedGedung,
            'status' => $status,
        ];
    }

    public function index(Request $request)
    {
        $gedungs = Gedung::all();
        $reportParams = $this->getReportData($request);
        $reportData = $reportParams['data'];

        $perPage = 15;
        $page = Paginator::resolveCurrentPage() ?: 1;
        $paginatedData = new LengthAwarePaginator(
            $reportData->forPage($page, $perPage),
            $reportData->count(),
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath()]
        );
        $paginatedData->appends($request->all());

        return view('reports.index', array_merge($reportParams, [
            'paginatedData' => $paginatedData,
            'gedungs' => $gedungs,
        ]));
    }

    public function exportPdf(Request $request)
    {
        $reportParams = $this->getReportData($request);
        $reportData = $reportParams['data'];

        $month = Carbon::createFromDate($reportParams['selectedYear'], $reportParams['selectedMonth'], 1);
        $monthName = __($month->format('F')).' '.$month->format('Y');
        $gedungName = $reportParams['selectedGedung'] ? Gedung::find($reportParams['selectedGedung'])->nama ?? __('All Buildings') : __('All Buildings');

        $pdf = Pdf::loadView('reports.pdf', [
            'reportData' => $reportData,
            'monthName' => $monthName,
            'gedungName' => $gedungName,
            'status' => $reportParams['status'],
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Laporan_Inspeksi_'.$monthName.'.pdf');
    }

    public function exportExcel(Request $request)
    {
        $reportParams = $this->getReportData($request);
        $reportData = $reportParams['data'];
        $month = Carbon::createFromDate($reportParams['selectedYear'], $reportParams['selectedMonth'], 1);
        $monthName = __($month->format('F')).' '.$month->format('Y');

        $filename = 'Laporan_Inspeksi_'.date('Ymd_His').'.csv';

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$filename",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $columns = [__('No'), __('PFE ID'), __('Building'), __('Location'), __('Type'), __('Capacity'), __('Inspection Status'), __('Inspection Date'), __('Inspector'), __('Condition'), __('Additional Notes')];

        $callback = function () use ($reportData, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            $no = 1;
            foreach ($reportData as $row) {
                $apar = $row['apar'];
                $inspeksi = $row['inspeksi'];
                $isInspected = $row['status'] === 'Sudah Diinspeksi';

                fputcsv($file, [
                    $no++,
                    $apar->kode,
                    $apar->lokasi->gedung->nama ?? 'n/a',
                    $apar->lokasi->nama ?? 'n/a',
                    $apar->jenis->nama ?? 'n/a',
                    $apar->kapasitas->ukuran ?? 'n/a',
                    $isInspected ? 'Sudah Diinspeksi' : 'Belum Diinspeksi',
                    $inspeksi ? Carbon::parse($inspeksi->created_at)->format('d M Y H:i') : 'n/a',
                    $inspeksi?->user?->name ?? 'n/a',
                    $inspeksi?->status ?? 'n/a',
                    $inspeksi?->catatan_tambahan ?? 'n/a',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function previewExcel(Request $request)
    {
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');

        $apars = Apar::with(['lokasi.gedung', 'jenis', 'kapasitas', 'inspeksis' => function ($q) use ($bulan, $tahun) {
            $q->whereYear('created_at', $tahun)
                ->whereMonth('created_at', $bulan);
        }])->get();

        $reportData = collect();
        foreach ($apars as $apar) {
            $inspeksiThisMonth = $apar->inspeksis->first();
            $reportData->push([
                'apar' => $apar,
                'status' => $inspeksiThisMonth ? 'Sudah Diinspeksi' : 'Belum Diinspeksi',
                'inspeksi' => $inspeksiThisMonth,
            ]);
        }

        if ($request->filled('status')) {
            $statusFilter = $request->status;
            $reportData = $reportData->filter(function ($item) use ($statusFilter) {
                return $item['status'] === $statusFilter;
            });
        }

        $columns = [__('No'), __('PFE ID'), __('Building'), __('Location'), __('Type'), __('Capacity'), __('Inspection Status'), __('Inspection Date'), __('Inspector'), __('Condition'), __('Additional Notes')];
        $rows = [];

        $no = 1;
        foreach ($reportData as $row) {
            $apar = $row['apar'];
            $inspeksi = $row['inspeksi'];
            $isInspected = $row['status'] === 'Sudah Diinspeksi';

            $rows[] = [
                $no++,
                $apar->kode,
                $apar->lokasi->gedung->nama ?? 'n/a',
                $apar->lokasi->nama ?? 'n/a',
                $apar->jenis->nama ?? 'n/a',
                $apar->kapasitas->ukuran ?? 'n/a',
                $isInspected ? 'Sudah Diinspeksi' : 'Belum Diinspeksi',
                $inspeksi ? Carbon::parse($inspeksi->created_at)->format('d M Y H:i') : 'n/a',
                $inspeksi?->user?->name ?? 'n/a',
                $inspeksi?->status ?? 'n/a',
                $inspeksi?->catatan_tambahan ?? 'n/a',
            ];
        }

        return response()->json([
            'headers' => $columns,
            'rows' => $rows,
        ]);
    }
}
