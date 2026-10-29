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

    public static function translateDescription(?string $desc): string
    {
        if (empty($desc)) {
            return '-';
        }

        if (app()->getLocale() !== 'en') {
            return $desc;
        }

        $patterns = [
            // APAR
            '/^Menghapus unit APAR \[(.*?)\]$/' => 'Deleted PFE unit [$1]',
            '/^Menambahkan unit APAR baru \[(.*?)\] di lokasi (.*?)$/' => 'Added new PFE unit [$1] at location $2',
            '/^Memperbarui data spesifikasi APAR \[(.*?)\]$/' => 'Updated PFE specification data [$1]',
            '/^APAR \[(.*?)\] telah di-(.*?)$/' => 'PFE [$1] was $2',

            // Lokasi
            '/^Menambahkan data lokasi baru \[(.*?)\] di (.*?)$/' => 'Added new location data [$1] in $2',
            '/^Memperbarui data lokasi \[(.*?)\] di (.*?)$/' => 'Updated location data [$1] in $2',
            '/^Menghapus data lokasi \[(.*?)\] di (.*?)$/' => 'Deleted location data [$1] in $2',
            '/^Lokasi \[(.*?)\] telah di-(.*?)$/' => 'Location [$1] was $2',

            // Stok Cadangan
            '/^Menambahkan stok cadangan APAR (.*?) di (.*?) \(Jumlah: (.*?)\)$/' => 'Added backup PFE stock $1 in $2 (Qty: $3)',
            '/^Memperbarui stok cadangan APAR (.*?) di (.*?) \(Jumlah: (.*?)\)$/' => 'Updated backup PFE stock $1 in $2 (Qty: $3)',
            '/^Menghapus stok cadangan APAR (.*?) di (.*?)$/' => 'Deleted backup PFE stock $1 in $2',
            '/^Stok cadangan APAR (.*?) telah di-(.*?)$/' => 'Backup PFE stock $1 was $2',

            // Inspeksi
            '/^Melakukan inspeksi rutin APAR \[(.*?)\] - Status: (.*?)$/' => 'Conducted routine PFE inspection [$1] - Status: $2',
            '/^Memperbarui hasil inspeksi APAR \[(.*?)\] - Status: (.*?)$/' => 'Updated PFE inspection result [$1] - Status: $2',
            '/^Menghapus riwayat inspeksi APAR \[(.*?)\]$/' => 'Deleted PFE inspection history [$1]',
            '/^Inspeksi APAR \[(.*?)\] telah di-(.*?)$/' => 'PFE inspection [$1] was $2',

            // Jadwal
            '/^Menetapkan jadwal inspeksi gedung (.*?) kepada (.*?)$/' => 'Assigned inspection schedule for building $1 to $2',
            '/^Memperbarui jadwal inspeksi gedung (.*?) untuk (.*?)$/' => 'Updated inspection schedule for building $1 for $2',
            '/^Menghapus jadwal inspeksi gedung (.*?)$/' => 'Deleted inspection schedule for building $1',
            '/^Jadwal inspeksi gedung (.*?) telah di-(.*?)$/' => 'Inspection schedule for building $1 was $2',

            // User
            '/^Menambahkan user baru: (.*?) \((.*?)\)$/' => 'Added new user: $1 ($2)',
            '/^Memperbarui data akun user: (.*?) \((.*?)\)$/' => 'Updated user account: $1 ($2)',
            '/^Menghapus akun user: (.*?)$/' => 'Deleted user account: $1',
            '/^User (.*?) telah di-(.*?)$/' => 'User $1 was $2',
        ];

        foreach ($patterns as $pattern => $replacement) {
            if (preg_match($pattern, $desc)) {
                return preg_replace($pattern, $replacement, $desc);
            }
        }

        return $desc;
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
            __('Time'),
            __('User'),
            __('Action'),
            __('Module'),
            __('Description'),
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

                $actionLabel = match ($activity->event) {
                    'created' => __('CREATED'),
                    'updated' => __('UPDATED'),
                    'deleted' => __('DELETED'),
                    default => strtoupper($activity->event),
                };

                $row = [
                    $activity->created_at->format('d M Y H:i:s'),
                    $activity->causer->name ?? __('System'),
                    $actionLabel,
                    __($moduleLabel),
                    self::translateDescription($activity->description),
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
            __('Time'),
            __('User'),
            __('Action'),
            __('Module'),
            __('Description'),
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

            $actionLabel = match ($activity->event) {
                'created' => __('CREATED'),
                'updated' => __('UPDATED'),
                'deleted' => __('DELETED'),
                default => strtoupper($activity->event),
            };

            $rows[] = [
                $activity->created_at->format('d M Y H:i:s'),
                $activity->causer->name ?? __('System'),
                $actionLabel,
                __($moduleLabel),
                self::translateDescription($activity->description),
            ];
        }

        return response()->json([
            'headers' => $columns,
            'rows' => $rows,
        ]);
    }
}
