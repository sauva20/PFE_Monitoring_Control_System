<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    private function purgePreviousMonthLogs(): void
    {
        // Bersihkan data log dari bulan-bulan sebelumnya
        Activity::where('created_at', '<', Carbon::now()->startOfMonth())->delete();
    }

    public function index()
    {
        $this->purgePreviousMonthLogs();

        $now = Carbon::now();
        $activities = Activity::with('causer')
            ->whereYear('created_at', $now->year)
            ->whereMonth('created_at', $now->month)
            ->latest()
            ->paginate(15)
            ->onEachSide(1);

        $currentPeriod = $now->translatedFormat('F Y');

        return view('activity-log.index', compact('activities', 'currentPeriod'));
    }

    public function exportPdf()
    {
        $this->purgePreviousMonthLogs();

        $now = Carbon::now();
        $activities = Activity::with('causer')
            ->whereYear('created_at', $now->year)
            ->whereMonth('created_at', $now->month)
            ->latest()
            ->get();

        $currentPeriod = $now->translatedFormat('F Y');
        $pdf = Pdf::loadView('activity-log.pdf', compact('activities', 'currentPeriod'))->setPaper('a4', 'portrait');

        return $pdf->stream('Audit_Trail_'.date('Ymd_His').'.pdf');
    }

    public function exportExcel()
    {
        $this->purgePreviousMonthLogs();

        $now = Carbon::now();
        $activities = Activity::with('causer')
            ->whereYear('created_at', $now->year)
            ->whereMonth('created_at', $now->month)
            ->latest()
            ->get();

        $filename = 'Audit_Trail_'.date('Ymd_His').'.csv';

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$filename",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $columns = [
            'Time / Waktu',
            'User / Pengguna',
            'Action / Aksi',
            'Module / Modul',
            'Description / Keterangan',
        ];

        $callback = function () use ($activities, $columns) {
            $file = fopen('php://output', 'w');
            // add BOM to fix UTF-8 in Excel
            fwrite($file, $bom = (chr(0xEF).chr(0xBB).chr(0xBF)));
            fputcsv($file, $columns);

            foreach ($activities as $activity) {
                $moduleName = class_basename($activity->subject_type);
                $moduleLabel = match ($moduleName) {
                    'Apar' => 'Master Data APAR',
                    'Inspeksi' => 'Inspeksi APAR',
                    'JadwalInspeksi' => 'Jadwal Inspeksi',
                    'User' => 'Manajemen User',
                    'AparCadangan' => 'Stok Cadangan',
                    'Lokasi' => 'Data Lokasi',
                    default => preg_replace('/([a-z])([A-Z])/s', '$1 $2', $moduleName ?: 'System'),
                };

                $row = [
                    $activity->created_at->format('d M Y H:i:s'),
                    $activity->causer->name ?? 'Sistem / System',
                    strtoupper($activity->event),
                    $moduleLabel,
                    $activity->description,
                ];
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function previewExcel()
    {
        $this->purgePreviousMonthLogs();

        $now = Carbon::now();
        $activities = Activity::with('causer')
            ->whereYear('created_at', $now->year)
            ->whereMonth('created_at', $now->month)
            ->latest()
            ->get();

        $columns = [
            'Time / Waktu',
            'User / Pengguna',
            'Action / Aksi',
            'Module / Modul',
            'Description / Keterangan',
        ];
        $rows = [];

        foreach ($activities as $activity) {
            $moduleName = class_basename($activity->subject_type);
            $moduleLabel = match ($moduleName) {
                'Apar' => 'Master Data APAR',
                'Inspeksi' => 'Inspeksi APAR',
                'JadwalInspeksi' => 'Jadwal Inspeksi',
                'User' => 'Manajemen User',
                'AparCadangan' => 'Stok Cadangan',
                'Lokasi' => 'Data Lokasi',
                default => preg_replace('/([a-z])([A-Z])/s', '$1 $2', $moduleName ?: 'System'),
            };

            $rows[] = [
                $activity->created_at->format('d M Y H:i:s'),
                $activity->causer->name ?? 'Sistem / System',
                strtoupper($activity->event),
                $moduleLabel,
                $activity->description,
            ];
        }

        return response()->json([
            'headers' => $columns,
            'rows' => $rows,
        ]);
    }
}
