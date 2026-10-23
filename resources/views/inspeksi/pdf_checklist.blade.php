<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Onsite Inspection Checklist</title>
    <style>
        @page { margin: 20px 20px 40px 20px; }
        body { font-family: Arial, sans-serif; font-size: 10px; color: #333; }
        h2 { text-align: center; color: #009B77; margin-bottom: 5px; text-transform: uppercase; font-size: 16px; }
        p.subtitle { text-align: center; font-size: 11px; color: #555; margin-top: 0; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        tr { page-break-inside: avoid; }
        th, td { border: 1px solid #777; padding: 2px 4px; text-align: left; vertical-align: top; }
        th { background-color: #f0f0f0; color: #111; font-weight: bold; text-align: center; }
        td.q-text { padding: 2px 4px; font-size: 9px; }
        .text-center { text-align: center; }
        .page-break { page-break-after: always; }
        .apar-header {
            font-size: 11px;
            font-weight: bold;
            color: #009B77;
        }
        .apar-sub {
            font-size: 9px;
            font-weight: normal;
            color: #555;
            margin-top: 2px;
        }
        .footer { position: fixed; bottom: -20px; left: 0px; right: 0px; height: 20px; text-align: right; font-size: 8px; color: #999; }
    </style>
</head>
<body>
    <div class="footer">
        Generated on {{ \Carbon\Carbon::now()->translatedFormat('d F Y H:i:s') }}
    </div>

    @php
        // Group APARs by building first
        $groupedApars = $apars->groupBy(function($apar) {
            return $apar->lokasi->gedung->nama ?? 'Unknown Building';
        });
    @endphp

    @foreach($groupedApars as $gedungName => $gedungApars)
        @php
            // Group APARs into chunks of 8 columns per page to fit on A4 landscape
            $chunks = $gedungApars->chunk(8);
        @endphp

        @foreach($chunks as $chunkIndex => $aparChunk)
            <h2>Daftar Periksa (Checklist) Inspeksi APAR</h2>
            <p class="subtitle">Gedung: <strong>{{ $gedungName }}</strong> &nbsp;|&nbsp; Dicetak Oleh: <strong>{{ auth()->user()->name ?? 'System' }}</strong> &nbsp;|&nbsp; Formulir Inspeksi Manual untuk Petugas Lapangan</p>
            
            <table>
                <thead>
                    <tr>
                        <th style="width: 25%; text-align:left; padding-left:6px;">Item Pemeriksaan / Pertanyaan</th>
                        @foreach($aparChunk as $apar)
                            <th style="width: 9.375%;">
                                <div class="apar-header">{{ $apar->kode }}</div>
                                <div class="apar-sub">{{ $apar->lokasi->nama ?? '' }}</div>
                            </th>
                        @endforeach
                        @for($i = 0; $i < (8 - $aparChunk->count()); $i++)
                            <th style="width: 9.375%;"></th>
                        @endfor
                    </tr>
                </thead>
                <tbody>
                    @php
                        // Reorder indices: 14-23 first, then 0-13
                        $orderedIndices = array_merge(range(14, 23), range(0, 13));
                    @endphp
                    @foreach($orderedIndices as $displayIndex => $index)
                        @php $q = $pertanyaan[$index]; @endphp
                        <tr>
                            <td class="q-text">{{ $displayIndex + 1 }}. {{ $q }}</td>
                            @foreach($aparChunk as $apar)
                                <td class="text-center" style="vertical-align: middle;">
                                    @if($index == 14) {{-- Jenis --}}
                                        {{ $apar->jenis->nama ?? '' }}
                                    @elseif($index == 15) {{-- Kapasitas --}}
                                        {{ $apar->kapasitas->ukuran ?? '' }}
                                    @elseif($index == 16) {{-- Tgl Isi Ulang --}}
                                        {{ $apar->tgl_isi_ulang ? $apar->tgl_isi_ulang->format('d/m/Y') : '' }}
                                    @elseif($index == 17) {{-- Tgl Kedaluwarsa --}}
                                        {{ $apar->tgl_kedaluwarsa ? $apar->tgl_kedaluwarsa->format('d/m/Y') : '' }}
                                    @elseif($index == 23) {{-- Lokasi --}}
                                        {{ $apar->lokasi->nama ?? '' }}
                                    @else
                                        <!-- Blank for manual check -->
                                    @endif
                                </td>
                            @endforeach
                            @for($i = 0; $i < (8 - $aparChunk->count()); $i++)
                                <td></td>
                            @endfor
                        </tr>
                    @endforeach
                    <tr>
                        <td style="font-weight:bold; padding: 6px;">Kondisi Akhir</td>
                        @foreach($aparChunk as $apar)
                            <td style="height: 25px;"></td>
                        @endforeach
                        @for($i = 0; $i < (8 - $aparChunk->count()); $i++)
                            <td style="height: 25px;"></td>
                        @endfor
                    </tr>
                </tbody>
            </table>

            @if(!($loop->parent->last && $loop->last))
                <div class="page-break"></div>
            @endif
        @endforeach
    @endforeach
</body>
</html>
