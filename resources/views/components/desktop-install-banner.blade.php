<div x-data="desktopInstallPrompt()"
     x-init="init()"
     @mouseenter="pauseTimer()"
     @mouseleave="resumeTimer()"
     x-show="canInstall && !isDismissed && isDesktop && !isInstalled"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 translate-y-8 scale-95"
     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
     x-transition:leave-end="opacity-0 translate-y-8 scale-95"
     x-cloak
     style="display: none;"
     class="fixed bottom-6 right-6 z-[999] max-w-sm w-full bg-white/95 backdrop-blur-md rounded-2xl p-4 shadow-[0_12px_36px_rgba(0,0,0,0.15)] border border-slate-200/80 text-left">
    
    <div class="flex items-start gap-3.5">
        <!-- Icon Badge -->
        <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-[#009B77] to-[#007A5E] text-white flex items-center justify-center flex-shrink-0 shadow-md shadow-[#009B77]/25">
            <i class="ph-bold ph-download-simple text-xl"></i>
        </div>

        <!-- Text Content -->
        <div class="flex-1 min-w-0 pr-6">
            <h4 class="text-sm font-bold text-slate-800 leading-snug">
                Install PFE Monitoring Control System
            </h4>
            <p class="text-xs text-slate-500 font-medium mt-0.5 leading-relaxed">
                Akses lebih cepat & mudah langsung dari desktop komputer Anda.
            </p>
        </div>

        <!-- Close Button with Depleting Circular Countdown Progress -->
        <button type="button" 
                @click="dismiss()" 
                class="absolute top-3.5 right-3.5 w-7 h-7 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors group cursor-pointer"
                :title="'Tutup (' + Math.ceil(remainingSeconds) + 's)'">
            <!-- Circular countdown track & depleting ring -->
            <svg class="absolute inset-0 w-full h-full -rotate-90 transform pointer-events-none" viewBox="0 0 28 28">
                <!-- Background track -->
                <circle cx="14" cy="14" r="11" fill="none" class="stroke-slate-200" stroke-width="2.5" />
                <!-- Depleting countdown progress ring -->
                <circle cx="14" cy="14" r="11" fill="none" 
                        class="stroke-[#009B77] transition-[stroke-dashoffset] duration-100 ease-linear" 
                        stroke-width="2.5" 
                        stroke-linecap="round" 
                        stroke-dasharray="69.115" 
                        :stroke-dashoffset="strokeDashoffset" />
            </svg>
            <i class="ph-bold ph-x text-xs text-slate-400 group-hover:text-slate-700 transition-colors pointer-events-none z-10"></i>
        </button>
    </div>

    <!-- Action Buttons -->
    <div class="mt-3.5 pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
        <button type="button" 
                @click="dismiss()" 
                class="px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-500 hover:text-slate-700 hover:bg-slate-100 transition-colors">
            Nanti Saja
        </button>
        <button type="button" 
                @click="install()" 
                class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-[#009B77] hover:bg-[#008264] shadow-md shadow-[#009B77]/20 transition-all flex items-center gap-1.5 active:scale-95">
            <i class="ph-bold ph-download-simple"></i>
            <span>Install Sekarang</span>
        </button>
    </div>
</div>

<script>
function desktopInstallPrompt() {
    return {
        deferredPrompt: window.__pfeDeferredPrompt || null,
        canInstall: false,
        isDismissed: false,
        isDesktop: false,
        isInstalled: false,
        countdownInterval: null,
        totalSeconds: 15,
        remainingSeconds: 15,
        isPaused: false,

        // Circumference for r=11 is 2 * PI * 11 ≈ 69.115
        get strokeDashoffset() {
            const fraction = Math.max(0, Math.min(1, this.remainingSeconds / this.totalSeconds));
            return (69.115 * (1 - fraction)).toFixed(2);
        },

        async init() {
            // Strict check: ONLY desktop (non-mobile user agent, pointer fine)
            const isMobileUA = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
            const isTouchOnly = window.matchMedia('(pointer: coarse)').matches && !window.matchMedia('(pointer: fine)').matches;
            const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;

            // 1. Check if opened inside standalone app or marked as installed
            if (isStandalone || localStorage.getItem('pfe_pwa_installed') === 'true') {
                this.isInstalled = true;
                return;
            }

            // 2. Stop immediately if on mobile / tablet
            if (isMobileUA || isTouchOnly) {
                return;
            }

            // 3. Check browser native getInstalledRelatedApps if supported
            if ('getInstalledRelatedApps' in navigator) {
                try {
                    const relatedApps = await navigator.getInstalledRelatedApps();
                    if (relatedApps && relatedApps.length > 0) {
                        this.isInstalled = true;
                        localStorage.setItem('pfe_pwa_installed', 'true');
                        return;
                    }
                } catch (err) {}
            }

            this.isDesktop = true;

            // 4. Check if manually dismissed in the last 7 days (clicked "Nanti Saja" or close "X")
            const dismissedAt = localStorage.getItem('pfe_pwa_dismissed_v2');
            if (dismissedAt) {
                const diffDays = (Date.now() - parseInt(dismissedAt, 10)) / (1000 * 60 * 60 * 24);
                if (diffDays < 7) {
                    this.isDismissed = true;
                }
            }

            // Register Service Worker on Desktop
            if ('serviceWorker' in navigator) {
                const registerSW = () => {
                    navigator.serviceWorker.register('/sw.js').catch(() => {});
                };
                if (document.readyState === 'complete') {
                    registerSW();
                } else {
                    window.addEventListener('load', registerSW);
                }
            }

            // Hook up deferred prompt if captured already
            if (window.__pfeDeferredPrompt) {
                this.deferredPrompt = window.__pfeDeferredPrompt;
            }

            // Listen for native beforeinstallprompt event if triggered later
            window.addEventListener('beforeinstallprompt', (e) => {
                e.preventDefault();
                this.deferredPrompt = e;
                if (!this.isDismissed && this.isDesktop && !this.isInstalled) {
                    this.canInstall = true;
                }
            });

            window.addEventListener('pfe:beforeinstallprompt', () => {
                if (window.__pfeDeferredPrompt) {
                    this.deferredPrompt = window.__pfeDeferredPrompt;
                    if (!this.isDismissed && this.isDesktop && !this.isInstalled) {
                        this.canInstall = true;
                    }
                }
            });

            // Listen for manual trigger (from header button)
            window.addEventListener('pfe:trigger-install', () => {
                this.isDismissed = false;
                this.canInstall = true;
                this.install();
            });

            // Listen for successful install event (from browser)
            window.addEventListener('appinstalled', () => {
                this.cleanupTimers();
                this.canInstall = false;
                this.isInstalled = true;
                this.deferredPrompt = null;
                localStorage.setItem('pfe_pwa_installed', 'true');
            });

            // Auto show banner on desktop
            this.scheduleShow();
        },

        scheduleShow() {
            if (this.isDismissed || !this.isDesktop || this.isInstalled) {
                return;
            }
            // Delay 1.5s so dashboard content renders smoothly first
            setTimeout(() => {
                if (this.isDismissed || this.isInstalled) return;
                this.canInstall = true;
                this.startCountdown(15);
            }, 1500);
        },

        startCountdown(seconds = 15) {
            this.cleanupTimers();
            this.totalSeconds = seconds;
            this.remainingSeconds = seconds;
            this.isPaused = false;

            this.countdownInterval = setInterval(() => {
                if (this.isPaused) return;

                this.remainingSeconds = Math.max(0, this.remainingSeconds - 0.1);
                if (this.remainingSeconds <= 0) {
                    this.cleanupTimers();
                    this.dismiss(true);
                }
            }, 100);
        },

        pauseTimer() {
            this.isPaused = true;
        },

        resumeTimer() {
            this.isPaused = false;
        },

        cleanupTimers() {
            if (this.countdownInterval) {
                clearInterval(this.countdownInterval);
                this.countdownInterval = null;
            }
        },

        async install() {
            this.cleanupTimers();

            const promptEvent = this.deferredPrompt || window.__pfeDeferredPrompt;

            if (promptEvent) {
                // Native one-click browser prompt directly
                try {
                    promptEvent.prompt();
                    const choiceResult = await promptEvent.userChoice;
                    if (choiceResult && choiceResult.outcome === 'accepted') {
                        this.canInstall = false;
                        this.isInstalled = true;
                        localStorage.setItem('pfe_pwa_installed', 'true');
                    }
                    this.deferredPrompt = null;
                    window.__pfeDeferredPrompt = null;
                } catch (e) {
                    console.error('[PFE PWA] Prompt failed:', e);
                }
                return;
            }

            // Fallback: If browser (Chrome) held beforeinstallprompt due to security flag on trycloudflare URL
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Pemasangan Otomatis Ditahan Browser',
                    html: `
                        <div class="text-left text-sm text-slate-600 space-y-3 pt-2">
                            <p>Google Chrome menahan pop-up otomatis karena domain trycloudflare acak ditandai <em>"Berbahaya"</em> oleh Safe Browsing.</p>
                            <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-amber-800 text-xs font-medium space-y-1.5">
                                <p><strong>2 Solusi Cepat:</strong></p>
                                <p>1. Buka melalui <strong>http://localhost:8001</strong> (bebas peringatan, 1-klik otomatis langsung muncul!).</p>
                                <p>2. Atau di URL ini, klik menu titik tiga (<strong>⋮</strong>) Chrome di pojok kanan atas &rarr; pilih <strong>"Install PFE Monitoring Control System"</strong>.</p>
                            </div>
                        </div>
                    `,
                    icon: 'warning',
                    confirmButtonColor: '#009B77',
                    confirmButtonText: 'Buka via Localhost',
                    showCancelButton: true,
                    cancelButtonText: 'Tutup',
                    customClass: {
                        popup: 'rounded-3xl shadow-2xl border border-slate-100',
                        title: 'text-slate-800 font-bold',
                        confirmButton: 'rounded-xl font-bold px-6 py-2.5',
                        cancelButton: 'rounded-xl font-bold px-6 py-2.5'
                    }
                }).then((res) => {
                    if (res.isConfirmed) {
                        window.location.href = 'http://localhost:8001/dashboard';
                    }
                });
            }
        },

        dismiss(isAuto = false) {
            this.cleanupTimers();
            this.canInstall = false;
            this.isDismissed = true;

            if (isAuto) {
                sessionStorage.setItem('pfe_pwa_autohidden', 'true');
            } else {
                localStorage.setItem('pfe_pwa_dismissed_v2', Date.now().toString());
            }
        }
    };
}
</script>
