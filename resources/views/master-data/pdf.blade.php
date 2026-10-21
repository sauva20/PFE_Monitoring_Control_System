<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data APAR</title>
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
        <h1>{{ __('PFE DATA REPORT') }} PT B. BRAUN PHARMACEUTICAL INDONESIA</h1>
        <p>Identify Fire Extinguisher Placement</p>
    </div>

    @php
        $activeFilters = [];
        if (request()->filled('search')) $activeFilters[] = request('search');
        if (request()->filled('gedung_id')) {
            $gedung = \App\Models\gedung::find(request('gedung_id'));
            if($gedung) $activeFilters[] = $gedung->nama;
        }
        if (request()->filled('lokasi_id')) {
            $lokasi = \App\Models\lokasi::find(request('lokasi_id'));
            if($lokasi) $activeFilters[] = $lokasi->nama;
        }
        if (request()->filled('kapasitas_id')) {
            $kapasitas = \App\Models\kapasitasApar::find(request('kapasitas_id'));
            if($kapasitas) $activeFilters[] = $kapasitas->ukuran;
        }
        if (request()->filled('jenis_id')) {
            $jenis = \App\Models\jenisApar::find(request('jenis_id'));
            if($jenis) $activeFilters[] = $jenis->nama;
        }
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
                <th class="text-center" style="width: 4%;">
                    <div>No</div>
                </th>
                <th style="width: 9%;">
                    <div>Fire Extinguisher Code</div>
                    <div style="font-weight: normal; font-style: italic; font-size: 7px; margin-top: 2px; text-transform: none;">Kode APAR</div>
                </th>
                <th style="width: 8%;">
                    <div>Room No</div>
                    <div style="font-weight: normal; font-style: italic; font-size: 7px; margin-top: 2px; text-transform: none;">No. Ruangan</div>
                </th>
                <th style="width: 11%;">
                    <div>Location</div>
                    <div style="font-weight: normal; font-style: italic; font-size: 7px; margin-top: 2px; text-transform: none;">Lokasi</div>
                </th>
                <th style="width: 10%;">
                    <div>Building</div>
                    <div style="font-weight: normal; font-style: italic; font-size: 7px; margin-top: 2px; text-transform: none;">Gedung</div>
                </th>
                <th style="width: 12%;">
                    <div>Fire Extinguisher Type</div>
                    <div style="font-weight: normal; font-style: italic; font-size: 7px; margin-top: 2px; text-transform: none;">Jenis APAR</div>
                </th>
                <th style="width: 8%;">
                    <div>Fill Capacity</div>
                    <div style="font-weight: normal; font-style: italic; font-size: 7px; margin-top: 2px; text-transform: none;">Kapasitas Isi</div>
                </th>
                <th class="text-center" style="width: 8%;">
                    <div>Fire Class</div>
                    <div style="font-weight: normal; font-style: italic; font-size: 7px; margin-top: 2px; text-transform: none;">Kelas Kebakaran</div>
                </th>
                <th style="width: 10%;">
                    <div>Expired Date</div>
                    <div style="font-weight: normal; font-style: italic; font-size: 7px; margin-top: 2px; text-transform: none;">Tanggal Kadaluarsa</div>
                </th>
                <th style="width: 10%;">
                    <div>Last Refill</div>
                    <div style="font-weight: normal; font-style: italic; font-size: 7px; margin-top: 2px; text-transform: none;">Terakhir Isi Ulang</div>
                </th>
                <th class="text-center" style="width: 4%;">
                    <div>Qty</div>
                    <div style="font-weight: normal; font-style: italic; font-size: 7px; margin-top: 2px; text-transform: none;">Jml</div>
                </th>
                <th style="width: 12%;">
                    <div>PIC</div>
                    <div style="font-weight: normal; font-style: italic; font-size: 7px; margin-top: 2px; text-transform: none;">Penanggung Jawab</div>
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach($apars as $index => $apar)
                @php
                    $jenisNamaL = strtolower($apar->jenis->nama ?? '');
                    $kelas = '-';
                    if (strpos($jenisNamaL, 'dry chemical') !== false || strpos($jenisNamaL, 'powder') !== false) {
                        $kelas = 'A-B-C';
                    } elseif (strpos($jenisNamaL, 'carbon') !== false || strpos($jenisNamaL, 'dioxide') !== false || strpos($jenisNamaL, 'co2') !== false) {
                        $kelas = 'B-C';
                    }
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="font-bold">{{ $apar->kode }}</td>
                    <td>{{ $apar->nomor_ruangan ?? '-' }}</td>
                    <td>{{ $apar->lokasi->nama ?? 'n/a' }}</td>
                    <td>{{ $apar->lokasi->gedung->nama ?? 'n/a' }}</td>
                    <td>{{ $apar->jenis->nama ?? 'n/a' }}</td>
                    <td>{{ $apar->kapasitas->ukuran ?? 'n/a' }}</td>
                    <td class="text-center">{{ $kelas }}</td>
                    <td>
                        @if($apar->tgl_kedaluwarsa && $apar->tgl_kedaluwarsa->isPast())
                            <span style="color: #dc2626; font-weight: bold;">{{ __('Expired') }} ({{ $apar->tgl_kedaluwarsa->format('d/m/Y') }})</span>
                        @else
                            {{ $apar->tgl_kedaluwarsa ? $apar->tgl_kedaluwarsa->format('d/m/Y') : '-' }}
                        @endif
                    </td>
                    <td>
                        {{ $apar->tgl_isi_ulang ? $apar->tgl_isi_ulang->format('d/m/Y') : '-' }}
                    </td>
                    <td class="text-center font-bold">
                        {{ $apar->qty }}
                    </td>
                    <td>
                        @php
                            $lastInspeksi = $apar->latestInspeksi;
                        @endphp
                        @if($lastInspeksi && $lastInspeksi->user)
                            {{ $lastInspeksi->user->name }}
                        @elseif($apar->pic)
                            {{ $apar->pic->name }}
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @endforeach
            
            @if($apars->isEmpty())
                <tr>
                    <td colspan="12" class="text-center" style="padding: 20px;">Belum ada data APAR.</td>
                </tr>
            @endif
        </tbody>
    </table>

    @if(isset($aparCadangans) && $aparCadangans->count() > 0)
    <div style="margin-top: 30px;">
        <h2 style="font-size: 10px; font-weight: bold; color: #009B77; margin-bottom: 8px; text-transform: uppercase;">Stok Cadangan</h2>
        <table>
            <thead>
                <tr>
                    <th class="text-center" style="width: 5%;">No</th>
                    <th style="width: 35%;">{{ __('Building') }}</th>
                    <th style="width: 25%;">{{ __('Type') }}</th>
                    <th style="width: 20%;">{{ __('Capacity') }}</th>
                    <th class="text-center" style="width: 15%;">{{ __('Stock Quantity') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($aparCadangans as $index => $cadangan)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $cadangan->gedung->nama ?? '-' }}</td>
                        <td>{{ $cadangan->jenis->nama ?? '-' }}</td>
                        <td>{{ $cadangan->kapasitas->ukuran ?? '-' }}</td>
                        <td class="text-center" style="font-weight: bold; color: #009B77;">{{ $cadangan->total }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" style="text-align: left; padding: 8px 6px; border: 1px solid #ddd; padding-left: 12px;">Grand Total</td>
                    <td class="text-center" style="font-weight: bold; color: #009B77; background-color: #e8f5f3; border: 1px solid #ddd;">{{ $aparCadangans->sum('total') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif

    @if(isset($resumeColumns) && count($resumeColumns) > 0)
    <div style="margin-top: 30px;">
        <h2 style="font-size: 10px; font-weight: bold; color: #009B77; margin-bottom: 8px; text-transform: uppercase;">Resume Data APAR</h2>
        <table>
            <thead>
                <tr>
                    <th rowspan="2" style="width: 5%;">No</th>
                    <th rowspan="2" style="width: 30%;">Keterangan</th>
                    @foreach($resumeColumns as $jenis => $kapasitasList)
                    <th colspan="{{ count($kapasitasList) }}">{{ $jenis }}</th>
                    @endforeach
                    <th rowspan="2" style="width: 10%;">Total</th>
                </tr>
                <tr>
                    @foreach($resumeColumns as $jenis => $kapasitasList)
                        @foreach($kapasitasList as $kapasitas)
                        <th>{{ $kapasitas }}</th>
                        @endforeach
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($resumeTable as $index => $row)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td style="font-weight: bold;">{{ __($row['name']) }}</td>
                    @foreach($resumeColumns as $jenis => $kapasitasList)
                        @foreach($kapasitasList as $kapasitas)
                        <td class="text-center">{{ $row['data'][$jenis][$kapasitas] ?? 0 }}</td>
                        @endforeach
                    @endforeach
                    <td class="text-center" style="font-weight: bold; color: #009B77; background-color: #e8f5f3;">{{ $row['total'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

</body>
</html>
