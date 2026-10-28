{{-- PWA Install Handler (No auto popup; triggered only via Navbar Install button) --}}
<script>
(function() {
    let deferredPrompt = window.__pfeDeferredPrompt || null;
    let isInstalled = false;

    if (window.__isPFEInstalled && window.__isPFEInstalled()) {
        isInstalled = true;
    }

    // Register Service Worker
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

    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        window.__pfeDeferredPrompt = e;
    });

    window.addEventListener('pfe:deferred-prompt-ready', () => {
        if (window.__pfeDeferredPrompt) {
            deferredPrompt = window.__pfeDeferredPrompt;
        }
    });

    window.addEventListener('appinstalled', () => {
        isInstalled = true;
        deferredPrompt = null;
        window.__pfeDeferredPrompt = null;
        localStorage.setItem('pfe_pwa_installed', 'true');
        window.dispatchEvent(new CustomEvent('pfe:installed'));
    });

    async function triggerPfeInstall() {
        const promptEvent = deferredPrompt || window.__pfeDeferredPrompt;

        if (promptEvent) {
            try {
                promptEvent.prompt();
                const choiceResult = await promptEvent.userChoice;
                if (choiceResult && choiceResult.outcome === 'accepted') {
                    isInstalled = true;
                    localStorage.setItem('pfe_pwa_installed', 'true');
                    window.dispatchEvent(new CustomEvent('pfe:installed'));
                }
                deferredPrompt = null;
                window.__pfeDeferredPrompt = null;
            } catch (e) {
                console.error('[PFE PWA] Prompt failed:', e);
            }
            return;
        }

        // Fallback instructions if native prompt is not available
        const isIOS = /iPhone|iPad|iPod/i.test(navigator.userAgent);
        const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

        let guideHtml = '';
        if (isIOS) {
            guideHtml = `
                <div class="text-left text-sm text-slate-600 space-y-2.5 pt-1">
                    <p>Pasang aplikasi ke layar utama iPhone/iPad Anda:</p>
                    <div class="p-3 bg-teal-50 rounded-xl border border-teal-200 text-teal-900 text-xs font-medium space-y-1.5">
                        <p>1. Ketuk tombol <strong>Share</strong> (ikon kotak panah ke atas) di bilah bawah Safari.</p>
                        <p>2. Gulir ke bawah lalu pilih <strong>"Tambahkan ke Layar Utama" (Add to Home Screen)</strong>.</p>
                    </div>
                </div>
            `;
        } else if (isMobile) {
            guideHtml = `
                <div class="text-left text-sm text-slate-600 space-y-2.5 pt-1">
                    <p>Pasang aplikasi (APK) langsung ke HP Android Anda:</p>
                    <div class="p-3 bg-teal-50 rounded-xl border border-teal-200 text-teal-900 text-xs font-medium space-y-1.5">
                        <p>1. Ketuk menu titik tiga (<strong>⋮</strong>) di pojok kanan atas browser Chrome.</p>
                        <p>2. Pilih menu <strong>"Install aplikasi"</strong> atau <strong>"Tambahkan ke Layar Utama"</strong>.</p>
                    </div>
                </div>
            `;
        } else {
            guideHtml = `
                <div class="text-left text-sm text-slate-600 space-y-2.5 pt-1">
                    <p>Pasang aplikasi ke Desktop komputer Anda:</p>
                    <div class="p-3 bg-teal-50 rounded-xl border border-teal-200 text-teal-900 text-xs font-medium space-y-1.5">
                        <p>1. Klik ikon <strong>Install</strong> di pojok kanan bilah alamat URL (Address Bar) Chrome/Edge.</p>
                        <p>2. Atau klik menu titik tiga (<strong>⋮</strong>) browser &rarr; pilih <strong>"Install PFE Monitoring Control System"</strong>.</p>
                    </div>
                </div>
            `;
        }

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Petunjuk Pasang Aplikasi',
                html: guideHtml,
                icon: 'info',
                confirmButtonColor: '#009B77',
                confirmButtonText: 'Saya Mengerti',
                customClass: {
                    popup: 'rounded-3xl shadow-2xl border border-slate-100',
                    title: 'text-slate-800 font-bold',
                    confirmButton: 'rounded-xl font-bold px-6 py-2.5'
                }
            });
        }
    }

    window.addEventListener('pfe:trigger-install', triggerPfeInstall);
})();
</script>
