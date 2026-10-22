<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="300"> <!-- Auto refresh every 5 minutes -->
    <title>@yield('title', 'PFE Monitoring Control System - Display')</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}?v=3" type="image/svg+xml">
    
    <!-- Tailwind CSS (via Vite or CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/js/app.js'])
    
    <!-- Alpine Plugins -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <!-- Alpine Core -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Phosphor Icons -->
    <script src="https://cdn.jsdelivr.net/npm/@phosphor-icons/web"></script>
    
    <style>
        @font-face {
            font-family: 'Rotis Sans Serif';
            src: local('Rotis Sans Serif'), local('Arial');
        }
        body {
            font-family: 'Rotis Sans Serif', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f8fafc;
        }
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9; 
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1; 
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8; 
        }

        /* Global UI Animations */
        .report-card {
            position: relative;
        }
        .report-card::after {
            content: '';
            position: absolute;
            top: -4px; right: -4px; bottom: -4px; left: -4px;
            border: 2px solid #009B77;
            border-radius: 20px;
            opacity: 0;
            transform: scale(0.96);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            pointer-events: none;
        }
        .report-card:hover::after {
            opacity: 1;
            transform: scale(1);
        }
    </style>
</head>
<body class="text-slate-800 antialiased selection:bg-[#009B77]/20 selection:text-[#009B77]">

    <div class="min-h-screen flex flex-col">
        <!-- Display Header (Optional, just a small branding) -->
        <header class="bg-white border-b border-slate-200 py-3 px-6 shadow-sm flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center bg-gradient-to-br from-[#009B77]/10 to-[#009B77]/5 w-10 h-10 rounded-xl border border-[#009B77]/20">
                    <i class="ph-duotone ph-fire-extinguisher text-2xl text-[#009B77]"></i>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-[#009B77] leading-tight">PFE Monitoring Control System</h1>
                    <p class="text-xs text-slate-500 font-semibold tracking-wide">Environment, Health, Safety & Security</p>
                </div>
            </div>
            
            <div class="flex items-center text-right">
                <div class="flex flex-col">
                    <span class="text-sm font-extrabold text-[#009B77] tracking-widest uppercase">EHSS</span>
                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Environment, Health, Safety, & Security</span>
                </div>
                <div class="ml-3 pl-3 border-l-2 border-slate-100 flex items-center justify-center text-slate-300">
                    <i class="ph-fill ph-shield-check text-3xl"></i>
                </div>
            </div>
        </header>

        @if(isset($guestDisplay))
        <!-- Guest Access Info Banner -->
        <div class="bg-gradient-to-r from-emerald-900 to-teal-900 text-white px-6 py-2 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs border-b border-emerald-800 shadow-inner print:hidden">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#009B77] text-white uppercase tracking-wider">
                    Sesi Viewer Terverifikasi
                </span>
                <span class="font-bold">{{ $guestDisplay->name }}</span>
                <span class="text-emerald-300 font-medium">({{ $guestDisplay->institution }})</span>
                <span class="text-emerald-400 font-medium hidden md:inline">&bull; Berlaku hingga {{ \Carbon\Carbon::parse($guestDisplay->valid_until)->translatedFormat('d F Y') }}</span>
            </div>
            <form action="{{ route('display.access.logout', $guestDisplay->access_token) }}" method="POST">
                @csrf
                <button type="submit" class="px-2.5 py-1 rounded-lg bg-white/10 hover:bg-white/20 text-emerald-100 hover:text-white font-bold text-[11px] transition-colors flex items-center gap-1.5">
                    <i class="ph-bold ph-sign-out"></i>
                    <span>Keluar Sesi</span>
                </button>
            </form>
        </div>
        @endif

        <!-- Main Content -->
        <main class="flex-1 p-3 overflow-y-auto w-full mx-auto flex flex-col">
            @yield('content')
        </main>
        
        <footer class="py-2 text-center text-xs font-bold text-slate-400">
            &copy; {{ date('Y') }} EHSS Department — Environment, Health, Safety & Security.
        </footer>
    </div>

    <!-- Script Chart.js if needed -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    @yield('scripts')

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
    <!-- Anti-Inspect Script -->
    <script>
        document.addEventListener('contextmenu', event => event.preventDefault());
        window.addEventListener('keydown', function (e) {
            // F12
            if (e.keyCode === 123) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
            // Ctrl+Shift+I / J / C
            if (e.ctrlKey && e.shiftKey && (e.keyCode === 73 || e.keyCode === 74 || e.keyCode === 67)) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
            // Ctrl+U (View Source)
            if (e.ctrlKey && e.keyCode === 85) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        }, { capture: true });
    </script>
</body>
</html>

