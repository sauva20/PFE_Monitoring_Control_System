<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Sesi Kedaluwarsa - APAR Monitoring System') }}</title>
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
            font-family: 'Rotis Sans Serif', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #F8FAFC;
        }
    </style>

    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body class="min-h-screen flex items-center justify-center p-4 bg-[#F8FAFC] text-slate-800 relative overflow-hidden">

    <!-- Decorative Gradients -->
    <div class="fixed top-[-10%] left-[-10%] w-[50vw] h-[50vw] max-w-[450px] max-h-[450px] bg-[#009B77] rounded-full mix-blend-multiply filter blur-[120px] opacity-15 pointer-events-none"></div>
    <div class="fixed bottom-[-10%] right-[-10%] w-[50vw] h-[50vw] max-w-[450px] max-h-[450px] bg-emerald-600 rounded-full mix-blend-multiply filter blur-[120px] opacity-10 pointer-events-none"></div>

    <div class="w-full max-w-md bg-white rounded-3xl shadow-[0_20px_50px_-15px_rgba(0,0,0,0.08)] border border-slate-200/80 overflow-hidden text-center p-8 relative z-10">
        
        <div class="w-16 h-16 rounded-2xl bg-amber-50 border border-amber-200 text-amber-600 flex items-center justify-center mx-auto mb-4 shadow-sm">
            <i class="ph-duotone ph-clock-countdown text-3xl"></i>
        </div>

        <span class="inline-block px-3 py-1 bg-amber-100 text-amber-800 text-xs font-bold rounded-full mb-3 uppercase tracking-wider">
            HTTP 419
        </span>

        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight mb-2">
            {{ __('Sesi Kedaluwarsa') }}
        </h1>
        <p class="text-sm text-slate-500 leading-relaxed mb-6">
            {{ __('Halaman atau formulir telah terbuka terlalu lama sehingga sesi keamanan kedaluwarsa. Silakan muat ulang halaman atau kembali.') }}
        </p>

        <div class="flex justify-center">
            <button onclick="window.location.reload()" 
                    class="w-full sm:w-auto px-6 py-2.5 bg-[#009B77] hover:bg-[#008264] text-white text-sm font-semibold rounded-xl shadow-sm transition-all flex items-center justify-center gap-2">
                <i class="ph-bold ph-arrow-clockwise"></i>
                <span>{{ __('Muat Ulang Halaman') }}</span>
            </button>
        </div>
    </div>

</body>
</html>
