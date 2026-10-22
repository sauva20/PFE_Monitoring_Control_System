<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Masa Akses Viewer Berakhir - PFE Monitoring Control System</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}?v=3" type="image/svg+xml">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Rotis Sans Serif font -->
    <style>
        @font-face {
            font-family: 'Rotis Sans Serif';
            src: url('/fonts/RotisSansSerif.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
            font-display: swap;
        }
        @font-face {
            font-family: 'Rotis Sans Serif';
            src: url('/fonts/RotisSansSerif-Bold.ttf') format('truetype');
            font-weight: bold;
            font-style: normal;
            font-display: swap;
        }
        body {
            font-family: 'Rotis Sans Serif', sans-serif;
            background-color: #F8FAFC;
        }
    </style>

    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body class="min-h-screen w-screen flex flex-col justify-between relative overflow-x-hidden text-slate-800 bg-[#F8FAFC]">

    <!-- Decorative Blurs -->
    <div class="fixed top-[-10%] left-[-10%] w-[50vw] h-[50vw] max-w-[500px] max-h-[500px] bg-rose-500 rounded-full mix-blend-multiply filter blur-[140px] opacity-10 pointer-events-none"></div>
    <div class="fixed bottom-[-10%] right-[-10%] w-[50vw] h-[50vw] max-w-[500px] max-h-[500px] bg-amber-500 rounded-full mix-blend-multiply filter blur-[140px] opacity-10 pointer-events-none"></div>

    <!-- Header Navigation -->
    <header class="w-full max-w-5xl mx-auto px-6 py-6 flex items-center justify-between relative z-10">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-2xl bg-[#009B77]/10 border border-[#009B77]/20 flex items-center justify-center text-[#009B77] shadow-sm">
                <i class="ph-duotone ph-fire-extinguisher text-2xl"></i>
            </div>
            <div>
                <h1 class="text-base font-bold text-[#009B77] leading-tight tracking-wide">PFE Monitoring Control System</h1>
                <p class="text-[11px] font-semibold text-slate-500 tracking-wider">Environment, Health, Safety & Security</p>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="flex-1 flex items-center justify-center p-4 sm:p-6 relative z-10 my-6">
        
        <div class="w-full max-w-md bg-white/95 backdrop-blur-xl rounded-[28px] shadow-[0_20px_60px_-15px_rgba(0,0,0,0.1)] border border-slate-200/70 overflow-hidden text-center p-8 sm:p-10">
            
            @if(session('success'))
            <div class="mb-6 bg-emerald-50 border border-emerald-200 rounded-2xl px-4 py-3 flex items-center gap-2.5 text-left shadow-sm">
                <i class="ph-bold ph-check-circle text-emerald-600 text-lg flex-shrink-0 leading-none"></i>
                <span class="text-xs text-emerald-800 font-semibold leading-normal">
                    {{ session('success') }}
                </span>
            </div>
            @endif

            <!-- Warning/Expired Icon -->
            <div class="w-20 h-20 rounded-3xl {{ $isInactive ? 'bg-slate-100 text-slate-500 border border-slate-200' : 'bg-rose-50 text-rose-500 border border-rose-200' }} mx-auto flex items-center justify-center mb-6 shadow-sm">
                @if($isInactive)
                    <i class="ph-bold ph-prohibit text-4xl"></i>
                @else
                    <i class="ph-bold ph-clock-countdown text-4xl"></i>
                @endif
            </div>

            <!-- Title & Subtitle -->
            @if($isInactive)
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">Akses Viewer Dinonaktifkan</h2>
                <p class="text-xs text-slate-500 font-medium mt-2 leading-relaxed">
                    Tautan akses display report ini telah dinonaktifkan oleh Administrator EHSS. Silakan hubungi tim EHSS jika Anda memerlukan aktivasi kembali.
                </p>
            @else
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">Masa Berlaku Akses Telah Berakhir</h2>
                <p class="text-xs text-slate-500 font-medium mt-2 leading-relaxed">
                    Masa aktif tautan display untuk Viewer ini telah kedaluwarsa pada tanggal <strong class="text-slate-800">{{ \Carbon\Carbon::parse($guest->valid_until)->translatedFormat('d F Y') }}</strong> (23:59 WIB).
                </p>
            @endif

            <!-- Guest Card -->
            <div class="my-6 bg-slate-50 rounded-2xl p-4 border border-slate-200 text-left">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Informasi Viewer</p>
                <h4 class="text-sm font-bold text-slate-800 mt-1">{{ $guest->name }}</h4>
                <p class="text-xs text-slate-600 font-medium flex items-center gap-1.5 mt-0.5">
                    <i class="ph-bold ph-buildings text-slate-400"></i>
                    <span>{{ $guest->institution }}</span>
                </p>
                <p class="text-xs text-slate-500 mt-1 flex items-center gap-1.5">
                    <i class="ph-bold ph-envelope-simple text-slate-400"></i>
                    <span>{{ $guest->email }}</span>
                </p>
            </div>

            <!-- Instruction Box -->
            <div class="bg-amber-50/80 border border-amber-200/80 rounded-2xl p-4 text-xs text-amber-900 leading-relaxed text-left flex items-start gap-3">
                <i class="ph-fill ph-info text-amber-600 text-base mt-0.5 flex-shrink-0"></i>
                <div>
                    <strong class="font-bold block mb-0.5">{{ __('Perpanjangan Akses') }}</strong>
                    <span>Jika Anda masih memerlukan akses pemantauan ini, silakan hubungi tim EHSS (Environment, Health, Safety & Security) untuk memperpanjang durasi akses.</span>
                </div>
            </div>

            <!-- Action Button -->
            <div class="mt-8">
                <a href="{{ url('/') }}" class="inline-flex items-center justify-center gap-2 w-full bg-slate-800 hover:bg-slate-900 text-white font-bold py-3.5 px-6 rounded-2xl shadow-sm transition-all text-xs uppercase tracking-wider">
                    <i class="ph-bold ph-arrow-left"></i>
                    <span>{{ __('Kembali ke Halaman Utama') }}</span>
                </a>
            </div>

        </div>

    </main>

    <!-- Page Footer -->
    <footer class="w-full text-center py-4 text-xs font-semibold text-slate-400 relative z-10">
        &copy; {{ date('Y') }} EHSS Department — Environment, Health, Safety & Security. All rights reserved.
    </footer>

</body>
</html>
