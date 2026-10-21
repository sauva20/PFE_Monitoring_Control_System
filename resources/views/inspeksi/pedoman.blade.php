<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ __('Inspection Guidelines') }} - PFE Monitoring Control System</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
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
    </style>
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body class="bg-[#F0F0F0] text-[#1A1A1A] relative min-h-screen w-screen overflow-x-hidden flex flex-col pb-24">

    <!-- Decorative Background Elements -->
    <div class="fixed top-[-10%] left-[-10%] w-[60%] h-[40%] bg-[#009B77] rounded-full mix-blend-multiply filter blur-[100px] opacity-20 pointer-events-none"></div>
    <div class="fixed bottom-10 right-[-10%] w-[50%] h-[40%] bg-[#8A4B9F] rounded-full mix-blend-multiply filter blur-[100px] opacity-15 pointer-events-none"></div>


    <div class="w-full {{ request('source') === 'schedule' ? 'max-w-6xl' : 'max-w-xl' }} mx-auto relative z-10 flex flex-col p-4 sm:p-6 lg:p-8 space-y-6">
        
        <!-- Header Area -->
        <div class="flex items-center justify-between gap-4 {{ request('source') === 'schedule' ? 'bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-sm' : '' }}">
            <div class="flex items-center gap-3">
                <a href="{{ request('source') === 'schedule' ? url('/inspection-schedule') : route('scan.apar', $apar->kode) }}" class="px-3 py-2 rounded-xl bg-white border border-slate-200/80 shadow-sm flex items-center gap-2 text-slate-600 hover:text-[#009B77] hover:border-[#009B77] transition-all text-xs font-bold" title="{{ __('Back') }}">
                    <i class="ph-bold ph-arrow-left text-base"></i>
                    <span class="{{ request('source') === 'schedule' ? 'inline' : 'hidden sm:inline' }}">{{ __('Kembali') }}</span>
                </a>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h2 class="text-base sm:text-lg font-black text-slate-800 leading-tight">{{ __('Pedoman Inspeksi APAR') }}</h2>
                        @if(request('source') === 'schedule')
                        <span class="text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200 flex items-center gap-1">
                            <i class="bi bi-pc-display"></i> {{ __('Mode Sistem Desktop') }}
                        </span>
                        @endif
                    </div>
                    <p class="text-xs font-semibold text-slate-500 mt-0.5 hidden sm:block">{{ __('Poin-poin penting yang wajib dipedomani saat melakukan pemeriksaan APAR.') }}</p>
                </div>
            </div>
            
            <!-- Language Switcher -->
            <div class="flex items-center gap-1 bg-white p-1 rounded-xl border border-slate-200/80 shadow-sm shrink-0">
                <a href="{{ route('set-locale', 'id') }}" class="text-[11px] font-bold px-2.5 py-1 rounded-lg {{ app()->getLocale() == 'id' ? 'bg-[#009B77] text-white shadow-sm' : 'bg-transparent text-slate-400 hover:text-slate-600' }} transition-colors">ID</a>
                <a href="{{ route('set-locale', 'en') }}" class="text-[11px] font-bold px-2.5 py-1 rounded-lg {{ app()->getLocale() == 'en' ? 'bg-[#009B77] text-white shadow-sm' : 'bg-transparent text-slate-400 hover:text-slate-600' }} transition-colors">EN</a>
            </div>
        </div>

        <!-- Information Box -->
        <div class="bg-blue-50 border border-blue-200/80 rounded-2xl p-4 sm:p-5 flex items-start gap-3.5 shadow-sm">
            <div class="w-9 h-9 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-600 shrink-0">
                <i class="ph-fill ph-info text-xl"></i>
            </div>
            <div>
                <span class="block text-xs font-bold text-blue-900 uppercase tracking-wider mb-0.5">{{ __('Dasar Hukum & Ketentuan K3') }}</span>
                <p class="text-xs font-medium text-blue-800 leading-relaxed">
                    {{ __('PERATURAN MENTRI TENAGA KERJA DAN TRANSMIGRASI No : PER.04/MEN/1980 TENTANG SYARAT-SYARAT PEMASANGAN DAN PEMELIHARAAN ALAT PEMADAM API RINGAN') }}
                </p>
            </div>
        </div>

        <!-- Guidelines List -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-5 sm:p-6">
            <div class="{{ request('source') === 'schedule' ? 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4' : 'space-y-4' }}">

                <div class="flex gap-3 p-3.5 rounded-xl border border-slate-100 bg-slate-50/60 hover:bg-white hover:border-slate-200 transition-all">
                    <div class="w-7 h-7 rounded-full bg-[#009B77]/10 text-[#009B77] flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">
                        1
                    </div>
                    <div>
                        <h4 class="text-xs sm:text-sm font-bold text-slate-800 mb-0.5">{{ __('Ketersediaan') }}</h4>
                        <p class="text-[11px] text-slate-500 leading-relaxed">{{ __('Pastikan APAR tersedia di tempat yang mudah diakses dan terlihat dengan jelas.') }}</p>
                    </div>
                </div>

                <div class="flex gap-3 p-3.5 rounded-xl border border-slate-100 bg-slate-50/60 hover:bg-white hover:border-slate-200 transition-all">
                    <div class="w-7 h-7 rounded-full bg-[#009B77]/10 text-[#009B77] flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">
                        2
                    </div>
                    <div>
                        <h4 class="text-xs sm:text-sm font-bold text-slate-800 mb-0.5">{{ __('Tanggal inspeksi terakhir') }}</h4>
                        <p class="text-[11px] text-slate-500 leading-relaxed">{{ __('Periksa apakah APAR telah diperiksa dan dikalibrasi sesuai dengan jadwal yang ditetapkan.') }}</p>
                    </div>
                </div>

                <div class="flex gap-3 p-3.5 rounded-xl border border-slate-100 bg-slate-50/60 hover:bg-white hover:border-slate-200 transition-all">
                    <div class="w-7 h-7 rounded-full bg-[#009B77]/10 text-[#009B77] flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">
                        3
                    </div>
                    <div>
                        <h4 class="text-xs sm:text-sm font-bold text-slate-800 mb-0.5">{{ __('Label dan tanda peringatan') }}</h4>
                        <p class="text-[11px] text-slate-500 leading-relaxed">{{ __('Pastikan label dan tanda peringatan pada APAR masih terbaca dengan jelas dan tidak rusak.') }}</p>
                    </div>
                </div>

                <div class="flex gap-3 p-3.5 rounded-xl border border-slate-100 bg-slate-50/60 hover:bg-white hover:border-slate-200 transition-all">
                    <div class="w-7 h-7 rounded-full bg-[#009B77]/10 text-[#009B77] flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">
                        4
                    </div>
                    <div>
                        <h4 class="text-xs sm:text-sm font-bold text-slate-800 mb-0.5">{{ __('Segel keselamatan') }}</h4>
                        <p class="text-[11px] text-slate-500 leading-relaxed">{{ __('Periksa segel keselamatan pada APAR. Pastikan segel tidak rusak atau telah terputus.') }}</p>
                    </div>
                </div>

                <div class="flex gap-3 p-3.5 rounded-xl border border-slate-100 bg-slate-50/60 hover:bg-white hover:border-slate-200 transition-all">
                    <div class="w-7 h-7 rounded-full bg-[#009B77]/10 text-[#009B77] flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">
                        5
                    </div>
                    <div>
                        <h4 class="text-xs sm:text-sm font-bold text-slate-800 mb-0.5">{{ __('Kondisi fisik') }}</h4>
                        <p class="text-[11px] text-slate-500 leading-relaxed">{{ __('Periksa apakah APAR dalam kondisi fisik yang baik, tanpa kerusakan atau kebocoran yang signifikan.') }}</p>
                    </div>
                </div>

                <div class="flex gap-3 p-3.5 rounded-xl border border-slate-100 bg-slate-50/60 hover:bg-white hover:border-slate-200 transition-all">
                    <div class="w-7 h-7 rounded-full bg-[#009B77]/10 text-[#009B77] flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">
                        6
                    </div>
                    <div>
                        <h4 class="text-xs sm:text-sm font-bold text-slate-800 mb-0.5">{{ __('Penunjuk tekanan') }}</h4>
                        <p class="text-[11px] text-slate-500 leading-relaxed">{{ __('Periksa penunjuk tekanan pada APAR. Pastikan tekanan berada dalam rentang yang diperbolehkan.') }}</p>
                    </div>
                </div>

                <div class="flex gap-3 p-3.5 rounded-xl border border-slate-100 bg-slate-50/60 hover:bg-white hover:border-slate-200 transition-all">
                    <div class="w-7 h-7 rounded-full bg-[#009B77]/10 text-[#009B77] flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">
                        7
                    </div>
                    <div>
                        <h4 class="text-xs sm:text-sm font-bold text-slate-800 mb-0.5">{{ __('Instruksi penggunaan') }}</h4>
                        <p class="text-[11px] text-slate-500 leading-relaxed">{{ __('Periksa apakah instruksi penggunaan APAR masih terpasang dan mudah diakses.') }}</p>
                    </div>
                </div>

                <div class="flex gap-3 p-3.5 rounded-xl border border-slate-100 bg-slate-50/60 hover:bg-white hover:border-slate-200 transition-all">
                    <div class="w-7 h-7 rounded-full bg-[#009B77]/10 text-[#009B77] flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">
                        8
                    </div>
                    <div>
                        <h4 class="text-xs sm:text-sm font-bold text-slate-800 mb-0.5">{{ __('Pemeriksaan visual') }}</h4>
                        <p class="text-[11px] text-slate-500 leading-relaxed">{{ __('Lakukan pemeriksaan visual terhadap selang, nozzle, dan katup pemadam. Pastikan tidak ada kerusakan, kebocoran, atau penyumbatan yang signifikan.') }}</p>
                    </div>
                </div>

                <div class="flex gap-3 p-3.5 rounded-xl border border-slate-100 bg-slate-50/60 hover:bg-white hover:border-slate-200 transition-all">
                    <div class="w-7 h-7 rounded-full bg-[#009B77]/10 text-[#009B77] flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">
                        9
                    </div>
                    <div>
                        <h4 class="text-xs sm:text-sm font-bold text-slate-800 mb-0.5">{{ __('Pengoperasian') }}</h4>
                        <p class="text-[11px] text-slate-500 leading-relaxed">{{ __('Pastikan tuas atau pengatur aliran APAR berfungsi dengan baik dan tidak mengalami kebocoran.') }}</p>
                    </div>
                </div>

                <div class="flex gap-3 p-3.5 rounded-xl border border-slate-100 bg-slate-50/60 hover:bg-white hover:border-slate-200 transition-all">
                    <div class="w-7 h-7 rounded-full bg-[#009B77]/10 text-[#009B77] flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">
                        10
                    </div>
                    <div>
                        <h4 class="text-xs sm:text-sm font-bold text-slate-800 mb-0.5">{{ __('Ketersediaan pelatihan') }}</h4>
                        <p class="text-[11px] text-slate-500 leading-relaxed">{{ __('Periksa apakah pengguna APAR telah menjalani pelatihan yang diperlukan.') }}</p>
                    </div>
                </div>

                <div class="flex gap-3 p-3.5 rounded-xl border border-slate-100 bg-slate-50/60 hover:bg-white hover:border-slate-200 transition-all">
                    <div class="w-7 h-7 rounded-full bg-[#009B77]/10 text-[#009B77] flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">
                        11
                    </div>
                    <div>
                        <h4 class="text-xs sm:text-sm font-bold text-slate-800 mb-0.5">{{ __('Dokumentasi') }}</h4>
                        <p class="text-[11px] text-slate-500 leading-relaxed">{{ __('Pastikan catatan inspeksi APAR diisi dengan lengkap dan diperbarui secara teratur.') }}</p>
                    </div>
                </div>

                <div class="flex gap-3 p-3.5 rounded-xl border border-slate-100 bg-slate-50/60 hover:bg-white hover:border-slate-200 transition-all">
                    <div class="w-7 h-7 rounded-full bg-[#009B77]/10 text-[#009B77] flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">
                        12
                    </div>
                    <div>
                        <h4 class="text-xs sm:text-sm font-bold text-slate-800 mb-0.5">{{ __('Tindakan perbaikan') }}</h4>
                        <p class="text-[11px] text-slate-500 leading-relaxed">{{ __('Jika ditemukan masalah atau kekurangan selama inspeksi, pastikan tindakan perbaikan yang tepat diambil dan dicatat.') }}</p>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Floating Action Button -->
    <div class="fixed bottom-0 left-0 right-0 p-4 sm:p-6 bg-gradient-to-t from-[#F0F0F0] via-[#F0F0F0]/90 to-transparent z-40 pb-6">
        <div class="w-full {{ request('source') === 'schedule' ? 'max-w-6xl' : 'max-w-xl' }} mx-auto">
            <a href="{{ route('inspeksi.mulai', $apar->id) . (request('source') ? '?source=' . request('source') : '') }}" class="w-full bg-[#009B77] hover:bg-[#008264] text-white font-bold py-3.5 sm:py-4 rounded-xl shadow-lg shadow-[#009B77]/30 transition-all text-sm flex items-center justify-center gap-2 tracking-wide">
                <span>{{ __('Mulai Formulir Inspeksi') }}</span>
                <i class="ph-bold ph-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Reset Session Storage for Re-inspection -->
    <script>
        sessionStorage.removeItem('form_selesai_{{ $apar->id }}');
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
