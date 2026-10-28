<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ __('Inspection Completed') }}</title>
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
            -webkit-tap-highlight-color: transparent;
        }
        
        [x-cloak] { display: none !important; }
        
        #reader {
            width: 100%;
            border-radius: 1rem;
            overflow: hidden;
            border: none;
        }
        #reader video {
            object-fit: cover;
            border-radius: 1rem;
        }
    </style>
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <!-- AlpineJS -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- HTML5 QR Code -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Block Back Button and Handle Scanner -->
    <script>
        history.pushState({ page: 'sukses' }, null, location.href);
        window.addEventListener('popstate', function (event) {
            if (typeof window.isScannerOpen === 'function' && window.isScannerOpen()) {
                // Jika scanner aktif, matikan scanner saja
                if (typeof window.stopScannerGlobal === 'function') {
                    window.stopScannerGlobal();
                }
                // Push state kembali agar tetap di halaman sukses
                history.pushState({ page: 'sukses' }, null, location.href);
            } else {
                // Jika tidak aktif, blokir tombol back sama sekali (tetap di halaman sukses)
                history.go(1);
            }
        });
    </script>
</head>
<body class="bg-[#F0F0F0] text-[#1A1A1A] relative min-h-[100dvh] w-full flex flex-col items-center justify-center overflow-x-hidden overflow-y-auto">

    <!-- Decorative Background Elements -->
    <div class="absolute top-[-10%] left-[-10%] w-[60%] h-[40%] bg-[#009B77] rounded-full mix-blend-multiply filter blur-[100px] opacity-20 pointer-events-none"></div>
    <div class="absolute bottom-10 right-[-10%] w-[50%] h-[40%] bg-[#8A4B9F] rounded-full mix-blend-multiply filter blur-[100px] opacity-15 pointer-events-none"></div>

    <div class="w-full min-h-[100dvh] max-w-md mx-auto relative z-10 flex flex-col items-center justify-center p-6" x-data="scannerApp()">
        
        @if(session('error'))
        <div class="bg-red-50/90 w-full backdrop-blur-md border border-red-100 p-4 rounded-xl mb-4 shadow-sm flex items-center justify-center gap-2">
            <i class="ph-fill ph-warning-circle text-red-500 text-xl shrink-0"></i>
            <p class="text-sm font-medium text-red-700 leading-tight text-center">{{ session('error') }}</p>
        </div>
        @endif

        <div x-show="!scanning" class="bg-white w-full rounded-3xl shadow-xl p-8 flex flex-col items-center text-center border border-slate-100"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            
            @if(request('restricted'))
            <div class="w-20 h-20 bg-amber-50 rounded-full flex items-center justify-center mb-6">
                <i class="ph-fill ph-lock-key text-5xl text-amber-500"></i>
            </div>
            
            <h1 class="text-2xl font-bold text-slate-800 mb-2">{{ __('Session Locked') }}</h1>
            <p class="text-sm font-medium text-slate-500 mb-8">{{ __('For security reasons, you cannot access the main system without a full login. Please scan another PFE to continue inspecting.') }}</p>
            @else
            <div class="w-20 h-20 bg-teal-50 rounded-full flex items-center justify-center mb-6">
                <i class="ph-fill ph-check-circle text-5xl text-[#009B77]"></i>
            </div>
            
            <h1 class="text-2xl font-bold text-slate-800 mb-2">{{ __('Inspection Completed!') }}</h1>
            <p class="text-sm font-medium text-slate-500 mb-8">{{ __('Inspection data for PFE') }} <span class="font-bold text-slate-800">{{ $apar->kode }}</span> {{ __('has been successfully saved.') }}</p>
            @endif
            
            @if(request('source') === 'schedule')
                @if($nextApar)
                <a href="{{ route('inspeksi.pedoman', ['apar' => $nextApar->id, 'source' => 'schedule']) }}" class="w-full py-4 bg-[#009B77] hover:bg-[#008264] text-white rounded-xl font-bold text-[15px] shadow-lg shadow-[#009B77]/30 transition-all flex items-center justify-center gap-2 mb-3">
                    <i class="ph-bold ph-arrow-right text-xl"></i> {{ __('Inspect Next PFE') }} ({{ $nextApar->kode }})
                </a>
                @endif
                
                <form x-ref="searchForm" action="{{ route('inspeksi.search') }}" method="GET" class="w-full mb-3"
                      x-data="{
                          searchQuery: '{{ old('kode') }}',
                          apars: {{ json_encode($allApars ?? []) }},
                          isOpen: false,
                          get filteredApars() {
                              if(this.searchQuery.trim() === '') return [];
                              let q = this.searchQuery.toLowerCase();
                              return this.apars.filter(i => 
                                  i.kode.toLowerCase().includes(q) || 
                                  i.lokasi.toLowerCase().includes(q) || 
                                  i.gedung.toLowerCase().includes(q)
                              ).slice(0, 5);
                          },
                          selectApar(kode) {
                              this.searchQuery = kode;
                              this.isOpen = false;
                              this.$nextTick(() => {
                                this.$refs.searchForm.submit();
                              });
                          }
                      }">
                    <input type="hidden" name="source" value="schedule">
                    <div class="relative">
                        <input type="text" name="kode" placeholder="{{ __('Or search PFE ID...') }}" required autocomplete="off"
                               x-model="searchQuery"
                               @focus="isOpen = true"
                               @click.away="isOpen = false"
                               class="w-full py-3 px-4 bg-slate-50 border-2 border-slate-200 rounded-xl font-medium text-sm focus:outline-none focus:border-[#009B77] focus:ring-2 focus:ring-[#009B77]/20 transition-all">
                        <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 p-2 bg-[#009B77] text-white rounded-lg hover:bg-[#008264] transition-colors">
                            <i class="ph-bold ph-magnifying-glass"></i>
                        </button>
                        
                        <!-- Autocomplete Dropdown -->
                        <div x-show="isOpen && filteredApars.length > 0" x-cloak
                             x-transition
                             class="absolute z-50 w-full bg-white mt-1 rounded-xl shadow-lg border border-slate-100 max-h-60 overflow-y-auto">
                            <template x-for="apar in filteredApars" :key="apar.kode">
                                <div @click="selectApar(apar.kode)" class="px-4 py-3 hover:bg-slate-50 cursor-pointer border-b border-slate-50 last:border-0 text-left">
                                    <div class="font-bold text-sm text-[#009B77]" x-text="apar.kode"></div>
                                    <div class="text-[10px] text-slate-500 font-medium mt-0.5" x-text="apar.gedung + ' - ' + apar.lokasi"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                    @error('kode')
                        <p class="text-red-500 text-xs font-medium mt-1 text-left">{{ $message }}</p>
                    @enderror
                </form>

                <a href="/inspection-schedule" class="w-full py-3 bg-white hover:bg-slate-50 text-slate-600 rounded-xl font-bold text-[15px] shadow-sm ring-1 ring-inset ring-slate-300 transition-all flex items-center justify-center gap-2">
                    <i class="ph-bold ph-calendar-check text-xl"></i> {{ __('Back to Schedule') }}
                </a>
            @else
                <button @click="startScanner()" class="w-full py-4 bg-[#009B77] hover:bg-[#008264] text-white rounded-xl font-bold text-[15px] shadow-lg shadow-[#009B77]/30 transition-all flex items-center justify-center gap-2">
                    <i class="ph-bold ph-scan text-xl"></i> {{ __('Scan Another PFE') }}
                </button>

                <a href="{{ route('scan.apar', $apar->kode) }}" class="w-full py-2.5 bg-transparent hover:bg-slate-100 text-slate-500 hover:text-[#009B77] rounded-xl font-bold text-xs transition-all flex items-center justify-center gap-1.5 mt-2">
                    <i class="ph-bold ph-arrow-left text-sm"></i> {{ __('Selesai & Kembali ke Detail APAR') }}
                </a>
            @endif
        </div>

        <!-- Scanner View -->
        <div x-show="scanning" class="fixed inset-0 z-50 bg-black flex flex-col" x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-full"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-full">
            
            <!-- Header Overlay -->
            <div class="absolute top-0 inset-x-0 z-20 p-6 flex items-center justify-between bg-gradient-to-b from-black/60 to-transparent">
                <h2 class="font-bold text-white text-lg flex items-center gap-2 drop-shadow-md">
                    <i class="ph-bold ph-scan"></i> {{ __('Scan QR Code') }}
                </h2>
                <button @click="stopScanner()" class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-md text-white flex items-center justify-center hover:bg-white/30 transition-all">
                    <i class="ph-bold ph-x text-lg"></i>
                </button>
            </div>
            
            <!-- Camera Viewport -->
            <div class="flex-1 relative overflow-hidden bg-black flex items-center justify-center">
                <div id="reader" class="w-full h-full object-cover"></div>
                
                <!-- Scanning Guidelines Overlay -->
                <div class="absolute inset-0 pointer-events-none z-10 flex flex-col items-center justify-center">
                    <!-- Overlay Mask (Dimming edges) -->
                    <div class="absolute inset-0 border-[60px] border-black/40"></div>
                    
                    <!-- Scanner Frame -->
                    <div class="relative w-64 h-64 border-2 border-white/20 rounded-xl overflow-hidden shadow-[0_0_0_4000px_rgba(0,0,0,0.5)]">
                        <!-- Corner brackets -->
                        <div class="absolute top-0 left-0 w-8 h-8 border-t-4 border-l-4 border-[#009B77] rounded-tl-xl"></div>
                        <div class="absolute top-0 right-0 w-8 h-8 border-t-4 border-r-4 border-[#009B77] rounded-tr-xl"></div>
                        <div class="absolute bottom-0 left-0 w-8 h-8 border-b-4 border-l-4 border-[#009B77] rounded-bl-xl"></div>
                        <div class="absolute bottom-0 right-0 w-8 h-8 border-b-4 border-r-4 border-[#009B77] rounded-br-xl"></div>
                        
                        <!-- Scanning line animation -->
                        <div class="absolute top-0 inset-x-0 h-0.5 bg-[#009B77] shadow-[0_0_15px_3px_rgba(0,155,119,0.7)] w-full animate-[scan_2s_ease-in-out_infinite]"></div>
                    </div>
                    
                    <p class="text-white font-medium text-sm mt-8 drop-shadow-md z-20">{{ __('Point camera at PFE QR Code') }}</p>
                </div>
            </div>
        </div>

        <style>
            @keyframes scan {
                0%, 100% { top: 0%; opacity: 0; }
                10%, 90% { opacity: 1; }
                50% { top: 100%; opacity: 1; }
            }
            #reader { border: none !important; }
            #reader video { object-fit: cover !important; width: 100% !important; height: 100% !important; border-radius: 0 !important; }
            #reader__dashboard_section_csr { display: none !important; }
        </style>

    </div>

    <script>
        function scannerApp() {
            return {
                scanning: {{ request('restricted') ? 'true' : 'false' }},
                html5QrcodeScanner: null,
                
                init() {
                    window.stopScannerGlobal = () => { this.stopScanner(); };
                    window.isScannerOpen = () => { return this.scanning; };
                    
                    if (this.scanning) {
                        this.startScanner();
                    }
                },
                
                startScanner() {
                    this.scanning = true;
                    
                    setTimeout(() => {
                        if (!this.html5QrcodeScanner) {
                            this.html5QrcodeScanner = new Html5Qrcode("reader");
                        }
                        
                        const config = { 
                            fps: 15, 
                            aspectRatio: 1.0,
                            qrbox: (viewfinderWidth, viewfinderHeight) => {
                                const minEdge = Math.min(viewfinderWidth, viewfinderHeight);
                                const qrboxSize = Math.floor(minEdge * 0.65);
                                return { width: Math.max(180, Math.min(250, qrboxSize)), height: Math.max(180, Math.min(250, qrboxSize)) };
                            }
                        };
                        
                        this.html5QrcodeScanner.start(
                            { facingMode: "environment" },
                            config,
                            (decodedText, decodedResult) => {
                                // Cegah scan APAR yang sama secara tidak sengaja karena terlalu cepat
                                if (decodedText.includes('{{ $apar->kode }}')) {
                                    this.html5QrcodeScanner.pause(true);
                                    Swal.fire({
                                        title: 'APAR Sama Terdeteksi!',
                                        text: 'Anda baru saja menscan APAR {{ $apar->kode }} yang baru selesai diinspeksi. Apakah Anda ingin menginspeksinya lagi?',
                                        icon: 'warning',
                                        showCancelButton: true,
                                        showDenyButton: true,
                                        confirmButtonText: 'Ya, Inspeksi Lagi',
                                        denyButtonText: 'Lanjut Scan Lain',
                                        cancelButtonText: 'Tutup Kamera',
                                        confirmButtonColor: '#009B77',
                                        denyButtonColor: '#3b82f6',
                                        customClass: {
                                            popup: 'rounded-2xl',
                                            confirmButton: 'rounded-lg font-bold',
                                            denyButton: 'rounded-lg font-bold',
                                            cancelButton: 'rounded-lg font-bold'
                                        }
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            try {
                                                let url = new URL(decodedText);
                                                url.protocol = window.location.protocol;
                                                url.host = window.location.host;
                                                @if(request('source') === 'schedule')
                                                url.searchParams.set('source', 'schedule');
                                                @else
                                                url.searchParams.delete('source');
                                                @endif
                                                window.location.href = url.toString();
                                            } catch (e) {
                                                window.location.href = decodedText;
                                            }
                                        } else if (result.isDenied) {
                                            setTimeout(() => {
                                                if (this.html5QrcodeScanner) {
                                                    this.html5QrcodeScanner.resume();
                                                }
                                            }, 300);
                                        } else {
                                            this.stopScanner();
                                        }
                                    });
                                    return; // Hentikan eksekusi selanjutnya
                                }

                                // Jika berhasil dan itu APAR berbeda, hentikan kamera dan redirect
                                let targetUrl = decodedText;
                                try {
                                    let url = new URL(decodedText);
                                    url.protocol = window.location.protocol;
                                    url.host = window.location.host;
                                    @if(request('source') === 'schedule')
                                    url.searchParams.set('source', 'schedule');
                                    @else
                                    url.searchParams.delete('source');
                                    @endif
                                    targetUrl = url.toString();
                                } catch (e) {
                                    // if decodedText is not a valid URL
                                }

                                this.html5QrcodeScanner.stop().then(() => {
                                    window.location.href = targetUrl;
                                }).catch((err) => {
                                    window.location.href = targetUrl;
                                });
                            },
                            (errorMessage) => {
                                // ignore error
                            }
                        ).catch((err) => {
                            alert("{{ __('Camera cannot be accessed or is blocked. Make sure to grant camera permissions.') }}");
                            this.scanning = false;
                        });
                    }, 300);
                },

                stopScanner() {
                    if (this.html5QrcodeScanner) {
                        this.html5QrcodeScanner.stop().then(() => {
                            this.scanning = false;
                        }).catch(err => console.error("Error stopping scanner", err));
                    } else {
                        this.scanning = false;
                    }
                }
            }
        }
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
