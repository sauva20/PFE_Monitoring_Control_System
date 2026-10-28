<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>PFE Inspection Reminder</title>
</head>
<body style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8fafc; color: #334155; line-height: 1.6; margin: 0; padding: 0;">
    <div style="max-width: 650px; margin: 40px auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); border: 1px solid #e2e8f0; border-top: 5px solid #009B77;">
        
        <div style="background-color: #009B77; color: #ffffff; padding: 24px; text-align: center;">
            <h1 style="margin: 0; font-size: 22px; font-weight: 700; letter-spacing: 0.5px;">Pengingat Deadline Inspeksi Bulanan</h1>
        </div>

        <div style="padding: 32px 24px;">
            <p style="font-weight: 600; font-size: 16px; margin-top: 0;">Halo, {{ $user->name }}</p>
            
            <p>
                Ini adalah pengingat otomatis dari PFE Monitoring Control System — EHSS mengenai <strong>Deadline Inspeksi Bulanan Anda</strong> yang jatuh pada tanggal:
            </p>

            <div style="background-color: #f0fdf4; padding: 16px 20px; border-radius: 8px; margin-bottom: 24px; border-left: 4px solid #009B77; border: 1px solid #dcfce7; border-left: 4px solid #009B77;">
                <p style="margin: 0; font-size: 13px; color: #166534; font-weight: 600; text-transform: uppercase;">Deadline Inspeksi Bulanan Anda:</p>
                <h3 style="margin: 6px 0 0 0; color: #009B77; font-size: 22px; font-weight: 800;">{{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }}</h3>
            </div>

            @php
                $lokasiGroups = $user->lokasis ? $user->lokasis->groupBy('gedung_id') : collect();
                $assignedAreas = collect();

                if ($user->gedungs) {
                    foreach ($user->gedungs as $g) {
                        $assignedAreas->put($g->id, [
                            'gedung' => $g,
                            'is_all' => true,
                            'lokasis' => collect(),
                        ]);
                    }
                }

                foreach ($lokasiGroups as $gId => $lokList) {
                    if ($assignedAreas->has($gId)) {
                        continue;
                    }
                    $parentGedung = $lokList->first()->gedung ?? \App\Models\Gedung::find($gId);
                    if ($parentGedung) {
                        $assignedAreas->put($gId, [
                            'gedung' => $parentGedung,
                            'is_all' => false,
                            'lokasis' => $lokList,
                        ]);
                    }
                }
            @endphp

            @if($jenis === 'Inspeksi Rutin Bulanan')
            <p style="font-weight: 600; color: #1e293b; margin-bottom: 10px;">
                Mohon luangkan waktu Anda untuk menyelesaikan inspeksi rutin pada seluruh area tanggung jawab Anda sebelum tanggal deadline tersebut:
            </p>

            @if($assignedAreas->isNotEmpty())
                <div style="border: 1px solid #cbd5e1; border-radius: 8px; overflow: hidden; background-color: #ffffff; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    @foreach($assignedAreas as $item)
                        @php
                            $gedung = $item['gedung'];
                            $isAll = $item['is_all'];
                            $lokasis = $item['lokasis'];
                        @endphp
                        <div style="border-bottom: {{ $loop->last ? 'none' : '1px solid #cbd5e1' }};">
                            <div style="background-color: #f1f5f9; padding: 12px 16px; border-bottom: 2px solid #009B77;">
                                <table style="width: 100%; border-collapse: collapse;">
                                    <tr>
                                        <td style="vertical-align: middle;">
                                            <strong style="color: #0f172a; font-size: 15px;">
                                                Building {{ $gedung->nama }}
                                            </strong>
                                        </td>
                                        <td style="vertical-align: middle; text-align: right;">
                                            @if($isAll)
                                                <span style="font-size: 11px; font-weight: 700; color: #047857; background-color: #d1fae5; border: 1px solid #a7f3d0; padding: 3px 10px; border-radius: 4px; display: inline-block;">
                                                    Seluruh Gedung (All Areas)
                                                </span>
                                            @else
                                                <span style="font-size: 11px; font-weight: 700; color: #0369a1; background-color: #e0f2fe; border: 1px solid #bae6fd; padding: 3px 10px; border-radius: 4px; display: inline-block;">
                                                    {{ $lokasis->count() }} Ruangan Khusus
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <div style="padding: 16px;">
                                @if($isAll)
                                    <p style="margin: 0 0 12px 0; font-size: 13px; color: #475569;">
                                        Seluruh unit APAR di area dan lantai <strong>Building {{ $gedung->nama }}</strong>:
                                    </p>
                                @endif

                                @if($lokasis->isNotEmpty())
                                    <table style="width: 100%; border-collapse: collapse; font-size: 13px; border: 1px solid #cbd5e1;">
                                        <thead>
                                            <tr style="background-color: #009B77; color: #ffffff;">
                                                <th style="padding: 10px 8px; border: 1px solid #008264; text-align: center; width: 36px; font-weight: 700; font-size: 12px; text-transform: uppercase;">No</th>
                                                <th style="padding: 10px 12px; border: 1px solid #008264; text-align: left; font-weight: 700; font-size: 12px; text-transform: uppercase;">Nama Lokasi / Ruangan</th>
                                                <th style="padding: 10px 10px; border: 1px solid #008264; text-align: center; width: 110px; font-weight: 700; font-size: 12px; text-transform: uppercase;">No. Ruangan</th>
                                                <th style="padding: 10px 10px; border: 1px solid #008264; text-align: center; width: 80px; font-weight: 700; font-size: 12px; text-transform: uppercase;">Lantai</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($lokasis as $idx => $lok)
                                                <tr style="background-color: {{ $idx % 2 === 0 ? '#ffffff' : '#f8fafc' }};">
                                                    <td style="padding: 9px 8px; border: 1px solid #e2e8f0; text-align: center; color: #64748b; font-weight: 600;">{{ $idx + 1 }}</td>
                                                    <td style="padding: 9px 12px; border: 1px solid #e2e8f0; color: #1e293b; font-weight: 600;">
                                                        {{ $lok->nama }}
                                                    </td>
                                                    <td style="padding: 9px 10px; border: 1px solid #e2e8f0; text-align: center; font-family: monospace; font-weight: 700; color: {{ $lok->nomor_ruangan ? '#009B77' : '#94a3b8' }};">
                                                        {{ $lok->nomor_ruangan ?: '-' }}
                                                    </td>
                                                    <td style="padding: 9px 10px; border: 1px solid #e2e8f0; text-align: center; color: #475569; font-weight: 600;">
                                                        {{ $lok->lantai ? 'Lantai '.$lok->lantai : '-' }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
            @endif

            <div style="background-color: #fffbeb; padding: 20px; border-radius: 8px; border: 1px solid #fde68a; text-align: center; margin-bottom: 24px;">
                <p style="margin-top: 0; color: #92400e; font-weight: 700; font-size: 13px; letter-spacing: 0.5px;">PIN INSPEKSI ANDA</p>
                @if(strlen($user->pin ?? '') > 10 || str_starts_with($user->pin ?? '', '$2y$'))
                    <p style="font-size: 14px; color: #b45309; font-weight: 700; margin: 10px 0;">Gunakan 4 digit PIN Inspeksi yang telah Anda miliki</p>
                    <p style="font-size: 12px; color: #92400e; margin: 0;"><em>(PIN Anda tersimpan aman secara terenkripsi)</em></p>
                @else
                    <h3 style="letter-spacing: 6px; color: #b45309; font-size: 28px; margin: 10px 0;">{{ $user->pin }}</h3>
                @endif
                <p style="font-size: 13px; color: #92400e; margin-top: 8px; margin-bottom: 0;">Gunakan PIN ini untuk verifikasi saat melakukan inspeksi APAR di aplikasi.</p>
            </div>

            <p style="margin-top: 24px;">
                Silakan datangi langsung lokasi unit APAR di lapangan dan lakukan scan QR Code pada setiap unit untuk memulai proses inspeksi.
            </p>
        </div>

        <div style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 20px 24px; text-align: center; font-size: 13px; color: #64748b;">
            <p style="margin: 0;">Email ini dikirim otomatis oleh PFE Monitoring Control System — EHSS. Harap tidak membalas email ini.</p>
        </div>
    </div>
</body>
</html>
