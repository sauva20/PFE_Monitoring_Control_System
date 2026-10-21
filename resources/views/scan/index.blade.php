<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ __('PFE Information') }} - {{ $apar->kode }}</title>
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
    </style>
        <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <!-- AlpineJS -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Flatpickr (for date picker) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <style>
        .flatpickr-calendar { font-family: 'Rotis Sans Serif', sans-serif !important; }
        .flatpickr-wrapper { display: block !important; width: 100% !important; }
    </style>
</head>
<body class="bg-[#F0F0F0] text-[#1A1A1A] relative h-[100dvh] w-screen overflow-hidden flex flex-col">

    <!-- Decorative Background Elements -->
    <div class="absolute top-[-10%] left-[-10%] w-[60%] h-[40%] bg-[#009B77] rounded-full mix-blend-multiply filter blur-[100px] opacity-20 pointer-events-none"></div>
    <div class="absolute bottom-10 right-[-10%] w-[50%] h-[40%] bg-[#8A4B9F] rounded-full mix-blend-multiply filter blur-[100px] opacity-15 pointer-events-none"></div>

    <div class="w-full h-full max-w-md mx-auto relative z-10 flex flex-col p-4 overflow-hidden" x-data="{ showPinModal: false, showImagePreview: false, pinAction: 'inspeksi', showModalEditApar: {{ request('edit') == 1 && !session('success') ? 'true' : 'false' }}, editApar: {
        id: {{ $apar->id }},
        kode: '{{ addslashes($apar->kode) }}',
        nomor_apar: '{{ addslashes($apar->kode) }}'.match(/\d+$/) ? '{{ addslashes($apar->kode) }}'.match(/\d+$/)[0] : '',
        gedung_id: '{{ $apar->lokasi->gedung_id ?? '' }}',
        lokasi: '{{ addslashes($apar->lokasi->nama ?? '') }}',
        jenis_id: '{{ $apar->jenis_id }}',
        kapasitas_id: '{{ $apar->kapasitas_id }}',
        vendor: '{{ addslashes($apar->vendor) }}',
        tgl_kedaluwarsa: '{{ $apar->tgl_kedaluwarsa ? $apar->tgl_kedaluwarsa->format('Y-m-d') : '' }}'
    } }">
        
        <!-- Top Bar for Language Switcher & Officer Badge -->
        <div class="flex {{ Auth::check() ? 'justify-between' : 'justify-end' }} items-center mb-3 shrink-0">
            @if(Auth::check())
            <div class="flex items-center gap-1.5 bg-white/80 backdrop-blur-md px-2.5 py-1 rounded-lg border border-emerald-200/60 shadow-sm text-slate-700">
                <i class="ph-bold ph-user-check text-[#009B77] text-xs"></i>
                <span class="text-[10px] font-bold text-slate-800">{{ Auth::user()->name }}</span>
                <span class="text-[9px] px-1 py-0.2 bg-emerald-50 text-[#009B77] font-semibold rounded">{{ __('Aktif') }}</span>
            </div>
            @endif
            <div class="flex items-center gap-1 bg-white/50 backdrop-blur-md p-1 rounded-lg border border-slate-200/50 shadow-sm">
                <a href="{{ route('set-locale', 'id') }}" class="text-[10px] font-bold px-2 py-1 rounded {{ app()->getLocale() == 'id' ? 'bg-[#009B77] text-white' : 'bg-transparent text-slate-500 hover:text-slate-700' }} transition-colors">ID</a>
                <a href="{{ route('set-locale', 'en') }}" class="text-[10px] font-bold px-2 py-1 rounded {{ app()->getLocale() == 'en' ? 'bg-[#009B77] text-white' : 'bg-transparent text-slate-500 hover:text-slate-700' }} transition-colors">EN</a>
            </div>
        </div>

        <!-- Header / Photo Section -->
        <div class="bg-white rounded-[24px] shadow-[0_10px_30px_-15px_rgba(0,155,119,0.15)] overflow-hidden border border-slate-100 mb-4 shrink-0 relative group">
            
            @if($apar->foto)
            <div class="w-full h-56 relative overflow-hidden bg-slate-900 cursor-pointer group/photo" @click="showImagePreview = true" title="{{ __('Klik untuk memperbesar foto APAR & watermark') }}">
                <img src="{{ Storage::url($apar->foto) }}" alt="Foto APAR {{ $apar->kode }}" class="w-full h-full object-cover transition-transform duration-300 group-hover/photo:scale-105">
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent pointer-events-none"></div>
                
                <!-- Badge & Hint Klik Perbesar di Pojok Kiri Atas Foto -->
                <div class="absolute top-3 left-3 bg-black/60 hover:bg-black/80 backdrop-blur-md border border-white/20 text-white text-[10px] font-bold px-2.5 py-1 rounded-lg flex items-center gap-1.5 shadow-md transition-all">
                    <i class="bi bi-arrows-fullscreen text-[11px] text-[#009B77]"></i>
                    <span>{{ __('Klik Foto untuk Perbesar') }}</span>
                </div>
            </div>
            <div class="absolute bottom-4 left-5 right-5 pointer-events-none">
                <div class="flex justify-between items-end">
                    <div>
                        <p class="text-white/80 text-[10px] font-bold tracking-widest uppercase mb-0.5 drop-shadow-md">{{ __('PFE ID') }}</p>
                        <h1 class="text-2xl font-bold text-white drop-shadow-md tracking-tight">{{ $apar->kode }}</h1>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white border border-white/30 shadow-lg pointer-events-auto cursor-pointer hover:bg-white/30 transition-colors" @click="showImagePreview = true" title="{{ __('Lihat Foto Ukuran Penuh') }}">
                        <i class="bi bi-zoom-in text-lg"></i>
                    </div>
                </div>
            </div>
            @else
            <div class="w-full h-56 bg-gradient-to-br from-[#009B77] to-[#007b5e] relative overflow-hidden flex flex-col items-center justify-center">
                <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-3xl"></div>
                <div class="absolute -left-10 -bottom-10 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
                
                <div class="w-12 h-12 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white border border-white/30 shadow-lg mb-2 z-10">
                    <i class="ph-light ph-fire-extinguisher text-2xl"></i>
                </div>
                <h1 class="text-xl font-bold text-white tracking-wide z-10">{{ $apar->kode }}</h1>
            </div>
            @endif
        </div>

        @if(session('error'))
        <div class="bg-red-50/90 backdrop-blur-md border border-red-100 p-3 rounded-xl mb-4 shadow-sm flex items-center gap-2 shrink-0">
            <i class="ph-fill ph-warning-circle text-red-500 text-lg shrink-0"></i>
            <p class="text-xs font-medium text-red-700 leading-tight">{{ session('error') }}</p>
        </div>
        @endif

        @if(session('success'))
        <div class="bg-teal-50/90 backdrop-blur-md border border-teal-100 p-3 rounded-xl mb-4 shadow-sm flex items-center gap-2 shrink-0">
            <i class="ph-fill ph-check-circle text-teal-500 text-lg shrink-0"></i>
            <p class="text-xs font-medium text-teal-700 leading-tight">{{ session('success') }}</p>
        </div>
        @endif

        @if($errors->any())
        <div class="bg-red-50/90 backdrop-blur-md border border-red-100 p-3 rounded-xl mb-4 shadow-sm flex items-center gap-2 shrink-0">
            <i class="ph-fill ph-warning-circle text-red-500 text-lg shrink-0"></i>
            <div class="flex flex-col gap-1">
                @foreach ($errors->all() as $error)
                    <p class="text-xs font-medium text-red-700 leading-tight">{{ $error }}</p>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Master Data Cards -->
        <div class="bg-white rounded-[24px] shadow-[0_10px_30px_-15px_rgba(0,155,119,0.1)] border border-slate-100 p-5 flex flex-col flex-1 overflow-hidden">
            
            <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3 shrink-0">
                <div class="flex items-center gap-2">
                    <i class="ph-light ph-list-dashes text-[#009B77] text-lg"></i>
                    <h2 class="text-xs font-bold text-slate-800 uppercase tracking-widest">{{ __('Information Details') }}</h2>
                </div>
                <!-- Action Buttons -->
                <div class="flex items-center gap-2">
                    @if(Auth::check())
                    <a href="{{ route('inspeksi.pedoman', array_filter(['apar' => $apar->id, 'source' => (request('source') === 'schedule' ? 'schedule' : null)])) }}" class="bg-[#009B77] hover:bg-[#008264] text-white px-3.5 py-1.5 rounded-lg shadow-sm flex items-center gap-1.5 transition-colors font-bold">
                        <i class="ph-light ph-scan text-sm"></i>
                        <span class="text-[10px] font-bold uppercase tracking-widest">{{ __('Inspection') }}</span>
                    </a>
                    @else
                    <button @click="showPinModal = true; pinAction = 'inspeksi'" class="bg-red-500 hover:bg-red-600 text-white px-3.5 py-1.5 rounded-lg shadow-sm flex items-center gap-1.5 transition-colors font-bold">
                        <i class="ph-light ph-scan text-sm"></i>
                        <span class="text-[10px] font-bold uppercase tracking-widest">{{ __('Inspection') }}</span>
                    </button>
                    @endif
                </div>
            </div>
            
            <div class="space-y-4 overflow-y-auto pr-1 pb-2" style="scrollbar-width: none;">
                <!-- Lokasi -->
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-[#009B77]/10 text-[#009B77] border border-[#009B77] flex items-center justify-center shrink-0">
                        <i class="ph-light ph-map-pin text-lg"></i>
                    </div>
                    <div>
                        <p class="text-[9px] font-bold text-[#009B77] uppercase tracking-widest">{{ __('Location & Building') }}</p>
                        <p class="text-xs font-medium text-slate-800">{{ $apar->lokasi->nama ?? 'n/a' }} — {{ $apar->lokasi->gedung->nama ?? 'n/a' }}</p>
                    </div>
                </div>

                <!-- Jenis -->
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-[#009B77]/10 text-[#009B77] border border-[#009B77] flex items-center justify-center shrink-0">
                        <i class="ph-light ph-drop text-lg"></i>
                    </div>
                    <div>
                        <p class="text-[9px] font-bold text-[#009B77] uppercase tracking-widest">{{ __('Type / Media') }}</p>
                        <p class="text-xs font-medium text-slate-800">{{ $apar->jenis->nama ?? 'n/a' }}</p>
                    </div>
                </div>

                <!-- Kelas Kebakaran -->
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-[#009B77]/10 text-[#009B77] border border-[#009B77] flex items-center justify-center shrink-0">
                        <i class="ph-light ph-fire text-lg"></i>
                    </div>
                    <div>
                        <p class="text-[9px] font-bold text-[#009B77] uppercase tracking-widest">{{ __('Fire Class') }}</p>
                        @php
                            $jenis = strtolower($apar->jenis->nama ?? '');
                            $kelas = 'A, B, C'; // Default for Dry Powder / Halotron
                            if (str_contains($jenis, 'co2') || str_contains($jenis, 'carbon')) {
                                $kelas = 'B, C';
                            } elseif (str_contains($jenis, 'foam')) {
                                $kelas = 'A, B';
                            } elseif (str_contains($jenis, 'air') || str_contains($jenis, 'water')) {
                                $kelas = 'A';
                            }
                        @endphp
                        <p class="text-xs font-medium text-slate-800">{{ __('Class') }} {{ $kelas }}</p>
                    </div>
                </div>

                <!-- Kapasitas -->
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-[#009B77]/10 text-[#009B77] border border-[#009B77] flex items-center justify-center shrink-0">
                        <i class="ph-light ph-scales text-lg"></i>
                    </div>
                    <div>
                        <p class="text-[9px] font-bold text-[#009B77] uppercase tracking-widest">{{ __('Capacity') }}</p>
                        <p class="text-xs font-medium text-slate-800">{{ $apar->kapasitas->ukuran ?? 'n/a' }}</p>
                    </div>
                </div>

                <!-- Kedaluwarsa -->
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-[#009B77]/10 text-[#009B77] border border-[#009B77] flex items-center justify-center shrink-0">
                        <i class="ph-light ph-calendar-blank text-lg"></i>
                    </div>
                    <div>
                        <p class="text-[9px] font-bold text-[#009B77] uppercase tracking-widest">{{ __('Expiry Date') }}</p>
                        <p class="text-xs font-medium text-slate-800">{{ $apar->tgl_kedaluwarsa ? \Carbon\Carbon::parse($apar->tgl_kedaluwarsa)->format('d F Y') : '-' }}</p>
                    </div>
                </div>

                <!-- Inspeksi Terakhir -->
                <div class="flex items-start gap-3 pt-1">
                    <div class="w-8 h-8 rounded-lg bg-[#009B77]/10 text-[#009B77] border border-[#009B77] flex items-center justify-center shrink-0">
                        <i class="ph-light ph-clock-counter-clockwise text-lg"></i>
                    </div>
                    <div>
                        <p class="text-[9px] font-bold text-[#009B77] uppercase tracking-widest mb-0.5">{{ __('Last Inspection') }}</p>
                        @if($latestInspeksi)
                            <p class="text-xs font-medium text-slate-800 leading-tight">
                                {{ $latestInspeksi->created_at->format('d M Y') }} {{ __('by') }} <span class="font-bold">{{ $latestInspeksi->user->name ?? 'User' }}</span>
                                <span class="ml-1 inline-flex items-center text-[8px] px-1.5 py-0.5 rounded {{ $latestInspeksi->status == 'layak' ? 'bg-teal-50 text-teal-600' : 'bg-red-50 text-red-600' }} uppercase font-bold">{{ $latestInspeksi->status }}</span>
                            </p>
                            @if(!empty($latestInspeksi->catatan_tambahan))
                                <p class="mt-1 text-[10px] text-slate-500 italic bg-slate-50 p-1.5 rounded border border-slate-100">
                                    "{{ $latestInspeksi->catatan_tambahan }}"
                                </p>
                            @endif
                        @else
                            <p class="text-xs font-medium text-slate-400 italic">{{ __('Never been inspected') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

                <!-- Modal Lightbox Pratinjau Foto APAR -->
        @if($apar->foto)
        <div x-show="showImagePreview" 
             class="fixed inset-0 z-[120] flex items-center justify-center p-3 sm:p-6 bg-black/90 backdrop-blur-md" 
             x-cloak 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             @keydown.escape.window="showImagePreview = false">
             
            <div class="relative max-w-4xl w-full bg-slate-900 rounded-2xl overflow-hidden shadow-2xl border border-white/10 flex flex-col max-h-[92vh]" @click.outside="showImagePreview = false">
                <!-- Header Modal -->
                <div class="flex items-center justify-between px-4 py-3 bg-slate-950/90 border-b border-white/10 text-white z-10">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg bg-[#009B77]/20 border border-[#009B77]/40 flex items-center justify-center text-[#009B77]">
                            <i class="bi bi-shield-check text-sm"></i>
                        </div>
                        <div>
                            <h3 class="text-xs sm:text-sm font-bold leading-tight">{{ $apar->kode }} • {{ __('Foto Kondisi Terkini') }}</h3>
                            <p class="text-[10px] text-slate-400 leading-none mt-0.5">{{ $apar->lokasi->gedung->nama ?? '' }} - {{ $apar->lokasi->nama ?? '' }}</p>
                        </div>
                    </div>
                    <button type="button" @click="showImagePreview = false" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/25 flex items-center justify-center text-white transition-colors cursor-pointer">
                        <i class="bi bi-x-lg text-sm"></i>
                    </button>
                </div>

                <!-- Body Gambar -->
                <div class="flex-1 overflow-auto bg-black flex items-center justify-center p-2 sm:p-4">
                    <img src="{{ Storage::url($apar->foto) }}" alt="Foto APAR {{ $apar->kode }}" class="max-w-full max-h-[75vh] object-contain rounded-lg shadow-lg">
                </div>

                <!-- Footer Info -->
                <div class="px-4 py-2.5 bg-slate-950/90 border-t border-white/10 flex items-center justify-between text-[11px] text-slate-300">
                    <div class="flex items-center gap-2">
                        <i class="bi bi-info-circle text-[#009B77]"></i>
                        <span class="text-slate-400">{{ __('Foto bukti fisik APAR Realtime GPS location') }}</span>
                    </div>
                    <button type="button" @click="showImagePreview = false" class="px-4 py-1.5 rounded-lg bg-[#009B77] hover:bg-[#008264] text-white font-bold text-xs transition-colors cursor-pointer">
                        {{ __('Tutup') }}
                    </button>
                </div>
            </div>
        </div>
        @endif

        <!-- PIN Modal -->
        <div x-show="showPinModal" class="fixed inset-0 z-[100] flex items-center justify-center pointer-events-auto px-4" x-cloak>
            <div x-show="showPinModal" class="fixed inset-0 bg-slate-900/60" @click="showPinModal = false"></div>
            
            <div x-show="showPinModal" class="relative w-full max-w-sm bg-white rounded-3xl shadow-2xl overflow-hidden p-6 border border-slate-100">
                
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h3 class="text-base font-bold text-slate-800 tracking-tight">{{ __('Officer Verification') }}</h3>
                        <p class="text-[10px] font-medium text-slate-500 mt-0.5">{{ __('Enter 4 digit PIN to start inspection.') }}</p>
                    </div>
                    <button @click="showPinModal = false" class="w-7 h-7 flex items-center justify-center text-slate-400 hover:text-slate-600 bg-slate-50 hover:bg-slate-100 border border-slate-100 rounded-full transition-colors">
                        <i class="ph-light ph-x"></i>
                    </button>
                </div>
                
                <form action="{{ route('scan.verify', $apar->kode) }}" method="POST">
                    @csrf
                    <input type="hidden" name="action" :value="pinAction">
                    <div class="mb-5">
                        <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">{{ __('Officer PIN') }}</label>
                        <div class="relative" x-data="{ show: false }">
                            <input :type="show ? 'text' : 'password'" name="pin" maxlength="4" inputmode="numeric" pattern="[0-9]{4}" required 
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 pr-12 text-center tracking-[0.5em] text-xl font-bold text-slate-800 focus:bg-white focus:border-[#009B77] focus:ring-2 focus:ring-[#009B77]/20 transition-all outline-none" 
                                placeholder="••••">
                            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-[#009B77] transition-colors focus:outline-none">
                                <i class="ph-fill text-lg" :class="show ? 'ph-eye-slash' : 'ph-eye'"></i>
                            </button>
                        </div>
                    </div>
                    
                    <button type="submit" class="w-full bg-[#009B77] hover:bg-[#008264] text-white font-medium py-3 rounded-xl shadow-lg shadow-[#009B77]/30 transition-all text-xs flex items-center justify-center gap-2 uppercase tracking-widest active:scale-[0.98]">
                        <span>{{ __('Verify') }}</span>
                        <i class="ph-bold ph-arrow-right"></i>
                    </button>
                </form>
            </div>
        </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            flatpickr(".datepicker", {
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "d F Y",
                allowInput: true,
                disableMobile: true
            });
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
