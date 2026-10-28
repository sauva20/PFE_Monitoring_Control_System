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
        $requestedYear = (int) $request->input('year', $now->year);
        $requestedMonth = (int) $request->input('month', $now->month);

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

        $selectedGedung = $request->input('gedung_id');
        $status = $request->input('status', 'all');

        $user = auth()->user();
        $isStaff = $user && $user->role === 'Staff';

        $reportData = collect();

        // 1. Ambil daftar ID APAR yang sudah diinspeksi pada bulan & tahun terpilih
        $inspeksiQuery = Inspeksi::whereMonth('created_at', $selectedMonth)
            ->whereYear('created_at', $selectedYear);

        if ($isStaff) {
            $inspeksiQuery->where('user_id', $user->id);
        }

        $inspeksiBulanIniAparIds = $inspeksiQuery->pluck('apar_id')->unique()->toArray();

        // 2. Query APAR sesuai hak akses role
        $query = Apar::with(['lokasi.gedung', 'jenis', 'kapasitas']);

        if ($isStaff) {
            // Staff dibatasi hanya pada APAR yang ditugaskan ke dirinya atau yang diinspeksi olehnya
            $query->where(function ($sub) use ($user, $inspeksiBulanIniAparIds) {
                $sub->whereHas('lokasi.gedung.users', fn ($q) => $q->where('user_id', $user->id))
                    ->orWhereHas('lokasi.users', fn ($q) => $q->where('user_id', $user->id))
                    ->orWhere('pic_id', $user->id);

                if (! empty($inspeksiBulanIniAparIds)) {
                    $sub->orWhereIn('id', $inspeksiBulanIniAparIds);
                }
            });
        }

        if ($selectedGedung) {
            $query->whereHas('lokasi', function ($q) use ($selectedGedung) {
                $q->where('gedung_id', $selectedGedung);
            });
        }

        $apars = $query->orderBy('kode', 'asc')->get();

        // 3. Bangun data baris laporan
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
                $detailQuery = Inspeksi::with('user')->where('apar_id', $apar->id)
                    ->whereMonth('created_at', $selectedMonth)
                    ->whereYear('created_at', $selectedYear);

                if ($isStaff) {
                    $detailQuery->where('user_id', $user->id);
                }

                $inspeksi = $detailQuery->latest()->first();
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
        $user = auth()->user();
        if ($user && $user->role === 'Staff') {
            $gedungs = Gedung::where(function ($sub) use ($user) {
                $sub->whereHas('users', fn ($q) => $q->where('user_id', $user->id))
                    ->orWhereHas('lokasi.users', fn ($q) => $q->where('user_id', $user->id))
                    ->orWhereHas('lokasi.apar.inspeksis', fn ($q) => $q->where('user_id', $user->id));
            })->get();
        } else {
            $gedungs = Gedung::all();
        }

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

        $columns = [
            'No',
            'PFE Code / Kode APAR',
            'Building / Gedung',
            'Location / Lokasi',
            'Fire Extinguisher Type / Jenis APAR',
            'Fill Capacity / Kapasitas Isi',
            'Inspection Status / Status Inspeksi',
            'Inspection Date / Tanggal Inspeksi',
            'Inspector / Petugas',
            'Condition / Kondisi',
            'Additional Notes / Catatan Tambahan',
        ];

        $callback = function () use ($reportData, $columns) {
            $file = fopen('php://output', 'w');
            fwrite($file, $bom = (chr(0xEF).chr(0xBB).chr(0xBF)));
            fputcsv($file, $columns);

            $no = 1;
            foreach ($reportData as $row) {
                $apar = $row['apar'];
                $inspeksi = $row['inspeksi'];
                $isInspected = $row['status'] === 'Sudah Diinspeksi';

                $conditionLabel = 'n/a';
                if ($inspeksi && $inspeksi->status) {
                    if (in_array($inspeksi->status, ['sesuai', 'layak'])) {
                        $conditionLabel = 'Compliant / Sesuai';
                    } elseif (in_array($inspeksi->status, ['tidak_sesuai', 'perbaikan', 'rusak', 'tidak_layak'])) {
                        $conditionLabel = 'Non-Compliant / Tidak Sesuai';
                    } elseif (in_array($inspeksi->status, ['perlu_refill', 'isi_ulang'])) {
                        $conditionLabel = 'Needs Refill / Perlu Refill';
                    } else {
                        $conditionLabel = str_replace('_', ' ', $inspeksi->status);
                    }
                }

                fputcsv($file, [
                    $no++,
                    $apar->kode,
                    $apar->lokasi->gedung->nama ?? 'n/a',
                    $apar->lokasi->nama ?? 'n/a',
                    $apar->jenis->nama ?? 'n/a',
                    $apar->kapasitas->ukuran ?? 'n/a',
                    $isInspected ? 'Inspected / Sudah Diinspeksi' : 'Uninspected / Belum Diinspeksi',
                    $inspeksi ? Carbon::parse($inspeksi->created_at)->format('d M Y H:i') : 'n/a',
                    $inspeksi?->user?->name ?? 'n/a',
                    $conditionLabel,
                    $inspeksi?->catatan_tambahan ?? 'n/a',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function previewExcel(Request $request)
    {
        $reportParams = $this->getReportData($request);
        $reportData = $reportParams['data'];

        $columns = [
            'No',
            'PFE Code / Kode APAR',
            'Building / Gedung',
            'Location / Lokasi',
            'Fire Extinguisher Type / Jenis APAR',
            'Fill Capacity / Kapasitas Isi',
            'Inspection Status / Status Inspeksi',
            'Inspection Date / Tanggal Inspeksi',
            'Inspector / Petugas',
            'Condition / Kondisi',
            'Additional Notes / Catatan Tambahan',
        ];
        $rows = [];

        $no = 1;
        foreach ($reportData as $row) {
            $apar = $row['apar'];
            $inspeksi = $row['inspeksi'];
            $isInspected = $row['status'] === 'Sudah Diinspeksi';

            $conditionLabel = 'n/a';
            if ($inspeksi && $inspeksi->status) {
                if (in_array($inspeksi->status, ['sesuai', 'layak'])) {
                    $conditionLabel = __('Sesuai');
                } elseif (in_array($inspeksi->status, ['tidak_sesuai', 'perbaikan', 'rusak', 'tidak_layak'])) {
                    $conditionLabel = __('Tidak Sesuai');
                } elseif (in_array($inspeksi->status, ['perlu_refill', 'isi_ulang'])) {
                    $conditionLabel = __('Perlu Refill');
                } else {
                    $conditionLabel = str_replace('_', ' ', $inspeksi->status);
                }
            }

            $rows[] = [
                $no++,
                $apar->kode,
                $apar->lokasi->gedung->nama ?? 'n/a',
                $apar->lokasi->nama ?? 'n/a',
                $apar->jenis->nama ?? 'n/a',
                $apar->kapasitas->ukuran ?? 'n/a',
                $isInspected ? __('Inspected') : __('Uninspected'),
                $inspeksi ? Carbon::parse($inspeksi->created_at)->format('d M Y H:i') : 'n/a',
                $inspeksi?->user?->name ?? 'n/a',
                $conditionLabel,
                $inspeksi?->catatan_tambahan ?? 'n/a',
            ];
        }

        return response()->json([
            'headers' => $columns,
            'rows' => $rows,
        ]);
    }
}
