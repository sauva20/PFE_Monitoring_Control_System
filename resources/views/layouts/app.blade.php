<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'PFE Monitoring Control System')</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}?v=3" type="image/svg+xml">
    
    <!-- PWA Configuration -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#009B77">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="PFE System">
    <link rel="apple-touch-icon" href="/images/icons/icon-192.png">
    <script>
        // Check if running inside installed app window or marked installed
        window.__isPFEInstalled = function() {
            const isStandalone = window.matchMedia('(display-mode: standalone)').matches;
            const isWco = window.matchMedia('(display-mode: window-controls-overlay)').matches;
            const isMinimal = window.matchMedia('(display-mode: minimal-ui)').matches;
            const isFullscreen = window.matchMedia('(display-mode: fullscreen)').matches;
            const isNotBrowser = window.matchMedia('(display-mode: browser)').matches === false;
            const isNavStandalone = window.navigator.standalone === true;

            if (isStandalone || isWco || isMinimal || isFullscreen || isNotBrowser || isNavStandalone) {
                return true;
            }
            if (localStorage.getItem('pfe_pwa_installed') === 'true') {
                return true;
            }
            return false;
        };

        // Immediate capture of beforeinstallprompt at head parsing time
        window.__pfeDeferredPrompt = null;
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            window.__pfeDeferredPrompt = e;
            // If beforeinstallprompt is fired by browser, it confirms the app is NOT currently installed in this browser
            if (window.matchMedia('(display-mode: browser)').matches) {
                localStorage.removeItem('pfe_pwa_installed');
            }
            window.dispatchEvent(new CustomEvent('pfe:deferred-prompt-ready'));
            console.log('[PFE PWA] Native beforeinstallprompt captured!');
        });

        // Listen for native appinstalled event
        window.addEventListener('appinstalled', () => {
            localStorage.setItem('pfe_pwa_installed', 'true');
            window.__pfeDeferredPrompt = null;
            window.dispatchEvent(new CustomEvent('pfe:installed'));
            console.log('[PFE PWA] App was successfully installed!');
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

        /* Global UI Animations & Polish */
        .report-card {
            position: relative;
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), 
                        box-shadow 0.25s cubic-bezier(0.16, 1, 0.3, 1), 
                        border-color 0.25s ease;
            will-change: transform, box-shadow;
        }
        .report-card:hover {
            transform: translateY(-4px);
            border-color: #009B77 !important;
            box-shadow: 0 0 0 2px #009B77, 0 14px 28px -4px rgba(0, 155, 119, 0.22) !important;
        }
        .report-card.bg-gradient-to-br:hover {
            transform: translateY(-4px);
            border-color: #ffffff !important;
            box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.9), 0 14px 28px -4px rgba(0, 155, 119, 0.45) !important;
        }
        
        .btn-smooth-ring {
            position: relative;
            transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), 
                        box-shadow 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            will-change: transform, box-shadow;
        }
        .btn-smooth-ring:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 0 3px rgba(0, 155, 119, 0.35), 0 8px 20px -2px rgba(0, 155, 119, 0.3) !important;
        }
        .btn-smooth-ring:active {
            transform: translateY(0) scale(0.97);
            box-shadow: 0 0 0 2px rgba(0, 155, 119, 0.6) !important;
        }

        #progressPieChart {
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), filter 0.3s ease;
            cursor: pointer;
        }
        #progressPieChart:hover {
            transform: scale(1.04);
            filter: drop-shadow(0 6px 16px rgba(0, 0, 0, 0.2));
        }

        /* Clean ApexCharts Tooltip */
        .apexcharts-tooltip {
            background: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.04) !important;
            border-radius: 12px !important;
            overflow: hidden !important;
            font-family: inherit !important;
        }
        .apexcharts-tooltip-title {
            background: #f8fafc !important;
            border-bottom: 1px solid #e2e8f0 !important;
            font-weight: 700 !important;
            font-size: 11px !important;
            color: #334155 !important;
            padding: 6px 10px !important;
        }
        .apexcharts-tooltip-series-group {
            padding: 4px 10px !important;
        }
        .apexcharts-tooltip-text {
            font-size: 12px !important;
            font-weight: 600 !important;
            color: #1e293b !important;
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
                <a href="/dashboard" class="justify-center flex items-center p-3 overflow-hidden rounded-xl font-semibold transition-all {{ request()->is('dashboard') ? 'bg-[#009B77] text-white shadow-[0_4px_12px_rgba(0,155,119,0.25)] hover:bg-[#008264]' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800' }}" :class="sidebarOpen ? 'justify-start' : 'justify-center'" title="{{ __('Dashboard') }}">
                    <i class="ph-fill ph-squares-four text-xl flex-shrink-0"></i>
                    <span class="opacity-0 w-0 ml-0 text-sm whitespace-nowrap transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] overflow-hidden" :class="sidebarOpen ? 'opacity-100 w-32 ml-3' : 'opacity-0 w-0 ml-0'">{{ __('Dashboard') }}</span>
                </a>

                @if(auth()->user()->role !== 'Staff')
                <a href="/users" class="justify-center flex items-center p-3 overflow-hidden rounded-xl font-semibold transition-all {{ request()->is('users') ? 'bg-[#009B77] text-white shadow-[0_4px_12px_rgba(0,155,119,0.25)] hover:bg-[#008264]' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800' }}" :class="sidebarOpen ? 'justify-start' : 'justify-center'" title="{{ __('User Management') }}">
                    <i class="ph-duotone ph-users text-xl flex-shrink-0"></i>
                    <span class="opacity-0 w-0 ml-0 text-sm whitespace-nowrap transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] overflow-hidden" :class="sidebarOpen ? 'opacity-100 w-32 ml-3' : 'opacity-0 w-0 ml-0'">{{ __('User Management') }}</span>
                </a>
                @endif
                <a href="/guest-display" class="justify-center flex items-center p-3 overflow-hidden rounded-xl font-semibold transition-all {{ request()->is('guest-display*') ? 'bg-[#009B77] text-white shadow-[0_4px_12px_rgba(0,155,119,0.25)] hover:bg-[#008264]' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800' }}" :class="sidebarOpen ? 'justify-start' : 'justify-center'" title="{{ __('Display Access') }}">
                    <i class="ph-duotone ph-broadcast text-xl flex-shrink-0"></i>
                    <span class="opacity-0 w-0 ml-0 text-sm whitespace-nowrap transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] overflow-hidden" :class="sidebarOpen ? 'opacity-100 w-32 ml-3' : 'opacity-0 w-0 ml-0'">{{ __('Display Access') }}</span>
                </a>
                
                <a href="/master-data" class="justify-center flex items-center p-3 overflow-hidden rounded-xl font-semibold transition-all {{ request()->is('master-data') ? 'bg-[#009B77] text-white shadow-[0_4px_12px_rgba(0,155,119,0.25)] hover:bg-[#008264]' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800' }}" :class="sidebarOpen ? 'justify-start' : 'justify-center'" title="{{ __('PFE Master Data') }}">
                    <i class="ph-duotone ph-list-dashes text-xl flex-shrink-0"></i>
                    <span class="opacity-0 w-0 ml-0 text-sm whitespace-nowrap transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] overflow-hidden" :class="sidebarOpen ? 'opacity-100 w-32 ml-3' : 'opacity-0 w-0 ml-0'">{{ __('PFE Master Data') }}</span>
                </a>
            </div>

            <!-- Group 3: Inspections & Reports -->
            <div class="p-4 border-t border-slate-200 flex flex-col gap-1.5">
                <a href="/inspection-schedule" class="justify-center flex items-center p-3 overflow-hidden rounded-xl font-semibold transition-all {{ request()->is('inspection-schedule') ? 'bg-[#009B77] text-white shadow-[0_4px_12px_rgba(0,155,119,0.25)] hover:bg-[#008264]' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800' }}" :class="sidebarOpen ? 'justify-start' : 'justify-center'" title="{{ __('Inspection Schedule') }}">
                    <i class="ph-duotone ph-calendar-blank text-xl flex-shrink-0"></i>
                    <span class="opacity-0 w-0 ml-0 text-sm whitespace-nowrap transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] overflow-hidden" :class="sidebarOpen ? 'opacity-100 w-32 ml-3' : 'opacity-0 w-0 ml-0'">{{ __('Inspection Schedule') }}</span>
                </a>

                <a href="/reports" class="justify-center flex items-center p-3 overflow-hidden rounded-xl font-semibold transition-all {{ request()->is('reports') ? 'bg-[#009B77] text-white shadow-[0_4px_12px_rgba(0,155,119,0.25)] hover:bg-[#008264]' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800' }}" :class="sidebarOpen ? 'justify-start' : 'justify-center'" title="{{ __('Inspection Reports') }}">
                    <i class="ph-duotone ph-file-text text-xl flex-shrink-0"></i>
                    <span class="opacity-0 w-0 ml-0 text-sm whitespace-nowrap transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] overflow-hidden" :class="sidebarOpen ? 'opacity-100 w-32 ml-3' : 'opacity-0 w-0 ml-0'">{{ __('Inspection Reports') }}</span>
                </a>
            </div>
            
            <!-- Group 4: Audit Trail -->
            @if(auth()->user()->role !== 'Staff')
            <div class="p-4 border-t border-slate-200 flex flex-col gap-1.5">
                <a href="/activity-log" class="justify-center flex items-center p-3 overflow-hidden rounded-xl font-semibold transition-all {{ request()->is('activity-log') ? 'bg-[#009B77] text-white shadow-[0_4px_12px_rgba(0,155,119,0.25)] hover:bg-[#008264]' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800' }}" :class="sidebarOpen ? 'justify-start' : 'justify-center'" title="{{ __('Audit Trail') }}">
                    <i class="ph-bold ph-clock-counter-clockwise text-xl flex-shrink-0"></i>
                    <span class="opacity-0 w-0 ml-0 text-sm whitespace-nowrap transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] overflow-hidden" :class="sidebarOpen ? 'opacity-100 w-32 ml-3' : 'opacity-0 w-0 ml-0'">{{ __('Audit Trail') }}</span>
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
            <div class="flex-1 flex justify-center"
                 x-data="{
                     query: '',
                     isOpen: false,
                     selectedIndex: -1,
                     isLoadingData: false,
                     debounceTimer: null,
                     liveApars: [],
                     liveLokasis: [],
                     features: [
                         {
                             title: '{{ __('PFE Master Data') }}',
                             category: '{{ __('Master Data') }}',
                             desc: '{{ __('Manage all PFE units, serial numbers, and validity periods') }}',
                             url: '{{ route('master-data.index') }}',
                             icon: 'ph-fire-extinguisher',
                             badge: '{{ __('Master') }}',
                             keywords: 'master data apar pfe unit serial barcode tabung pemadam'
                         },
                         {
                             title: '{{ __('Backup Stock (Spare APAR)') }}',
                             category: '{{ __('Master Data') }}',
                             desc: '{{ __('Manage spare APAR units and emergency replacements') }}',
                             url: '{{ route('master-data.index') }}?tab=cadangan',
                             icon: 'ph-archive-box',
                             badge: '{{ __('Stock') }}',
                             keywords: 'backup stock cadangan apar cadangan pengganti unit spare gudang'
                         },
                         {
                             title: '{{ __('Reference Settings') }}',
                             category: '{{ __('Master Data') }}',
                             desc: '{{ __('Manage placement locations, buildings, PFE types, and capacities') }}',
                             url: '{{ route('master-data.index') }}?tab=referensi',
                             icon: 'ph-sliders-horizontal',
                             badge: '{{ __('Reference') }}',
                             keywords: 'referensi reference settings lokasi location gedung building type jenis kapasitas capacity'
                         },
                         {
                             title: '{{ __('Inspection Schedule') }}',
                             category: '{{ __('Inspection') }}',
                             desc: '{{ __('Monthly inspection routines, calendar, and PIC assignments') }}',
                             url: '/inspection-schedule',
                             icon: 'ph-calendar-blank',
                             badge: '{{ __('Schedule') }}',
                             keywords: 'jadwal inspeksi inspection schedule kalender routine bulanan checklist pic'
                         },
                         {
                             title: '{{ __('Inspection Reports') }}',
                             category: '{{ __('Reports') }}',
                             desc: '{{ __('Monthly compliance recaps, export inspection data to PDF and Excel') }}',
                             url: '{{ route('reports.index') }}',
                             icon: 'ph-file-text',
                             badge: '{{ __('Report') }}',
                             keywords: 'laporan report rekapitulasi export unduh cetak pdf excel inspeksi hasil'
                         },
                         {
                             title: '{{ __('TV Monitoring Display') }}',
                             category: '{{ __('Monitoring') }}',
                             desc: '{{ __('Real-time full-screen display for plant and facility monitoring') }}',
                             url: '{{ route('display-report') }}',
                             icon: 'ph-television',
                             badge: '{{ __('Display') }}',
                             keywords: 'display report tv monitoring layar presentasi fullscreen monitor'
                         },
                         {
                             title: '{{ __('Guest Display Access') }}',
                             category: '{{ __('System') }}',
                             desc: '{{ __('Manage viewer display links and secure audit access tokens') }}',
                             url: '{{ route('display-access.index') }}',
                             icon: 'ph-broadcast',
                             badge: '{{ __('Guest') }}',
                             keywords: 'guest display access tamu eksternal token audit external link'
                         },
                         {
                             title: '{{ __('Print All QR Codes') }}',
                             category: '{{ __('Quick Action') }}',
                             desc: '{{ __('Batch generate and print QR code labels for all APAR units') }}',
                             url: '{{ route('apar.print-all-qr') }}',
                             icon: 'ph-qr-code',
                             badge: '{{ __('Print QR') }}',
                             keywords: 'print cetak qr code label barcode stiker tabung'
                         }@if(auth()->user()->role !== 'Staff'),
                         {
                             title: '{{ __('User Management') }}',
                             category: '{{ __('Users') }}',
                             desc: '{{ __('Manage user accounts, roles (EHSS / Staff), and security PINs') }}',
                             url: '{{ route('users.index') }}',
                             icon: 'ph-users',
                             badge: '{{ __('Users') }}',
                             keywords: 'user pengguna akun employee id pic manajemen akun role ehss staff pin'
                         },
                         {
                             title: '{{ __('Audit Trail') }}',
                              category: '{{ __('System') }}',
                             desc: '{{ __('Audit logs of user logins, data modifications, and system activity') }}',
                             url: '{{ route('activity-log.index') }}',
                             icon: 'ph-clock-counter-clockwise',
                             badge: '{{ __('Audit') }}',
                             keywords: 'activity log log aktivitas audit trail riwayat login history jejak'
                         }
                         @endif
                     ],
                     get filteredFeatures() {
                         let q = this.query.trim().toLowerCase();
                         if (!q) return this.features.slice(0, 6);
                         return this.features.filter(f => 
                             f.title.toLowerCase().includes(q) || 
                             f.category.toLowerCase().includes(q) || 
                             f.desc.toLowerCase().includes(q) || 
                             f.keywords.toLowerCase().includes(q)
                         );
                     },
                     get totalItems() {
                         return this.filteredFeatures.length + this.liveApars.length + this.liveLokasis.length;
                     },
                     searchDataLive() {
                         let q = this.query.trim();
                         if (this.debounceTimer) clearTimeout(this.debounceTimer);
                         if (q.length < 1) {
                             this.liveApars = [];
                             this.liveLokasis = [];
                             this.isLoadingData = false;
                             return;
                         }
                         this.isLoadingData = true;
                         this.debounceTimer = setTimeout(async () => {
                             try {
                                 let res = await fetch(`{{ route('dashboard.quick-search') }}?q=${encodeURIComponent(q)}`, {
                                     headers: { 'Accept': 'application/json' }
                                 });
                                 if (res.ok) {
                                     let data = await res.json();
                                     this.liveApars = data.apars || [];
                                     this.liveLokasis = data.lokasis || [];
                                 }
                             } catch (e) {
                                 // ignore
                             } finally {
                                 this.isLoadingData = false;
                             }
                         }, 200);
                     },
                     navigateItem(direction) {
                         if (!this.isOpen || this.totalItems === 0) return;
                         if (direction === 'down') {
                             this.selectedIndex = (this.selectedIndex + 1) % this.totalItems;
                         } else if (direction === 'up') {
                             this.selectedIndex = (this.selectedIndex - 1 + this.totalItems) % this.totalItems;
                         }
                     },
                     selectActive() {
                         if (!this.isOpen) return;
                         let all = [
                             ...this.filteredFeatures.map(f => f.url),
                             ...this.liveApars.map(a => a.url),
                             ...this.liveLokasis.map(l => l.url)
                         ];
                         if (this.selectedIndex >= 0 && this.selectedIndex < all.length) {
                             window.location.href = all[this.selectedIndex];
                         } else if (all.length > 0) {
                             window.location.href = all[0];
                         }
                     }
                 }"
                 @click.away="isOpen = false; selectedIndex = -1"
                 @keydown.escape.window="isOpen = false; selectedIndex = -1"
                 @keydown.down.prevent="navigateItem('down')"
                 @keydown.up.prevent="navigateItem('up')"
                 @keydown.enter.prevent="selectActive()">
                <div class="relative w-full max-w-lg hidden sm:block">
                    <!-- Search Input Wrapper -->
                    <div class="relative flex items-center">
                        <i class="ph-bold ph-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-[#009B77] text-lg transition-transform"
                           :class="isOpen ? 'scale-110' : ''"></i>
                        <input type="text" 
                               x-model="query"
                               @focus="isOpen = true"
                               @input="isOpen = true; searchDataLive()"
                               placeholder="{{ __('Search features, APAR units, locations...') }}" 
                               class="w-full bg-white border-2 border-[#009B77] rounded-xl py-2.5 pl-11 pr-10 text-sm text-slate-700 focus:outline-none focus:ring-4 focus:ring-[#009B77]/20 transition-all placeholder:text-slate-400 font-medium shadow-sm">
                        
                        <!-- Clear or Spinner Icon -->
                        <div class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center gap-1">
                            <i x-show="isLoadingData" class="ph-bold ph-spinner animate-spin text-[#009B77] text-base" x-cloak></i>
                            <button type="button" 
                                    x-show="query.length > 0" 
                                    @click="query = ''; liveApars = []; liveLokasis = []; $nextTick(() => $el.closest('div').previousElementSibling.focus())" 
                                    class="text-slate-400 hover:text-slate-600 transition-colors p-0.5 rounded-md hover:bg-slate-100" 
                                    x-cloak>
                                <i class="ph-bold ph-x-circle text-base"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Autocomplete & Recommendations Dropdown -->
                    <div x-show="isOpen"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-1 scale-98"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                         x-transition:leave-end="opacity-0 translate-y-1 scale-98"
                         class="absolute left-0 right-0 top-full mt-2 bg-white rounded-2xl shadow-2xl border border-slate-200/80 overflow-hidden z-50 divide-y divide-slate-100 max-h-[75vh] flex flex-col"
                         x-cloak
                         style="display: none;">
                        
                        <!-- Header Label -->
                        <div class="px-4 py-2.5 bg-slate-50/80 border-b border-slate-100 flex items-center justify-between">
                            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
                                <i class="ph-bold ph-sparkle text-[#009B77]"></i>
                                <span x-text="query.trim().length > 0 ? '{{ __('Search Results & Recommendations') }}' : '{{ __('Popular Feature Recommendations') }}'"></span>
                            </span>
                            <span class="text-[10.5px] font-semibold text-slate-400" x-text="`${totalItems} {{ __('options') }}`"></span>
                        </div>

                        <!-- Results Scroll Container -->
                        <div class="overflow-y-auto divide-y divide-slate-50 p-1.5 flex-1 max-h-[60vh]">
                            
                            <!-- Category: Features -->
                            <div class="py-1">
                                <div class="px-3 py-1 text-[10px] font-extrabold text-[#009B77] uppercase tracking-wider flex items-center gap-1">
                                    <i class="ph-bold ph-squares-four text-xs"></i>
                                    <span>{{ __('System Features & Menus') }}</span>
                                </div>
                                <template x-for="(item, idx) in filteredFeatures" :key="idx">
                                    <a :href="item.url" 
                                       @mouseenter="selectedIndex = idx"
                                       class="flex items-center justify-between gap-3 px-3 py-2.5 rounded-xl transition-all cursor-pointer group"
                                       :class="selectedIndex === idx ? 'bg-[#009B77]/10 text-[#007A5E]' : 'hover:bg-slate-50 text-slate-700'">
                                        <div class="flex items-center gap-3 min-w-0 flex-1">
                                            <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 transition-colors"
                                                 :class="selectedIndex === idx ? 'bg-[#009B77] text-white' : 'bg-slate-100 text-slate-600 group-hover:bg-[#009B77]/20 group-hover:text-[#009B77]'">
                                                <i :class="`ph-bold ${item.icon} text-base`"></i>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-xs font-bold truncate" :class="selectedIndex === idx ? 'text-[#007A5E]' : 'text-slate-800'" x-text="item.title"></span>
                                                    <span class="text-[9.5px] font-extrabold px-1.5 py-0.5 rounded uppercase tracking-wider shrink-0"
                                                          :class="selectedIndex === idx ? 'bg-[#009B77] text-white' : 'bg-slate-100 text-slate-500'" 
                                                          x-text="item.badge"></span>
                                                </div>
                                                <p class="text-[11px] text-slate-400 truncate mt-0.5" x-text="item.desc"></p>
                                            </div>
                                        </div>
                                        <div class="flex items-center text-slate-400 shrink-0 group-hover:text-[#009B77] transition-transform group-hover:translate-x-0.5">
                                            <i class="ph-bold ph-arrow-right text-xs"></i>
                                        </div>
                                    </a>
                                </template>
                            </div>

                            <!-- Category: Live APAR Units (if any) -->
                            <div x-show="liveApars.length > 0" class="py-1">
                                <div class="px-3 py-1 text-[10px] font-extrabold text-blue-600 uppercase tracking-wider flex items-center gap-1">
                                    <i class="ph-bold ph-fire-extinguisher text-xs"></i>
                                    <span>{{ __('Matching APAR Units') }} (<span x-text="liveApars.length"></span>)</span>
                                </div>
                                <template x-for="(apar, aIdx) in liveApars" :key="'apar-' + apar.id">
                                    <a :href="apar.url" 
                                       @mouseenter="selectedIndex = filteredFeatures.length + aIdx"
                                       class="flex items-center justify-between gap-3 px-3 py-2 rounded-xl transition-all cursor-pointer group"
                                       :class="selectedIndex === (filteredFeatures.length + aIdx) ? 'bg-blue-50 text-blue-900' : 'hover:bg-slate-50 text-slate-700'">
                                        <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                            <span class="font-mono text-xs font-bold text-[#009B77] bg-[#009B77]/10 px-2 py-0.5 rounded border border-[#009B77]/20 shrink-0" x-text="apar.kode"></span>
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="text-xs font-semibold text-slate-800 truncate" x-text="apar.lokasi"></span>
                                                </div>
                                                <span class="text-[10px] text-slate-400 font-medium" x-text="apar.jenis"></span>
                                            </div>
                                        </div>
                                        <i class="ph-bold ph-arrow-right text-xs text-slate-400 group-hover:text-blue-600 shrink-0"></i>
                                    </a>
                                </template>
                            </div>

                            <!-- Category: Live Locations (if any) -->
                            <div x-show="liveLokasis.length > 0" class="py-1">
                                <div class="px-3 py-1 text-[10px] font-extrabold text-teal-600 uppercase tracking-wider flex items-center gap-1">
                                    <i class="ph-bold ph-map-pin text-xs"></i>
                                    <span>{{ __('Rooms & Areas') }} (<span x-text="liveLokasis.length"></span>)</span>
                                </div>
                                <template x-for="(lok, lIdx) in liveLokasis" :key="'lok-' + lok.id">
                                    <a :href="lok.url" 
                                       @mouseenter="selectedIndex = filteredFeatures.length + liveApars.length + lIdx"
                                       class="flex items-center justify-between gap-3 px-3 py-2 rounded-xl transition-all cursor-pointer group"
                                       :class="selectedIndex === (filteredFeatures.length + liveApars.length + lIdx) ? 'bg-teal-50 text-teal-900' : 'hover:bg-slate-50 text-slate-700'">
                                        <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                            <div class="w-6 h-6 rounded-md bg-teal-100 text-teal-700 flex items-center justify-center shrink-0">
                                                <i class="ph-bold ph-door text-xs"></i>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <span class="text-xs font-bold text-slate-800 truncate block" x-text="lok.nama"></span>
                                                <span class="text-[10px] text-slate-400" x-text="'{{ __('Building') }} ' + lok.gedung"></span>
                                            </div>
                                        </div>
                                        <i class="ph-bold ph-arrow-right text-xs text-slate-400 group-hover:text-teal-600 shrink-0"></i>
                                    </a>
                                </template>
                            </div>

                            <!-- Empty State -->
                            <div x-show="totalItems === 0" class="py-8 text-center px-4" x-cloak>
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto text-slate-400 mb-2">
                                    <i class="ph-bold ph-magnifying-glass text-2xl"></i>
                                </div>
                                <p class="text-xs font-bold text-slate-700">{{ __('No matching features or data found') }}</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">
                                    {{ __('Try searching keywords like') }} <span class="font-bold text-[#009B77]">master</span>, <span class="font-bold text-[#009B77]">stock</span>, <span class="font-bold text-[#009B77]">location</span>
                                </p>
                            </div>
                        </div>

                        <!-- Dropdown Footer Shortcuts -->
                        <div class="px-4 py-2 bg-slate-50/90 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                            <div class="flex items-center gap-3">
                                <span class="flex items-center gap-1"><kbd class="px-1.5 py-0.5 bg-white border border-slate-200 rounded text-[10px] font-mono shadow-xs">↑↓</kbd> {{ __('Navigate') }}</span>
                                <span class="flex items-center gap-1"><kbd class="px-1.5 py-0.5 bg-white border border-slate-200 rounded text-[10px] font-mono shadow-xs">↵</kbd> {{ __('Open') }}</span>
                                <span class="flex items-center gap-1"><kbd class="px-1.5 py-0.5 bg-white border border-slate-200 rounded text-[10px] font-mono shadow-xs">ESC</kbd> {{ __('Close') }}</span>
                            </div>
                            <span class="text-[#009B77] font-bold text-[10px] tracking-wide">{{ __('PFE Control System') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @else
            <div class="flex-1"></div>
            @endif

            <!-- Right Actions -->
            <div class="flex-1 flex items-center justify-end gap-3">
                <!-- Install PWA / APK Button (Desktop & Mobile, hides when installed) -->
                <button type="button" 
                        x-data="{
                            isInstalled: window.__isPFEInstalled ? window.__isPFEInstalled() : false,
                            init() {
                                this.isInstalled = window.__isPFEInstalled ? window.__isPFEInstalled() : false;
                                window.addEventListener('appinstalled', () => {
                                    this.isInstalled = true;
                                });
                                window.addEventListener('pfe:installed', () => {
                                    this.isInstalled = true;
                                });
                                if ('getInstalledRelatedApps' in navigator) {
                                    navigator.getInstalledRelatedApps().then(apps => {
                                        if (apps && apps.length > 0) {
                                            this.isInstalled = true;
                                            localStorage.setItem('pfe_pwa_installed', 'true');
                                        }
                                    }).catch(() => {});
                                }
                            }
                        }"
                        x-show="!isInstalled"
                        x-cloak
                        @click="window.dispatchEvent(new CustomEvent('pfe:trigger-install'))"
                        class="flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 rounded-xl bg-[#009B77]/10 hover:bg-[#009B77] text-[#009B77] hover:text-white font-bold text-xs transition-all border border-[#009B77]/20 cursor-pointer shadow-sm active:scale-95"
                        title="Install PFE Monitoring Control System (Aplikasi / APK)">
                    <i class="ph-bold ph-download-simple text-sm"></i>
                    <span class="hidden sm:inline">Install App</span>
                    <span class="sm:hidden text-[11px]">Install</span>
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
    <div x-data="{ 
            open: false,
            currentPin: '',
            newPin: '',
            confirmPin: '',
            isPinUnsafe: false,
            pinCheckTimer: null,
            checkPinUnsafe(pin) {
                if (this.pinCheckTimer) clearTimeout(this.pinCheckTimer);
                if (!pin || pin.length !== 4 || !/^\d{4}$/.test(pin)) {
                    this.isPinUnsafe = false;
                    return;
                }
                this.pinCheckTimer = setTimeout(async () => {
                    try {
                        let res = await fetch(`/users/check-pin?pin=${encodeURIComponent(pin)}&user_id={{ auth()->id() }}`, {
                            headers: { 'Accept': 'application/json' }
                        });
                        let data = await res.json();
                        this.isPinUnsafe = !data.safe;
                    } catch (e) {
                        // ignore
                    }
                }, 200);
            }
         }" 
         @open-change-pin.window="open = true; currentPin = ''; newPin = ''; confirmPin = ''; isPinUnsafe = false;" 
         x-show="open" 
         class="fixed inset-0 z-[100] flex items-center justify-center overflow-hidden bg-slate-900/40 p-4" 
         style="display: none;" 
         x-cloak>
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
                            <input :type="show ? 'text' : 'password'" 
                                   name="current_pin" 
                                   x-model="currentPin"
                                   inputmode="numeric" 
                                   pattern="[0-9]{4}" 
                                   maxlength="4" 
                                   required 
                                   @input="currentPin = currentPin.replace(/[^0-9]/g, '')" 
                                   class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 px-4 text-center text-lg font-mono tracking-widest text-slate-700 focus:bg-white focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none" 
                                   placeholder="••••">
                            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-[#009B77] transition-colors focus:outline-none">
                                <i class="ph-fill text-lg" :class="show ? 'ph-eye-slash' : 'ph-eye'"></i>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('New PIN (4 Digits)') }}</label>
                        <div class="relative" x-data="{ show: false }">
                            <input :type="show ? 'text' : 'password'" 
                                   name="new_pin" 
                                   x-model="newPin"
                                   inputmode="numeric" 
                                   pattern="[0-9]{4}" 
                                   maxlength="4" 
                                   required 
                                   @input="newPin = newPin.replace(/[^0-9]/g, ''); if(newPin.length === 4) { checkPinUnsafe(newPin); } else { isPinUnsafe = false; }" 
                                   class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 px-4 text-center text-lg font-mono tracking-widest text-slate-700 focus:bg-white focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none" 
                                   :class="isPinUnsafe ? 'border-red-400 bg-red-50/50 text-red-600 focus:border-red-500 focus:ring-red-500/15' : ''"
                                   placeholder="••••">
                            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-[#009B77] transition-colors focus:outline-none">
                                <i class="ph-fill text-lg" :class="show ? 'ph-eye-slash' : 'ph-eye'"></i>
                            </button>
                        </div>
                        <p x-show="isPinUnsafe" class="mt-1.5 text-xs text-red-600 font-bold leading-tight flex items-center gap-1.5" x-cloak>
                            <i class="ph-fill ph-warning-circle text-sm shrink-0"></i>
                            <span>{{ __('PIN Anda tidak aman') }}</span>
                        </p>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('Confirm New PIN') }}</label>
                        <div class="relative" x-data="{ show: false }">
                            <input :type="show ? 'text' : 'password'" 
                                   name="new_pin_confirmation" 
                                   x-model="confirmPin"
                                   inputmode="numeric" 
                                   pattern="[0-9]{4}" 
                                   maxlength="4" 
                                   required 
                                   @input="confirmPin = confirmPin.replace(/[^0-9]/g, '')" 
                                   class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 px-4 text-center text-lg font-mono tracking-widest text-slate-700 focus:bg-white focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none" 
                                   placeholder="••••">
                            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-[#009B77] transition-colors focus:outline-none">
                                <i class="ph-fill text-lg" :class="show ? 'ph-eye-slash' : 'ph-eye'"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50 flex items-center justify-end rounded-b-2xl">
                    <button type="submit" 
                            :disabled="isPinUnsafe"
                            :class="isPinUnsafe ? 'opacity-50 cursor-not-allowed pointer-events-none' : ''"
                            class="bg-[#009B77] hover:bg-[#008264] text-white font-bold py-2.5 px-6 rounded-xl shadow-lg shadow-teal-500/30 transition-all text-sm">
                        {{ __('Save New PIN') }}
                    </button>
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
                            @if($user->gedungs->isEmpty() && $user->lokasis->isEmpty())
                                <div class="p-4 bg-slate-50 border border-slate-100 border-dashed rounded-xl text-center text-sm font-medium text-slate-500">
                                    {{ __('You have not been assigned as a PIC for any building or location.') }}
                                </div>
                            @else
                                <div class="space-y-2 mb-4 max-h-52 overflow-y-auto pr-1">
                                    @foreach($user->gedungs as $gedung)
                                        <div class="p-2.5 border border-slate-100 rounded-xl flex items-center justify-between hover:bg-slate-50 transition-colors">
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0 text-sm">
                                                    <i class="ph-fill ph-building"></i>
                                                </div>
                                                <div>
                                                    <p class="text-xs font-bold text-slate-800">{{ $gedung->nama }} <span class="text-[10px] text-emerald-600 font-bold bg-emerald-50 px-1.5 py-0.5 rounded ml-1">{{ __('All Rooms') }}</span></p>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                    @foreach($user->lokasis as $lok)
                                        <div class="p-2.5 border border-slate-100 rounded-xl flex items-center justify-between hover:bg-slate-50 transition-colors">
                                            <div class="flex items-center gap-2.5 min-w-0">
                                                <div class="w-7 h-7 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center flex-shrink-0 text-sm">
                                                    <i class="ph-fill ph-door"></i>
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="text-xs font-bold text-slate-800 truncate flex items-center gap-1.5">
                                                        <span class="text-teal-700">{{ $lok->gedung->nama ?? '' }}:</span>
                                                        @if($lok->nomor_ruangan)
                                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-mono font-bold bg-[#009B77]/10 text-[#009B77]">{{ $lok->nomor_ruangan }}</span>
                                                        @endif
                                                        <span>{{ $lok->nama }}</span>
                                                    </p>
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
                                        <span class="text-[10px] font-semibold text-slate-500 mt-0.5">{{ __('Full screen kiosk mode (Active Session Credential)') }}</span>
                                    </div>
                                </div>
                                <i class="ph-bold ph-arrow-square-out text-slate-400 group-hover:text-indigo-500 transition-colors"></i>
                            </a>
                            <a href="{{ route('display-access.index') }}" class="w-full text-left px-4 py-3 rounded-xl border border-slate-200 hover:border-[#009B77] hover:bg-teal-50 transition-all flex items-center justify-between group">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-500 group-hover:bg-white group-hover:text-[#009B77] flex items-center justify-center transition-colors shadow-sm">
                                        <i class="ph-bold ph-key text-lg"></i>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-slate-700 group-hover:text-[#009B77] transition-colors">{{ __('Manage Viewer Access (PIN & Duration)') }}</span>
                                        <span class="text-[10px] font-semibold text-slate-500 mt-0.5">{{ __('Create PIN credentials and configure active duration for viewers') }}</span>
                                    </div>
                                </div>
                                <i class="ph-bold ph-caret-right text-slate-400 group-hover:text-[#009B77] transition-colors"></i>
                            </a>
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

        // Pastikan seluruh unduhan, export, PDF, dan dokumen cetak selalu dibuka di tab/layar baru
        // agar pengguna desktop PWA / standalone tidak terjebak tanpa tombol kembali.
        document.addEventListener('click', function(e) {
            const link = e.target.closest('a');
            if (!link) return;
            const href = link.getAttribute('href') || link.href || '';
            if (!href || href === '#' || href.startsWith('javascript:')) return;

            const isDownloadOrDoc = /export-pdf|export-excel|export-checklist|print-all-qr|print-qr|download-qr|\.pdf(\?|$)/i.test(href) || link.hasAttribute('download');
            if (isDownloadOrDoc) {
                link.setAttribute('target', '_blank');
                link.setAttribute('rel', 'noopener noreferrer');
            }
        }, true);

        // Anti-BFCache: Reload page if navigated back from browser cache after logout
        window.addEventListener('pageshow', function(event) {
            if (event.persisted || (window.performance && window.performance.navigation && window.performance.navigation.type === 2)) {
                window.location.reload();
            }
        });
    </script>
    @stack('scripts')

    <!-- PWA Desktop Install Banner (Only rendered for authenticated users on Desktop) -->
    <x-desktop-install-banner />
</body>
</html>

