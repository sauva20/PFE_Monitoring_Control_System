<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ __('User Data') }}</title>
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
        <h1>{{ __('PFE Monitoring Control System User Data Report') }}</h1>
        <p>Authorized Personnel and Access Level Documentation</p>
    </div>

    @php
        $activeFilters = [];
        if (request()->filled('search')) $activeFilters[] = request('search');
        if (request()->filled('role')) $activeFilters[] = request('role');
    @endphp

    <table style="width: 100%; margin-bottom: 8px; border: none;">
        <tr>
            <td style="text-align: left; font-size: 11px; border: none !important; padding: 0 !important; background: transparent;">
                Identification Date: {{ \Carbon\Carbon::now()->translatedFormat('d F Y H:i:s') }}
            </td>
            <td style="text-align: right; font-size: 11px; border: none !important; padding: 0 !important; background: transparent;">
                @if(count($activeFilters) > 0)
                    {{ implode(' | ', $activeFilters) }}
                @endif
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 5%;">No</th>
                <th style="width: 12%;">User ID</th>
                <th style="width: 23%;">{{ __('Name') }}</th>
                <th style="width: 22%;">Email</th>
                <th style="width: 15%;">{{ __('Building') }}</th>
                <th style="width: 15%;">{{ __('Inspection Schedule') }}</th>
                <th style="width: 8%;">Role</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $index => $user)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $user->employee_id ?? 'n/a' }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        @php
                            $areas = [];
                            foreach ($user->gedungs as $g) {
                                $areas[] = $g->nama;
                            }
                            foreach ($user->lokasis as $l) {
                                $areas[] = ($l->gedung->nama ?? '') . ': ' . ($l->nomor_ruangan ? '[' . $l->nomor_ruangan . '] ' : '') . $l->nama;
                            }
                        @endphp
                        @if(count($areas) > 0)
                            {{ implode(', ', $areas) }}
                        @else
                            n/a
                        @endif
                    </td>
                    <td>{{ $user->jadwal_rutin_tanggal ? __('Date') . ' ' . $user->jadwal_rutin_tanggal : 'n/a' }}</td>
                    <td>{{ $user->role }}</td>
                </tr>
            @endforeach
            
            @if($users->isEmpty())
                <tr>
                    <td colspan="7" class="text-center" style="padding: 20px;">{{ __('No User data yet.') }}</td>
                </tr>
            @endif
        </tbody>
    </table>

</body>
</html>
