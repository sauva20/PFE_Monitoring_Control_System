<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Login - PFE Monitoring Control System') }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}?v=3" type="image/svg+xml">

    <!-- Tailwind CSS (via Vite) -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif

    <style>
        @font-face {
            font-family: 'Rotis Sans Serif';
            src: local('Rotis Sans Serif'), local('Arial');
        }

        body {
            font-family: 'Rotis Sans Serif', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #F0F0F0;
            color: #1A1A1A;
        }
    </style>
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Google reCAPTCHA v2 -->
    @if(config('services.recaptcha.site_key'))
        <script src="https://www.google.com/recaptcha/api.js?hl={{ app()->getLocale() }}" async defer></script>
    @endif
</head>

<body class="bg-[#F0F0F0] text-[#1A1A1A] relative overflow-y-auto sm:overflow-hidden w-full min-h-screen grid place-items-center p-4 py-8"
    oncontextmenu="return false;">
    <!-- Global Toast Notification -->
    @if(session('success') || session('error'))
        <div x-data="{ show: false, type: '{{ session('success') ? 'success' : 'error' }}', message: '{{ addslashes(session('success') ?? session('error')) }}' }"
            x-init="setTimeout(() => show = true, 100); setTimeout(() => show = false, 4000)" x-show="show"
            style="display: none;" x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="opacity-0 translate-y-8 scale-95"
            x-transition:enter-end="opacity-100 translate-x-0 scale-100"
            x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="opacity-100 translate-x-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-8 scale-95"
            class="fixed bottom-6 right-6 z-[9999] flex items-center gap-3 px-5 py-4 rounded-2xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.2)] border min-w-[300px] max-w-sm backdrop-blur-md"
            :class="type === 'success' ? 'bg-white/95 border-teal-100' : 'bg-white/95 border-red-100'">

            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                :class="type === 'success' ? 'bg-teal-50 text-[#009B77]' : 'bg-red-50 text-red-500'">
                <i class="ph-fill text-xl" :class="type === 'success' ? 'ph-check-circle' : 'ph-warning-circle'"></i>
            </div>

            <div class="flex flex-col">
                <span class="text-sm font-bold text-slate-800"
                    x-text="type === 'success' ? '{{ __('Success!') }}' : '{{ __('Oops, an error occurred!') }}'"></span>
                <span class="text-xs font-medium text-slate-500 mt-0.5" x-text="message"></span>
            </div>

            <button @click="show = false"
                class="ml-auto w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                <i class="ph-bold ph-x text-lg"></i>
            </button>
        </div>
    @endif

    <!-- Decorative Background Elements (Modern Mesh Blur) -->
    <div
        class="absolute top-[-15%] left-[-10%] w-[50%] h-[50%] bg-[#009B77] rounded-full mix-blend-multiply filter blur-[120px] opacity-20 pointer-events-none">
    </div>
    <div
        class="absolute bottom-[-10%] right-[-10%] w-[45%] h-[45%] bg-[#8A4B9F] rounded-full mix-blend-multiply filter blur-[120px] opacity-20 pointer-events-none">
    </div>

    <div
        class="w-full max-w-md bg-white/95 backdrop-blur-xl rounded-[28px] shadow-[0_20px_60px_-15px_rgba(0,155,119,0.2)] overflow-hidden relative z-10 border border-white/50">

        <!-- Language Switcher -->
        <div class="absolute top-4 right-4 z-20" x-data="{ open: false }">
            <button @click="open = !open" @click.away="open = false"
                class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 transition-colors text-xs font-bold text-gray-600">
                <i class="ph-bold ph-translate"></i>
                <span class="uppercase">{{ app()->getLocale() }}</span>
                <i class="ph-bold ph-caret-down text-[10px]"></i>
            </button>
            <div x-show="open" x-transition.opacity.duration.200ms
                class="absolute right-0 mt-2 w-32 bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden py-1">
                <a href="{{ route('set-locale', 'id') }}"
                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-[#009B77]/10 hover:text-[#009B77] transition-colors font-medium flex items-center justify-between">
                    <span>Indonesia</span>
                    @if(app()->getLocale() == 'id') <i class="ph-bold ph-check text-[#009B77]"></i> @endif
                </a>
                <a href="{{ route('set-locale', 'en') }}"
                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-[#009B77]/10 hover:text-[#009B77] transition-colors font-medium flex items-center justify-between">
                    <span>English</span>
                    @if(app()->getLocale() == 'en') <i class="ph-bold ph-check text-[#009B77]"></i> @endif
                </a>
            </div>
        </div>

        <!-- Top Gradient Bar -->
        <div class="h-1.5 w-full bg-gradient-to-r from-[#009B77] via-[#8A4B9F] to-[#009B77]"></div>

        <div class="p-8 sm:p-10">
            <!-- Logo area -->
            <div class="flex justify-center mb-8">
                <div
                    class="flex items-center justify-center bg-gradient-to-br from-[#009B77]/10 to-[#009B77]/5 w-20 h-20 rounded-2xl border border-[#009B77]/20 shadow-inner">
                    <i class="ph-duotone ph-fire-extinguisher text-5xl text-[#009B77]"></i>
                </div>
            </div>

            <!-- Titles -->
            <div class="text-center mb-10">
                <h1 class="text-xl sm:text-xl font-extrabold text-[#1A1A1A] tracking-tight mb-1.5">
                    {{ __('PFE MONITORING CONTROL SYSTEM') }}
                </h1>
                <p class="text-sm text-[#009B77] font-bold">
                    PT B | BRAUN PHARMACEUTICAL INDONESIA
                </p>
            </div>

            <!-- Form -->
            <form action="/login" method="POST" class="space-y-5">
                @csrf

                <!-- Honeypot Field (Bot Trap) -->
                <!-- 
                CARA TEST (DEVELOPER MODE):
                Ganti 'opacity-0 absolute -z-50 h-0 w-0 overflow-hidden' 
                menjadi 'p-3 bg-red-50 border border-red-300 rounded-xl mb-4'
                -->
                <div class="opacity-0 absolute -z-50 h-0 w-0 overflow-hidden" aria-hidden="true">
                    <label for="_contact_number">Contact Number</label>
                    <input type="text" name="_contact_number" id="_contact_number" tabindex="-1" autocomplete="off">
                </div>

                @if ($errors->any())
                    <div class="text-red-500 text-[13px] flex items-start justify-center gap-1.5 pb-2 px-2">
                        <i class="ph-fill ph-warning-circle text-[15px] mt-[2px] shrink-0"></i>
                        <p class="leading-tight text-left">{{ $errors->first() }}</p>
                    </div>
                @endif

                @if (session('auth_error'))
                    <div class="text-amber-500 text-[13px] flex items-start justify-center gap-1.5 pb-2 px-2">
                        <i class="ph-fill ph-lock-key text-[15px] mt-[2px] shrink-0"></i>
                        <p class="leading-tight text-left">{{ session('auth_error') }}</p>
                    </div>
                @endif

                <!-- Email -->
                <div>
                    <label for="email"
                        class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">Email</label>
                    <div class="relative group">
                        <div
                            class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-[#009B77] transition-colors">
                            <i class="ph-fill ph-envelope text-lg"></i>
                        </div>
                        <input type="email" id="email" name="email" placeholder="{{ __('Enter your email') }}"
                            value="{{ old('email') }}"
                            class="w-full pl-11 pr-4 py-3.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-4 focus:ring-[#009B77]/10 focus:border-[#009B77] focus:bg-white transition-all outline-none text-[#1A1A1A] text-sm font-medium placeholder:font-normal"
                            required>
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="password"
                            class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider">Password</label>
                        <a href="{{ route('password.request') }}"
                            class="text-xs font-bold text-[#8A4B9F] hover:text-[#009B77] transition-colors">{{ __('Forgot Password?') }}</a>
                    </div>
                    <div class="relative group" x-data="{ show: false }">
                        <div
                            class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-[#009B77] transition-colors">
                            <i class="ph-fill ph-lock-key text-lg"></i>
                        </div>
                        <input :type="show ? 'text' : 'password'" id="password" name="password" placeholder="••••••••"
                            class="w-full pl-11 pr-12 py-3.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-4 focus:ring-[#009B77]/10 focus:border-[#009B77] focus:bg-white transition-all outline-none text-[#1A1A1A] text-sm tracking-widest font-mono placeholder:font-sans placeholder:tracking-normal"
                            required>
                        <button type="button" @click="show = !show"
                            class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-[#009B77] transition-colors focus:outline-none">
                            <i class="ph-fill text-lg" :class="show ? 'ph-eye-slash' : 'ph-eye'"></i>
                        </button>
                    </div>
                </div>

                @if(config('services.recaptcha.site_key'))
                    <!-- Google reCAPTCHA v2 Themed Wrapper -->
                    <div class="bg-gray-50/80 border border-gray-200/90 rounded-2xl p-3 flex flex-col items-center gap-2 shadow-sm hover:border-[#009B77]/40 hover:bg-white transition-all">
                        <div class="w-full flex items-center justify-between px-1 text-gray-500">
                            <span class="text-[11px] font-bold uppercase tracking-wider flex items-center gap-1.5 text-gray-500">
                                <i class="ph-fill ph-shield-check text-[#009B77] text-sm"></i>
                                <span>{{ __('Security Verification') }}</span>
                            </span>
                            <span class="text-[10px] text-gray-400 font-medium">Google reCAPTCHA</span>
                        </div>
                        <div class="w-full flex justify-center overflow-hidden rounded-xl border border-gray-200/60 bg-white p-1 shadow-inner">
                            <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}"></div>
                        </div>
                    </div>
                @endif

                <!-- Submit Button -->
                <div class="pt-4">
                    <button type="submit"
                        class="w-full bg-[#009B77] hover:bg-[#008264] text-white font-bold py-3.5 px-4 rounded-xl shadow-lg shadow-[#009B77]/30 hover:shadow-[#009B77]/50 transition-all duration-300 flex items-center justify-center gap-2 text-sm uppercase tracking-widest transform hover:-translate-y-0.5">
                        <span>{{ __('Login') }}</span>
                    </button>
                </div>

                <div class="mt-4 text-center">
                    <a href="{{ url('/display-report') }}"
                        class="inline-flex items-center justify-center gap-1.5 text-xs font-bold text-gray-500 hover:text-[#009B77] transition-colors">
                        <i class="ph-bold ph-monitor-play text-sm"></i>
                        {{ __('Open Display Report (Kiosk Mode)') }}
                    </a>
                </div>
            </form>

            <!-- Footer -->
            <div class="mt-10 pt-6 border-t border-gray-100 flex flex-col items-center gap-1 text-center">
                <div class="flex items-center gap-1.5">
                    <i class="ph-fill ph-shield-check text-[#009B77] text-sm"></i>
                    <p class="text-[11px] text-gray-400 font-medium">PFE Monitoring Control System v1.0 | PTBP</p>
                </div>
                <p class="text-[11px] text-gray-400 font-medium">&copy; 2026 q.sauva | Intern of EHSS, SM, OE &amp; LPMO
                </p>
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