<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Success') }} - PFE Monitoring Control System</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}?v=3" type="image/svg+xml">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Setup Rotis Sans Serif - same as app.blade.php */
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
            background-color: #F0F0F0;
            color: #1A1A1A;
        }
    </style>
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body class="bg-[#F0F0F0] text-[#1A1A1A] relative overflow-hidden w-full h-screen grid place-items-center p-4">

    <!-- Decorative Background Elements -->
    <div class="absolute top-[-15%] left-[-10%] w-[50%] h-[50%] bg-[#009B77] rounded-full mix-blend-multiply filter blur-[120px] opacity-20 pointer-events-none"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[45%] h-[45%] bg-[#8A4B9F] rounded-full mix-blend-multiply filter blur-[120px] opacity-20 pointer-events-none"></div>

    <div class="w-full max-w-md bg-white/95 backdrop-blur-xl rounded-[28px] shadow-[0_20px_60px_-15px_rgba(0,155,119,0.2)] overflow-hidden relative z-10 border border-white/50 text-center">
        <!-- Top Gradient Bar -->
        <div class="h-1.5 w-full bg-gradient-to-r from-[#009B77] via-[#8A4B9F] to-[#009B77]"></div>

        <div class="p-8 sm:p-10">
            <!-- Logo area -->
            <div class="flex justify-center mb-8">
                <div class="flex items-center justify-center bg-gradient-to-br from-[#009B77] to-[#008264] w-20 h-20 rounded-2xl shadow-[0_10px_20px_rgba(0,155,119,0.3)]">
                    <i class="ph-bold ph-check text-5xl text-white"></i>
                </div>
            </div>

            <!-- Titles -->
            <div class="mb-10">
                <h1 class="text-xl sm:text-2xl font-bold text-[#1A1A1A] tracking-tight mb-2">{{ __('SETUP SUCCESSFUL') }}</h1>
                <p class="text-sm text-gray-500 leading-relaxed">
                    {{ __('Your account password has been successfully created and safely stored.') }}
                </p>
            </div>

            <!-- Submit Button -->
            <div class="pt-2">
                <a href="{{ route('login') }}" class="w-full bg-[#009B77] hover:bg-[#008264] text-white font-bold py-3.5 px-4 rounded-xl shadow-lg shadow-[#009B77]/30 hover:shadow-[#009B77]/50 transition-all duration-300 flex items-center justify-center gap-2 text-sm uppercase tracking-widest transform hover:-translate-y-0.5">
                    <span>{{ __('Login to System Now') }}</span>
                    <i class="ph-bold ph-arrow-right"></i>
                </a>
            </div>

            <!-- Footer -->
            <div class="mt-10 pt-6 border-t border-gray-100 flex flex-col items-center gap-1 text-center">
                <div class="flex items-center gap-1.5">
                    <i class="ph-fill ph-shield-check text-[#009B77] text-sm"></i>
                    <p class="text-[11px] text-gray-400 font-medium">PFE Monitoring Control System v1.0 | EHSS</p>
                </div>
                <p class="text-[11px] text-gray-400 font-medium">&copy; 2026 q.sauva | Intern of EHSS, SM, OE &amp; LPMO</p>
            </div>
        </div>
    </div>

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
