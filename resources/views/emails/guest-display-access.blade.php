<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Viewer Display Monitoring APAR - PFE Monitoring Control System</title>
</head>
<body style="font-family: 'Segoe UI', Arial, sans-serif; background-color: #f1f5f9; color: #334155; line-height: 1.6; margin: 0; padding: 0;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f1f5f9; padding: 30px 10px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width: 600px; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06); border: 1px solid #e2e8f0; border-top: 4px solid #009B77;">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background-color: #009B77; padding: 24px 30px; text-align: left;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td>
                                        <h1 style="margin: 0; color: #ffffff; font-size: 20px; font-weight: 800; letter-spacing: 0.5px; text-transform: uppercase;">
                                            PFE Monitoring Control System
                                        </h1>
                                        <p style="margin: 4px 0 0 0; color: #d1fae5; font-size: 12px; font-weight: 600; letter-spacing: 0.5px;">
                                            EHSS Department &bull; Environment, Health, Safety & Security
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Main Content -->
                    <tr>
                        <td style="padding: 32px 30px 24px 30px;">
                            <p style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0 0 16px 0;">
                                Halo, {{ $guest->name }}
                            </p>

                            <p style="font-size: 14px; color: #475569; margin: 0 0 24px 0; line-height: 1.6;">
                                Anda telah diberikan akses resmi sebagai <strong>Viewer</strong> untuk memantau laporan pemantauan proteksi kebakaran (PFE Monitoring) Departemen EHSS.
                            </p>

                            <!-- Metadata Table -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 24px; overflow: hidden;">
                                <tr>
                                    <td style="padding: 12px 16px; width: 38%; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #e2e8f0;">
                                        Instansi / Lembaga
                                    </td>
                                    <td style="padding: 12px 16px; font-size: 13px; font-weight: 700; color: #0f172a; border-bottom: 1px solid #e2e8f0;">
                                        {{ $guest->institution }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 16px; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #e2e8f0;">
                                        Peran Akses
                                    </td>
                                    <td style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #009B77; border-bottom: 1px solid #e2e8f0;">
                                        Viewer (Display Kiosk)
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 16px; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">
                                        Masa Berlaku Akses
                                    </td>
                                    <td style="padding: 12px 16px; font-size: 13px; font-weight: 700; color: #047857;">
                                        Aktif s.d. {{ \Carbon\Carbon::parse($guest->valid_until)->translatedFormat('d F Y') }} <span style="font-weight: 500; font-size: 11px; color: #64748b;">(23:59 WIB)</span>
                                    </td>
                                </tr>
                            </table>

                            <!-- PIN Card -->
                            <div style="background-color: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 22px; text-align: center; margin-bottom: 26px;">
                                <p style="margin: 0; color: #92400e; font-size: 11px; font-weight: 800; letter-spacing: 1.5px; text-transform: uppercase;">
                                    PIN AKSES VIEWER (4 DIGIT)
                                </p>
                                <div style="font-family: 'Consolas', 'Courier New', monospace; font-size: 36px; font-weight: 800; letter-spacing: 14px; color: #b45309; padding: 12px 0 6px 14px; line-height: 1;">
                                    {{ $guest->pin }}
                                </div>
                                <p style="margin: 8px 0 0 0; font-size: 12px; color: #92400e; line-height: 1.5;">
                                    Gunakan 4 digit PIN di atas saat membuka tautan display untuk memverifikasi identitas Anda.
                                </p>
                            </div>

                            <!-- CTA Button -->
                            <div style="text-align: center; margin: 28px 0 24px 0;">
                                <a href="{{ $accessUrl }}" style="background-color: #009B77; color: #ffffff; padding: 13px 28px; text-decoration: none; border-radius: 8px; font-weight: 700; font-size: 14px; display: inline-block; letter-spacing: 0.3px;">
                                    Buka Display Monitoring
                                </a>
                            </div>

                            <!-- Fallback Link -->
                            <div style="padding-top: 18px; border-top: 1px dashed #cbd5e1; margin-top: 24px;">
                                <p style="font-size: 12px; color: #64748b; margin: 0 0 6px 0;">
                                    Jika tombol di atas tidak dapat diklik, salin dan buka tautan berikut pada peramban (browser) Anda:
                                </p>
                                <a href="{{ $accessUrl }}" style="color: #009B77; word-break: break-all; font-size: 12px; text-decoration: underline;">
                                    {{ $accessUrl }}
                                </a>
                            </div>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 18px 30px; text-align: center;">
                            <p style="margin: 0 0 4px 0; font-size: 11px; color: #94a3b8; line-height: 1.5;">
                                Email ini dikirimkan secara otomatis oleh PFE Monitoring Control System — EHSS Department. Harap tidak membalas email ini.
                            </p>
                            <p style="margin: 0; font-size: 11px; font-weight: 600; color: #64748b;">
                                &copy; {{ date('Y') }} Departemen EHSS. All rights reserved.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
