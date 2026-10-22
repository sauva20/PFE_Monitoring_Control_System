<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>PFE Inspection Reminder</title>
</head>
<body style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8fafc; color: #334155; line-height: 1.6; margin: 0; padding: 0;">
    <div style="max-width: 650px; margin: 40px auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); border: 1px solid #e2e8f0; border-top: 5px solid #009B77;">
        
        <div style="background-color: #009B77; color: #ffffff; padding: 24px; text-align: center;">
            <h1 style="margin: 0; font-size: 22px; font-weight: 700; letter-spacing: 0.5px;">Pengingat Batas Deadline Inspeksi Bulanan</h1>
        </div>

        <div style="padding: 32px 24px;">
            <p style="font-weight: 600; font-size: 16px; margin-top: 0;">Halo, {{ $user->name }}</p>
            
            <p>
                Ini adalah pengingat otomatis dari PFE Monitoring Control System — EHSS Department mengenai <strong>Batas Deadline Inspeksi Bulanan Anda</strong> yang jatuh pada tanggal:
            </p>

            <div style="background-color: #f1f5f9; padding: 16px; border-radius: 8px; margin-bottom: 24px; border-left: 4px solid #009B77;">
                <p style="margin: 0; font-size: 13px; color: #64748b; font-weight: 600; text-transform: uppercase;">Batas Deadline Inspeksi Bulanan Anda:</p>
                <h3 style="margin: 4px 0 0 0; color: #009B77; font-size: 20px;">{{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }}</h3>
            </div>

            @if($jenis === 'Inspeksi Rutin Bulanan')
            <p>
                Mohon luangkan waktu Anda untuk menyelesaikan inspeksi rutin pada seluruh area gedung tanggung jawab Anda sebelum batas deadline tersebut:
            </p>
            <ul style="color: #475569; font-weight: 600; margin-bottom: 24px;">
                @foreach($user->gedungs as $gedung)
                    <li>Gedung {{ $gedung->nama }}</li>
                @endforeach
            </ul>
            @endif

            <div style="background-color: #fffbeb; padding: 20px; border-radius: 8px; border: 1px solid #fde68a; text-align: center; margin-bottom: 24px;">
                <p style="margin-top: 0; color: #92400e; font-weight: 600; font-size: 14px; letter-spacing: 0.5px;">PIN INSPEKSI ANDA</p>
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
            <p style="margin: 0;">Email ini dikirim otomatis oleh PFE Monitoring Control System — EHSS Department. Harap tidak membalas email ini.</p>
        </div>
    </div>
</body>
</html>
