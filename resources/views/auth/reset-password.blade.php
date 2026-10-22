<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Reset Password') }} - PFE Monitoring Control System</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}?v=3" type="image/svg+xml">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Setup Rotis Sans Serif */
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

    <div class="w-full max-w-md bg-white/95 backdrop-blur-xl rounded-[28px] shadow-[0_20px_60px_-15px_rgba(0,155,119,0.2)] overflow-hidden relative z-10 border border-white/50">
        <!-- Top Gradient Bar -->
        <div class="h-1.5 w-full bg-gradient-to-r from-[#009B77] via-[#8A4B9F] to-[#009B77]"></div>

        <div class="p-8 sm:p-10">
            <!-- Logo area -->
            <div class="flex justify-center mb-8">
                <div class="flex items-center justify-center bg-gradient-to-br from-[#009B77]/10 to-[#009B77]/5 w-16 h-16 rounded-2xl border border-[#009B77]/20 shadow-inner">
                    <i class="ph-duotone ph-lock-key text-4xl text-[#009B77]"></i>
                </div>
            </div>

            <!-- Titles -->
            <div class="text-center mb-8">
                <h1 class="text-xl sm:text-xl font-bold text-[#1A1A1A] tracking-tight mb-1.5">RESET PASSWORD</h1>
                <p class="text-sm text-gray-500">
                    {{ __('Hello,') }} <strong>{{ $user->name }}</strong>.<br>{{ __('Please create a new password for your account.') }}
                </p>
            </div>

            <!-- Form -->
            <form action="{{ URL::full() }}" method="POST" class="space-y-5">
                @csrf
                
                @if ($errors->any())
                <div class="text-red-500 text-[13px] font-bold flex items-center justify-center gap-1.5 pb-2 text-center">
                    <i class="ph-fill ph-warning-circle text-base shrink-0"></i>
                    <p class="leading-tight">{{ $errors->first() }}</p>
                </div>
                @endif

                <!-- Email (Readonly) -->
                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">{{ __('Account Email') }}</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                            <i class="ph-fill ph-envelope text-lg"></i>
                        </div>
                        <input type="email" value="{{ $user->email }}" readonly
                            class="w-full pl-11 pr-4 py-3.5 bg-gray-100 border border-gray-200 rounded-xl text-gray-500 text-sm font-medium outline-none cursor-not-allowed">
                    </div>
                </div>

                <!-- New Password -->
                <div>
                    <label for="password" class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">{{ __('New Password') }}</label>
                    <div class="relative group" x-data="{ show: false }">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-[#009B77] transition-colors">
                            <i class="ph-fill ph-lock-key text-lg"></i>
                        </div>
                        <input :type="show ? 'text' : 'password'" id="password" name="password" placeholder="{{ __('Minimum 8 characters') }}" minlength="8"
                            class="w-full pl-11 pr-12 py-3.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-4 focus:ring-[#009B77]/10 focus:border-[#009B77] focus:bg-white transition-all outline-none text-[#1A1A1A] text-sm tracking-widest font-mono placeholder:font-sans placeholder:tracking-normal" required autofocus>
                        <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-[#009B77] transition-colors focus:outline-none">
                            <i class="ph-fill text-lg" :class="show ? 'ph-eye-slash' : 'ph-eye'"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Confirm New Password -->
                <div>
                    <label for="password_confirmation" class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">{{ __('Confirm New Password') }}</label>
                    <div class="relative group" x-data="{ show: false }">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-[#009B77] transition-colors">
                            <i class="ph-fill ph-check-circle text-lg"></i>
                        </div>
                        <input :type="show ? 'text' : 'password'" id="password_confirmation" name="password_confirmation" placeholder="{{ __('Repeat the password above') }}" minlength="8"
                            class="w-full pl-11 pr-12 py-3.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-4 focus:ring-[#009B77]/10 focus:border-[#009B77] focus:bg-white transition-all outline-none text-[#1A1A1A] text-sm tracking-widest font-mono placeholder:font-sans placeholder:tracking-normal" required>
                        <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-[#009B77] transition-colors focus:outline-none">
                            <i class="ph-fill text-lg" :class="show ? 'ph-eye-slash' : 'ph-eye'"></i>
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-4">
                    <button type="submit" class="w-full bg-[#009B77] hover:bg-[#008264] text-white font-medium py-3.5 px-4 rounded-xl shadow-lg shadow-[#009B77]/30 hover:shadow-[#009B77]/50 transition-all duration-300 flex items-center justify-center gap-2 text-sm uppercase tracking-widest transform hover:-translate-y-0.5">
                        <span>{{ __('Save Password') }}</span>
                        <i></i>
                    </button>
                </div>
            </form>

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
