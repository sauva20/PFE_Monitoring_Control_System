<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Print All PFE QR Codes') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        body {
            background-color: #f8fafc;
            color: #1e293b;
            padding: 24px;
        }
        .header-bar {
            max-width: 900px;
            margin: 0 auto 24px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            padding: 16px 24px;
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
        }
        .btn-print {
            background-color: #009B77;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(0, 155, 119, 0.25);
            transition: all 0.2s;
        }
        .btn-print:hover {
            background-color: #008264;
        }
        .btn-back {
            background: white;
            color: #64748b;
            border: 1px solid #cbd5e1;
            padding: 10px 16px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-back:hover {
            background: #f1f5f9;
            color: #334155;
        }
        .grid-container {
            max-width: 900px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        .label-card {
            background: white;
            border: 2px dashed #009B77;
            border-radius: 16px;
            padding: 18px 20px;
            display: flex;
            gap: 16px;
            align-items: center;
            box-shadow: 0 2px 6px rgba(0,0,0,0.02);
            position: relative;
        }
        .qr-wrapper {
            flex-shrink: 0;
            width: 130px;
            height: 130px;
            background: white;
            padding: 6px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .qr-wrapper svg {
            width: 100%;
            height: 100%;
            display: block;
        }
        .info-wrapper {
            flex-grow: 1;
            min-width: 0;
        }
        .tag-system {
            font-size: 11px;
            font-weight: 800;
            color: #009B77;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 4px;
            line-height: 1.25;
        }
        .tag-system span {
            display: block;
        }
        .kode-apar {
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.3px;
            margin-bottom: 5px;
            white-space: nowrap;
        }
        .meta-row {
            font-size: 12px;
            color: #1e293b;
            margin-bottom: 4px;
            display: flex;
            align-items: flex-start;
            gap: 6px;
            line-height: 1.35;
        }
        .meta-row i {
            font-size: 15px;
            color: #009B77;
            flex-shrink: 0;
            margin-top: 1px;
        }
        .meta-row strong, .meta-row span {
            color: #0f172a;
            font-weight: 700;
        }
        .scan-hint {
            margin-top: 8px;
            padding-top: 6px;
            border-top: 1px solid #e2e8f0;
            font-size: 10px;
            font-weight: 500;
            color: #475569;
            display: flex;
            align-items: center;
            gap: 5px;
            line-height: 1.25;
        }
        .scan-hint i {
            font-size: 13px;
            flex-shrink: 0;
            color: #009B77;
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 6mm 6mm;
            }
            html, body {
                background: white !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .header-bar {
                display: none !important;
            }
            .grid-container {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                grid-template-columns: 1fr 1fr !important;
                gap: 14px !important;
            }
            .label-card {
                width: 100% !important;
                height: 220px !important;
                min-height: 220px !important;
                max-height: 220px !important;
                box-shadow: none !important;
                border: 1.5px dashed #009B77 !important;
                break-inside: avoid !important;
                page-break-inside: avoid !important;
                padding: 12px 14px !important;
                gap: 14px !important;
                display: flex !important;
                align-items: center !important;
                box-sizing: border-box !important;
            }
            /* 4 baris per lembar = 8 kartu stiker per lembar kertas A4 */
            .label-card:nth-child(8n) {
                break-after: page !important;
                page-break-after: always !important;
            }
            /* QR Code: SETENGAH (50%) DARI FRAME KARTU - UKURAN BESAR PASTI */
            .qr-wrapper {
                flex: 0 0 175px !important;
                width: 175px !important;
                height: 175px !important;
                min-width: 175px !important;
                min-height: 175px !important;
                background: white !important;
                padding: 5px !important;
                border: 1px solid #e2e8f0 !important;
                border-radius: 12px !important;
                box-sizing: border-box !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
            }
            .qr-wrapper svg {
                width: 165px !important;
                height: 165px !important;
                min-width: 165px !important;
                min-height: 165px !important;
                display: block !important;
            }
            /* Info Sisi Kanan: 50% DARI FRAME KARTU */
            .info-wrapper {
                flex: 1 1 auto !important;
                min-width: 0 !important;
                display: flex !important;
                flex-direction: column !important;
                justify-content: center !important;
            }
            /* Judul PFE MONITORING <enter> CONTROL SYSTEM */
            .tag-system { 
                font-size: 9.5px !important; 
                letter-spacing: 0.3px !important; 
                margin-bottom: 2px !important; 
                line-height: 1.25 !important; 
                color: #009B77 !important; 
            }
            .tag-system span { 
                display: block !important; 
            }
            /* Kode APAR tidak terpotong ke bawah */
            .kode-apar { 
                font-size: 17.5px !important; 
                margin-bottom: 4px !important; 
                white-space: nowrap !important; 
                font-weight: 800 !important; 
                color: #000000 !important; 
            }
            /* Lokasi dan Tipe APAR BOLD di print out */
            .meta-row { 
                font-size: 11px !important; 
                margin-bottom: 3px !important; 
                gap: 5px !important; 
                line-height: 1.25 !important; 
            }
            .meta-row i { 
                font-size: 13.5px !important; 
                margin-top: 1px !important; 
            }
            .meta-row strong, .meta-row span { 
                font-weight: 800 !important; 
                color: #000000 !important; 
            }
            /* Teks Scan QR: jelas terlihat di print out, TAPI TIDAK DI-BOLD */
            .scan-hint {
                font-size: 9.5px !important;
                font-weight: 500 !important; /* Regular/Medium, BUKAN BOLD */
                color: #1e293b !important;   /* Hitam arang tajam agar tidak pudar di kertas */
                margin-top: 6px !important;
                padding-top: 4px !important;
                border-top: 1px solid #cbd5e1 !important;
            }
            .scan-hint i { 
                font-size: 12px !important; 
                color: #009B77 !important; 
            }
        }
    </style>
</head>
<body>

    <div class="header-bar">
        <div>
            <h2 style="font-size: 18px; font-weight: 800; color: #0f172a;">{{ __('Print PFE QR Code Stickers') }}</h2>
            <p style="font-size: 12px; color: #64748b; margin-top: 2px;">{{ __('Total:') }} {{ $apars->count() }} {{ __('PFE units ready to print on label/sticker paper') }}</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="/master-data" class="btn-back">
                <i class="ph-bold ph-arrow-left"></i> {{ __('Back') }}
            </a>
            <button onclick="window.print()" class="btn-print">
                <i class="ph-bold ph-printer"></i> {{ __('Print Now') }}
            </button>
        </div>
    </div>

    <div class="grid-container">
        @forelse($apars as $apar)
        <div class="label-card">
            <div class="qr-wrapper">
                {!! SimpleSoftwareIO\QrCode\Facades\QrCode::size(200)->generate(route('scan.apar', $apar->kode)) !!}
            </div>
            <div class="info-wrapper">
                <div class="tag-system">
                    <span>PFE MONITORING</span>
                    <span>CONTROL SYSTEM</span>
                </div>
                <div class="kode-apar">{{ $apar->kode }}</div>
                
                <div class="meta-row">
                    <i class="ph-bold ph-buildings" style="color: #009B77;"></i>
                    <span><strong>{{ $apar->lokasi->gedung->nama ?? 'n/a' }}</strong></span>
                </div>
                <div class="meta-row">
                    <i class="ph-bold ph-map-pin" style="color: #009B77;"></i>
                    <span><strong>{{ $apar->lokasi->nama ?? 'n/a' }}</strong></span>
                </div>
                <div class="meta-row">
                    <i class="ph-bold ph-fire-extinguisher" style="color: #009B77;"></i>
                    <span><strong>{{ $apar->jenis->nama ?? 'n/a' }} ({{ $apar->kapasitas->ukuran ?? 'n/a' }})</strong></span>
                </div>

                <div class="scan-hint">
                    <i class="ph-bold ph-scan"></i>
                    <span>{{ __('Scan QR to check details & inspection') }}</span>
                </div>
            </div>
        </div>
        @empty
        <div style="grid-column: span 2; text-align: center; padding: 48px; background: white; border-radius: 16px; color: #64748b;">
            {{ __('No registered PFE data available.') }}
        </div>
        @endforelse
    </div>


    <!-- Anti-Inspect Element Script -->
    <script>
        // Disable Right Click
        document.addEventListener('contextmenu', event => event.preventDefault());
        
        // Disable Keyboard Shortcuts
        document.addEventListener('keydown', function(e) {
            // Disable F12
            if(e.keyCode == 123) {
                e.preventDefault();
                return false;
            }
            // Disable Ctrl+Shift+I, Ctrl+Shift+J, Ctrl+Shift+C
            if(e.ctrlKey && e.shiftKey && (e.keyCode == 'I'.charCodeAt(0) || e.keyCode == 'J'.charCodeAt(0) || e.keyCode == 'C'.charCodeAt(0))) {
                e.preventDefault();
                return false;
            }
            // Disable Ctrl+U (View Source)
            if(e.ctrlKey && e.keyCode == 'U'.charCodeAt(0)) {
                e.preventDefault();
                return false;
            }
        });
    </script>
</body>
</html>

