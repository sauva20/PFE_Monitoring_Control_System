<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Audit Trail</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #009B77;
            padding-bottom: 15px;
        }
        .header h1 {
            color: #009B77;
            margin: 0 0 5px 0;
            font-size: 18px;
            text-transform: uppercase;
        }
        .header p {
            margin: 0;
            color: #555;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px 6px;
            text-align: left;
        }
        th {
            background-color: #e8f5f3;
            color: #009B77;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
            text-align: center;
        }
        .text-center { text-align: center; }
        .badge {
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .bg-green { background-color: #d1fae5; color: #059669; }
        .bg-red { background-color: #fee2e2; color: #dc2626; }
        .bg-orange { background-color: #fef3c7; color: #d97706; }
        
        .footer {
            margin-top: 40px;
            font-size: 10px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>Audit Trail PFE Monitoring Control System</h1>
        <p>Comprehensive Record of System Activities &bull; {{ $currentPeriod ?? \Carbon\Carbon::now()->translatedFormat('F Y') }}</p>
    </div>

    @php
        $activeFilters = [];
    @endphp

    <table style="width: 100%; margin-bottom: 8px; border: none;">
        <tr>
            <td style="text-align: left; font-size: 10px; border: none !important; padding: 0 !important; background: transparent; color: #555;">
                Identification Date: {{ \Carbon\Carbon::now()->translatedFormat('d F Y H:i:s') }}
            </td>
            <td style="text-align: right; font-size: 10px; border: none !important; padding: 0 !important; background: transparent; color: #009B77; font-weight: bold;">
                Period / Periode: {{ $currentPeriod ?? \Carbon\Carbon::now()->translatedFormat('F Y') }}
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th style="width: 18%;">
                    <div>Time</div>
                    <div style="font-weight: normal; font-style: italic; font-size: 7px; margin-top: 1px;">Waktu</div>
                </th>
                <th style="width: 18%;">
                    <div>User</div>
                    <div style="font-weight: normal; font-style: italic; font-size: 7px; margin-top: 1px;">Pengguna</div>
                </th>
                <th class="text-center" style="width: 14%;">
                    <div>Action</div>
                    <div style="font-weight: normal; font-style: italic; font-size: 7px; margin-top: 1px;">Aksi</div>
                </th>
                <th style="width: 18%;">
                    <div>Module</div>
                    <div style="font-weight: normal; font-style: italic; font-size: 7px; margin-top: 1px;">Modul</div>
                </th>
                <th style="width: 32%;">
                    <div>Description</div>
                    <div style="font-weight: normal; font-style: italic; font-size: 7px; margin-top: 1px;">Keterangan</div>
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach($activities as $activity)
                @php
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
                @endphp
                <tr>
                    <td class="font-bold">{{ $activity->created_at->format('d M Y H:i:s') }}</td>
                    <td>{{ $activity->causer->name ?? 'Sistem / System' }}</td>
                    <td class="text-center font-bold">{{ strtoupper($activity->event) }}</td>
                    <td>{{ $moduleLabel }}</td>
                    <td>{{ $activity->description }}</td>
                </tr>
            @endforeach
            
            @if($activities->isEmpty())
                <tr>
                    <td colspan="5" class="text-center" style="padding: 20px;">{{ __('No activity log for this month yet / Belum ada riwayat aktivitas untuk bulan ini.') }}</td>
                </tr>
            @endif
        </tbody>
    </table>

</body>
</html>
