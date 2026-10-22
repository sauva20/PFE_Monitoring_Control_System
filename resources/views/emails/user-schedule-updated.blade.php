<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pembaruan Jadwal Inspeksi</title>
</head>
<body style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8fafc; color: #334155; line-height: 1.6; margin: 0; padding: 0;">
    <div style="max-width: 650px; margin: 40px auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); border: 1px solid #e2e8f0; border-top: 5px solid #009B77;">
        
        <div style="background-color: #009B77; color: #ffffff; padding: 24px; text-align: center;">
            <h1 style="margin: 0; font-size: 22px; font-weight: 700; letter-spacing: 0.5px;">Pembaruan Profil & Jadwal Inspeksi</h1>
        </div>

        <div style="padding: 32px 24px;">
            <p style="font-weight: 600; font-size: 16px; margin-top: 0;">Halo, {{ $user->name }}</p>
            
            <p>
                Informasi profil Anda pada sistem PFE Monitoring Control System telah diperbarui oleh Administrator. Berikut adalah data terbaru mengenai tanggung jawab inspeksi Anda:
            </p>

            <div style="background-color: #f1f5f9; padding: 16px; border-radius: 8px; margin-bottom: 24px; border-left: 4px solid #009B77;">
                <p style="margin: 0; font-size: 14px; color: #64748b; margin-bottom: 4px;">Batas Deadline Inspeksi Bulanan Anda:</p>
                <h3 style="margin: 0; color: #009B77; font-size: 18px;">Setiap Tanggal {{ $user->jadwal_rutin_tanggal ?? '-' }} setiap bulannya</h3>
            </div>

            <p>
                Anda sekarang ditugaskan untuk melakukan inspeksi rutin pada area gedung berikut:
            </p>
            <ul style="color: #475569; font-weight: 600; margin-bottom: 24px;">
                @if(count($buildings) > 0)
                    @foreach($buildings as $gedung)
                        <li>Building {{ $gedung->nama }}</li>
                    @endforeach
                @else
                    <li><em>Tidak ada gedung yang ditugaskan</em></li>
                @endif
            </ul>

            <div style="background-color: #fffbeb; padding: 20px; border-radius: 8px; border: 1px solid #fde68a; text-align: center; margin-bottom: 24px;">
                <p style="margin-top: 0; color: #92400e; font-weight: 600; font-size: 14px; letter-spacing: 0.5px;">PIN INSPEKSI ANDA</p>
                <p style="font-size: 13px; color: #92400e; margin-bottom: 0;">(Gunakan PIN ini untuk login inspeksi via QR Code)</p>
                @if($user->pin)
                    <p style="font-size: 12px; color: #b45309;"><em>*PIN Anda tetap sama, gunakan PIN yang Anda ketahui.</em></p>
                @else
                    <p style="font-size: 12px; color: #b45309;"><em>*Hubungi Admin jika Anda belum memiliki PIN.</em></p>
                @endif
            </div>

            <p style="margin-top: 24px;">
                Pastikan seluruh inspeksi APAR telah selesai dilaksanakan sebelum atau pada tanggal batas deadline yang telah ditetapkan. Terima kasih atas kerja sama Anda.
            </p>
        </div>

        <div style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 20px 24px; text-align: center; font-size: 13px; color: #64748b;">
            <p style="margin: 0;">Email ini dikirim otomatis oleh PFE Monitoring Control System — EHSS Department. Harap tidak membalas email ini.</p>
        </div>
    </div>
</body>
</html>
