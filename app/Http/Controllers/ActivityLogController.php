<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index()
    {
        $activities = Activity::with('causer')
            ->latest()
            ->paginate(15)
            ->onEachSide(1);

        return view('activity-log.index', compact('activities'));
    }

    public function exportPdf()
    {
        $activities = Activity::with('causer')->latest()->get();

        $pdf = Pdf::loadView('activity-log.pdf', compact('activities'))->setPaper('a4', 'portrait');

        return $pdf->stream('Audit_Trail_'.date('Ymd_His').'.pdf');
    }

    public function exportExcel()
    {
        $activities = Activity::with('causer')->latest()->get();
        $filename = 'Audit_Trail_'.date('Ymd_His').'.csv';

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$filename",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $columns = [__('Time'), __('User'), __('Action'), __('Module'), __('Description')];

        $callback = function () use ($activities, $columns) {
            $file = fopen('php://output', 'w');
            // add BOM to fix UTF-8 in Excel
            fwrite($file, $bom = (chr(0xEF).chr(0xBB).chr(0xBF)));
            fputcsv($file, $columns);

            foreach ($activities as $activity) {
                $row = [
                    $activity->created_at->format('d M Y H:i:s'),
                    $activity->causer->name ?? 'Sistem / Guest',
                    strtoupper($activity->event),
                    preg_replace('/([a-z])([A-Z])/s', '$1 $2', class_basename($activity->subject_type)),
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
        $activities = Activity::with('causer')->latest()->get();

        $columns = [__('Time'), __('User'), __('Action'), __('Module'), __('Description')];
        $rows = [];

        foreach ($activities as $activity) {
            $rows[] = [
                $activity->created_at->format('d M Y H:i:s'),
                $activity->causer->name ?? 'Sistem / Guest',
                strtoupper($activity->event),
                preg_replace('/([a-z])([A-Z])/s', '$1 $2', class_basename($activity->subject_type)),
                $activity->description,
            ];
        }

        return response()->json([
            'headers' => $columns,
            'rows' => $rows,
        ]);
    }
}
