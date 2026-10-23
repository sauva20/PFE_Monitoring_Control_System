<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ __('Start Inspection') }} - PFE Monitoring Control System</title>
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
    <!-- Alpine Plugins -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/persist@3.x.x/dist/cdn.min.js"></script>
    <!-- AlpineJS -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Flatpickr (Datepicker) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .flatpickr-wrapper {
            display: block !important;
            width: 100% !important;
        }
        .flatpickr-day.selected, .flatpickr-day.startRange, .flatpickr-day.endRange, .flatpickr-day.selected:hover {
            background: #009B77 !important;
            border-color: #009B77 !important;
        }
    </style>
    
    <!-- Anti BFCache & Strict History Lock -->
    <script>
        // Paksa reload jika dari BFCache
        window.addEventListener("pageshow", function (event) {
            if (event.persisted || sessionStorage.getItem('form_selesai_{{ $apar->id }}')) {
                window.location.reload();
            }
        });
        
        // Cek secara instan
        if (sessionStorage.getItem('form_selesai_{{ $apar->id }}')) {
            window.location.replace("{{ route('inspeksi.sukses', ['apar' => $apar->id, 'source' => request()->query('source')]) }}");
        }
    </script>
</head>
<body class="bg-[#F0F0F0] text-[#1A1A1A] relative min-h-screen w-screen overflow-x-hidden flex flex-col pb-24">

    <!-- Decorative Background Elements -->
    <div class="fixed top-[-10%] left-[-10%] w-[60%] h-[40%] bg-[#009B77] rounded-full mix-blend-multiply filter blur-[100px] opacity-20 pointer-events-none"></div>
    <div class="fixed bottom-10 right-[-10%] w-[50%] h-[40%] bg-[#8A4B9F] rounded-full mix-blend-multiply filter blur-[100px] opacity-15 pointer-events-none"></div>

    <div class="w-full {{ (request('source') === 'schedule') ? 'max-w-6xl' : 'max-w-xl' }} mx-auto relative z-10 flex flex-col p-4 sm:p-6 lg:p-8 space-y-6" x-data="{
    statusAkhir: $persist('{{ old('status', 'sesuai') }}').using(sessionStorage).as('inspeksi_status_{{ $apar->id }}'),
    qty: $persist('{{ old('qty', $apar->qty ?? 0) }}').using(sessionStorage).as('inspeksi_qty_{{ $apar->id }}'),
    tgl_kedaluwarsa: $persist('{{ old('tgl_kedaluwarsa', $apar->tgl_kedaluwarsa ? $apar->tgl_kedaluwarsa->format('Y-m-d') : '') }}').using(sessionStorage).as('inspeksi_tgl_{{ $apar->id }}'),
    catatan_tambahan: $persist('{{ addslashes(old('catatan_tambahan', '')) }}').using(sessionStorage).as('inspeksi_catatan_{{ $apar->id }}'),
    showModalEditApar: false,
    editApar: {
        id: {{ $apar->id }},
        kode: '{{ addslashes($apar->kode) }}',
        nomor_apar: '{{ addslashes($apar->kode) }}'.match(/\d+$/) ? '{{ addslashes($apar->kode) }}'.match(/\d+$/)[0] : '',
        nomor_ruangan: '{{ addslashes($apar->nomor_ruangan ?? '') }}',
        gedung_id: '{{ $apar->lokasi->gedung_id ?? '' }}',
        lokasi: '{{ addslashes($apar->lokasi->nama ?? '') }}',
        jenis_id: '{{ $apar->jenis_id }}',
        kapasitas_id: '{{ $apar->kapasitas_id }}',
        vendor: '{{ addslashes($apar->vendor) }}',
        tgl_kedaluwarsa: '{{ $apar->tgl_kedaluwarsa ? $apar->tgl_kedaluwarsa->format('Y-m-d') : '' }}'
    }
}" x-init="if (statusAkhir === 'layak') statusAkhir = 'sesuai'; if (statusAkhir === 'perbaikan' || statusAkhir === 'rusak' || statusAkhir === 'tidak_layak') statusAkhir = 'tidak_sesuai'; if (statusAkhir === 'isi_ulang') statusAkhir = 'perlu_refill'; $watch('qty', value => { if (value === '' || Number(value) < 1) statusAkhir = 'perlu_refill'; }); if (qty === '' || Number(qty) < 1) statusAkhir = 'perlu_refill';">
    <!-- Header Area -->
    <div class="flex items-center justify-between gap-4 {{ (request('source') === 'schedule') ? 'bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-sm' : '' }}">
        <div class="flex items-center gap-3">
            <a href="{{ $backUrl }}" class="px-3 py-2 rounded-xl bg-white border border-slate-200/80 shadow-sm flex items-center gap-2 text-slate-600 hover:text-[#009B77] hover:border-[#009B77] transition-all text-xs font-bold" title="{{ __('Back') }}">
                <i class="ph-bold ph-arrow-left text-base"></i>
                <span class="{{ (request('source') === 'schedule') ? 'inline' : 'hidden sm:inline' }}">{{ __('Kembali') }}</span>
            </a>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h2 class="text-base sm:text-lg font-black text-slate-800 leading-tight">{{ __('Formulir Inspeksi APAR') }}</h2>
                    @if(request('source') === 'schedule')
                    <span class="text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200 flex items-center gap-1">
                        <i class="bi bi-pc-display"></i> {{ __('Mode Sistem Desktop') }}
                    </span>
                    @endif
                </div>
                <p class="text-xs font-semibold text-slate-500 mt-0.5 hidden sm:block">{{ __('Catat hasil pengecekan fisik dan fungsi komponen APAR secara akurat.') }}</p>
            </div>
        </div>
        
        <!-- Language Switcher -->
        <div class="flex items-center gap-1 bg-white p-1 rounded-xl border border-slate-200/80 shadow-sm shrink-0">
            <a href="{{ route('set-locale', 'id') }}" class="text-[11px] font-bold px-2.5 py-1 rounded-lg {{ app()->getLocale() == 'id' ? 'bg-[#009B77] text-white shadow-sm' : 'bg-transparent text-slate-400 hover:text-slate-600' }} transition-colors">ID</a>
            <a href="{{ route('set-locale', 'en') }}" class="text-[11px] font-bold px-2.5 py-1 rounded-lg {{ app()->getLocale() == 'en' ? 'bg-[#009B77] text-white shadow-sm' : 'bg-transparent text-slate-400 hover:text-slate-600' }} transition-colors">EN</a>
        </div>
    </div>

    <!-- Info Card -->
    <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-slate-200/80 overflow-hidden p-4 sm:p-5 flex flex-col md:flex-row md:items-center gap-4 justify-between relative">
        <div class="absolute -right-10 -top-10 w-48 h-48 bg-[#009B77]/5 rounded-full blur-3xl pointer-events-none"></div>
        <div class="flex items-center gap-4 relative z-10">
            <div class="w-13 h-13 rounded-2xl bg-gradient-to-br from-[#009B77] to-teal-800 text-white flex items-center justify-center shadow-md shadow-[#009B77]/20 flex-shrink-0 border border-white/20">
                <i class="ph-fill ph-fire-extinguisher text-2xl drop-shadow-md"></i>
            </div>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h3 class="font-black text-slate-800 text-lg sm:text-xl tracking-tight">{{ $apar->kode ?? 'APAR-XXX' }}</h3>
                    @if(isset($apar->lokasi->gedung->nama))
                    <span class="text-xs font-bold px-2.5 py-0.5 rounded-md bg-emerald-50 text-emerald-800 border border-emerald-200">
                        {{ $apar->lokasi->gedung->nama }}
                    </span>
                    @endif
                </div>
                <p class="text-xs font-semibold text-slate-500 flex items-center gap-1.5 mt-0.5">
                    <i class="ph-fill ph-map-pin text-[#009B77]"></i> 
                    @if(!empty($apar->nomor_ruangan))
                        <span class="font-bold text-slate-700">{{ $apar->nomor_ruangan }}</span>
                        <span class="text-slate-300">•</span>
                    @endif
                    {{ $apar->lokasi->nama ?? 'n/a' }}
                </p>
            </div>
        </div>
        <div class="grid grid-cols-3 gap-2 w-full md:w-auto md:flex md:items-center relative z-10 pt-3 md:pt-0 border-t md:border-t-0 border-slate-100">
            <div class="px-2 py-2 sm:px-3.5 sm:py-2 bg-slate-50/80 rounded-xl border border-slate-100 flex flex-col items-center justify-center text-center min-h-[58px] min-w-0 md:min-w-[90px]">
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">{{ __('Type') }}</span>
                <span class="text-[11px] sm:text-xs font-black text-[#009B77] leading-tight text-center break-words line-clamp-2" title="{{ $apar->jenis->nama ?? 'n/a' }}">{{ $apar->jenis->nama ?? 'n/a' }}</span>
            </div>
            <div class="px-2 py-2 sm:px-3.5 sm:py-2 bg-slate-50/80 rounded-xl border border-slate-100 flex flex-col items-center justify-center text-center min-h-[58px] min-w-0 md:min-w-[85px]">
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">{{ __('Capacity') }}</span>
                <span class="text-[11px] sm:text-xs font-black text-[#009B77] leading-tight">{{ $apar->kapasitas->ukuran ?? 'n/a' }}</span>
            </div>
            @if($apar->tgl_kedaluwarsa)
            <div class="px-2 py-2 sm:px-3.5 sm:py-2 bg-slate-50/80 rounded-xl border border-slate-100 flex flex-col items-center justify-center text-center min-h-[58px] min-w-0 md:min-w-[95px]">
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">{{ __('Expired') }}</span>
                <span class="text-[11px] sm:text-xs font-black {{ \Carbon\Carbon::parse($apar->tgl_kedaluwarsa)->isPast() ? 'text-red-500' : 'text-slate-700' }} leading-tight">{{ \Carbon\Carbon::parse($apar->tgl_kedaluwarsa)->format('d M Y') }}</span>
            </div>
            @else
            <div class="px-2 py-2 sm:px-3.5 sm:py-2 bg-slate-50/80 rounded-xl border border-slate-100 flex flex-col items-center justify-center text-center min-h-[58px] min-w-0 md:min-w-[95px]">
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">{{ __('Expired') }}</span>
                <span class="text-[11px] sm:text-xs font-black text-slate-400 leading-tight">-</span>
            </div>
            @endif
        </div>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 p-4 rounded-xl border border-red-200">
            <div class="flex items-center gap-3 text-red-600 font-bold text-sm mb-2">
                <i class="ph-fill ph-warning-circle text-xl"></i>
                {{ __('There are some errors:') }}
            </div>
            <ul class="list-disc list-inside text-xs font-semibold text-red-500 ml-1 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Checklist Section -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden" 
         x-data="{ 
             step: $persist(1).using(sessionStorage).as('inspeksi_step_{{ $apar->id }}'),
             isDesktop: @json(request('source') === 'schedule')
         }">
        <form action="{{ route('inspeksi.store', $apar->id ?? 0) }}" method="POST" enctype="multipart/form-data" 
              @submit.prevent="
                  let uncompleted = Array.from($el.querySelectorAll('input[name^=\'checklist[\'][name$=\'[jawaban]\']')).some(i => !i.value || i.value.trim() === '');
                  if (uncompleted) {
                      Swal.fire({
                          icon: 'warning',
                          title: '{{ __('Pemeriksaan Belum Lengkap') }}',
                          text: '{{ __('Harap jawab seluruh pertanyaan inspeksi sebelum menyelesaikan.') }}',
                          confirmButtonColor: '#009B77',
                          confirmButtonText: '{{ __('OK') }}',
                          customClass: {
                              popup: 'rounded-2xl',
                              confirmButton: 'rounded-lg font-bold shadow-sm'
                          }
                      });
                      return;
                  }
                  let foto = $el.querySelector('input[name=\'foto_base64\']').value;
                  if (!foto || foto.trim() === '') {
                      let isSystem = ('{{ request('source') }}' === 'schedule');
                      let pesanText = isSystem 
                          ? '{{ __('Harap lampirkan foto kondisi APAR saat inspeksi berlangsung.') }}' 
                          : '{{ __('Ambil foto kondisi APAR secara langsung dari lokasi menggunakan kamera.') }}';
                          
                      Swal.fire({
                          icon: 'warning',
                          title: '{{ __('Peringatan') }}',
                          text: pesanText,
                          confirmButtonColor: '#009B77',
                          confirmButtonText: '{{ __('OK') }}',
                          customClass: {
                              popup: 'rounded-2xl',
                              confirmButton: 'rounded-lg font-bold shadow-sm'
                          }
                      });
                      return;
                  }
                  Object.keys(sessionStorage).forEach(k => { if(k.startsWith('inspeksi_')) sessionStorage.removeItem(k); });
                  sessionStorage.setItem('form_selesai_{{ $apar->id }}', 'true');
                  $el.submit();
              ">
            <input type="hidden" name="source" value="{{ request('source') }}">
            @csrf
            
            <!-- Step 1: Pemeriksaan Fisik -->
            <div x-show="isDesktop || step === 1">
                <div class="p-4 sm:p-5">
                    <h4 class="font-bold text-slate-800 mb-4 flex items-center gap-2.5 border-b border-slate-100 pb-3">
                        <div class="w-8 h-8 rounded-lg bg-[#009B77]/10 flex items-center justify-center text-[#009B77]">
                            <i class="ph-bold ph-magnifying-glass text-lg"></i>
                        </div>
                        <div>
                            <span class="block text-base" x-text="isDesktop ? '{{ __('1. Pemeriksaan Fisik APAR') }}' : '{{ __('Pemeriksaan Fisik (Langkah 1 dari 2)') }}'"></span>
                            <span class="block text-[10px] text-slate-500 font-medium mt-0.5">{{ __('Cek ketersediaan dan kondisi fisik tiap bagian APAR.') }}</span>
                        </div>
                    </h4>
                    
                    <div class="space-y-3 {{ (request('source') === 'schedule') ? 'lg:grid lg:grid-cols-2 lg:gap-3.5 lg:space-y-0' : '' }}">
                        @foreach($pertanyaan as $index => $tanya)
                        @if($index >= 14)
                        
                        @if($index === 14)
                        {{-- 1. Jenis APAR --}}
                        <div class="group flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3.5 rounded-xl border-2 transition-all duration-300 hover:shadow-sm {{ (request('source') === 'schedule') ? 'lg:col-span-2' : '' }}" 
                             x-data="{ 
                                 selectedJenis: $persist('{{ old('jenis_id', $apar->jenis_id) }}').using(sessionStorage).as('inspeksi_jenis_{{ $apar->id }}'), 
                                 expectedJenis: '{{ $apar->jenis_id }}',
                                 keterangan: $persist('{{ addslashes(old('checklist.'.$index.'.keterangan', '')) }}').using(sessionStorage).as('inspeksi_ket_{{ $index }}_{{ $apar->id }}')
                             }"
                             :class="selectedJenis == expectedJenis ? 'bg-teal-50/50 border-teal-200/60' : 'bg-red-50/80 border-red-200'">
                            <!-- Number & Question -->
                            <div class="flex-1 flex gap-3">
                                <div class="w-6 h-6 rounded-full flex items-center justify-center font-black text-[10px] flex-shrink-0 transition-colors"
                                     :class="selectedJenis == expectedJenis ? 'bg-[#009B77] text-white' : 'bg-red-500 text-white'">
                                    {{ $index - 13 }}
                                </div>
                                <div>
                                    <p class="font-bold text-slate-700 text-xs leading-relaxed pt-0.5">{{ $tanya }}</p>
                                    <p class="text-[10px] text-slate-400 font-medium mt-0.5">Master: <strong class="text-slate-600">{{ $apar->jenis->nama ?? '-' }}</strong></p>
                                </div>
                            </div>
                            
                            <!-- Toggle Buttons -->
                            <div class="flex flex-col gap-2 w-full sm:w-[340px] sm:shrink-0">
                                <div class="grid grid-cols-2 gap-1 bg-slate-100/80 rounded-xl p-1 border border-slate-200/80 shadow-inner w-full">
                                    <input type="hidden" name="checklist[{{ $index }}][jawaban]" :value="selectedJenis == expectedJenis ? 'ada' : 'tidak ada'">
                                    <input type="hidden" name="jenis_id" :value="selectedJenis" required>
                                    
                                    @foreach($jenisApars as $jenis)
                                    <button type="button" @click="selectedJenis = '{{ $jenis->id }}'" 
                                            class="px-2.5 py-2 rounded-lg text-[10px] font-bold transition-all duration-200 flex items-center justify-center text-center leading-tight truncate" 
                                            :class="selectedJenis == '{{ $jenis->id }}' ? (selectedJenis == expectedJenis ? 'bg-[#009B77] text-white shadow-sm font-black' : 'bg-red-500 text-white shadow-sm font-black') : 'text-slate-500 hover:bg-white hover:text-slate-700'">
                                        {{ $jenis->nama }}
                                    </button>
                                    @endforeach
                                </div>
                                
                                <!-- Input Keterangan -->
                                <div x-show="selectedJenis !== '' && selectedJenis != expectedJenis" x-collapse>
                                    <div class="relative">
                                        <i class="ph-bold absolute left-2.5 top-1/2 -translate-y-1/2 text-base" :class="selectedJenis != expectedJenis ? 'ph-warning-circle text-red-400' : 'ph-note-pencil text-slate-400'"></i>
                                        <input type="text" name="checklist[{{ $index }}][keterangan]" x-model="keterangan" 
                                               placeholder="{{ __('Kondisi / Keterangan (Opsional)...') }}" 
                                               class="w-full text-[11px] py-2 pl-8 pr-3 border-2 bg-white rounded-lg focus:ring-2 outline-none transition-all font-medium shadow-sm"
                                               :class="selectedJenis != expectedJenis ? 'border-red-200 focus:border-red-400 focus:ring-red-100 placeholder:text-red-300 text-red-700' : 'border-slate-200 focus:border-[#009B77] focus:ring-[#009B77]/15 placeholder:text-slate-300 text-slate-700'">
                                    </div>
                                </div>
                            </div>
                        </div>
                        @elseif($index === 15)
                        {{-- 2. Kapasitas --}}
                        <div class="group flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3.5 rounded-xl border-2 transition-all duration-300 hover:shadow-sm {{ (request('source') === 'schedule') ? 'lg:col-span-2' : '' }}" 
                             x-data="{ 
                                 selectedKapasitas: $persist('{{ old('kapasitas_id', $apar->kapasitas_id) }}').using(sessionStorage).as('inspeksi_kapasitas_{{ $apar->id }}'), 
                                 expectedKapasitas: '{{ $apar->kapasitas_id }}',
                                 keterangan: $persist('{{ addslashes(old('checklist.'.$index.'.keterangan', '')) }}').using(sessionStorage).as('inspeksi_ket_{{ $index }}_{{ $apar->id }}')
                             }"
                             :class="selectedKapasitas == expectedKapasitas ? 'bg-teal-50/50 border-teal-200/60' : 'bg-red-50/80 border-red-200'">
                            <!-- Number & Question -->
                            <div class="flex-1 flex gap-3">
                                <div class="w-6 h-6 rounded-full flex items-center justify-center font-black text-[10px] flex-shrink-0 transition-colors"
                                     :class="selectedKapasitas == expectedKapasitas ? 'bg-[#009B77] text-white' : 'bg-red-500 text-white'">
                                    {{ $index - 13 }}
                                </div>
                                <div>
                                    <p class="font-bold text-slate-700 text-xs leading-relaxed pt-0.5">{{ $tanya }}</p>
                                    <p class="text-[10px] text-slate-400 font-medium mt-0.5">Master: <strong class="text-slate-600">{{ $apar->kapasitas->ukuran ?? '-' }}</strong></p>
                                </div>
                            </div>
                            
                            <!-- Toggle Buttons -->
                            <div class="flex flex-col gap-2 w-full sm:w-[340px] sm:shrink-0">
                                <div class="grid grid-cols-4 sm:grid-cols-7 gap-1 bg-slate-100/80 rounded-xl p-1 border border-slate-200/80 shadow-inner w-full">
                                    <input type="hidden" name="checklist[{{ $index }}][jawaban]" :value="selectedKapasitas == expectedKapasitas ? 'ada' : 'tidak ada'">
                                    <input type="hidden" name="kapasitas_id" :value="selectedKapasitas" required>
                                    
                                    @foreach($kapasitasApars as $kapasitas)
                                    <button type="button" @click="selectedKapasitas = '{{ $kapasitas->id }}'" 
                                            class="px-1.5 py-1.5 rounded-lg text-[10px] font-bold transition-all duration-200 flex items-center justify-center text-center leading-tight whitespace-nowrap" 
                                            :class="selectedKapasitas == '{{ $kapasitas->id }}' ? (selectedKapasitas == expectedKapasitas ? 'bg-[#009B77] text-white shadow-sm font-black' : 'bg-red-500 text-white shadow-sm font-black') : 'text-slate-500 hover:bg-white hover:text-slate-700'">
                                        {{ $kapasitas->ukuran }}
                                    </button>
                                    @endforeach
                                </div>
                                
                                <!-- Input Keterangan -->
                                <div x-show="selectedKapasitas !== '' && selectedKapasitas != expectedKapasitas" x-collapse>
                                    <div class="relative">
                                        <i class="ph-bold absolute left-2.5 top-1/2 -translate-y-1/2 text-base" :class="selectedKapasitas != expectedKapasitas ? 'ph-warning-circle text-red-400' : 'ph-note-pencil text-slate-400'"></i>
                                        <input type="text" name="checklist[{{ $index }}][keterangan]" x-model="keterangan" 
                                               placeholder="{{ __('Kondisi / Keterangan (Opsional)...') }}" 
                                               class="w-full text-[11px] py-2 pl-8 pr-3 border-2 bg-white rounded-lg focus:ring-2 outline-none transition-all font-medium shadow-sm"
                                               :class="selectedKapasitas != expectedKapasitas ? 'border-red-200 focus:border-red-400 focus:ring-red-100 placeholder:text-red-300 text-red-700' : 'border-slate-200 focus:border-[#009B77] focus:ring-[#009B77]/15 placeholder:text-slate-300 text-slate-700'">
                                    </div>
                                </div>
                            </div>
                        </div>
                        @elseif($index === 16)
                        {{-- 3. Tanggal isi ulang terakhir --}}
                        @php
                            $tglIsiUlangStr = $apar->tgl_isi_ulang ? $apar->tgl_isi_ulang->format('Y-m-d') : '';
                        @endphp
                        <div class="group flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3.5 rounded-xl border-2 transition-all duration-300 hover:shadow-sm {{ (request('source') === 'schedule') ? 'lg:col-span-2' : '' }}" 
                             x-data="{ 
                                 selectedTgl: $persist('{{ old('tgl_isi_ulang', $tglIsiUlangStr) }}').using(sessionStorage).as('inspeksi_tglisiulang_{{ $apar->id }}'), 
                                 expectedTgl: '{{ $tglIsiUlangStr }}',
                                 keterangan: $persist('{{ addslashes(old('checklist.'.$index.'.keterangan', '')) }}').using(sessionStorage).as('inspeksi_ket_{{ $index }}_{{ $apar->id }}')
                             }"
                             :class="(selectedTgl == expectedTgl && expectedTgl !== '') ? 'bg-teal-50/50 border-teal-200/60' : 'bg-red-50/80 border-red-200'">
                            <!-- Number & Question -->
                            <div class="flex-1 flex gap-3">
                                <div class="w-6 h-6 rounded-full flex items-center justify-center font-black text-[10px] flex-shrink-0 transition-colors"
                                     :class="(selectedTgl == expectedTgl && expectedTgl !== '') ? 'bg-[#009B77] text-white' : 'bg-red-500 text-white'">
                                    {{ $index - 13 }}
                                </div>
                                <div>
                                    <p class="font-bold text-slate-700 text-xs leading-relaxed pt-0.5">{{ $tanya }}</p>
                                    <p class="text-[10px] text-slate-400 font-medium mt-0.5">Master: <strong class="text-slate-600">{{ $apar->tgl_isi_ulang ? $apar->tgl_isi_ulang->format('d/m/Y') : '-' }}</strong></p>
                                </div>
                            </div>
                            
                            <!-- Date Input -->
                            <div class="flex flex-col gap-2 w-full sm:w-[340px] sm:shrink-0">
                                <div class="relative w-full">
                                    <input type="hidden" name="checklist[{{ $index }}][jawaban]" :value="(selectedTgl == expectedTgl && expectedTgl !== '') ? 'ada' : 'tidak ada'">
                                    <i class="ph-bold ph-calendar-blank absolute left-3 top-1/2 -translate-y-1/2 text-base pointer-events-none transition-colors"
                                       :class="(selectedTgl == expectedTgl && expectedTgl !== '') ? 'text-[#009B77]' : 'text-red-500'"></i>
                                    <input type="text" name="tgl_isi_ulang" x-model="selectedTgl" required
                                           x-init="flatpickr($el, { dateFormat: 'Y-m-d', disableMobile: 'true', defaultDate: selectedTgl, onChange: (s, d) => { selectedTgl = d; } })"
                                           class="w-full bg-white border-2 rounded-xl py-2 pl-9 pr-3 text-xs font-bold transition-all outline-none shadow-sm cursor-pointer"
                                           :class="(selectedTgl == expectedTgl && expectedTgl !== '') ? 'border-teal-200/90 text-teal-900 bg-teal-50/50 focus:border-[#009B77] focus:ring-2 focus:ring-[#009B77]/15' : 'border-red-300 text-red-700 bg-red-50/50 focus:border-red-400 focus:ring-2 focus:ring-red-100'">
                                </div>
                                
                                <!-- Input Keterangan -->
                                <div x-show="selectedTgl !== '' && (selectedTgl != expectedTgl || expectedTgl === '')" x-collapse>
                                    <div class="relative">
                                        <i class="ph-bold absolute left-2.5 top-1/2 -translate-y-1/2 text-base" :class="(selectedTgl != expectedTgl || expectedTgl === '') ? 'ph-warning-circle text-red-400' : 'ph-note-pencil text-slate-400'"></i>
                                        <input type="text" name="checklist[{{ $index }}][keterangan]" x-model="keterangan" 
                                               placeholder="{{ __('Kondisi / Keterangan (Opsional)...') }}" 
                                               class="w-full text-[11px] py-2 pl-8 pr-3 border-2 bg-white rounded-lg focus:ring-2 outline-none transition-all font-medium shadow-sm"
                                               :class="(selectedTgl != expectedTgl || expectedTgl === '') ? 'border-red-200 focus:border-red-400 focus:ring-red-100 placeholder:text-red-300 text-red-700' : 'border-slate-200 focus:border-[#009B77] focus:ring-[#009B77]/15 placeholder:text-slate-300 text-slate-700'">
                                    </div>
                                </div>
                            </div>
                        </div>
                        @elseif($index === 17)
                        {{-- 4. Tanggal masa berlaku (Expired) --}}
                        @php
                            $tglKedaluwarsaStr = $apar->tgl_kedaluwarsa ? $apar->tgl_kedaluwarsa->format('Y-m-d') : '';
                        @endphp
                        <div class="group flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3.5 rounded-xl border-2 transition-all duration-300 hover:shadow-sm {{ (request('source') === 'schedule') ? 'lg:col-span-2' : '' }}" 
                             x-data="{ 
                                 expectedTgl: '{{ $tglKedaluwarsaStr }}',
                                 keterangan: $persist('{{ addslashes(old('checklist.'.$index.'.keterangan', '')) }}').using(sessionStorage).as('inspeksi_ket_{{ $index }}_{{ $apar->id }}')
                             }"
                             :class="(tgl_kedaluwarsa == expectedTgl && expectedTgl !== '') ? 'bg-teal-50/50 border-teal-200/60' : 'bg-red-50/80 border-red-200'">
                            <!-- Number & Question -->
                            <div class="flex-1 flex gap-3">
                                <div class="w-6 h-6 rounded-full flex items-center justify-center font-black text-[10px] flex-shrink-0 transition-colors"
                                     :class="(tgl_kedaluwarsa == expectedTgl && expectedTgl !== '') ? 'bg-[#009B77] text-white' : 'bg-red-500 text-white'">
                                    {{ $index - 13 }}
                                </div>
                                <div>
                                    <p class="font-bold text-slate-700 text-xs leading-relaxed pt-0.5">{{ $tanya }}</p>
                                    <p class="text-[10px] text-slate-400 font-medium mt-0.5">Master: <strong class="text-slate-600">{{ $apar->tgl_kedaluwarsa ? $apar->tgl_kedaluwarsa->format('d/m/Y') : '-' }}</strong></p>
                                </div>
                            </div>
                            
                            <!-- Date Input -->
                            <div class="flex flex-col gap-2 w-full sm:w-[340px] sm:shrink-0">
                                <div class="relative w-full">
                                    <input type="hidden" name="checklist[{{ $index }}][jawaban]" :value="(tgl_kedaluwarsa == expectedTgl && expectedTgl !== '') ? 'ada' : 'tidak ada'">
                                    <i class="ph-bold ph-calendar-blank absolute left-3 top-1/2 -translate-y-1/2 text-base pointer-events-none transition-colors"
                                       :class="(tgl_kedaluwarsa == expectedTgl && expectedTgl !== '') ? 'text-[#009B77]' : 'text-red-500'"></i>
                                    <input type="text" name="tgl_kedaluwarsa" x-model="tgl_kedaluwarsa" required
                                           x-init="flatpickr($el, { dateFormat: 'Y-m-d', disableMobile: 'true', defaultDate: tgl_kedaluwarsa, onChange: (s, d) => { tgl_kedaluwarsa = d; } })"
                                           class="w-full bg-white border-2 rounded-xl py-2 pl-9 pr-3 text-xs font-bold transition-all outline-none shadow-sm cursor-pointer"
                                           :class="(tgl_kedaluwarsa == expectedTgl && expectedTgl !== '') ? 'border-teal-200/90 text-teal-900 bg-teal-50/50 focus:border-[#009B77] focus:ring-2 focus:ring-[#009B77]/15' : 'border-red-300 text-red-700 bg-red-50/50 focus:border-red-400 focus:ring-2 focus:ring-red-100'">
                                </div>
                                
                                <!-- Input Keterangan -->
                                <div x-show="tgl_kedaluwarsa !== '' && (tgl_kedaluwarsa != expectedTgl || expectedTgl === '')" x-collapse>
                                    <div class="relative">
                                        <i class="ph-bold absolute left-2.5 top-1/2 -translate-y-1/2 text-base" :class="(tgl_kedaluwarsa != expectedTgl || expectedTgl === '') ? 'ph-warning-circle text-red-400' : 'ph-note-pencil text-slate-400'"></i>
                                        <input type="text" name="checklist[{{ $index }}][keterangan]" x-model="keterangan" 
                                               placeholder="{{ __('Kondisi / Keterangan (Opsional)...') }}" 
                                               class="w-full text-[11px] py-2 pl-8 pr-3 border-2 bg-white rounded-lg focus:ring-2 outline-none transition-all font-medium shadow-sm"
                                               :class="(tgl_kedaluwarsa != expectedTgl || expectedTgl === '') ? 'border-red-200 focus:border-red-400 focus:ring-red-100 placeholder:text-red-300 text-red-700' : 'border-slate-200 focus:border-[#009B77] focus:ring-[#009B77]/15 placeholder:text-slate-300 text-slate-700'">
                                    </div>
                                </div>
                            </div>
                        </div>
                        @else
                        {{-- 5-10: Segel, Karat, Kepadatan, Nozzle, Pin, Lokasi --}}
                        @php
                            $expected = ($index === 19) ? 'tidak ada' : 'ada';
                        @endphp
                        <div x-data="{ 
                                jawaban: $persist('{{ old('checklist.'.$index.'.jawaban', '') }}').using(sessionStorage).as('inspeksi_jawaban_{{ $index }}_{{ $apar->id }}'), 
                                keterangan: $persist('{{ addslashes(old('checklist.'.$index.'.keterangan', '')) }}').using(sessionStorage).as('inspeksi_ket_{{ $index }}_{{ $apar->id }}'),
                                expected: '{{ $expected }}' 
                             }" 
                             @if($index === 23)
                              x-effect="if ($refs.lokasiInput) {
                                if (jawaban === 'tidak ada' && keterangan.trim().toLowerCase() === '{{ strtolower($apar->lokasi->nama) }}') {
                                    $refs.lokasiInput.setCustomValidity('Lokasi yang dimasukkan sama dengan lokasi saat ini.');
                                } else {
                                    $refs.lokasiInput.setCustomValidity('');
                                }
                              }"
                             @endif
                             class="group flex flex-col sm:flex-row gap-3 p-3 rounded-xl border-2 transition-all duration-300 hover:shadow-sm {{ ($index === 23 && (request('source') === 'schedule')) ? 'lg:col-span-2' : '' }}" 
                             :class="jawaban === expected ? 'bg-teal-50/50 border-teal-200/60' : (jawaban !== '' ? 'bg-red-50/80 border-red-200' : 'bg-white border-slate-100 hover:border-slate-200')">
                             
                            <!-- Number & Question -->
                            <div class="flex-1 flex gap-3">
                                <div class="w-6 h-6 rounded-full flex items-center justify-center font-black text-[10px] flex-shrink-0 transition-colors"
                                     :class="jawaban === expected ? 'bg-[#009B77] text-white' : (jawaban !== '' ? 'bg-red-500 text-white' : 'bg-slate-100 text-slate-400 group-hover:bg-[#009B77] group-hover:text-white')">
                                    {{ $index - 13 }}
                                </div>
                                <div>
                                    <p class="font-bold text-slate-700 text-xs leading-relaxed pt-0.5">{{ $tanya }}</p>
                                    @if($index === 23)
                                    <p class="text-[10px] text-slate-500 font-semibold mt-0.5">Master: <strong class="text-slate-700">{{ $apar->lokasi->nama ?? '-' }}</strong></p>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Toggle Ya / Tidak -->
                            <div class="flex flex-col gap-2 min-w-[180px] sm:w-[320px]">
                                <div class="flex bg-slate-100/80 rounded-lg p-1 border border-slate-200 shadow-inner self-start w-full sm:w-auto">
                                    <input type="hidden" name="checklist[{{ $index }}][jawaban]" x-model="jawaban">
                                    <button type="button" @click="jawaban = 'ada'" 
                                            class="flex-1 px-3 py-1.5 rounded text-[10px] font-bold transition-all duration-300 flex items-center justify-center gap-1.5" 
                                            :class="jawaban === 'ada' ? (expected === 'ada' ? 'bg-[#009B77] text-white shadow-sm scale-[1.02]' : 'bg-red-500 text-white shadow-sm scale-[1.02]') : 'text-slate-500 hover:bg-white hover:text-slate-700 hover:shadow-sm'">
                                        <i class="ph-bold ph-check text-[10px]"></i> {{ $index === 20 ? __('Padat') : ($index === 23 ? __('Sesuai') : __('Ada')) }}
                                    </button>
                                    <button type="button" @click="jawaban = 'tidak ada'" 
                                            class="flex-1 px-3 py-1.5 rounded text-[10px] font-bold transition-all duration-300 flex items-center justify-center gap-1.5" 
                                            :class="jawaban === 'tidak ada' ? (expected === 'tidak ada' ? 'bg-[#009B77] text-white shadow-sm scale-[1.02]' : 'bg-red-500 text-white shadow-sm scale-[1.02]') : 'text-slate-500 hover:bg-white hover:text-slate-700 hover:shadow-sm'">
                                        <i class="ph-bold ph-x text-[10px]"></i> {{ $index === 20 ? __('Tidak') : ($index === 23 ? __('Tidak Sesuai') : __('Tidak Ada')) }}
                                    </button>
                                </div>
                                
                                <!-- Input Keterangan -->
                                <div x-show="jawaban !== '' && jawaban !== expected" x-collapse>
                                    <div class="relative">
                                        <i class="ph-bold absolute left-2.5 top-1/2 -translate-y-1/2 text-base" :class="jawaban !== expected ? 'ph-warning-circle text-red-400' : 'ph-note-pencil text-slate-400'"></i>
                                        <input type="text" name="checklist[{{ $index }}][keterangan]" x-model="keterangan" 
                                               @if($index === 23) x-ref="lokasiInput" @endif 
                                               placeholder="{{ $index === 23 ? __('Masukkan lokasi yang tepat...') : __('Kondisi / Keterangan (Opsional)...') }}" 
                                               :required="jawaban === 'tidak ada' && {{ $index === 23 ? 'true' : 'false' }}"
                                               class="w-full text-[11px] py-2 pl-8 pr-3 border-2 bg-white rounded-lg focus:ring-2 outline-none transition-all font-medium shadow-sm"
                                               :class="jawaban !== expected ? 'border-red-200 focus:border-red-400 focus:ring-red-100 placeholder:text-red-300 text-red-700' : 'border-slate-200 focus:border-[#009B77] focus:ring-[#009B77]/15 placeholder:text-slate-300 text-slate-700'">
                                    </div>
                                    @if($index === 23)
                                    <div x-show="jawaban === 'tidak ada' && keterangan.trim().toLowerCase() === '{{ strtolower($apar->lokasi->nama) }}'" x-collapse>
                                        <div class="mt-1.5 flex items-start gap-1.5 text-amber-600 bg-amber-50 p-2 rounded border border-amber-200">
                                            <i class="ph-bold ph-warning text-sm mt-0.5"></i>
                                            <p class="text-[10px] font-medium leading-tight">{{ __('Lokasi yang Anda masukkan sama dengan lokasi saat ini. Masukkan lokasi yang berbeda jika memang tidak sesuai.') }}</p>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif
                        @endif
                        @endforeach
                    </div>
                </div>

                <!-- Lanjut Button Step 1 (Mobile only) -->
                <div x-show="!isDesktop && step === 1" class="p-4 sm:p-5 border-t border-slate-100 flex items-center justify-end bg-white sticky bottom-0 z-10 shadow-[0_-5px_15px_rgba(0,0,0,0.02)]">
                    <button type="button" @click="
                        let isValid = true;
                        let inputs = $el.closest('div[x-show=\'isDesktop || step === 1\']').querySelectorAll('input, select, textarea');
                        for (let input of inputs) {
                            if (!input.checkValidity()) {
                                input.reportValidity();
                                isValid = false;
                                break;
                            }
                        }
                        if (isValid) {
                            let step1Checklist = $el.closest('div[x-show=\'isDesktop || step === 1\']').querySelectorAll('input[name^=\'checklist[\'][name$=\'[jawaban]\']');
                            for (let c of step1Checklist) {
                                if (!c.value || c.value.trim() === '') {
                                    isValid = false;
                                    Swal.fire({
                                        icon: 'warning',
                                        title: '{{ __('Pemeriksaan Fisik Belum Lengkap') }}',
                                        text: '{{ __('Harap lengkapi semua opsi pemeriksaan fisik sebelum melanjutkan.') }}',
                                        confirmButtonColor: '#009B77',
                                        confirmButtonText: '{{ __('OK') }}',
                                        customClass: { popup: 'rounded-2xl', confirmButton: 'rounded-lg font-bold shadow-sm' }
                                    });
                                    break;
                                }
                            }
                        }
                        if (isValid) {
                            step = 2;
                            setTimeout(() => window.scrollTo({ top: 0, behavior: 'smooth' }), 50);
                        }
                    " class="w-full sm:w-auto h-11 btn-smooth-ring bg-[#009B77] hover:bg-[#008264] text-white font-bold px-6 rounded-xl shadow-sm shadow-[#009B77]/25 transition-all flex items-center justify-center gap-2 text-xs">
                        {{ __('Lanjut (Fungsi & Kelayakan)') }} <i class="ph-bold ph-arrow-right text-base"></i>
                    </button>
                </div>
            </div>

            <!-- Step 2: Pemeriksaan Fungsi & Kesimpulan -->
            <div x-show="isDesktop || step === 2" :class="isDesktop ? 'border-t-4 border-slate-100' : ''" style="display: none;">
                <div class="p-4 sm:p-5">
                    <h4 class="font-bold text-slate-800 mb-4 flex items-center gap-2.5 border-b border-slate-100 pb-3">
                        <div class="w-8 h-8 rounded-lg bg-[#009B77]/10 flex items-center justify-center text-[#009B77]">
                            <i class="ph-bold ph-list-checks text-lg"></i>
                        </div>
                        <div>
                            <span class="block text-base" x-text="isDesktop ? '{{ __('2. Pemeriksaan Fungsi & Kelayakan') }}' : '{{ __('Pemeriksaan Fungsi (Langkah 2 dari 2)') }}'"></span>
                            <span class="block text-[10px] text-slate-500 font-medium mt-0.5">{{ __('Jawab setiap poin sesuai kondisi aktual di lapangan.') }}</span>
                        </div>
                    </h4>
                    
                    <div class="space-y-3 {{ (request('source') === 'schedule') ? 'lg:grid lg:grid-cols-2 lg:gap-3.5 lg:space-y-0' : '' }}">
                        @foreach($pertanyaan as $index => $tanya)
                        @if($index < 14)
                        <div x-data="{ 
                                jawaban: $persist('{{ old('checklist.'.$index.'.jawaban', '') }}').using(sessionStorage).as('inspeksi_jawaban_{{ $index }}_{{ $apar->id }}'),
                                keterangan: $persist('{{ addslashes(old('checklist.'.$index.'.keterangan', '')) }}').using(sessionStorage).as('inspeksi_ket_{{ $index }}_{{ $apar->id }}')
                             }" 
                             class="group flex flex-col sm:flex-row gap-3 p-3 rounded-xl border-2 transition-all duration-300 hover:shadow-sm" 
                             :class="jawaban === 'ya' ? 'bg-teal-50/50 border-teal-200/60' : (jawaban === 'tidak' ? 'bg-red-50/80 border-red-200' : 'bg-white border-slate-100 hover:border-slate-200')">
                             
                            <!-- Number & Question -->
                            <div class="flex-1 flex gap-3">
                                <div class="w-6 h-6 rounded-full flex items-center justify-center font-black text-[10px] flex-shrink-0 transition-colors"
                                     :class="jawaban === 'ya' ? 'bg-[#009B77] text-white' : (jawaban === 'tidak' ? 'bg-red-500 text-white' : 'bg-slate-100 text-slate-400 group-hover:bg-[#009B77] group-hover:text-white')">
                                    {{ $index + 1 }}
                                </div>
                                <p class="font-bold text-slate-700 text-xs leading-relaxed pt-0.5">{{ $tanya }}</p>
                            </div>
                            
                            <!-- Toggle Ya / Tidak -->
                            <div class="flex flex-col gap-2 min-w-[180px]">
                                <div class="flex bg-slate-100/80 rounded-lg p-1 border border-slate-200 shadow-inner self-start w-full sm:w-auto">
                                    <input type="hidden" name="checklist[{{ $index }}][jawaban]" x-model="jawaban">
                                    <button type="button" @click="jawaban = 'ya'" 
                                            class="flex-1 px-3 py-1.5 rounded text-[10px] font-bold transition-all duration-300 flex items-center justify-center gap-1.5" 
                                            :class="jawaban === 'ya' ? 'bg-[#009B77] text-white shadow-sm scale-[1.02]' : 'text-slate-500 hover:bg-white hover:text-slate-700 hover:shadow-sm'">
                                        <i class="ph-bold ph-check text-[10px]"></i> {{ __('Yes') }}
                                    </button>
                                    <button type="button" @click="jawaban = 'tidak'" 
                                            class="flex-1 px-3 py-1.5 rounded text-[10px] font-bold transition-all duration-300 flex items-center justify-center gap-1.5" 
                                            :class="jawaban === 'tidak' ? 'bg-red-500 text-white shadow-sm scale-[1.02]' : 'text-slate-500 hover:bg-white hover:text-slate-700 hover:shadow-sm'">
                                        <i class="ph-bold ph-x text-[10px]"></i> {{ __('No') }}
                                    </button>
                                </div>
                                
                                <!-- Input Keterangan -->
                                <div x-show="jawaban === 'tidak'" x-collapse>
                                    <div class="relative">
                                        <i class="ph-bold absolute left-2.5 top-1/2 -translate-y-1/2 text-base" :class="jawaban === 'tidak' ? 'ph-warning-circle text-red-400' : 'ph-note-pencil text-slate-400'"></i>
                                        <input type="text" name="checklist[{{ $index }}][keterangan]" x-model="keterangan" 
                                               placeholder="{{ __('Notes (Optional)...') }}" 
                                               class="w-full text-[11px] py-2 pl-8 pr-3 border-2 bg-white rounded-lg focus:ring-2 outline-none transition-all font-medium shadow-sm"
                                               :class="jawaban === 'tidak' ? 'border-red-200 focus:border-red-400 focus:ring-red-100 placeholder:text-red-300 text-red-700' : 'border-slate-200 focus:border-[#009B77] focus:ring-[#009B77]/15 placeholder:text-slate-300 text-slate-700'">
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        @endforeach
                    </div>
                </div>
                
                <div class="p-4 sm:p-5 border-t border-slate-100 bg-slate-50/50">
                <h4 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <i class="ph-bold ph-note text-[#009B77] text-lg"></i> {{ __('Conclusion & Documentation') }}
                </h4>
                
                <div class="space-y-4">
                    <div class="grid grid-cols-1 {{ (request('source') === 'schedule') ? 'lg:grid-cols-12 lg:gap-6 items-start' : 'md:grid-cols-2 gap-6' }}">
                        <!-- Status Akhir -->
                        <div class="{{ (request('source') === 'schedule') ? 'lg:col-span-6' : 'md:col-span-2' }}">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('Overall Status') }}</label>
                            <div x-data="{ open: false, options: { 'sesuai': '{{ __('Sesuai') }}', 'tidak_sesuai': '{{ __('Tidak Sesuai') }}', 'perlu_refill': '{{ __('Perlu Refill') }}' } }" class="relative">
                                <input type="hidden" name="status" :value="Number(qty) < 1 ? 'perlu_refill' : statusAkhir">
                                <i class="ph-bold absolute left-4 top-1/2 -translate-y-1/2 text-lg transition-colors z-10"
                                   :class="{
                                       'ph-shield-check text-[#009B77]': Number(qty) >= 1 && statusAkhir === 'sesuai',
                                       'ph-warning-circle text-red-500': Number(qty) >= 1 && statusAkhir === 'tidak_sesuai',
                                       'ph-arrows-clockwise text-amber-500': Number(qty) < 1 || statusAkhir === 'perlu_refill'
                                   }"></i>
                                <button type="button" 
                                        :disabled="Number(qty) < 1"
                                        @click="if (Number(qty) >= 1) open = !open" 
                                        @click.away="open = false" 
                                        class="w-full border-2 rounded-xl py-3 pl-11 pr-4 text-sm font-bold transition-all outline-none text-left flex items-center justify-between"
                                        :class="{
                                            'bg-slate-100/80 border-slate-200 text-slate-500 cursor-not-allowed select-none': Number(qty) < 1,
                                            'bg-white border-slate-200 text-slate-800 cursor-pointer focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15': Number(qty) >= 1,
                                            'border-[#009B77] ring-4 ring-[#009B77]/15': open && Number(qty) >= 1
                                        }">
                                    <span x-text="Number(qty) < 1 ? options['perlu_refill'] : (options[statusAkhir] || options['sesuai'])" class="truncate block"></span>
                                    <i class="ph-bold ph-caret-down text-slate-400 transition-transform duration-200 flex-shrink-0" :class="{ 'rotate-180 text-[#009B77]': open && Number(qty) >= 1, 'opacity-40': Number(qty) < 1 }"></i>
                                </button>
                                
                                <!-- Dropdown Menu (Hanya aktif jika Qty >= 1) -->
                                <div x-show="open && Number(qty) >= 1" 
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-100"
                                     x-transition:leave-start="opacity-100 scale-100"
                                     x-transition:leave-end="opacity-0 scale-95"
                                     style="display: none;" 
                                     class="absolute left-0 bottom-full mb-2 z-50 w-full bg-white rounded-xl shadow-[0_-10px_40px_rgba(0,0,0,0.1)] border border-slate-100 py-2 overflow-hidden origin-bottom">
                                    <template x-for="(label, value) in options" :key="value">
                                        <button type="button" @click="statusAkhir = value; open = false" class="w-full text-left px-4 py-2.5 text-sm font-bold transition-colors flex items-center justify-between" :class="statusAkhir === value ? 'text-[#009B77] bg-[#009B77]/5' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'">
                                            <span x-text="label"></span>
                                            <i class="ph-bold ph-check text-[#009B77]" x-show="statusAkhir === value"></i>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>
                        <!-- Qty -->
                        <div class="{{ (request('source') === 'schedule') ? 'lg:col-span-3' : '' }}">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Qty</label>
                            <div class="relative">
                                <i class="ph-bold ph-hash absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
                                <input type="number" name="qty" min="0" x-model="qty" required
                                       class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 pl-12 pr-4 text-sm font-bold text-slate-700 focus:bg-white focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none">
                            </div>
                        </div>
                        
                        <!-- Tgl Kedaluwarsa Display/Confirmed -->
                        <div class="{{ (request('source') === 'schedule') ? 'lg:col-span-3' : '' }}">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Expired</label>
                            <div class="relative">
                                <input type="text" :value="tgl_kedaluwarsa" readonly
                                       class="w-full bg-slate-100 border-2 border-slate-200 rounded-xl py-2.5 px-4 text-sm font-bold text-slate-700 outline-none cursor-default">
                            </div>
                        </div>

                        <!-- Upload Foto & Watermark Lokasi Real -->
                        <div class="{{ (request('source') === 'schedule') ? 'lg:col-start-7 lg:col-span-6 lg:row-start-1 lg:row-span-4' : 'md:col-span-2' }}">
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">
                                    {{ __('Condition Photo') }}
                                    <span class="text-[10px] text-red-500 ml-1">
                                        {{ __('(Wajib)') }}
                                    </span>
                                </label>
                            </div>
                            @if(request('source') === 'schedule')
                            <p class="text-[10px] text-slate-500 mb-3 leading-relaxed">
                                <i class="ph-bold ph-info text-[#009B77] mr-0.5"></i> {{ __('Inspeksi by Sistem: Unggah foto kondisi APAR / form fisik. Watermark resmi berbasis data sistem & master APAR akan dicetak otomatis seketika.') }}
                            </p>
                            @else
                            <p class="text-[10px] text-slate-500 mb-3 leading-relaxed">
                                <i class="ph-bold ph-info text-[#009B77] mr-0.5"></i> {{ __('Inspeksi Langsung Lapangan: Ambil foto kondisi APAR. Watermark lokasi terdaftar, koordinat GPS riil, & peta akan dicetak di pojok kiri atas.') }}
                            </p>
                            @endif
                            <div class="space-y-3" x-data="photoUploader">
                                <div class="relative w-full rounded-xl overflow-hidden bg-slate-100 border-2 border-dashed border-slate-300 hover:border-[#009B77] transition-all min-h-[220px] flex items-center justify-center group">
                                    <input type="hidden" name="foto_base64" :value="photoPreview">
                                    <input type="file" id="foto" class="hidden" accept="image/*" {{ request('source') === 'schedule' ? '' : 'capture="environment"' }}
                                           @change="processImage($event.target.files[0])">
                                    
                                    <!-- Loading State -->
                                    <div x-show="isProcessing" class="absolute inset-0 z-30 bg-white/95 backdrop-blur-sm flex flex-col items-center justify-center gap-2">
                                        <i class="bi bi-arrow-repeat animate-spin text-3xl text-[#009B77]"></i>
                                        <span class="text-xs font-bold text-slate-700" x-text="isSystemSource ? '{{ __('Mencetak Watermark Data Sistem...') }}' : '{{ __('Mencetak Watermark Lokasi & Maps...') }}'"></span>
                                        <span class="text-[10px] text-slate-400" x-text="isSystemSource ? '{{ __('Menyematkan data master & verifikasi sistem...') }}' : '{{ __('Menyematkan koordinat dan nama area riil...') }}'"></span>
                                    </div>

                                    <!-- Tombol Take Photo saat belum ada foto -->
                                    <label for="foto" class="absolute inset-0 z-10 flex flex-col items-center justify-center gap-2.5 p-4 cursor-pointer transition-all" x-show="!photoPreview && !isProcessing">
                                        <div class="w-13 h-13 rounded-full bg-white shadow-sm flex items-center justify-center text-slate-400 group-hover:text-[#009B77] group-hover:scale-110 transition-all flex-shrink-0 border border-slate-200">
                                            <i class="bi" :class="isSystemSource ? 'bi-cloud-arrow-up text-2xl' : 'bi-camera text-2xl'"></i>
                                        </div>
                                        <span class="text-sm font-bold text-slate-600 group-hover:text-[#009B77] text-center leading-none" x-text="isSystemSource ? '{{ __('Pilih / Unggah Foto Kondisi APAR') }}' : '{{ __('Take Photo') }}'"></span>
                                        <span class="text-[10px] font-bold text-red-600 bg-red-50 px-3 py-1.5 border border-red-200 rounded-md uppercase tracking-wider text-center leading-tight">
                                            <i class="bi bi-aspect-ratio mr-1"></i>{{ __('TAKE PHOTO IN LANDSCAPE') }}
                                        </span>
                                        
                                        <!-- Status Live GPS & Maps / Sistem Terkoneksi -->
                                        <template x-if="isSystemSource">
                                            <div class="flex items-center gap-1.5 text-[10px] font-bold px-3 py-1 rounded-full border bg-emerald-50 text-emerald-700 border-emerald-200 mt-1">
                                                <i class="bi bi-shield-check text-emerald-600"></i>
                                                <span>{{ __('Mode Sistem: Watermark Instan Tanpa GPS') }}</span>
                                            </div>
                                        </template>
                                        <template x-if="!isSystemSource">
                                            <div class="flex items-center gap-1.5 text-[10px] font-bold px-3 py-1 rounded-full cursor-pointer hover:opacity-90 transition-opacity mt-1 border"
                                                 @click.stop="requestLocation()"
                                                 :class="{
                                                     'bg-emerald-50 text-emerald-700 border-emerald-200': gpsStatus === 'success',
                                                     'bg-amber-50 text-amber-700 border-amber-200': gpsStatus === 'locating',
                                                     'bg-red-50 text-red-600 border-red-200': gpsStatus === 'error' || gpsStatus === 'idle'
                                                 }">
                                                <i class="bi" :class="gpsStatus === 'success' ? 'bi-check-circle-fill text-emerald-600' : (gpsStatus === 'locating' ? 'bi-arrow-repeat animate-spin text-amber-600' : 'bi-geo-alt-fill text-red-500')"></i>
                                                <span x-text="gpsStatus === 'success' ? 'GPS Terhubung (±' + (gpsAccuracy || 0) + 'm)' : (gpsStatus === 'locating' ? 'Mencari sinyal GPS...' : 'GPS belum terhubung (Klik untuk aktifkan)')"></span>
                                            </div>
                                        </template>
                                    </label>

                                    <!-- Preview Image Display (Klik untuk Zoom/Modal) -->
                                    <template x-if="photoPreview">
                                        <div class="absolute inset-0 w-full h-full cursor-pointer group/img" @click="showFullPhoto = true" title="{{ __('Klik untuk melihat foto ukuran penuh') }}">
                                            <img :src="photoPreview" class="w-full h-full object-cover transition-transform duration-300 group-hover/img:scale-105">
                                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent pointer-events-none"></div>
                                            
                                            <!-- Badge Status Verifikasi di Pojok Kiri Atas Preview -->
                                            <div class="absolute top-3 left-3 bg-slate-900/85 backdrop-blur-md border border-white/20 text-white text-[10px] font-bold px-2.5 py-1 rounded-lg flex items-center gap-1.5 shadow-md">
                                                <i class="bi bi-shield-fill-check text-emerald-400"></i>
                                                <span>{{ __('Foto Berwatermark') }}</span>
                                            </div>

                                            <!-- Tombol & Hint Zoom di Tengah/Bawah -->
                                            <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover/img:opacity-100 transition-opacity bg-black/30 pointer-events-none">
                                                <div class="px-3.5 py-1.5 rounded-full bg-white/90 text-slate-800 text-xs font-bold flex items-center gap-1.5 shadow-lg backdrop-blur">
                                                    <i class="bi bi-zoom-in text-base text-[#009B77]"></i>
                                                    {{ __('Klik untuk Perbesar') }}
                                                </div>
                                            </div>

                                            <!-- Tombol Ambil Ulang Foto -->
                                            <label for="foto" @click.stop class="absolute bottom-3 right-3 bg-white/95 hover:bg-white backdrop-blur px-3 py-1.5 rounded-lg text-xs font-bold text-slate-700 shadow-md cursor-pointer transition-colors flex items-center gap-1.5 z-20 border border-slate-200 hover:text-[#009B77]">
                                                <i class="bi bi-arrow-repeat text-[#009B77]"></i> {{ __('Ambil Ulang Foto') }}
                                            </label>

                                            <!-- Petunjuk Kecil di Pojok Kiri Bawah -->
                                            <div class="absolute bottom-3 left-3 text-[10px] text-white/90 font-medium flex items-center gap-1 drop-shadow pointer-events-none">
                                                <i class="bi bi-arrows-fullscreen text-[11px]"></i> {{ __('Ketuk foto untuk pratinjau penuh') }}
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                
                                <!-- Modal Lightbox Fullscreen Preview Foto (Ringan Tanpa Animasi) -->
                                <div x-show="showFullPhoto" 
                                     class="fixed inset-0 z-[120] flex items-center justify-center p-3 sm:p-6 bg-black/85" 
                                     x-cloak 
                                     @keydown.escape.window="showFullPhoto = false">
                                     
                                    <div class="relative max-w-4xl w-full bg-slate-900 rounded-xl overflow-hidden shadow-xl border border-white/10 flex flex-col max-h-[92vh]" @click.outside="showFullPhoto = false">
                                        <!-- Header Modal -->
                                        <div class="flex items-center justify-between px-4 py-3 bg-slate-950/80 border-b border-white/10 text-white z-10">
                                            <div class="flex items-center gap-2">
                                                <i class="bi bi-patch-check-fill text-[#009B77] text-lg"></i>
                                                <div>
                                                    <h3 class="text-xs sm:text-sm font-bold leading-tight" x-text="kodeApar + ' • Pratinjau Foto Asli Watermark'"></h3>
                                                    <p class="text-[10px] text-slate-400 leading-none mt-0.5" x-text="lokasiTerdaftar"></p>
                                                </div>
                                            </div>
                                            <button type="button" @click="showFullPhoto = false" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/25 flex items-center justify-center text-white transition-colors cursor-pointer">
                                                <i class="bi bi-x-lg text-sm"></i>
                                            </button>
                                        </div>

                                        <!-- Body Gambar -->
                                        <div class="flex-1 overflow-auto bg-black flex items-center justify-center p-2 sm:p-4">
                                            <img :src="photoPreview" class="max-w-full max-h-[75vh] object-contain rounded-lg shadow-lg">
                                        </div>

                                        <!-- Footer Info -->
                                        <div class="px-4 py-2.5 bg-slate-950/80 border-t border-white/10 flex items-center justify-between text-[11px] text-slate-300">
                                            <div class="flex items-center gap-3">
                                                <span class="flex items-center gap-1"><i class="bi bi-person-fill text-slate-400"></i> <strong x-text="picName"></strong></span>
                                                <span class="hidden sm:inline-flex items-center gap-1 text-slate-400"><i class="bi bi-clock-fill"></i> <span x-text="capturedTime"></span></span>
                                            </div>
                                            <button type="button" @click="showFullPhoto = false" class="px-4 py-1.5 rounded-lg bg-[#009B77] hover:bg-[#008264] text-white font-bold text-xs transition-colors">
                                                {{ __('Tutup') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Preview Data Watermark di Bagian Form (Tanpa Emoticon, dengan Bootstrap Icons) -->
                                <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3 space-y-2 text-xs transition-all" x-show="photoPreview" x-cloak>
                                    <div class="flex items-center justify-between pb-2 border-b border-slate-200">
                                        <span class="font-bold text-slate-700 flex items-center gap-1.5">
                                            <i class="bi bi-file-earmark-check text-[#009B77] text-sm"></i>
                                            {{ __('Detail Watermark Tersemat') }}
                                        </span>
                                        <span class="text-[10px] font-bold bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-md flex items-center gap-1">
                                            <i class="bi bi-patch-check-fill text-emerald-600"></i> {{ __('Tercetak di Pojok Kiri Atas') }}
                                        </span>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-[11px]">
                                        <!-- Lokasi Terdaftar -->
                                        <div class="bg-white p-2 rounded-lg border border-slate-200/70 flex items-start gap-2">
                                            <i class="bi bi-building text-[#009B77] mt-0.5 text-sm"></i>
                                            <div>
                                                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Lokasi APAR') }}</span>
                                                <span class="font-bold text-slate-800" x-text="lokasiTerdaftar"></span>
                                            </div>
                                        </div>

                                        <!-- Status Validasi / Koordinat -->
                                        <div class="bg-white p-2 rounded-lg border border-slate-200/70 flex items-start gap-2">
                                            <i class="bi" :class="isSystemSource ? 'bi-shield-check text-[#009B77]' : 'bi-crosshair text-amber-600'" mt-0.5 text-sm></i>
                                            <div>
                                                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider" x-text="isSystemSource ? '{{ __('Validasi') }}' : '{{ __('Koordinat GPS') }}'"></span>
                                                <span class="font-bold text-slate-700" x-text="isSystemSource ? '{{ __('Verified by System Data') }}' : (gpsLat && gpsLng ? `${gpsLat.toFixed(6)}, ${gpsLng.toFixed(6)} (±${gpsAccuracy}m)` : 'GPS tidak aktif')"></span>
                                            </div>
                                        </div>

                                        <!-- PIC -->
                                        <div class="bg-white p-2 rounded-lg border border-slate-200/70 flex items-start gap-2">
                                            <i class="bi bi-person-badge-fill text-indigo-600 mt-0.5 text-sm"></i>
                                            <div>
                                                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Petugas (PIC)') }}</span>
                                                <span class="font-bold text-slate-800" x-text="picName"></span>
                                            </div>
                                        </div>

                                        <!-- Waktu Pengambilan -->
                                        <div class="bg-white p-2 rounded-lg border border-slate-200/70 flex items-start gap-2">
                                            <i class="bi bi-clock-history text-slate-600 mt-0.5 text-sm"></i>
                                            <div>
                                                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Waktu Pencatatan') }}</span>
                                                <span class="font-bold text-slate-800" x-text="capturedTime || 'Waktu Real-time'"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @error('foto_base64')
                                <p class="text-xs text-red-500 font-bold mt-2"><i class="ph-bold ph-warning-circle mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Catatan Tambahan -->
                        <div class="{{ (request('source') === 'schedule') ? 'lg:col-span-6' : 'md:col-span-2' }}">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('Additional Notes (Optional)') }}</label>
                            <div class="relative">
                                <i class="ph-bold ph-note-pencil absolute left-4 top-4 text-slate-400 text-lg"></i>
                                <textarea rows="{{ (request('source') === 'schedule') ? '4' : '3' }}" name="catatan_tambahan" placeholder="{{ __('Write other general observation notes...') }}" x-model="catatan_tambahan"
                                          class="w-full bg-white border-2 border-slate-200 rounded-xl py-3 pl-11 pr-4 text-sm font-bold text-slate-800 focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none placeholder:text-slate-400 placeholder:font-medium resize-none"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-4 sm:p-5 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 bg-white sticky bottom-0 z-10 shadow-[0_-5px_15px_rgba(0,0,0,0.02)]">
                <!-- Tombol Kembali: Hanya muncul di mobile saat step 2 -->
                <button x-show="!isDesktop && step === 2" type="button" @click="step = 1; setTimeout(() => window.scrollTo({ top: 0, behavior: 'smooth' }), 50);" class="w-full sm:w-auto h-11 px-5 rounded-xl font-bold text-slate-600 hover:text-slate-900 bg-white border-2 border-slate-200 hover:border-slate-300 transition-all text-xs flex items-center justify-center gap-2 shrink-0">
                    <i class="ph-bold ph-arrow-left text-base"></i>
                    {{ __('Kembali ke Langkah 1') }}
                </button>
                <div x-show="isDesktop || step === 1" class="text-xs text-slate-500 font-medium hidden sm:flex items-center gap-2">
                    <i class="ph-bold ph-shield-check text-[#009B77] text-base"></i>
                    <span>{{ __('Pastikan seluruh checklist fisik, fungsi, dan foto APAR telah dilengkapi.') }}</span>
                </div>
                <div class="flex items-center gap-2.5 w-full sm:w-auto ml-auto">
                    <a href="{{ $backUrl }}" class="w-28 sm:w-auto px-4 h-11 rounded-xl font-bold text-red-500 hover:text-red-600 bg-red-50 hover:bg-red-100 border border-red-100/80 transition-all text-xs flex items-center justify-center shrink-0">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit" class="flex-1 sm:flex-none h-11 btn-smooth-ring bg-[#009B77] hover:bg-[#008264] text-white font-bold px-5 sm:px-6 rounded-xl shadow-sm shadow-[#009B77]/25 transition-all flex items-center justify-center gap-2 text-xs whitespace-nowrap">
                        <i class="ph-bold ph-check-circle text-base"></i>
                        {{ __('Complete Inspection') }}
                    </button>
                </div>
            </div>
        </div>
    </form>
    </div>

    <!-- Modal Edit Data APAR (Mobile Friendly) -->
    <div x-show="showModalEditApar" class="fixed inset-0 z-[100] flex flex-col bg-slate-50" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-full"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-full">
         
        <!-- Header Modal -->
        <div class="bg-white border-b border-slate-200 p-4 flex items-center gap-3 sticky top-0 z-10 shadow-sm">
            <button type="button" @click="showModalEditApar = false" class="w-10 h-10 rounded-full flex items-center justify-center text-slate-500 hover:bg-slate-100 transition-colors">
                <i class="ph-bold ph-x text-xl"></i>
            </button>
            <h2 class="text-lg font-bold text-slate-800">{{ __('Update PFE Data') }}</h2>
        </div>

        <!-- Form Content -->
        <div class="flex-1 overflow-y-auto p-4 sm:p-6 bg-slate-50">
            <form :action="`/master-data/apar/${editApar.id}`" method="POST" id="form-edit-apar-mobile">
                @csrf
                @method('PUT')
                
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 space-y-5" x-data="{
                        lokasiOptions: [
                            @foreach($gedungs->pluck('lokasi')->flatten() as $lok)
                            { id: '{{ $lok->id }}', nama: '{{ addslashes($lok->nama) }}', gedung_id: '{{ $lok->gedung_id }}' },
                            @endforeach
                        ],
                        get filteredLokasi() {
                            if (!editApar.gedung_id) return [];
                            return this.lokasiOptions.filter(l => l.gedung_id == editApar.gedung_id);
                        }
                    }">
                    
                    <!-- {{ __('PFE Number') }} -->
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('PFE Number') }} <span class="text-[10px] text-slate-400 font-medium normal-case">{{ __('(Automatic)') }}</span></label>
                        <div class="relative">
                            <i class="ph-bold ph-hash absolute left-3.5 top-1/2 -translate-y-1/2 text-amber-500 text-lg"></i>
                            <input type="number" name="nomor_apar" placeholder="{{ __('Enter sequence number...') }}" required x-model="editApar.nomor_apar" class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-3 pl-10 pr-4 text-sm font-medium text-slate-700 focus:bg-white focus:border-amber-500 focus:ring-4 focus:ring-amber-500/15 transition-all outline-none">
                        </div>
                    </div>

                    <!-- Nomor Ruangan -->
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('ROOM NO') }} <span class="text-[10px] text-slate-400 font-medium normal-case">({{ __('Optional') }})</span></label>
                        <div class="relative">
                            <i class="ph-bold ph-door absolute left-3.5 top-1/2 -translate-y-1/2 text-amber-500 text-lg"></i>
                            <input type="text" name="nomor_ruangan" placeholder="{{ __('Example: R-101') }}" x-model="editApar.nomor_ruangan" class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-3 pl-10 pr-4 text-sm font-medium text-slate-700 focus:bg-white focus:border-amber-500 focus:ring-4 focus:ring-amber-500/15 transition-all outline-none">
                        </div>
                    </div>

                    <!-- Gedung -->
                    <div x-data="{
                        open: false,
                        search: '',
                        options: [
                            @foreach($gedungs as $gedung)
                            { id: '{{ $gedung->id }}', name: '{{ addslashes($gedung->nama) }}' },
                            @endforeach
                        ],
                        get selectedName() {
                            let sel = this.options.find(o => o.id == editApar.gedung_id);
                            return sel ? sel.name : '{{ __('Select Building') }}';
                        },
                        get filteredOptions() {
                            if (this.search === '') return this.options;
                            return this.options.filter(o => o.name.toLowerCase().includes(this.search.toLowerCase()));
                        }
                    }">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('Building') }}</label>
                        <div class="relative">
                            <input type="hidden" name="gedung_id" :value="editApar.gedung_id" required>
                            <i class="ph-bold ph-buildings absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg transition-colors z-10" :class="open ? 'text-amber-500' : 'peer-focus:text-amber-500'"></i>
                            <button type="button" @click="open = !open" @click.away="open = false" 
                                    class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-3 pl-10 pr-4 text-sm font-medium text-slate-700 focus:bg-white focus:border-amber-500 focus:ring-4 focus:ring-amber-500/15 transition-all outline-none cursor-pointer text-left flex items-center justify-between"
                                    :class="open ? 'bg-white border-amber-500 ring-4 ring-amber-500/15' : ''">
                                <span x-text="selectedName" :class="editApar.gedung_id ? 'font-bold text-slate-700' : 'text-slate-400 font-medium'" class="truncate block"></span>
                                <i class="ph-bold ph-caret-down text-slate-400 transition-transform duration-200 flex-shrink-0" :class="open ? 'rotate-180 text-amber-500' : ''"></i>
                            </button>
                            <div x-show="open" x-cloak class="absolute left-0 z-50 w-full mt-2 bg-white rounded-xl shadow-[0_10px_40px_rgba(0,0,0,0.1)] border border-slate-100 py-2 max-h-60 overflow-y-auto origin-top">
                                <div class="sticky top-0 bg-white p-2 border-b border-slate-100 z-10">
                                    <div class="relative">
                                        <i class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                        <input type="text" x-model="search" placeholder="{{ __('Search...') }}" class="w-full bg-slate-50 border border-slate-200 rounded-lg py-2 pl-9 pr-3 text-sm font-medium text-slate-700 focus:outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 transition-all" @click.stop @keydown.enter.prevent>
                                    </div>
                                </div>
                                <div class="py-1">
                                    <template x-for="option in filteredOptions" :key="option.id">
                                    <button type="button" @click="editApar.gedung_id = option.id; open = false" class="w-full text-left px-4 py-3 text-sm font-medium transition-colors flex items-center justify-between" :class="editApar.gedung_id == option.id ? 'text-amber-500 bg-amber-50' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'">
                                        <span x-text="option.name" class="font-bold"></span>
                                    </button>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lokasi -->
                    <div x-data="{
                        open: false,
                        search: '',
                        get filtered() {
                            if (this.search === '') return this.filteredLokasi;
                            return this.filteredLokasi.filter(l => l.nama.toLowerCase().includes(this.search.toLowerCase()));
                        },
                        init() {
                            this.search = editApar.lokasi || '';
                            this.$watch('editApar.lokasi', val => this.search = val || '');
                        }
                    }">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('Location') }} <span class="text-[10px] text-slate-400 font-medium normal-case">{{ __('(Type)') }}</span></label>
                        <div class="relative">
                            <input type="hidden" name="lokasi" :value="editApar.lokasi">
                            <i class="ph-bold ph-map-pin absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg transition-colors z-10" :class="open ? 'text-amber-500' : 'peer-focus:text-amber-500'"></i>
                            <input type="text" x-model="editApar.lokasi" @focus="open = true" @click.away="open = false" placeholder="{{ __('Example: Corridor') }}" required 
                                   class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-3 pl-10 pr-4 text-sm font-medium text-slate-700 focus:bg-white focus:border-amber-500 focus:ring-4 focus:ring-amber-500/15 transition-all outline-none"
                                   :class="open ? 'bg-white border-amber-500 ring-4 ring-amber-500/15' : ''">
                            <div x-show="open && filteredLokasi.length > 0" x-cloak class="absolute left-0 z-50 w-full mt-2 bg-white rounded-xl shadow-[0_10px_40px_rgba(0,0,0,0.1)] border border-slate-100 py-2 max-h-60 overflow-y-auto origin-top">
                                <div class="py-1">
                                    <template x-for="lok in filtered" :key="lok.id">
                                    <button type="button" @click="editApar.lokasi = lok.nama; open = false" class="w-full text-left px-4 py-3 text-sm font-medium transition-colors flex items-center justify-between" :class="editApar.lokasi == lok.nama ? 'text-amber-500 bg-amber-50' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'">
                                        <span x-text="lok.nama" class="font-bold"></span>
                                    </button>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Jenis APAR -->
                    <div x-data="{
                        open: false,
                        search: '',
                        options: [
                            @foreach($jenisApars as $jenis)
                            { id: '{{ $jenis->id }}', name: '{{ addslashes($jenis->nama) }}' },
                            @endforeach
                        ],
                        get selectedName() {
                            let sel = this.options.find(o => o.id == editApar.jenis_id);
                            return sel ? sel.name : '{{ __('Select Type') }}';
                        },
                        get filteredOptions() {
                            if (this.search === '') return this.options;
                            return this.options.filter(o => o.name.toLowerCase().includes(this.search.toLowerCase()));
                        }
                    }">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('PFE Type') }}</label>
                        <div class="relative">
                            <input type="hidden" name="jenis_id" :value="editApar.jenis_id" required>
                            <i class="ph-bold ph-fire-extinguisher absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg transition-colors z-10" :class="open ? 'text-amber-500' : 'peer-focus:text-amber-500'"></i>
                            <button type="button" @click="open = !open" @click.away="open = false" 
                                    class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-3 pl-10 pr-4 text-sm font-medium text-slate-700 focus:bg-white focus:border-amber-500 focus:ring-4 focus:ring-amber-500/15 transition-all outline-none cursor-pointer text-left flex items-center justify-between"
                                    :class="open ? 'bg-white border-amber-500 ring-4 ring-amber-500/15' : ''">
                                <span x-text="selectedName" :class="editApar.jenis_id ? 'font-bold text-slate-700' : 'text-slate-400 font-medium'" class="truncate block"></span>
                                <i class="ph-bold ph-caret-down text-slate-400 transition-transform duration-200 flex-shrink-0" :class="open ? 'rotate-180 text-amber-500' : ''"></i>
                            </button>
                            <div x-show="open" x-cloak class="absolute left-0 z-50 w-full mt-2 bg-white rounded-xl shadow-[0_10px_40px_rgba(0,0,0,0.1)] border border-slate-100 py-2 max-h-60 overflow-y-auto origin-top">
                                <div class="sticky top-0 bg-white p-2 border-b border-slate-100 z-10">
                                    <div class="relative">
                                        <i class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                        <input type="text" x-model="search" placeholder="{{ __('Search...') }}" class="w-full bg-slate-50 border border-slate-200 rounded-lg py-2 pl-9 pr-3 text-sm font-medium text-slate-700 focus:outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 transition-all" @click.stop @keydown.enter.prevent>
                                    </div>
                                </div>
                                <div class="py-1">
                                    <template x-for="option in filteredOptions" :key="option.id">
                                    <button type="button" @click="editApar.jenis_id = option.id; open = false" class="w-full text-left px-4 py-3 text-sm font-medium transition-colors flex items-center justify-between" :class="editApar.jenis_id == option.id ? 'text-amber-500 bg-amber-50' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'">
                                        <span x-text="option.name" class="font-bold"></span>
                                    </button>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kapasitas -->
                    <div x-data="{
                        open: false,
                        search: '',
                        options: [
                            @foreach($kapasitasApars as $kapasitas)
                            { id: '{{ $kapasitas->id }}', name: '{{ addslashes($kapasitas->ukuran) }}' },
                            @endforeach
                        ],
                        get selectedName() {
                            let sel = this.options.find(o => o.id == editApar.kapasitas_id);
                            return sel ? sel.name : '{{ __('Select Capacity') }}';
                        },
                        get filteredOptions() {
                            if (this.search === '') return this.options;
                            return this.options.filter(o => o.name.toLowerCase().includes(this.search.toLowerCase()));
                        }
                    }">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('Capacity') }}</label>
                        <div class="relative">
                            <input type="hidden" name="kapasitas_id" :value="editApar.kapasitas_id" required>
                            <i class="ph-bold ph-scales absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg transition-colors z-10" :class="open ? 'text-amber-500' : 'peer-focus:text-amber-500'"></i>
                            <button type="button" @click="open = !open" @click.away="open = false" 
                                    class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-3 pl-10 pr-4 text-sm font-medium text-slate-700 focus:bg-white focus:border-amber-500 focus:ring-4 focus:ring-amber-500/15 transition-all outline-none cursor-pointer text-left flex items-center justify-between"
                                    :class="open ? 'bg-white border-amber-500 ring-4 ring-amber-500/15' : ''">
                                <span x-text="selectedName" :class="editApar.kapasitas_id ? 'font-bold text-slate-700' : 'text-slate-400 font-medium'" class="truncate block"></span>
                                <i class="ph-bold ph-caret-down text-slate-400 transition-transform duration-200 flex-shrink-0" :class="open ? 'rotate-180 text-amber-500' : ''"></i>
                            </button>
                            <div x-show="open" x-cloak class="absolute left-0 z-50 w-full mt-2 bg-white rounded-xl shadow-[0_10px_40px_rgba(0,0,0,0.1)] border border-slate-100 py-2 max-h-60 overflow-y-auto origin-top">
                                <div class="sticky top-0 bg-white p-2 border-b border-slate-100 z-10">
                                    <div class="relative">
                                        <i class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                        <input type="text" x-model="search" placeholder="{{ __('Search...') }}" class="w-full bg-slate-50 border border-slate-200 rounded-lg py-2 pl-9 pr-3 text-sm font-medium text-slate-700 focus:outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 transition-all" @click.stop @keydown.enter.prevent>
                                    </div>
                                </div>
                                <div class="py-1">
                                    <template x-for="option in filteredOptions" :key="option.id">
                                    <button type="button" @click="editApar.kapasitas_id = option.id; open = false" class="w-full text-left px-4 py-3 text-sm font-medium transition-colors flex items-center justify-between" :class="editApar.kapasitas_id == option.id ? 'text-amber-500 bg-amber-50' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'">
                                        <span x-text="option.name" class="font-bold"></span>
                                    </button>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Vendor -->
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('Vendor') }}</label>
                        <div class="relative">
                            <i class="ph-bold ph-storefront absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
                            <input type="text" name="vendor" placeholder="{{ __('Vendor name (Optional)') }}" x-model="editApar.vendor" class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-3 pl-10 pr-4 text-sm font-medium text-slate-700 focus:bg-white focus:border-amber-500 focus:ring-4 focus:ring-amber-500/15 transition-all outline-none">
                        </div>
                    </div>

                    <!-- Tanggal Kedaluwarsa -->
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('Expiration Date') }}</label>
                        <div class="relative">
                            <i class="ph-bold ph-calendar-blank absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg z-10"></i>
                            <input type="text" name="tgl_kedaluwarsa" required x-model="editApar.tgl_kedaluwarsa" class="datepicker w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-3 pl-10 pr-10 text-sm font-medium text-slate-700 focus:bg-white focus:border-amber-500 focus:ring-4 focus:ring-amber-500/15 transition-all outline-none appearance-none cursor-pointer">
                            <i class="ph-bold ph-caret-down absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Footer Button -->
        <div class="bg-white border-t border-slate-200 p-4 sticky bottom-0 z-10">
            <button type="submit" form="form-edit-apar-mobile" class="w-full py-3.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-bold text-[15px] shadow-lg shadow-amber-500/30 transition-all flex items-center justify-center gap-2">
                <i class="ph-bold ph-floppy-disk text-lg"></i> {{ __('Save Changes') }}
            </button>
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

    <!-- Photo Uploader with GPS & Real Maps Watermark Script -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('photoUploader', () => ({
                isSystemSource: @json(request('source') === 'schedule'),
                fileName: '',
                photoPreview: null,
                isProcessing: false,
                gpsStatus: 'idle',
                gpsLat: null,
                gpsLng: null,
                gpsAccuracy: null,
                lokasiTerdaftar: @json(($apar->lokasi->gedung->nama ?? '') . ' - ' . ($apar->lokasi->nama ?? '')),
                kodeApar: @json($apar->kode ?? 'APAR'),
                picName: @json(auth()->user()->name ?? 'Petugas'),
                capturedTime: null,
                showFullPhoto: false,

                init() {
                    if (this.isSystemSource) {
                        this.gpsStatus = 'system';
                    } else {
                        this.requestLocation();
                    }
                },

                requestLocation() {
                    if (this.isSystemSource) return;
                    if ('geolocation' in navigator) {
                        this.gpsStatus = 'locating';
                        navigator.geolocation.getCurrentPosition(
                            (pos) => {
                                this.gpsLat = pos.coords.latitude;
                                this.gpsLng = pos.coords.longitude;
                                this.gpsAccuracy = Math.round(pos.coords.accuracy);
                                this.gpsStatus = 'success';
                            },
                            (err) => {
                                console.warn('GPS Notice/Error:', err.message);
                                this.gpsStatus = 'error';
                            },
                            { enableHighAccuracy: true, timeout: 10000, maximumAge: 30000 }
                        );
                    } else {
                        this.gpsStatus = 'error';
                    }
                },

                processImage(file) {
                    if (!file) {
                        this.photoPreview = null;
                        this.fileName = '';
                        return;
                    }
                    this.fileName = file.name;
                    this.isProcessing = true;

                    const reader = new FileReader();
                    reader.onload = (e) => {
                        const img = new Image();
                        img.onload = () => {
                            const canvas = document.createElement('canvas');
                            let width = img.width;
                            let height = img.height;
                            const MAX_WIDTH = 1280;
                            if (width > MAX_WIDTH) {
                                height = Math.round(height * (MAX_WIDTH / width));
                                width = MAX_WIDTH;
                            }
                            canvas.width = width;
                            canvas.height = height;
                            const ctx = canvas.getContext('2d');

                            // 1. Gambar foto asli
                            ctx.drawImage(img, 0, 0, width, height);

                            // 2. Skala adaptif watermark agar proporsional di resolusi foto apapun
                            const scale = Math.max(width / 1000, 0.85);
                            const padding = 16 * scale;
                            const lineHeight = 20 * scale;
                            const headerFontSize = Math.round(14.5 * scale);
                            const smallFontSize = Math.round(11 * scale);
                            const fontFamily = "'Rotis Sans Serif', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif";

                            // Format waktu real-time seragam (tanggal dan jam dengan titik dua HH:mm:ss WIB)
                            const now = new Date();
                            const pad = (n) => String(n).padStart(2, '0');
                            const timeStr = `${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())} WIB`;
                            const datePart = now.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
                            const dateStr = `${datePart}, ${timeStr}`;

                            // Siapkan baris data metadata (Label dan Nilai) - 4 Tipe Watermark
                            const rows = [];
                            if (this.isSystemSource) {
                                rows.push({ label: 'LOKASI TERDAFTAR', value: this.lokasiTerdaftar || '-', highlight: true });
                                rows.push({ label: 'METODE', value: 'SYSTEM RECORD / DESKTOP ENTRY' });
                                rows.push({ label: 'PIC', value: this.picName || '-' });
                                rows.push({ label: 'WAKTU', value: dateStr });
                            } else {
                                let coordStr = 'Menunggu sinyal GPS';
                                if (this.gpsLat && this.gpsLng) {
                                    coordStr = `${this.gpsLat.toFixed(6)}, ${this.gpsLng.toFixed(6)} (±${this.gpsAccuracy || 0}m)`;
                                }

                                rows.push({ label: 'LOKASI TERDAFTAR', value: this.lokasiTerdaftar || '-', highlight: true });
                                rows.push({ label: 'KOORDINAT', value: coordStr });
                                rows.push({ label: 'PIC', value: this.picName || '-' });
                                rows.push({ label: 'WAKTU', value: dateStr });
                            }

                            // Batas lebar box watermark & posisi pojok kiri atas
                            const boxWidth = Math.min(width * 0.72, 510 * scale);
                            const boxMargin = 16 * scale;
                            const boxX = boxMargin;
                            const boxY = boxMargin;

                            const textX = boxX + padding + (4 * scale);

                            // Ukur lebar label terpanjang agar posisi titik dua (:) sejajar rapi
                            ctx.font = `bold ${smallFontSize}px ${fontFamily}`;
                            let maxLabelWidth = 0;
                            rows.forEach(r => {
                                const w = ctx.measureText(r.label).width;
                                if (w > maxLabelWidth) maxLabelWidth = w;
                            });

                            const colonX = textX + maxLabelWidth + (6 * scale);
                            const valueX = colonX + (12 * scale);
                            const maxValueWidth = (boxX + boxWidth - padding) - valueX;

                            // Helper pemecah teks jika nilai terlalu panjang (misal nama alamat riil GPS)
                            const wrapText = (text, maxWidth, font) => {
                                ctx.font = font;
                                const words = String(text || '').split(' ');
                                const lines = [];
                                let currentLine = '';
                                for (let w of words) {
                                    let testLine = currentLine ? currentLine + ' ' + w : w;
                                    if (ctx.measureText(testLine).width > maxWidth && currentLine) {
                                        lines.push(currentLine);
                                        currentLine = w;
                                    } else {
                                        currentLine = testLine;
                                    }
                                }
                                if (currentLine) lines.push(currentLine);
                                return lines;
                            };

                            // Hitung pembagian baris teks untuk nilai
                            let totalVisualLines = 0;
                            rows.forEach(r => {
                                if (r.multiline) {
                                    let lines = wrapText(r.value, maxValueWidth, `500 ${smallFontSize}px ${fontFamily}`);
                                    if (lines.length > 2) {
                                        lines = lines.slice(0, 2);
                                        lines[1] += '...';
                                    }
                                    r.lines = lines;
                                    totalVisualLines += lines.length;
                                } else {
                                    r.lines = [r.value];
                                    totalVisualLines += 1;
                                }
                            });

                            // Hitung tinggi box secara presisi
                            const headerSpace = (headerFontSize * 0.9) + (8 * scale) + (14 * scale);
                            const rowsSpace = totalVisualLines * (lineHeight * 0.96) + (rows.length * (2 * scale));
                            const boxHeight = (padding * 2) + headerSpace + rowsSpace;

                            // 3. Render Card Background (Sleek Dark Slate 88%)
                            ctx.save();
                            ctx.fillStyle = 'rgba(15, 23, 42, 0.88)'; // Slate-900 88%
                            ctx.strokeStyle = 'rgba(255, 255, 255, 0.20)';
                            ctx.lineWidth = 1.5 * scale;

                            const radius = 10 * scale;
                            ctx.beginPath();
                            ctx.moveTo(boxX + radius, boxY);
                            ctx.lineTo(boxX + boxWidth - radius, boxY);
                            ctx.quadraticCurveTo(boxX + boxWidth, boxY, boxX + boxWidth, boxY + radius);
                            ctx.lineTo(boxX + boxWidth, boxY + boxHeight - radius);
                            ctx.quadraticCurveTo(boxX + boxWidth, boxY + boxHeight, boxX + boxWidth - radius, boxY + boxHeight);
                            ctx.lineTo(boxX + radius, boxY + boxHeight);
                            ctx.quadraticCurveTo(boxX, boxY + boxHeight, boxX, boxY + boxHeight - radius);
                            ctx.lineTo(boxX, boxY + radius);
                            ctx.quadraticCurveTo(boxX, boxY, boxX + radius, boxY);
                            ctx.closePath();
                            ctx.fill();
                            ctx.stroke();

                            // Aksen Hijau Brand EHSS di sisi kiri badge
                            ctx.fillStyle = '#009B77';
                            ctx.fillRect(boxX, boxY + radius, 4.5 * scale, boxHeight - (radius * 2));

                            // 4. Render Header Badge
                            let currentY = boxY + padding + (headerFontSize * 0.85);

                            ctx.font = `bold ${headerFontSize}px ${fontFamily}`;
                            ctx.fillStyle = '#FFFFFF';
                            const headerTitle = `${this.kodeApar} • ${this.isSystemSource ? 'VERIFIED BY SYSTEM' : 'VERIFIED INSPECTION'}`;
                            ctx.fillText(headerTitle, textX, currentY);

                            currentY += (headerFontSize * 0.45) + (4 * scale);

                            // Garis Pembatas Header yang Elegan
                            ctx.strokeStyle = 'rgba(255, 255, 255, 0.14)';
                            ctx.lineWidth = 1 * scale;
                            ctx.beginPath();
                            ctx.moveTo(textX, currentY);
                            ctx.lineTo(boxX + boxWidth - padding, currentY);
                            ctx.stroke();

                            currentY += (14 * scale);

                            // 5. Render Baris Informasi Seragam (Label, Titik Dua Sejajar, dan Nilai)
                            rows.forEach((r) => {
                                // Label Seragam (Slate-400, Bold, Rata Kiri)
                                ctx.font = `bold ${smallFontSize}px ${fontFamily}`;
                                ctx.fillStyle = '#94A3B8';
                                ctx.fillText(r.label, textX, currentY);

                                // Titik Dua ( : ) Seragam & Sejajar di Semua Baris
                                ctx.font = `bold ${smallFontSize}px ${fontFamily}`;
                                ctx.fillStyle = '#94A3B8';
                                ctx.fillText(':', colonX, currentY);

                                // Nilai Seragam (Putih Bersih / Aksen Emerald untuk Lokasi Terdaftar)
                                ctx.font = r.highlight 
                                    ? `bold ${smallFontSize}px ${fontFamily}` 
                                    : `500 ${smallFontSize}px ${fontFamily}`;
                                ctx.fillStyle = r.highlight ? '#34D399' : '#F8FAFC';

                                r.lines.forEach((line, lIdx) => {
                                    ctx.fillText(line, valueX, currentY);
                                    if (lIdx < r.lines.length - 1) {
                                        currentY += lineHeight * 0.95;
                                    }
                                });

                                currentY += lineHeight * 0.98;
                            });

                            ctx.restore();

                            // Simpan hasil render ke photoPreview (Base64 JPEG)
                            this.capturedTime = dateStr;
                            this.photoPreview = canvas.toDataURL('image/jpeg', 0.82);
                            this.isProcessing = false;
                        };
                        img.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            }));
        });
    </script>
</body>
</html>
