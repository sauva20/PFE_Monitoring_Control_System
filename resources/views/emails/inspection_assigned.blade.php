<!DOCTYPE html>
<html>
<head>
    <title>PFE Inspection Schedule</title>
</head>
<body style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8fafc; color: #334155; line-height: 1.6; margin: 0; padding: 0;">
    <div style="max-width: 650px; margin: 40px auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); border: 1px solid #e2e8f0; border-top: 5px solid #009B77;">
        
        <div style="background-color: #009B77; color: #ffffff; padding: 24px; text-align: center;">
            <h1 style="margin: 0; font-size: 22px; font-weight: 700; letter-spacing: 0.5px;">PFE Inspection Schedule</h1>
        </div>

        <div style="padding: 32px 24px;">
            <p style="font-weight: 600; font-size: 16px; margin-top: 0;">Hello, {{ $user->name }}</p>
            
            <p>You have been assigned to perform a PFE inspection. Please ensure the inspection is completed on or before the following monthly deadline:</p>
            
            @php
                $firstJadwal = $jadwals->first();
                
                $apars = collect();
                foreach($jadwals as $jadwal) {
                    if ($jadwal->tipe_area == 'gedung' && $jadwal->gedung) {
                        foreach($jadwal->gedung->lokasi as $l) {
                            foreach($l->apar as $a) {
                                $apars->push($a);
                            }
                        }
                    } elseif ($jadwal->tipe_area == 'lokasi' && $jadwal->lokasi) {
                        foreach($jadwal->lokasi->apar as $a) {
                            $apars->push($a);
                        }
                    }
                }
                $apars = $apars->unique('id')->values();

                $rawJenis = $firstJadwal->jenis_jadwal;
                if ($rawJenis == 'Inspeksi Rutin Bulanan') $rawJenis = 'Routine Monthly Inspection';
                if ($rawJenis == 'Inspeksi Khusus / Temuan') $rawJenis = 'Special Inspection / Findings';
                $scheduleType = ucfirst(str_replace('_', ' ', $rawJenis));
            @endphp

            <div style="background-color: #f1f5f9; padding: 16px; border-radius: 8px; margin-bottom: 24px; border-left: 4px solid #009B77; display: flex; flex-direction: column; gap: 8px;">
                <div style="margin: 0;"><strong>Batas Deadline Inspeksi:</strong> <span style="color: #0f172a; font-weight: 700;">{{ \Carbon\Carbon::parse($firstJadwal->tanggal_inspeksi)->translatedFormat('d F Y') }}</span></div>
                <div style="margin: 0;"><strong>Schedule Type:</strong> <span style="color: #0f172a;">{{ $scheduleType }}</span></div>
            </div>

            <p style="font-weight: 600; color: #0f172a;">Here is the list of PFE units to be inspected:</p>

            <table style="width: 100%; border-collapse: collapse; margin-bottom: 25px; font-size: 14px;">
                <thead>
                    <tr>
                        <th style="padding: 12px; text-align: left; border-bottom: 1px solid #e2e8f0; background-color: #f8fafc; font-weight: 600; color: #64748b; width: 50px;">No</th>
                        <th style="padding: 12px; text-align: left; border-bottom: 1px solid #e2e8f0; background-color: #f8fafc; font-weight: 600; color: #64748b;">PFE ID</th>
                        <th style="padding: 12px; text-align: left; border-bottom: 1px solid #e2e8f0; background-color: #f8fafc; font-weight: 600; color: #64748b;">Building</th>
                        <th style="padding: 12px; text-align: left; border-bottom: 1px solid #e2e8f0; background-color: #f8fafc; font-weight: 600; color: #64748b;">Location</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($apars as $index => $apar)
                    <tr>
                        <td style="padding: 12px; border-bottom: 1px solid #e2e8f0; text-align: center;">{{ $index + 1 }}</td>
                        <td style="padding: 12px; border-bottom: 1px solid #e2e8f0; font-weight: 600; color: #0f172a;">{{ $apar->kode }}</td>
                        <td style="padding: 12px; border-bottom: 1px solid #e2e8f0;">{{ $apar->lokasi->gedung->nama ?? '-' }}</td>
                        <td style="padding: 12px; border-bottom: 1px solid #e2e8f0;">{{ $apar->lokasi->nama ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="padding: 16px; border-bottom: 1px solid #e2e8f0; text-align: center; color: #94a3b8; font-style: italic;">No PFE data in this area.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <p style="margin-bottom: 24px;">To start the inspection, please go to the PFE location and scan the QR Code attached to the PFE.</p>

            <div style="background-color: #fffbeb; padding: 20px; border-radius: 8px; border: 1px solid #fde68a; text-align: center; margin-bottom: 24px;">
                <p style="margin-top: 0; color: #92400e; font-weight: 600; font-size: 14px; letter-spacing: 0.5px;">YOUR INSPECTION PIN</p>
                @php $displayPin = $pin ?? $user->pin; @endphp
                @if(strlen($displayPin ?? '') > 10 || str_starts_with($displayPin ?? '', '$2y$'))
                    <p style="font-size: 14px; color: #b45309; font-weight: 700; margin: 10px 0;">Use your personal 4-digit Inspection PIN</p>
                    <p style="font-size: 12px; color: #92400e; margin: 0;"><em>(Your PIN is securely encrypted)</em></p>
                @else
                    <h3 style="letter-spacing: 6px; color: #b45309; font-size: 28px; margin: 10px 0;">{{ $displayPin }}</h3>
                @endif
                <p style="font-size: 13px; color: #92400e; margin-top: 8px; margin-bottom: 0;">Use this PIN for verification when clicking the 'Inspect' button after scanning the PFE QR Code.</p>
            </div>

            @if($jadwals->first()->catatan_tambahan)
            <div style="margin-top: 24px; padding-top: 20px; border-top: 1px dashed #cbd5e1;">
                <p style="font-weight: 600; margin-bottom: 8px; color: #475569;">Notes:</p>
                <p style="font-style: italic; color: #64748b; margin: 0;">{{ $jadwals->first()->catatan_tambahan }}</p>
            </div>
            @endif
        </div>
        
        <div style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 20px 24px; text-align: center; font-size: 13px; color: #64748b;">
            <p style="margin: 0;">This email is sent automatically by the PFE Monitoring Control System — EHSS Department. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
