<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'PFE Monitoring Control System')</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}?v=3" type="image/svg+xml">
    
    <!-- PWA Desktop Configuration -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#009B77">
    <script>
        // Immediate capture of beforeinstallprompt at head parsing time
        window.__pfeDeferredPrompt = null;
        window.addEventListener('beforeinstallprompt', (e) => {
            const isMobileUA = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
            const isTouchOnly = window.matchMedia('(pointer: coarse)').matches && !window.matchMedia('(pointer: fine)').matches;
            if (isMobileUA || isTouchOnly) return;

            e.preventDefault();
            window.__pfeDeferredPrompt = e;
            window.dispatchEvent(new CustomEvent('pfe:deferred-prompt-ready'));
            console.log('[PFE PWA] Native beforeinstallprompt captured!');
        });

        // Register Service Worker immediately in head
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js').catch(() => {});
        }
    </script>
    
    <!-- Tailwind CSS (via Vite or CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Keep JS Vite for Alpine and others if needed -->
    @vite(['resources/js/app.js'])

    
    <!-- Alpine Plugins -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <!-- Alpine Core -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Phosphor Icons -->
    <script src="https://cdn.jsdelivr.net/npm/@phosphor-icons/web"></script>
    
    <!-- Flatpickr (Datepicker) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmDelete(event, message) {
            event.preventDefault();
            const form = event.target;
            Swal.fire({
                title: '{{ __('Are you sure?') }}',
                text: message || "{{ __('This data will be permanently deleted!') }}",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: '{{ __('Yes, Delete!') }}',
                cancelButtonText: '{{ __('Cancel') }}',
                customClass: {
                    popup: 'rounded-3xl shadow-[0_10px_40px_rgba(0,0,0,0.1)] border border-slate-100',
                    title: 'text-slate-800 font-bold',
                    htmlContainer: 'text-slate-500 font-semibold',
                    confirmButton: 'rounded-xl font-bold px-6 py-2.5 transition-transform hover:-translate-y-0.5',
                    cancelButton: 'rounded-xl font-bold px-6 py-2.5 transition-transform hover:-translate-y-0.5'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }
    </script>

    <style>
        .flatpickr-wrapper {
            display: block !important;
            width: 100% !important;
        }
        .flatpickr-day.selected, .flatpickr-day.startRange, .flatpickr-day.endRange, .flatpickr-day.selected.inRange, .flatpickr-day.startRange.inRange, .flatpickr-day.endRange.inRange, .flatpickr-day.selected:focus, .flatpickr-day.startRange:focus, .flatpickr-day.endRange:focus, .flatpickr-day.selected:hover, .flatpickr-day.startRange:hover, .flatpickr-day.endRange:hover, .flatpickr-day.selected.prevMonthDay, .flatpickr-day.startRange.prevMonthDay, .flatpickr-day.endRange.prevMonthDay, .flatpickr-day.selected.nextMonthDay, .flatpickr-day.startRange.nextMonthDay, .flatpickr-day.endRange.nextMonthDay {
            background: #009B77 !important;
            border-color: #009B77 !important;
        }

        @font-face {
            font-family: 'Rotis Sans Serif';
            src: local('Rotis Sans Serif'), local('Arial');
        }
        
        body {
            font-family: 'Rotis Sans Serif', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        
        [x-cloak] { display: none !important; }
        
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
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
        
        .btn-smooth-ring {
            position: relative;
        }
        .btn-smooth-ring::after {
            content: '';
            position: absolute;
            top: -4px; right: -4px; bottom: -4px; left: -4px;
            border: 2px solid #009B77;
            border-radius: 16px;
            opacity: 1;
            transform: scale(1);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            pointer-events: none;
        }
        .btn-smooth-ring:active {
            transform: scale(0.95);
        }
    </style>
    <script>
        window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                window.location.reload();
            }
        });
    </script>
</head>
<body class="bg-[#F8FAFC] text-slate-800 antialiased min-h-screen flex" x-data="{ sidebarOpen: false }">
    <!-- Global Toast Notification -->
    @if(session('success') || session('error') || $errors->any())
        <div x-data="{ show: false, type: '{{ session('success') ? 'success' : 'error' }}', message: '{{ addslashes(session('success') ?? session('error') ?? $errors->first()) }}' }" 
             x-init="setTimeout(() => show = true, 100); setTimeout(() => show = false, 4000)"
             x-show="show"
             style="display: none;"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 -translate-y-8 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 -translate-y-8 scale-95"
             class="fixed top-6 right-6 z-[9999] flex items-center gap-3 px-5 py-4 rounded-2xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.2)] border min-w-[300px] max-w-sm backdrop-blur-md"
             :class="type === 'success' ? 'bg-white/95 border-teal-100' : 'bg-white/95 border-red-100'">
             
            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                 :class="type === 'success' ? 'bg-teal-50 text-[#009B77]' : 'bg-red-50 text-red-500'">
                <i class="ph-fill text-xl" :class="type === 'success' ? 'ph-check-circle' : 'ph-warning-circle'"></i>
            </div>
            
            <div class="flex flex-col">
                <span class="text-sm font-bold text-slate-800" x-text="type === 'success' ? '{{ __('Success!') }}' : '{{ __('Oops, An Error Occurred!') }}'"></span>
                <span class="text-xs font-medium text-slate-500 mt-0.5" x-text="message"></span>
            </div>
            
            <button @click="show = false" class="ml-auto w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                <i class="ph-bold ph-x text-lg"></i>
            </button>
        </div>
    @endif
    
    <!-- Sidebar -->
    <aside @mouseenter="sidebarOpen = true" @mouseleave="sidebarOpen = false" 
           :class="sidebarOpen ? 'w-[260px] shadow-2xl' : 'w-[80px] shadow-[4px_0_24px_rgba(0,0,0,0.01)]'" 
           class="h-screen absolute left-0 top-0 bg-white flex-col hidden md:flex border-r border-slate-200/60 z-[70] transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] will-change-[width]">
        
        <!-- Logo Area -->
        <div class="h-[72px] flex items-center border-b border-slate-100 transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] w-full overflow-hidden" :class="sidebarOpen ? 'px-5 justify-start' : 'px-0 justify-center'">
            <div class="flex items-center gap-3 overflow-hidden">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#009B77] to-[#007A5E] flex items-center justify-center text-white shadow-md shadow-[#009B77]/25 flex-shrink-0">
                    <i class="ph-bold ph-shield-check text-xl"></i>
                </div>
                <div class="flex flex-col overflow-hidden transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)]" :class="sidebarOpen ? 'opacity-100 w-36' : 'opacity-0 w-0'">
                    <span class="text-base font-black tracking-wider text-slate-800 leading-none">EHSS</span>
                    <span class="text-[9px] font-bold text-[#009B77] tracking-wider uppercase mt-1 leading-none">Safety & Security</span>
                </div>
            </div>
        </div>
        
        <!-- User Profile (Moved to Navbar) -->

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto flex flex-col transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)]">
            
            <!-- Group 1: Dashboard & Data Admin -->
            <div class="p-4 flex flex-col gap-1.5">
                <a href="/dashboard" class="justify-center flex items-center p-3 overflow-hidden rounded-xl font-semibold transition-all {{ request()->is('dashboard') ? 'bg-[#009B77] text-white shadow-[0_4px_12px_rgba(0,155,119,0.25)] hover:bg-[#008264]' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800' }}" :class="sidebarOpen ? 'justify-start' : 'justify-center'" title="Dashboard">
                    <i class="ph-fill ph-squares-four text-xl flex-shrink-0"></i>
                    <span class="opacity-0 w-0 ml-0 text-sm whitespace-nowrap transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] overflow-hidden" :class="sidebarOpen ? 'opacity-100 w-32 ml-3' : 'opacity-0 w-0 ml-0'">Dashboard</span>
                </a>

                @if(auth()->user()->role !== 'Staff')
                <a href="/users" class="justify-center flex items-center p-3 overflow-hidden rounded-xl font-semibold transition-all {{ request()->is('users') ? 'bg-[#009B77] text-white shadow-[0_4px_12px_rgba(0,155,119,0.25)] hover:bg-[#008264]' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800' }}" :class="sidebarOpen ? 'justify-start' : 'justify-center'" title="{{ __('User Management') }}">
                    <i class="ph-duotone ph-users text-xl flex-shrink-0"></i>
                    <span class="opacity-0 w-0 ml-0 text-sm whitespace-nowrap transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] overflow-hidden" :class="sidebarOpen ? 'opacity-100 w-32 ml-3' : 'opacity-0 w-0 ml-0'">{{ __('User Management') }}</span>
                </a>
                <a href="/guest-display" class="justify-center flex items-center p-3 overflow-hidden rounded-xl font-semibold transition-all {{ request()->is('guest-display*') ? 'bg-[#009B77] text-white shadow-[0_4px_12px_rgba(0,155,119,0.25)] hover:bg-[#008264]' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800' }}" :class="sidebarOpen ? 'justify-start' : 'justify-center'" title="Akses Guest Display">
                    <i class="ph-duotone ph-broadcast text-xl flex-shrink-0"></i>
                    <span class="opacity-0 w-0 ml-0 text-sm whitespace-nowrap transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] overflow-hidden" :class="sidebarOpen ? 'opacity-100 w-32 ml-3' : 'opacity-0 w-0 ml-0'">Akses Display</span>
                </a>
                @endif
                
                <a href="/master-data" class="justify-center flex items-center p-3 overflow-hidden rounded-xl font-semibold transition-all {{ request()->is('master-data') ? 'bg-[#009B77] text-white shadow-[0_4px_12px_rgba(0,155,119,0.25)] hover:bg-[#008264]' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800' }}" :class="sidebarOpen ? 'justify-start' : 'justify-center'" title="{{ __('PFE Master Data') }}">
                    <i class="ph-duotone ph-list-dashes text-xl flex-shrink-0"></i>
                    <span class="opacity-0 w-0 ml-0 text-sm whitespace-nowrap transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] overflow-hidden" :class="sidebarOpen ? 'opacity-100 w-32 ml-3' : 'opacity-0 w-0 ml-0'">{{ __('PFE Master Data') }}</span>
                </a>
            </div>

            <!-- Group 3: Inspections & Reports -->
            <div class="p-4 border-t border-slate-200 flex flex-col gap-1.5">
                <a href="/inspection-schedule" class="justify-center flex items-center p-3 overflow-hidden rounded-xl font-semibold transition-all {{ request()->is('inspection-schedule') ? 'bg-[#009B77] text-white shadow-[0_4px_12px_rgba(0,155,119,0.25)] hover:bg-[#008264]' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800' }}" :class="sidebarOpen ? 'justify-start' : 'justify-center'" title="Inspection Schedule">
                    <i class="ph-duotone ph-calendar-blank text-xl flex-shrink-0"></i>
                    <span class="opacity-0 w-0 ml-0 text-sm whitespace-nowrap transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] overflow-hidden" :class="sidebarOpen ? 'opacity-100 w-32 ml-3' : 'opacity-0 w-0 ml-0'">Inspection Schedule</span>
                </a>

                @if(auth()->user()->role !== 'Staff')
                <a href="/reports" class="justify-center flex items-center p-3 overflow-hidden rounded-xl font-semibold transition-all {{ request()->is('reports') ? 'bg-[#009B77] text-white shadow-[0_4px_12px_rgba(0,155,119,0.25)] hover:bg-[#008264]' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800' }}" :class="sidebarOpen ? 'justify-start' : 'justify-center'" title="Reports">
                    <i class="ph-duotone ph-file-text text-xl flex-shrink-0"></i>
                    <span class="opacity-0 w-0 ml-0 text-sm whitespace-nowrap transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] overflow-hidden" :class="sidebarOpen ? 'opacity-100 w-32 ml-3' : 'opacity-0 w-0 ml-0'">Inspection Reports</span>
                </a>
                @endif
            </div>
            
            <!-- Group 4: Audit Trail -->
            @if(auth()->user()->role !== 'Staff')
            <div class="p-4 border-t border-slate-200 flex flex-col gap-1.5">
                <a href="/activity-log" class="justify-center flex items-center p-3 overflow-hidden rounded-xl font-semibold transition-all {{ request()->is('activity-log') ? 'bg-[#009B77] text-white shadow-[0_4px_12px_rgba(0,155,119,0.25)] hover:bg-[#008264]' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800' }}" :class="sidebarOpen ? 'justify-start' : 'justify-center'" title="Audit Trail">
                    <i class="ph-bold ph-clock-counter-clockwise text-xl flex-shrink-0"></i>
                    <span class="opacity-0 w-0 ml-0 text-sm whitespace-nowrap transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] overflow-hidden" :class="sidebarOpen ? 'opacity-100 w-32 ml-3' : 'opacity-0 w-0 ml-0'">Audit Trail</span>
                </a>
            </div>
            @endif
        </nav>
        
        <!-- Bottom Actions (Settings & Logout) -->
        <div class="mt-auto p-4 border-t border-slate-250 flex flex-col gap-1.5 transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)]">
            <!-- Settings Gear -->
            <div class="relative" x-data="{ showSettings: false }" @click.outside="showSettings = false">
                <button @click="showSettings = !showSettings" class="w-full justify-center flex items-center p-3 overflow-hidden rounded-xl font-semibold text-slate-500 hover:bg-slate-50 hover:text-[#009B77] transition-all" :class="sidebarOpen ? 'justify-start' : 'justify-center'" title="{{ __('Settings') }}">
                    <i class="ph-bold ph-gear text-xl flex-shrink-0"></i>
                    <span class="opacity-0 w-0 ml-0 text-sm whitespace-nowrap transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] overflow-hidden" :class="sidebarOpen ? 'opacity-100 w-32 ml-3 text-left' : 'opacity-0 w-0 ml-0'">{{ __('Settings') }}</span>
                </button>

                <!-- Settings Dropdown -->
                <div x-show="showSettings" 
                     style="display: none;"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 translate-y-2"
                     class="absolute left-full bottom-0 ml-2 w-56 bg-white rounded-xl shadow-lg border border-slate-200/60 overflow-hidden z-50">
                    
                    <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/50">
                        <h3 class="font-bold text-slate-800 text-sm">{{ __('Settings') }}</h3>
                    </div>
                    
                    <ul class="divide-y divide-slate-100">
                        <li x-data="{ langOpen: false }" class="relative">
                            <button @click="langOpen = !langOpen" class="w-full text-left px-4 py-3 hover:bg-slate-50 transition-colors flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-[#8A4B9F]/10 flex items-center justify-center text-[#8A4B9F]">
                                        <i class="ph-fill ph-translate text-lg"></i>
                                    </div>
                                    <div>
                                        <span class="block text-sm font-bold text-slate-800">Bahasa / Language</span>
                                        <span class="block text-xs text-slate-500 uppercase">{{ app()->getLocale() == 'id' ? 'Indonesia' : 'English' }}</span>
                                    </div>
                                </div>
                                <i class="ph-bold ph-caret-right text-xs text-slate-400 transition-transform" :class="langOpen ? 'rotate-90' : ''"></i>
                            </button>
                            
                            <div x-show="langOpen" class="bg-slate-50 px-4 py-2 border-y border-slate-100 flex flex-col gap-1">
                                <a href="{{ route('set-locale', 'id') }}" class="flex items-center justify-between p-2 rounded-lg hover:bg-white transition-colors {{ app()->getLocale() == 'id' ? 'text-[#009B77] font-bold' : 'text-slate-600' }}">
                                    <span class="text-sm">Indonesia</span>
                                    @if(app()->getLocale() == 'id') <i class="ph-bold ph-check"></i> @endif
                                </a>
                                <a href="{{ route('set-locale', 'en') }}" class="flex items-center justify-between p-2 rounded-lg hover:bg-white transition-colors {{ app()->getLocale() == 'en' ? 'text-[#009B77] font-bold' : 'text-slate-600' }}">
                                    <span class="text-sm">English</span>
                                    @if(app()->getLocale() == 'en') <i class="ph-bold ph-check"></i> @endif
                                </a>
                            </div>
                        </li>
                        <li>
                            <button @click="$dispatch('open-profile-modal'); showSettings = false" class="w-full text-left px-4 py-3 hover:bg-slate-50 transition-colors flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-[#009B77]/10 flex items-center justify-center text-[#009B77]">
                                    <i class="ph-fill ph-user-circle text-lg"></i>
                                </div>
                                <div>
                                    <span class="block text-sm font-bold text-slate-800">{{ __('My Profile') }}</span>
                                    <span class="block text-xs text-slate-500">{{ __('View your profile') }}</span>
                                </div>
                            </button>
                        </li>
                        <li>
                            <button @click="$dispatch('open-change-password'); showSettings = false" class="w-full text-left px-4 py-3 hover:bg-slate-50 transition-colors flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-amber-50 flex items-center justify-center text-amber-600">
                                    <i class="ph-fill ph-key text-lg"></i>
                                </div>
                                <div>
                                    <span class="block text-sm font-bold text-slate-800">{{ __('Change Password') }}</span>
                                    <span class="block text-xs text-slate-500">{{ __('Update password') }}</span>
                                </div>
                            </button>
                        </li>
                        <li>
                            <button @click="$dispatch('open-change-pin'); showSettings = false" class="w-full text-left px-4 py-3 hover:bg-slate-50 transition-colors flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-purple-50 flex items-center justify-center text-purple-600">
                                    <i class="ph-fill ph-password text-lg"></i>
                                </div>
                                <div>
                                    <span class="block text-sm font-bold text-slate-800">{{ __('Change PIN') }}</span>
                                    <span class="block text-xs text-slate-500">{{ __('Change PIN') }} 4-digit</span>
                                </div>
                            </button>
                        </li>
                    </ul>
                </div>
            </div>

            <button type="button" @click="$dispatch('open-logout-modal')" class="w-full justify-center flex items-center p-3 overflow-hidden rounded-xl font-semibold text-slate-500 hover:bg-red-50 hover:text-red-500 transition-all" :class="sidebarOpen ? 'justify-start' : 'justify-center'" title="{{ __('Logout') }}">
                <i class="ph-bold ph-sign-out text-xl flex-shrink-0"></i>
                <span class="opacity-0 w-0 ml-0 text-sm whitespace-nowrap transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] overflow-hidden" :class="sidebarOpen ? 'opacity-100 w-32 ml-3 text-left' : 'opacity-0 w-0 ml-0'">{{ __('Logout') }}</span>
            </button>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden relative print:h-auto print:overflow-visible md:ml-[80px]">
        
        <!-- Top Navbar -->
        <header class="h-[72px] bg-white border-b border-slate-200/60 flex items-center justify-between px-8 z-[60] relative flex-shrink-0 shadow-[0_2px_10px_rgba(0,0,0,0.01)]">
            <!-- Left Title -->
            <div class="flex-1 flex items-center gap-3">
                <div class="flex items-center justify-center w-9 h-9 rounded-xl bg-gradient-to-br from-[#009B77] to-[#007A5E] shadow-sm shadow-[#009B77]/30 text-white">
                    <i class="ph-fill ph-fire-extinguisher text-lg"></i>
                </div>
                <div class="flex flex-col justify-center">
                    <h1 class="text-[15px] font-extrabold text-slate-800 tracking-tight leading-none uppercase">
                        {{ __('PFE') }} <span class="text-[#009B77]">MONITORING</span>
                    </h1>
                    <span class="text-[10px] font-bold text-slate-400 tracking-[0.2em] uppercase mt-0.5">Control System</span>
                </div>
            </div>

            <!-- Center Search Area (Dashboard only) -->
            @if(request()->is('dashboard'))
            <div class="flex-1 flex justify-center">
                <div class="relative w-full max-w-md hidden sm:block">
                    <i class="ph-bold ph-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-[#009B77] text-lg"></i>
                    <input type="text" placeholder="{{ __('Search PFE ID, location, or status...') }}" 
                           class="w-full bg-white border-2 border-[#009B77] rounded-xl py-2.5 pl-11 pr-4 text-sm text-slate-700 focus:outline-none focus:ring-4 focus:ring-[#009B77]/20 transition-all placeholder:text-slate-400 font-medium">
                </div>
            </div>
            @else
            <div class="flex-1"></div>
            @endif

            <!-- Right Actions -->
            <div class="flex-1 flex items-center justify-end gap-3">
                <!-- Desktop Install PWA Button (Desktop Only) -->
                <button type="button" 
                        x-data="{ showBtn: false }"
                        x-init="
                            const isMobileUA = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
                            const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
                            if (!isMobileUA && !isStandalone && localStorage.getItem('pfe_pwa_installed') !== 'true') {
                                showBtn = true;
                            }
                        "
                        x-show="showBtn"
                        @click="window.dispatchEvent(new CustomEvent('pfe:trigger-install'))"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#009B77]/10 hover:bg-[#009B77] text-[#009B77] hover:text-white font-bold text-xs transition-all border border-[#009B77]/20 cursor-pointer shadow-sm active:scale-95"
                        title="Install PFE Monitoring Control System ke Desktop">
                    <i class="ph-bold ph-download-simple text-sm"></i>
                    <span>Install App</span>
                </button>

                @php
                    $notifSignature = md5($importantNotifications->pluck('id')->join(','));
                @endphp
                <!-- Notification Bell -->
                 <!-- Notifications -->
                <div class="relative" x-data="{ 
                    showNotif: false, 
                    hasUnread: false,
                    initialReadIds: JSON.parse(localStorage.getItem('read_notif_ids') || '[]').map(String),
                    currentIds: {{ json_encode($importantNotifications->pluck('id')) }}.map(String),
                    signature: '{{ md5($importantNotifications->pluck('id')->sort()->join(',')) }}',
                    init() {
                        if (localStorage.getItem('notif_signature') !== this.signature) {
                            this.hasUnread = true;
                        }
                    },
                    openNotif() {
                        this.showNotif = !this.showNotif;
                        if (this.showNotif) {
                            this.hasUnread = false;
                            localStorage.setItem('notif_signature', this.signature);
                            // Simpan semua ID saat ini sebagai terbaca untuk reload berikutnya
                            let allRead = [...new Set([...this.initialReadIds, ...this.currentIds])];
                            localStorage.setItem('read_notif_ids', JSON.stringify(allRead));
                        }
                    }
                }" x-init="init()" @click.outside="showNotif = false">
                    
                    <button @click="openNotif()" class="w-10 h-10 rounded-full flex items-center justify-center text-slate-500 hover:bg-slate-100 hover:text-[#009B77] transition-colors relative">
                        <i class="ph-bold ph-bell text-xl"></i>
                        <span x-show="hasUnread" style="display: none;" class="absolute top-2.5 right-2.5 w-2 h-2 bg-red-500 rounded-full border-2 border-white"></span>
                    </button>

                    <!-- Dropdown -->
                    <div x-show="showNotif" 
                         style="display: none;"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 translate-y-2"
                         class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-lg border border-slate-200/60 overflow-hidden z-50">
                        
                        <div style="max-height: 400px; overflow-y: auto;">
                            @if($importantNotifications->isEmpty())
                                <div class="px-4 py-6 text-center text-sm text-slate-500">
                                    <i class="ph-duotone ph-check-circle text-3xl text-emerald-500 mb-2"></i>
                                    <p>{{ __('No important notifications.') }}</p>
                                </div>
                            @else
                                <ul class="divide-y divide-slate-100">
                                    @foreach($importantNotifications as $notifApar)
                                        @php
                                            $diffDays = \Carbon\Carbon::now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($notifApar->tgl_kedaluwarsa)->startOfDay(), false);
                                            $isExpired = $diffDays < 0;
                                            $statusText = $isExpired ? __('Expired!') : ($diffDays == 0 ? __('Expiring Today!') : __('Expires in') . ' ' . $diffDays . ' ' . __('Days'));
                                            $statusColor = $isExpired ? 'text-red-600 bg-red-50' : 'text-amber-600 bg-amber-50';
                                            $iconColor = $isExpired ? 'text-red-500' : 'text-amber-500';
                                        @endphp
                                        <li :class="initialReadIds.includes('{{ $notifApar->id }}') ? 'bg-white hover:bg-slate-50' : 'bg-blue-50/50 hover:bg-blue-50'">
                                            <a href="/master-data" style="display: block; padding: 12px 16px;" class="transition-colors relative">
                                                <div style="display: flex; align-items: flex-start; gap: 12px;">
                                                    <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 {{ $statusColor }}">
                                                        <i class="ph-fill ph-warning-circle text-lg {{ $iconColor }}"></i>
                                                    </div>
                                                    <div style="flex: 1; min-width: 0;">
                                                        <div class="flex items-center justify-between mb-0.5">
                                                            <p class="text-sm font-bold text-slate-800 truncate">APAR {{ $notifApar->kode }}</p>
                                                        </div>
                                                        <p class="text-xs text-slate-500 truncate">{{ $notifApar->lokasi->gedung->nama ?? 'n/a' }} - {{ $notifApar->lokasi->nama ?? 'n/a' }}</p>
                                                        <p class="text-xs font-semibold text-red-600" style="margin-top: 4px;">{{ $statusText }}</p>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="w-px h-6 bg-slate-200 mx-1"></div>
                
                <!-- User Profile Area -->
                <div @click="$dispatch('open-profile-modal')" class="flex items-center gap-3 cursor-pointer hover:bg-slate-50 p-1.5 rounded-xl transition-colors">
                    <div class="text-right hidden sm:block">
                        <h3 class="font-bold text-sm text-[#009B77]">{{ auth()->user()->name }}</h3>
                        <p class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider">{{ auth()->user()->role }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center flex-shrink-0">
                        @php
                            $nameParts = explode(' ', auth()->user()->name);
                            $initials = isset($nameParts[1]) ? substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1) : substr($nameParts[0], 0, 2);
                        @endphp
                        <span class="text-sm font-bold text-[#009B77] uppercase">{{ $initials }}</span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <div class="p-4 overflow-y-auto flex-1 print:overflow-visible print:h-auto print:p-0">
            <div class="w-full max-w-[1400px] mx-auto">
                @yield('content')
            </div>
        </div>
    </main>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if(document.querySelector('.datepicker')) {
                flatpickr(".datepicker", {
                    dateFormat: "Y-m-d",
                    allowInput: true,
                    static: true
                });
            }
        });
    </script>

    <!-- Change Password Modal -->
    <div x-data="{ open: false }" @open-change-password.window="open = true" x-show="open" class="fixed inset-0 z-[100] flex items-center justify-center overflow-hidden bg-slate-900/40 p-4" style="display: none;" x-cloak>
        <div x-show="open" class="bg-white rounded-2xl shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto flex flex-col custom-scrollbar border border-slate-100">
            <!-- Modal Header -->
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between sticky top-0 bg-white z-10">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                        <i class="ph-bold ph-key text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800 text-lg">{{ __('Change Password') }}</h3>
                        <p class="text-xs font-semibold text-slate-500">{{ __('Update your account password') }}</p>
                    </div>
                </div>
                <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600 hover:bg-slate-100 p-2 rounded-lg transition-colors">
                    <i class="ph-bold ph-x text-lg"></i>
                </button>
            </div>
            
            <form action="{{ route('profile.change-password') }}" method="POST">
                @csrf
                <div class="p-6 space-y-5">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('Current Password') }}</label>
                        <div class="relative" x-data="{ show: false }">
                            <input :type="show ? 'text' : 'password'" name="current_password" required class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 pl-4 pr-12 text-sm font-medium text-slate-700 focus:bg-white focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none">
                            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-[#009B77] transition-colors focus:outline-none">
                                <i class="ph-fill text-lg" :class="show ? 'ph-eye-slash' : 'ph-eye'"></i>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('New Password (Min. 8)') }}</label>
                        <div class="relative" x-data="{ show: false }">
                            <input :type="show ? 'text' : 'password'" name="new_password" required minlength="8" class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 pl-4 pr-12 text-sm font-medium text-slate-700 focus:bg-white focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none">
                            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-[#009B77] transition-colors focus:outline-none">
                                <i class="ph-fill text-lg" :class="show ? 'ph-eye-slash' : 'ph-eye'"></i>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('Confirm New Password') }}</label>
                        <div class="relative" x-data="{ show: false }">
                            <input :type="show ? 'text' : 'password'" name="new_password_confirmation" required minlength="8" class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 pl-4 pr-12 text-sm font-medium text-slate-700 focus:bg-white focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none">
                            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-[#009B77] transition-colors focus:outline-none">
                                <i class="ph-fill text-lg" :class="show ? 'ph-eye-slash' : 'ph-eye'"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50 flex items-center justify-end rounded-b-2xl">
                    <button type="submit" class="bg-[#009B77] hover:bg-[#008264] text-white font-bold py-2.5 px-6 rounded-xl shadow-lg shadow-teal-500/30 transition-all text-sm">{{ __('Save New Password') }}</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Change PIN Modal -->
    <div x-data="{ open: false }" @open-change-pin.window="open = true" x-show="open" class="fixed inset-0 z-[100] flex items-center justify-center overflow-hidden bg-slate-900/40 p-4" style="display: none;" x-cloak>
        <div x-show="open" class="bg-white rounded-2xl shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto flex flex-col custom-scrollbar border border-slate-100">
            <!-- Modal Header -->
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between sticky top-0 bg-white z-10">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                        <i class="ph-bold ph-password text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800 text-lg">{{ __('Change PIN') }}</h3>
                        <p class="text-xs font-semibold text-slate-500">{{ __('Update your 4-digit PIN') }}</p>
                    </div>
                </div>
                <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600 hover:bg-slate-100 p-2 rounded-lg transition-colors">
                    <i class="ph-bold ph-x text-lg"></i>
                </button>
            </div>
            
            <form action="{{ route('profile.change-pin') }}" method="POST">
                @csrf
                <div class="p-6 space-y-5">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('Current PIN') }}</label>
                        <div class="relative" x-data="{ show: false }">
                            <input :type="show ? 'text' : 'password'" name="current_pin" inputmode="numeric" pattern="[0-9]{4}" maxlength="4" required oninput="this.value = this.value.replace(/[^0-9]/g, '')" class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 px-4 text-center text-lg font-mono tracking-widest text-slate-700 focus:bg-white focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none" placeholder="••••">
                            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-[#009B77] transition-colors focus:outline-none">
                                <i class="ph-fill text-lg" :class="show ? 'ph-eye-slash' : 'ph-eye'"></i>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('New PIN (4 Digits)') }}</label>
                        <div class="relative" x-data="{ show: false }">
                            <input :type="show ? 'text' : 'password'" name="new_pin" inputmode="numeric" pattern="[0-9]{4}" maxlength="4" required oninput="this.value = this.value.replace(/[^0-9]/g, '')" class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 px-4 text-center text-lg font-mono tracking-widest text-slate-700 focus:bg-white focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none" placeholder="••••">
                            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-[#009B77] transition-colors focus:outline-none">
                                <i class="ph-fill text-lg" :class="show ? 'ph-eye-slash' : 'ph-eye'"></i>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('Confirm New PIN') }}</label>
                        <div class="relative" x-data="{ show: false }">
                            <input :type="show ? 'text' : 'password'" name="new_pin_confirmation" inputmode="numeric" pattern="[0-9]{4}" maxlength="4" required oninput="this.value = this.value.replace(/[^0-9]/g, '')" class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 px-4 text-center text-lg font-mono tracking-widest text-slate-700 focus:bg-white focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none" placeholder="••••">
                            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-[#009B77] transition-colors focus:outline-none">
                                <i class="ph-fill text-lg" :class="show ? 'ph-eye-slash' : 'ph-eye'"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50 flex items-center justify-end rounded-b-2xl">
                    <button type="submit" class="bg-[#009B77] hover:bg-[#008264] text-white font-bold py-2.5 px-6 rounded-xl shadow-lg shadow-teal-500/30 transition-all text-sm">{{ __('Save New PIN') }}</button>
                </div>
            </form>
        </div>
    </div>
        <!-- Profile Modal -->
        <div x-data="{ show: false }"
             @open-profile-modal.window="show = true"
             x-show="show"
             style="display: none;"
             class="fixed inset-0 z-[100] flex items-center justify-center overflow-hidden bg-slate-900/40 p-4">
            
            <div x-show="show" 
                 class="bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto flex flex-col custom-scrollbar">
                
                <!-- Modal Header -->
                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between sticky top-0 bg-white z-10">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-[#009B77]/10 flex items-center justify-center text-[#009B77]">
                            <i class="ph-fill ph-user-circle text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-800 text-lg">{{ __('My Profile') }}</h3>
                            <p class="text-xs font-semibold text-slate-500">{{ __('Account and PFE PIC information') }}</p>
                        </div>
                    </div>
                    <button @click="show = false" class="text-slate-400 hover:text-slate-600 hover:bg-slate-100 p-2 rounded-lg transition-colors">
                        <i class="ph-bold ph-x text-lg"></i>
                    </button>
                </div>
                
                <!-- Modal Body -->
                <div class="p-6 space-y-6">
                    <!-- Info Section -->
                    <div class="flex items-center gap-5 p-5 bg-slate-50 rounded-2xl border border-slate-100">
                        <div class="w-16 h-16 rounded-full bg-white border-2 border-slate-200 flex items-center justify-center shadow-sm flex-shrink-0">
                            @php
                                $nameParts = explode(' ', auth()->user()->name);
                                $initials = isset($nameParts[1]) ? substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1) : substr($nameParts[0], 0, 2);
                            @endphp
                            <span class="text-xl font-bold text-[#009B77] uppercase">{{ $initials }}</span>
                        </div>
                        <div class="overflow-hidden">
                            <h4 class="font-bold text-slate-800 text-lg mb-1 truncate">{{ auth()->user()->name }}</h4>
                            <div class="flex items-center gap-3 text-sm text-slate-500 font-medium truncate">
                                <span class="flex items-center gap-1.5 truncate"><i class="ph-bold ph-envelope-simple text-slate-400"></i> {{ auth()->user()->email }}</span>
                            </div>
                            <div class="mt-2 inline-flex px-2.5 py-1 rounded-md text-[11px] font-bold bg-[#009B77]/10 text-[#009B77] uppercase tracking-wider">
                                {{ auth()->user()->role }}
                            </div>
                        </div>
                    </div>

                    <!-- PIC Info -->
                    <div>
                        <h5 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                            <i class="ph-bold ph-buildings"></i> {{ __('Main Building Responsibility') }}
                        </h5>
                        @php
                            $user = auth()->user();
                        @endphp
                        
                        @if($user->role === 'EHSS')
                            <div class="p-4 bg-[#009B77]/10 border border-[#009B77]/20 rounded-xl flex items-center gap-4">
                                <div class="w-10 h-10 rounded-lg bg-white text-[#009B77] flex items-center justify-center shadow-sm flex-shrink-0">
                                    <i class="ph-fill ph-buildings text-xl"></i>
                                </div>
                                <div>
                                    <h6 class="font-bold text-slate-800">{{ __('All Buildings (Admin)') }}</h6>
                                    <p class="text-xs font-medium text-slate-500 mt-0.5">{{ __('Has full monitoring access to all areas.') }}</p>
                                </div>
                            </div>
                        @else
                            @if($user->gedungs->isEmpty())
                                <div class="p-4 bg-slate-50 border border-slate-100 border-dashed rounded-xl text-center text-sm font-medium text-slate-500">
                                    {{ __('You have not been assigned as a PIC for any building.') }}
                                </div>
                            @else
                                <div class="space-y-3 mb-4">
                                    @foreach($user->gedungs as $gedung)
                                        <div class="p-3 border border-slate-100 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between hover:bg-slate-50 transition-colors gap-2">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0">
                                                    <i class="ph-fill ph-building"></i>
                                                </div>
                                                <div>
                                                    <p class="text-sm font-bold text-slate-800">{{ $gedung->nama }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                
                                @if($user->jadwal_rutin_tanggal)
                                <div class="p-3 bg-amber-50 border border-amber-100 rounded-xl flex items-start gap-3">
                                    <i class="ph-fill ph-calendar-blank text-amber-500 text-lg mt-0.5"></i>
                                    <div>
                                        <h6 class="text-sm font-bold text-slate-800">{{ __('Inspection Schedule') }} Rutin</h6>
                                        <p class="text-xs font-medium text-slate-600 mt-0.5">{{ __('Every') }} <strong>{{ $user->jadwal_rutin_tanggal }}</strong> {{ __('of the month.') }}</p>
                                    </div>
                                </div>
                                @endif
                            @endif
                        @endif
                    </div>

                    <!-- Display Mode / Kiosk -->
                    <div>
                        <h5 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                            <i class="ph-bold ph-monitor-play"></i> {{ __('Display Mode') }}
                        </h5>
                        <div class="flex flex-col gap-2 mb-6">
                            <a href="{{ url('/display-report') }}" target="_blank" class="w-full text-left px-4 py-3 rounded-xl border border-slate-200 hover:border-indigo-500 hover:bg-indigo-50 transition-all flex items-center justify-between group">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-500 group-hover:bg-white group-hover:text-indigo-500 flex items-center justify-center transition-colors shadow-sm">
                                        <i class="ph-bold ph-presentation-chart text-lg"></i>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-slate-700 group-hover:text-indigo-600 transition-colors">{{ __('Open Display Report') }}</span>
                                        <span class="text-[10px] font-semibold text-slate-500 mt-0.5">Mode Kiosk layar penuh (Kredensial Sesi Aktif)</span>
                                    </div>
                                </div>
                                <i class="ph-bold ph-arrow-square-out text-slate-400 group-hover:text-indigo-500 transition-colors"></i>
                            </a>
                            @if(auth()->user()->role !== 'Staff')
                            <a href="{{ route('display-access.index') }}" class="w-full text-left px-4 py-3 rounded-xl border border-slate-200 hover:border-[#009B77] hover:bg-teal-50 transition-all flex items-center justify-between group">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-500 group-hover:bg-white group-hover:text-[#009B77] flex items-center justify-center transition-colors shadow-sm">
                                        <i class="ph-bold ph-key text-lg"></i>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-slate-700 group-hover:text-[#009B77] transition-colors">Kelola Akses Viewer (PIN & Durasi)</span>
                                        <span class="text-[10px] font-semibold text-slate-500 mt-0.5">Buat kredensial PIN dan atur tanggal aktif untuk viewer</span>
                                    </div>
                                </div>
                                <i class="ph-bold ph-caret-right text-slate-400 group-hover:text-[#009B77] transition-colors"></i>
                            </a>
                            @endif
                        </div>
                    </div>

                    <!-- Actions -->
                    <div>
                        <h5 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                            <i class="ph-bold ph-shield-check"></i> {{ __('Account Security') }}
                        </h5>
                        <div class="flex flex-col gap-2">
                            <button type="button" @click="$dispatch('open-change-password'); show = false" class="w-full text-left px-4 py-3 rounded-xl border border-slate-200 hover:border-[#009B77] hover:bg-[#009B77]/5 transition-all flex items-center justify-between group">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-500 group-hover:bg-white group-hover:text-[#009B77] flex items-center justify-center transition-colors">
                                        <i class="ph-bold ph-key"></i>
                                    </div>
                                    <span class="text-sm font-bold text-slate-700 group-hover:text-[#009B77] transition-colors">{{ __('Change Password') }}</span>
                                </div>
                                <i class="ph-bold ph-caret-right text-slate-400 group-hover:text-[#009B77] transition-colors"></i>
                            </button>
                            <button type="button" @click="$dispatch('open-change-pin'); show = false" class="w-full text-left px-4 py-3 rounded-xl border border-slate-200 hover:border-[#009B77] hover:bg-[#009B77]/5 transition-all flex items-center justify-between group">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-500 group-hover:bg-white group-hover:text-[#009B77] flex items-center justify-center transition-colors">
                                        <i class="ph-bold ph-password"></i>
                                    </div>
                                    <span class="text-sm font-bold text-slate-700 group-hover:text-[#009B77] transition-colors">{{ __('Change PIN') }}</span>
                                </div>
                                <i class="ph-bold ph-caret-right text-slate-400 group-hover:text-[#009B77] transition-colors"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50 flex items-center justify-end rounded-b-2xl">
                    <button type="button" @click="$dispatch('open-logout-modal'); show = false" class="w-full flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl font-bold text-rose-500 hover:text-white border-2 border-rose-500 hover:bg-rose-500 transition-all text-sm">
                        <i class="ph-bold ph-sign-out text-lg"></i>
                        {{ __('Logout from System') }}
                    </button>
                </div>
            </div>
        </div>
        
    <!-- Logout Confirmation Modal -->
    <div x-data="{ open: false }" @open-logout-modal.window="open = true" x-show="open" class="fixed inset-0 z-[100] flex items-center justify-center overflow-hidden bg-slate-900/40 p-4" style="display: none;" x-cloak>
        <div x-show="open" class="bg-white rounded-3xl shadow-xl w-full max-w-sm flex flex-col border border-slate-100 overflow-hidden">
            <div class="p-8 text-center space-y-4">
                <div class="w-16 h-16 rounded-2xl bg-rose-50 text-rose-500 flex items-center justify-center mx-auto mb-4 border border-rose-100">
                    <i class="ph-bold ph-sign-out text-3xl"></i>
                </div>
                <h3 class="font-black text-slate-800 text-xl tracking-tight">{{ __('Logout from System?') }}</h3>
                <p class="text-sm text-slate-500 font-medium">{{ __('Are you sure you want to logout from the PFE Monitoring Control System application?') }}</p>
            </div>
            <div class="px-6 py-5 bg-slate-50 border-t border-slate-100 flex gap-3">
                <button type="button" @click="open = false" class="flex-1 px-4 py-2.5 rounded-xl font-bold text-slate-600 bg-white border-2 border-slate-200 hover:bg-slate-50 transition-colors text-sm">{{ __('Cancel') }}</button>
                <form action="{{ route('logout') }}" method="POST" class="flex-1 flex">
                    @csrf
                    <button type="submit" class="w-full px-4 py-2.5 rounded-xl font-bold text-white bg-rose-500 hover:bg-rose-600 transition-colors shadow-lg shadow-rose-500/30 text-sm">{{ __('Yes, Logout') }}</button>
                </form>
            </div>
        </div>
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
        <!-- Global Lightbox Modal Preview Foto Inspeksi (Admin) -->
    <div id="global-admin-photo-modal" 
         class="fixed inset-0 z-[9999] items-center justify-center p-3 sm:p-6 bg-black/85" 
         style="display: none;"
         onclick="if(event.target === this) closeGlobalPhotoModal();">
         
        <div class="relative max-w-4xl w-full bg-slate-900 rounded-2xl overflow-hidden shadow-2xl border border-white/10 flex flex-col max-h-[92vh]" onclick="event.stopPropagation();">
            <!-- Header Modal -->
            <div class="flex items-center justify-between px-4 py-3 bg-slate-950/90 border-b border-white/10 text-white z-10">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-[#009B77]/20 border border-[#009B77]/40 flex items-center justify-center text-[#009B77]">
                        <i class="ph-fill ph-fire-extinguisher text-base"></i>
                    </div>
                    <div>
                        <h3 class="text-xs sm:text-sm font-bold leading-tight flex items-center gap-1.5">
                            <span id="global-photo-apar-kode"></span>
                            <span class="text-slate-400">•</span>
                            <span>Foto Bukti Fisik Inspeksi Realtime</span>
                        </h3>
                        <p class="text-[10px] text-slate-400 leading-none mt-0.5" id="global-photo-apar-lokasi"></p>
                    </div>
                </div>
                <button type="button" onclick="closeGlobalPhotoModal()" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/25 flex items-center justify-center text-white transition-colors cursor-pointer">
                    <i class="ph-bold ph-x text-sm"></i>
                </button>
            </div>

            <!-- Body Gambar Resolusi Penuh -->
            <div class="flex-1 overflow-auto bg-black flex items-center justify-center p-2 sm:p-4">
                <img id="global-photo-img" src="" alt="Inspection Photo" class="max-w-full max-h-[75vh] object-contain rounded-lg shadow-lg">
            </div>

            <!-- Footer Info Modal -->
            <div class="px-4 py-2.5 bg-slate-950/90 border-t border-white/10 flex items-center justify-between text-[11px] text-slate-300">
                <div class="flex items-center gap-3">
                    <span class="flex items-center gap-1.5">
                        <i class="ph-fill ph-user text-slate-400"></i>
                        <span class="text-slate-400">PIC:</span>
                        <strong id="global-photo-pic"></strong>
                    </span>
                    <span class="hidden sm:inline-flex items-center gap-1.5 text-slate-400">
                        <i class="ph-fill ph-clock"></i>
                        <span id="global-photo-waktu"></span>
                    </span>
                </div>
                <button type="button" onclick="closeGlobalPhotoModal()" class="px-4 py-1.5 rounded-lg bg-[#009B77] hover:bg-[#008264] text-white font-bold text-xs transition-colors cursor-pointer">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <script>
        function openGlobalPhotoModal(data) {
            const modal = document.getElementById('global-admin-photo-modal');
            if (!modal) return;
            
            let photoSrc = data.foto || '';
            if (photoSrc && !photoSrc.startsWith('http') && !photoSrc.startsWith('/storage/')) {
                photoSrc = '/storage/' + photoSrc;
            }
            
            document.getElementById('global-photo-img').src = photoSrc;
            document.getElementById('global-photo-apar-kode').textContent = data.apar_kode || 'APAR';
            document.getElementById('global-photo-apar-lokasi').textContent = data.lokasi || '';
            document.getElementById('global-photo-pic').textContent = data.petugas || '-';
            document.getElementById('global-photo-waktu').textContent = data.waktu || '';
            
            modal.style.display = 'flex';
        }

        function closeGlobalPhotoModal() {
            const modal = document.getElementById('global-admin-photo-modal');
            if (modal) modal.style.display = 'none';
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeGlobalPhotoModal();
        });
    </script>
    @stack('scripts')

    <!-- PWA Desktop Install Banner (Only rendered for authenticated users on Desktop) -->
    <x-desktop-install-banner />
</body>
</html>

