<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PFE Expiry Warning</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8fafc;
            color: #334155;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 650px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border: 1px solid #e2e8f0;
            border-top: 5px solid #009B77;
        }
        .header {
            background-color: #009B77; /* EHSS Emerald Green */
            color: #ffffff;
            padding: 24px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .content {
            padding: 32px 24px;
        }
        .warning-text {
            color: #b91c1c;
            font-weight: 700;
            font-size: 16px;
            margin-bottom: 24px;
            text-align: center;
            background-color: #fef2f2;
            padding: 14px;
            border-radius: 8px;
            border: 1px solid #fca5a5;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            margin-bottom: 25px;
            font-size: 14px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        th {
            background-color: #f8fafc;
            font-weight: 600;
            color: #64748b;
        }
        .footer {
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 20px 24px;
            text-align: center;
            font-size: 13px;
            color: #64748b;
        }
        .btn {
            display: inline-block;
            background-color: #009B77;
            color: #ffffff;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: bold;
            margin-top: 10px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>PFE Expiry Warning (30 Days)</h1>
        </div>
        
        <div class="content">
            <div class="warning-text">
                There are PFE units that will expire on {{ $targetDate }}
            </div>

            <p style="font-weight: 600; font-size: 16px;">Hello{{ $recipientName ? ' ' . $recipientName : '' }},</p>
            <p>This email is an automated reminder that there are <strong>{{ $apars->count() }} Portable Fire Extinguishers (PFE)</strong> in your responsible area that will expire in 30 days.</p>
            
            <table>
                <thead>
                    <tr>
                        <th>PFE ID</th>
                        <th>Building</th>
                        <th>Location Point</th>
                        <th>Type</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($apars as $apar)
                    <tr>
                        <td style="font-weight: 600;">{{ $apar->kode }}</td>
                        <td>{{ $apar->lokasi->gedung->nama ?? 'n/a' }}</td>
                        <td>{{ $apar->lokasi->nama ?? 'n/a' }}</td>
                        <td>{{ $apar->jenis->nama ?? 'n/a' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <p>Please coordinate immediately for checking, refilling, or replacing these PFE units before the expiry date to maintain safety and security standards.</p>

            <div style="text-align: center; margin-top: 30px;">
                <a href="{{ url('/master-data') }}" class="btn" style="color: #ffffff !important; text-decoration: none; display: inline-block; background-color: #009B77; padding: 12px 24px; border-radius: 8px; font-weight: bold;">View PFE Data in System</a>
            </div>
        </div>

        <div class="footer">
            <p>This email is sent automatically by the PFE Monitoring Control System — EHSS. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
