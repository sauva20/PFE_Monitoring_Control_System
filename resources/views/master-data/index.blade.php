@extends('layouts.app')

@section('title', 'Master Data APAR - APAR Monitoring System')

@section('content')
    <div class="space-y-6"
        x-data="{ activeTab: localStorage.getItem('masterDataTab') || 'data', showModalApar: {{ old('form_type') == 'tambah_apar' && $errors->any() ? 'true' : 'false' }}, 
            showQrModal: false, qrData: { id: '', kode: '', svg: '' },
            showModalEditApar: {{ old('form_type') == 'edit_apar' && $errors->any() ? 'true' : 'false' }}, 
            editApar: { id:'{{ old('form_type') == 'edit_apar' ? old('id') : '' }}', kode:'{{ old('form_type') == 'edit_apar' ? old('kode') : '' }}', nomor_apar:'{{ old('form_type') == 'edit_apar' ? old('nomor_apar') : '' }}', nomor_ruangan:'{{ old('form_type') == 'edit_apar' ? old('nomor_ruangan') : '' }}', gedung_id:'{{ old('form_type') == 'edit_apar' ? old('gedung_id') : '' }}', lokasi:'{{ old('form_type') == 'edit_apar' ? old('lokasi') : '' }}', jenis_id:'{{ old('form_type') == 'edit_apar' ? old('jenis_id') : '' }}', kapasitas_id:'{{ old('form_type') == 'edit_apar' ? old('kapasitas_id') : '' }}', qty:'{{ old('form_type') == 'edit_apar' ? old('qty') : '' }}', tgl_kedaluwarsa:'{{ old('form_type') == 'edit_apar' ? old('tgl_kedaluwarsa') : '' }}', tgl_isi_ulang:'{{ old('form_type') == 'edit_apar' ? old('tgl_isi_ulang') : '' }}' },  
            showModalLokasi: {{ old('form_type') == 'tambah_lokasi' && $errors->any() ? 'true' : 'false' }}, 
            showModalGedung: {{ old('form_type') == 'tambah_gedung' && $errors->any() ? 'true' : 'false' }}, 
            showModalJenis: {{ old('form_type') == 'tambah_jenis' && $errors->any() ? 'true' : 'false' }}, 
            showModalKapasitas: {{ old('form_type') == 'tambah_kapasitas' && $errors->any() ? 'true' : 'false' }}, 
            showEditGedung: {{ old('form_type') == 'edit_gedung' && $errors->any() ? 'true' : 'false' }}, 
            editGedung: { id:'{{ old('form_type') == 'edit_gedung' ? old('id') : '' }}', nama:'{{ old('form_type') == 'edit_gedung' ? old('nama') : '' }}' }, 
            showEditLokasi: {{ old('form_type') == 'edit_lokasi' && $errors->any() ? 'true' : 'false' }}, 
            editLokasi: { id:'{{ old('form_type') == 'edit_lokasi' ? old('id') : '' }}', nama:'{{ old('form_type') == 'edit_lokasi' ? old('nama') : '' }}', gedung_id:'{{ old('form_type') == 'edit_lokasi' ? old('gedung_id') : '' }}' }, 
            showEditJenis: {{ old('form_type') == 'edit_jenis' && $errors->any() ? 'true' : 'false' }}, 
            editJenis: { id:'{{ old('form_type') == 'edit_jenis' ? old('id') : '' }}', nama:'{{ old('form_type') == 'edit_jenis' ? old('nama') : '' }}' }, 
            showEditKapasitas: {{ old('form_type') == 'edit_kapasitas' && $errors->any() ? 'true' : 'false' }}, 
            editKapasitas: { id:'{{ old('form_type') == 'edit_kapasitas' ? old('id') : '' }}', ukuran:'{{ old('form_type') == 'edit_kapasitas' ? old('ukuran') : '' }}' },
            originalEditApar: '', originalEditGedung: '', originalEditLokasi: '', originalEditJenis: '', originalEditKapasitas: '' }">

        <!-- Header Area & Tabs -->
        <div class="flex flex-col gap-6">
            <div
                class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200/60">
                <!-- Left: Page Context -->
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-xl bg-[#009B77]/10 border border-[#009B77]/20 shadow-sm flex items-center justify-center text-[#009B77]">
                        <i class="ph-bold ph-list-dashes text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-[#007A5E] leading-tight">{{ __('PFE Master Data') }}</h2>
                        <p class="text-xs font-semibold text-slate-500 mt-0.5">{{ __('Manage all PFE data in the system') }}
                        </p>
                    </div>
                </div>

                <!-- Right: Actions -->
                <div class="flex items-center gap-3">
                    <div x-data="{ openExport: false }" class="relative z-40">
                        <button @click="openExport = !openExport" @click.away="openExport = false"
                            class="bg-white border border-slate-200/60 text-slate-600 hover:text-slate-900 hover:border-slate-300 hover:bg-slate-50 font-bold py-2.5 px-4 rounded-xl shadow-sm transition-all flex items-center gap-2 text-sm hover:-translate-y-0.5">
                            <i class="ph-bold ph-download-simple text-lg"></i>
                            <span class="hidden sm:inline">{{ __('Export Data') }}</span>
                            <i class="ph-bold ph-caret-down text-slate-400 ml-1 transition-transform"
                                :class="openExport ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="openExport" x-transition.opacity.duration.200ms
                            class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg shadow-slate-200/50 border border-slate-100 py-2"
                            x-cloak style="display: none;">
                            <a href="{{ route('master-data.export-pdf', request()->all()) }}"
                                class="px-4 py-2 text-sm font-medium text-slate-600 hover:bg-red-50 hover:text-red-600 flex items-center gap-2 transition-colors">
                                <i class="ph-bold ph-file-pdf text-lg text-red-500"></i> {{ __('Export to PDF') }}
                            </a>
                            <a href="#"
                                @click.prevent="$dispatch('open-excel-preview', { previewUrl: '{{ route('master-data.export-excel-preview', request()->all()) }}', downloadUrl: '{{ route('master-data.export-excel', request()->all()) }}' })"
                                class="px-4 py-2 text-sm font-medium text-slate-600 hover:bg-green-50 hover:text-green-600 flex items-center gap-2 transition-colors">
                                <i class="ph-bold ph-file-csv text-lg text-green-500"></i> {{ __('Export to Excel (CSV)') }}
                            </a>
                        </div>
                    </div>
                    @if(auth()->user()->role !== 'Staff')
                        <a href="/master-data/apar/print-all-qr" target="_blank"
                            class="bg-white border border-slate-200/60 text-slate-600 hover:text-slate-900 hover:border-slate-300 hover:bg-slate-50 font-bold py-2.5 px-4 rounded-xl shadow-sm transition-all flex items-center gap-2 text-sm hover:-translate-y-0.5">
                            <i class="ph-bold ph-printer text-lg text-[#009B77]"></i>
                            <span class="hidden sm:inline">{{ __('Print All QR') }}</span>
                        </a>
                    @endif
                    @if(auth()->user()->role !== 'Staff')
                        <button @click="showModalApar = true"
                            class="btn-smooth-ring bg-[#009B77] hover:bg-[#008264] text-white font-bold py-2.5 px-5 rounded-xl shadow-[0_4px_12px_rgba(0,155,119,0.25)] transition-all flex items-center gap-2 text-sm hover:-translate-y-0.5">
                            <i class="ph-bold ph-plus text-lg"></i>
                            <span>{{ __('Add PFE Data') }}</span>
                        </button>
                    @endif
                </div>
            </div>

            <!-- Custom Tabs -->
            <div class="relative bg-slate-200/50 p-1.5 rounded-xl inline-flex gap-1 self-start z-0"
                x-data="{ tabRect: { left: 0, width: 0 }, isInitialized: false }" x-init="
                            $watch('activeTab', (val) => {
                                localStorage.setItem('masterDataTab', val);
                                let activeEl = $refs[activeTab];
                                if(activeEl) {
                                    tabRect.left = activeEl.offsetLeft;
                                    tabRect.width = activeEl.offsetWidth;
                                }
                            });
                            // Immediate calculation for initial render
                            $nextTick(() => {
                                let activeEl = $refs[activeTab];
                                if(activeEl) {
                                    tabRect.left = activeEl.offsetLeft;
                                    tabRect.width = activeEl.offsetWidth;
                                }
                                setTimeout(() => isInitialized = true, 50);
                            });
                         ">
                <!-- Sliding Background Pill -->
                <div class="absolute top-1.5 bottom-1.5 bg-white rounded-lg shadow-sm ease-[cubic-bezier(0.4,0,0.2,1)] -z-10"
                    :class="isInitialized ? 'transition-all duration-300' : ''"
                    :style="tabRect.width ? `left: ${tabRect.left}px; width: ${tabRect.width}px; opacity: 1;` : 'opacity: 0;'"
                    x-cloak></div>

                <button x-ref="data" @click="activeTab = 'data'"
                    :class="activeTab === 'data' ? 'text-[#009B77]' : 'text-slate-500 hover:text-slate-700'"
                    class="px-5 py-2 rounded-lg font-bold text-sm transition-colors duration-300 flex items-center gap-2">
                    <i class="ph-bold ph-table text-lg"></i>
                    {{ __('APAR Data') }}
                </button>
                <button x-ref="cadangan" @click="activeTab = 'cadangan'"
                    :class="activeTab === 'cadangan' ? 'text-[#009B77]' : 'text-slate-500 hover:text-slate-700'"
                    class="px-5 py-2 rounded-lg font-bold text-sm transition-colors duration-300 flex items-center gap-2">
                    <i class="ph-bold ph-archive text-lg"></i>
                    {{ __('Stok Cadangan') }}
                </button>
                @if(auth()->user()->role !== 'Staff')
                    <button x-ref="referensi" @click="activeTab = 'referensi'"
                        :class="activeTab === 'referensi' ? 'text-[#009B77]' : 'text-slate-500 hover:text-slate-700'"
                        class="px-5 py-2 rounded-lg font-bold text-sm transition-colors duration-300 flex items-center gap-2">
                        <i class="ph-bold ph-database text-lg"></i>
                        {{ __('Reference Settings') }}
                    </button>
                @endif
                <button x-ref="resume" @click="activeTab = 'resume'"
                    :class="activeTab === 'resume' ? 'text-[#009B77]' : 'text-slate-500 hover:text-slate-700'"
                    class="px-5 py-2 rounded-lg font-bold text-sm transition-colors duration-300 flex items-center gap-2">
                    <i class="ph-bold ph-chart-pie-slice text-lg"></i>
                    {{ __('Resume') }}
                </button>
            </div>
        </div>

        <!-- Tab 1: Data APAR -->
        <div x-show="activeTab === 'data'" x-cloak x-transition:enter="transition ease-out duration-400"
            x-transition:enter-start="opacity-0 translate-y-6" x-transition:enter-end="opacity-100 translate-y-0"
            class="space-y-6" x-cloak>

            <!-- Filter & Search Section -->
            <form method="GET" action="/master-data"
                class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-5 space-y-4">
                <!-- Row 1: Search Input (Full Width) -->
                <div>
                    <label
                        class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('SEARCH KEYWORD') }}</label>
                    <div class="relative">
                        <i
                            class="ph-bold ph-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-[#009B77] text-base"></i>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="{{ __('Enter keyword...') }}"
                            class="w-full bg-white border-2 border-[#009B77] rounded-xl py-2.5 pl-10 pr-4 text-sm text-slate-700 focus:outline-none focus:ring-4 focus:ring-[#009B77]/15 transition-all placeholder:text-slate-400 font-medium"
                            x-data
                            x-init="if ($el.value) { $nextTick(() => { $el.focus({ preventScroll: true }); let val = $el.value; $el.value = ''; $el.value = val; }) }"
                            @input.debounce.750ms="$el.form.submit()">
                    </div>
                </div>

                <!-- Row 2: Filters (2 Columns) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Filter Gedung -->
                    <div>
                        <label
                            class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('BUILDING') }}</label>
                        <div x-data="{ open: false, selected: '{{ request('gedung_id') ? addslashes($gedungs->firstWhere('id', request('gedung_id'))->nama ?? __('All Buildings')) : __('All Buildings') }}' }"
                            class="relative">
                            <input type="hidden" name="gedung_id" value="{{ request('gedung_id') }}" x-ref="gedung_input">
                            <button type="button" @click="open = !open" @click.away="open = false"
                                class="w-full flex items-center justify-between py-2.5 px-3.5 bg-white border-2 border-slate-200 rounded-xl text-sm font-semibold text-slate-700 focus:outline-none focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all cursor-pointer hover:border-slate-300">
                                <span x-text="selected" class="truncate"></span>
                                <i class="ph-bold ph-caret-down text-slate-400 transition-transform duration-200 flex-shrink-0"
                                    :class="open ? 'rotate-180' : ''"></i>
                            </button>
                            <div x-show="open" x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                                style="display: none;"
                                class="absolute left-0 z-50 w-full mt-2 bg-white rounded-xl shadow-[0_10px_40px_rgba(0,0,0,0.1)] border border-slate-100 py-2 max-h-60 overflow-y-auto origin-top"
                                x-cloak>

                                <button type="button"
                                    @click="selected = '{{ __('All Buildings') }}'; open = false; $refs.gedung_input.value = ''; $refs.gedung_input.form.submit();"
                                    class="w-full text-left px-4 py-2.5 text-sm font-medium transition-colors flex items-center justify-between"
                                    :class="selected === '{{ __('All Buildings') }}' ? 'text-[#009B77] bg-[#009B77]/5' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'">
                                    <span>{{ __('All Buildings') }}</span>
                                    <i class="ph-bold ph-check text-[#009B77]"
                                        x-show="selected === '{{ __('All Buildings') }}'" x-cloak></i>
                                </button>

                                @foreach($gedungs as $ged)
                                    <button type="button"
                                        @click="selected = '{{ addslashes($ged->nama) }}'; open = false; $refs.gedung_input.value = '{{ $ged->id }}'; $refs.gedung_input.form.submit();"
                                        class="w-full text-left px-4 py-2.5 text-sm font-medium transition-colors flex items-center justify-between"
                                        :class="selected === '{{ addslashes($ged->nama) }}' ? 'text-[#009B77] bg-[#009B77]/5' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'">
                                        <span>{{ $ged->nama }}</span>
                                        <i class="ph-bold ph-check text-[#009B77]"
                                            x-show="selected === '{{ addslashes($ged->nama) }}'" x-cloak></i>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Filter Lokasi -->
                    <div>
                        <label
                            class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('LOCATION') }}</label>
                        <div x-data="{ open: false, selected: '{{ request('lokasi_id') ? addslashes($lokasis->firstWhere('id', request('lokasi_id'))->nama ?? __('All Locations')) : __('All Locations') }}' }"
                            class="relative">
                            <input type="hidden" name="lokasi_id" value="{{ request('lokasi_id') }}" x-ref="lokasi_input">
                            <button type="button" @click="open = !open" @click.away="open = false"
                                class="w-full flex items-center justify-between py-2.5 px-3.5 bg-white border-2 border-slate-200 rounded-xl text-sm font-semibold text-slate-700 focus:outline-none focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all cursor-pointer hover:border-slate-300">
                                <span x-text="selected" class="truncate"></span>
                                <i class="ph-bold ph-caret-down text-slate-400 transition-transform duration-200 flex-shrink-0"
                                    :class="open ? 'rotate-180' : ''"></i>
                            </button>
                            <div x-show="open" x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                                style="display: none;"
                                class="absolute left-0 z-50 w-full mt-2 bg-white rounded-xl shadow-[0_10px_40px_rgba(0,0,0,0.1)] border border-slate-100 py-2 max-h-60 overflow-y-auto origin-top"
                                x-cloak>

                                <button type="button"
                                    @click="selected = '{{ __('All Locations') }}'; open = false; $refs.lokasi_input.value = ''; $refs.lokasi_input.form.submit();"
                                    class="w-full text-left px-4 py-2.5 text-sm font-medium transition-colors flex items-center justify-between"
                                    :class="selected === '{{ __('All Locations') }}' ? 'text-[#009B77] bg-[#009B77]/5' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'">
                                    <span>{{ __('All Locations') }}</span>
                                    <i class="ph-bold ph-check text-[#009B77]"
                                        x-show="selected === '{{ __('All Locations') }}'" x-cloak></i>
                                </button>

                                @foreach($lokasis as $lok)
                                    <button type="button"
                                        @click="selected = '{{ addslashes($lok->nama) }}'; open = false; $refs.lokasi_input.value = '{{ $lok->id }}'; $refs.lokasi_input.form.submit();"
                                        class="w-full text-left px-4 py-2.5 text-sm font-medium transition-colors flex items-center justify-between"
                                        :class="selected === '{{ addslashes($lok->nama) }}' ? 'text-[#009B77] bg-[#009B77]/5' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'">
                                        <span>{{ $lok->nama }} ({{ $lok->gedung->nama ?? '' }})</span>
                                        <i class="ph-bold ph-check text-[#009B77]"
                                            x-show="selected === '{{ addslashes($lok->nama) }}'" x-cloak></i>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Filter Jenis -->
                    <div>
                        <label
                            class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('Type') }}</label>
                        <div x-data="{ open: false, selected: '{{ request('jenis_id') ? addslashes($jenisApars->firstWhere('id', request('jenis_id'))->nama ?? __('All Types')) : __('All Types') }}' }"
                            class="relative">
                            <input type="hidden" name="jenis_id" value="{{ request('jenis_id') }}" x-ref="jenis_input">
                            <button type="button" @click="open = !open" @click.away="open = false"
                                class="w-full flex items-center justify-between py-2.5 px-3.5 bg-white border-2 border-slate-200 rounded-xl text-sm font-semibold text-slate-700 focus:outline-none focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all cursor-pointer hover:border-slate-300">
                                <span x-text="selected" class="truncate"></span>
                                <i class="ph-bold ph-caret-down text-slate-400 transition-transform duration-200 flex-shrink-0"
                                    :class="open ? 'rotate-180' : ''"></i>
                            </button>
                            <div x-show="open" x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                                style="display: none;"
                                class="absolute left-0 z-50 w-full mt-2 bg-white rounded-xl shadow-[0_10px_40px_rgba(0,0,0,0.1)] border border-slate-100 py-2 max-h-60 overflow-y-auto origin-top"
                                x-cloak>

                                <button type="button"
                                    @click="selected = '{{ __('All Types') }}'; open = false; $refs.jenis_input.value = ''; $refs.jenis_input.form.submit();"
                                    class="w-full text-left px-4 py-2.5 text-sm font-medium transition-colors flex items-center justify-between"
                                    :class="selected === '{{ __('All Types') }}' ? 'text-[#009B77] bg-[#009B77]/5' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'">
                                    <span>{{ __('All Types') }}</span>
                                    <i class="ph-bold ph-check text-[#009B77]" x-show="selected === '{{ __('All Types') }}'"
                                        x-cloak></i>
                                </button>

                                @foreach($jenisApars as $jen)
                                    <button type="button"
                                        @click="selected = '{{ addslashes($jen->nama) }}'; open = false; $refs.jenis_input.value = '{{ $jen->id }}'; $refs.jenis_input.form.submit();"
                                        class="w-full text-left px-4 py-2.5 text-sm font-medium transition-colors flex items-center justify-between"
                                        :class="selected === '{{ addslashes($jen->nama) }}' ? 'text-[#009B77] bg-[#009B77]/5' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'">
                                        <span>{{ $jen->nama }}</span>
                                        <i class="ph-bold ph-check text-[#009B77]"
                                            x-show="selected === '{{ addslashes($jen->nama) }}'" x-cloak></i>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Filter Kapasitas -->
                    <div>
                        <label
                            class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('Capacity') }}</label>
                        <div x-data="{ open: false, selected: '{{ request('kapasitas_id') ? addslashes($kapasitasApars->firstWhere('id', request('kapasitas_id'))->ukuran ?? __('All Capacities')) : __('All Capacities') }}' }"
                            class="relative">
                            <input type="hidden" name="kapasitas_id" value="{{ request('kapasitas_id') }}"
                                x-ref="kapasitas_input">
                            <button type="button" @click="open = !open" @click.away="open = false"
                                class="w-full flex items-center justify-between py-2.5 px-3.5 bg-white border-2 border-slate-200 rounded-xl text-sm font-semibold text-slate-700 focus:outline-none focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all cursor-pointer hover:border-slate-300">
                                <span x-text="selected" class="truncate"></span>
                                <i class="ph-bold ph-caret-down text-slate-400 transition-transform duration-200 flex-shrink-0"
                                    :class="open ? 'rotate-180' : ''"></i>
                            </button>
                            <div x-show="open" x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                                style="display: none;"
                                class="absolute left-0 z-50 w-full mt-2 bg-white rounded-xl shadow-[0_10px_40px_rgba(0,0,0,0.1)] border border-slate-100 py-2 max-h-60 overflow-y-auto origin-top"
                                x-cloak>

                                <button type="button"
                                    @click="selected = '{{ __('All Capacities') }}'; open = false; $refs.kapasitas_input.value = ''; $refs.kapasitas_input.form.submit();"
                                    class="w-full text-left px-4 py-2.5 text-sm font-medium transition-colors flex items-center justify-between"
                                    :class="selected === '{{ __('All Capacities') }}' ? 'text-[#009B77] bg-[#009B77]/5' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'">
                                    <span>{{ __('All Capacities') }}</span>
                                    <i class="ph-bold ph-check text-[#009B77]"
                                        x-show="selected === '{{ __('All Capacities') }}'" x-cloak></i>
                                </button>

                                @foreach($kapasitasApars as $kap)
                                    <button type="button"
                                        @click="selected = '{{ addslashes($kap->ukuran) }}'; open = false; $refs.kapasitas_input.value = '{{ $kap->id }}'; $refs.kapasitas_input.form.submit();"
                                        class="w-full text-left px-4 py-2.5 text-sm font-medium transition-colors flex items-center justify-between"
                                        :class="selected === '{{ addslashes($kap->ukuran) }}' ? 'text-[#009B77] bg-[#009B77]/5' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'">
                                        <span>{{ $kap->ukuran }}</span>
                                        <i class="ph-bold ph-check text-[#009B77]"
                                            x-show="selected === '{{ addslashes($kap->ukuran) }}'" x-cloak></i>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Data Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-[#009B77] text-white divide-x divide-white/20 text-center">
                                <th class="py-4 px-5 text-xs font-bold uppercase tracking-wider w-12">No</th>
                                <th class="py-4 px-5 text-xs font-bold uppercase tracking-wider">{{ __('PFE ID') }}</th>
                                <th class="py-4 px-5 text-xs font-bold uppercase tracking-wider">{{ __('ROOM NO') }}</th>
                                <th class="py-4 px-5 text-xs font-bold uppercase tracking-wider">{{ __('LOCATION') }}</th>
                                <th class="py-4 px-5 text-xs font-bold uppercase tracking-wider">{{ __('BUILDING') }}</th>
                                <th class="py-4 px-5 text-xs font-bold uppercase tracking-wider">{{ __('Type') }}</th>
                                <th class="py-4 px-5 text-xs font-bold uppercase tracking-wider">{{ __('Capacity') }}</th>
                                <th class="py-4 px-5 text-xs font-bold uppercase tracking-wider">{{ __('Fire Class') }}</th>
                                <th class="py-4 px-5 text-xs font-bold uppercase tracking-wider">{{ __('Expired DATE') }}
                                </th>
                                <th class="py-4 px-5 text-xs font-bold uppercase tracking-wider">{{ __('Last Refill') }}
                                </th>
                                <th class="py-4 px-5 text-xs font-bold uppercase tracking-wider text-center">{{ __('Qty') }}
                                </th>
                                <th class="py-4 px-5 text-xs font-bold uppercase tracking-wider">{{ __('PIC') }}</th>
                                @if(auth()->user()->role !== 'Staff')
                                    <th class="py-4 px-5 text-xs font-bold uppercase tracking-wider">{{ __('ACTIONS') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-slate-100">
                            @forelse($apars as $index => $apar)
                                <tr class="hover:bg-slate-50 transition-colors divide-x divide-slate-100">
                                    <td class="py-4 px-5 text-center font-semibold text-slate-500">{{ $index + 1 }}</td>
                                    <td class="py-4 px-5">
                                        <span class="font-bold text-slate-800">{{ $apar->kode }}</span>
                                    </td>
                                    <td class="py-4 px-5 font-semibold text-slate-600">
                                        {{ $apar->nomor_ruangan ?? '-' }}
                                    </td>
                                    <td class="py-4 px-5 font-semibold text-slate-600 capitalize">
                                        {{ $apar->lokasi->nama ?? 'n/a' }}
                                    </td>
                                    <td class="py-4 px-5 font-semibold text-slate-600">
                                        {{ $apar->lokasi->gedung->nama ?? 'n/a' }}
                                    </td>
                                    <td class="py-4 px-5 font-semibold text-slate-600">{{ $apar->jenis->nama ?? 'n/a' }}</td>
                                    <td class="py-4 px-5 font-semibold text-slate-600">{{ $apar->kapasitas->ukuran ?? 'n/a' }}
                                    </td>
                                    <td class="py-4 px-5">
                                        @php
                                            $jenisNamaL = strtolower($apar->jenis->nama ?? '');
                                            $kelas = '-';
                                            if (strpos($jenisNamaL, 'dry chemical') !== false || strpos($jenisNamaL, 'powder') !== false) {
                                                $kelas = 'A-B-C';
                                            } elseif (strpos($jenisNamaL, 'carbon') !== false || strpos($jenisNamaL, 'dioxide') !== false || strpos($jenisNamaL, 'co2') !== false) {
                                                $kelas = 'B-C';
                                            }
                                        @endphp
                                        @if($kelas != '-')
                                            <span
                                                class="text-[10px] font-bold tracking-wider py-1 px-2.5 rounded-lg bg-orange-50 text-orange-600 border border-orange-100">{{ $kelas }}</span>
                                        @else
                                            <span class="font-semibold text-slate-400">-</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-5">
                                        @if($apar->tgl_kedaluwarsa && $apar->tgl_kedaluwarsa->isPast())
                                            <span class="font-semibold text-red-500">Expired
                                                ({{ $apar->tgl_kedaluwarsa->format('d M Y') }})</span>
                                        @else
                                            <span
                                                class="font-semibold text-slate-600">{{ $apar->tgl_kedaluwarsa ? $apar->tgl_kedaluwarsa->format('d M Y') : '-' }}</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-5">
                                        <span
                                            class="font-semibold text-slate-600">{{ $apar->tgl_isi_ulang ? $apar->tgl_isi_ulang->format('d M Y') : '-' }}</span>
                                    </td>
                                    <td class="py-4 px-5 text-center font-bold text-slate-800">
                                        @if($apar->qty <= 0)
                                            <span
                                                class="text-[10px] font-bold tracking-wider py-1 px-2.5 rounded-lg bg-red-50 text-red-600 border border-red-100 whitespace-nowrap">{{ __('OUT OF STOCK') }}</span>
                                        @else
                                            {{ $apar->qty }}
                                        @endif
                                    </td>
                                    <td class="py-4 px-5 font-semibold text-slate-600">
                                        @php
                                            $lastInspeksi = $apar->latestInspeksi;
                                            $picUser = null;
                                            if ($lastInspeksi && $lastInspeksi->user) {
                                                $picUser = $lastInspeksi->user;
                                            } elseif ($apar->pic) {
                                                $picUser = $apar->pic;
                                            }
                                        @endphp

                                        @if($picUser)
                                            <div class="flex items-center gap-2">
                                                <div
                                                    class="w-8 h-8 rounded-full bg-[#009B77]/10 flex items-center justify-center text-[#009B77] text-xs font-bold border border-[#009B77]/20">
                                                    {{ strtoupper(substr($picUser->name, 0, 2)) }}
                                                </div>
                                                <span class="font-semibold text-slate-700">{{ $picUser->name }}</span>
                                            </div>
                                        @else
                                            <span class="text-slate-400 font-medium">-</span>
                                        @endif
                                    </td>
                                    @if(auth()->user()->role !== 'Staff')
                                        <td class="py-4 px-5">
                                            <div class="flex items-center justify-center gap-1">
                                                <button type="button"
                                                    @click="editApar = { id: {{ $apar->id }}, kode: '{{ addslashes($apar->kode) }}', nomor_apar: '{{ addslashes($apar->kode) }}'.match(/\d+$/) ? '{{ addslashes($apar->kode) }}'.match(/\d+$/)[0] : '', nomor_ruangan: '{{ addslashes($apar->nomor_ruangan ?? '') }}', gedung_id: '{{ $apar->lokasi->gedung_id ?? '' }}', lokasi: '{{ addslashes($apar->lokasi->nama ?? '') }}', jenis_id: '{{ $apar->jenis_id }}', kapasitas_id: '{{ $apar->kapasitas_id }}', qty: {{ $apar->qty }}, tgl_kedaluwarsa: '{{ $apar->tgl_kedaluwarsa ? $apar->tgl_kedaluwarsa->format('Y-m-d') : '' }}', tgl_isi_ulang: '{{ $apar->tgl_isi_ulang ? $apar->tgl_isi_ulang->format('Y-m-d') : '' }}', gunakanCadangan: false, cadanganId: '' }; originalEditApar = JSON.stringify(editApar); showModalEditApar = true"
                                                    class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-slate-400 hover:text-amber-500 hover:bg-amber-50 transition-colors"
                                                    title="Edit">
                                                    <i class="ph-bold ph-pencil-simple text-lg"></i>
                                                </button>
                                                <form action="/master-data/apar/{{ $apar->id }}" method="POST" class="inline"
                                                    onsubmit="confirmDelete(event, '{{ __('Are you sure you want to delete this PFE?') }}');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-slate-400 hover:text-red-500 hover:bg-red-50 transition-colors"
                                                        title="Hapus">
                                                        <i class="ph-bold ph-trash text-base"></i>
                                                    </button>
                                                </form>
                                                <button
                                                    @click="$dispatch('open-history', { id: {{ $apar->id }}, kode: '{{ addslashes($apar->kode) }}' })"
                                                    class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-slate-400 hover:text-indigo-500 hover:bg-indigo-50 transition-colors"
                                                    title="Riwayat Perubahan">
                                                    <i class="ph-bold ph-clock-counter-clockwise text-base"></i>
                                                </button>
                                                <button @click="
                                                                        fetch('/master-data/apar/{{ $apar->id }}/qr-data')
                                                                            .then(res => res.json())
                                                                            .then(data => {
                                                                                qrData = data;
                                                                                showQrModal = true;
                                                                            });
                                                                    "
                                                    class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-slate-400 hover:text-[#009B77] hover:bg-[#009B77]/10 transition-colors"
                                                    title="QR Code">
                                                    <i class="ph-bold ph-qr-code text-base"></i>
                                                </button>
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="13" class="py-8 text-center text-slate-500 font-semibold">
                                        {{ __('No PFE data available.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-slate-100">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                        <p class="text-sm font-semibold text-slate-500">{{ __('Showing') }} <span
                                class="font-bold text-slate-800">{{ $apars->firstItem() ?? 0 }}-{{ $apars->lastItem() ?? 0 }}</span>
                            {{ __('of') }} <span class="font-bold text-slate-800">{{ $apars->total() }}</span>
                            {{ __('data') }}
                        </p>
                        <div class="w-full sm:w-auto flex items-center gap-2">
                            @if ($apars->onFirstPage())
                                <span
                                    class="w-8 h-8 flex items-center justify-center bg-slate-100 border border-slate-200 text-slate-400 rounded-lg cursor-not-allowed text-sm font-medium"><i
                                        class="ph-bold ph-caret-left"></i></span>
                            @else
                                <a href="{{ $apars->previousPageUrl() }}"
                                    class="w-8 h-8 flex items-center justify-center bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-[#009B77] rounded-lg transition-colors text-sm font-medium shadow-sm"><i
                                        class="ph-bold ph-caret-left"></i></a>
                            @endif

                            @php
                                $current = $apars->currentPage();
                                $last = $apars->lastPage();
                                $delta = 1; // Tampilkan 1 di kiri & 1 di kanan (+ halaman pertama & terakhir)
                                $left = $current - $delta;
                                $right = $current + $delta;
                                $range = [];

                                for ($i = 1; $i <= $last; $i++) {
                                    if ($i == 1 || $i == $last || ($i >= $left && $i <= $right)) {
                                        $range[] = $i;
                                    }
                                }

                                $rangeWithDots = [];
                                $l = null;
                                foreach ($range as $i) {
                                    if ($l) {
                                        if ($i - $l == 2) {
                                            $rangeWithDots[] = $l + 1; // Jika selisih cuma 1 angka, tampilkan angkanya (jangan titik-titik)
                                        } elseif ($i - $l != 1) {
                                            $rangeWithDots[] = '...';
                                        }
                                    }
                                    $rangeWithDots[] = $i;
                                    $l = $i;
                                }
                            @endphp

                            @foreach ($rangeWithDots as $i)
                                @if ($i === '...')
                                    <span
                                        class="w-8 h-8 flex items-center justify-center text-slate-400 text-sm font-bold">...</span>
                                @elseif ($i == $apars->currentPage())
                                    <span
                                        class="w-8 h-8 flex items-center justify-center bg-[#009B77]/10 border border-[#009B77]/20 text-[#009B77] rounded-lg font-bold text-sm">{{ $i }}</span>
                                @else
                                    <a href="{{ $apars->url($i) }}"
                                        class="w-8 h-8 flex items-center justify-center bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-[#009B77] rounded-lg transition-colors text-sm font-medium shadow-sm">{{ $i }}</a>
                                @endif
                            @endforeach

                            @if ($apars->hasMorePages())
                                <a href="{{ $apars->nextPageUrl() }}"
                                    class="w-8 h-8 flex items-center justify-center bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-[#009B77] rounded-lg transition-colors text-sm font-medium shadow-sm"><i
                                        class="ph-bold ph-caret-right"></i></a>
                            @else
                                <span
                                    class="w-8 h-8 flex items-center justify-center bg-slate-100 border border-slate-200 text-slate-400 rounded-lg cursor-not-allowed text-sm font-medium"><i
                                        class="ph-bold ph-caret-right"></i></span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Tab 1 -->

        <!-- Tab 2: Pengaturan Referensi -->
        <!-- Tab Cadangan: Stok Cadangan -->
        <div x-show="activeTab === 'cadangan'" x-cloak x-transition:enter="transition ease-out duration-400"
            x-transition:enter-start="opacity-0 translate-y-6" x-transition:enter-end="opacity-100 translate-y-0"
            class="space-y-6" x-cloak>

            <!-- Card for Cadangan Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-[#009B77]/20 overflow-hidden">
                <div class="px-5 py-3 border-b border-[#009B77]/10 flex items-center justify-between bg-[#009B77]/5">
                    <h2 class="text-base font-bold text-[#009B77] uppercase tracking-wider flex items-center gap-2">
                        <i class="ph-bold ph-archive"></i>
                        {{ __('Stok Cadangan') }}
                    </h2>
                    <button type="button"
                        @click="$dispatch('open-cadangan', { type: 'tambah_cadangan', action: '/master-data/apar-cadangan' })"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-[#009B77] hover:bg-[#007b5e] text-white text-sm font-bold rounded-xl transition-all shadow-sm">
                        <i class="ph-bold ph-plus"></i> {{ __('Add Stock') }}
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-[#009B77] text-white divide-x divide-white/20 text-center">
                                <th class="py-4 px-5 text-xs font-bold uppercase tracking-wider w-12">No</th>
                                <th class="py-4 px-5 text-xs font-bold uppercase tracking-wider">{{ __('Building') }}</th>
                                <th class="py-4 px-5 text-xs font-bold uppercase tracking-wider">{{ __('Type') }}</th>
                                <th class="py-4 px-5 text-xs font-bold uppercase tracking-wider">{{ __('Capacity') }}</th>
                                <th class="py-4 px-5 text-xs font-bold uppercase tracking-wider text-center">
                                    {{ __('Stock Quantity') }}
                                </th>
                                <th class="py-4 px-5 text-xs font-bold uppercase tracking-wider text-center">
                                    {{ __('Action') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-slate-100">
                            @forelse($aparCadangans as $index => $cadangan)
                                <tr class="hover:bg-slate-50 transition-colors divide-x divide-slate-100">
                                    <td class="py-4 px-5 text-center font-semibold text-slate-500">{{ $index + 1 }}</td>
                                    <td class="py-4 px-5 font-semibold text-slate-600 capitalize">
                                        {{ $cadangan->gedung->nama ?? '-' }}
                                    </td>
                                    <td class="py-4 px-5 font-semibold text-slate-600">{{ $cadangan->jenis->nama ?? '-' }}</td>
                                    <td class="py-4 px-5 font-semibold text-slate-600">{{ $cadangan->kapasitas->ukuran ?? '-' }}
                                    </td>
                                    <td class="py-4 px-5 font-bold text-[#009B77] text-center">{{ $cadangan->total }}</td>
                                    <td class="py-4 px-5 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <button type="button"
                                                @click="$dispatch('open-cadangan', { type: 'edit_cadangan', action: '/master-data/apar-cadangan/{{ $cadangan->id }}', gedung: '{{ $cadangan->gedung_id }}', jenis: '{{ $cadangan->jenis_id }}', kapasitas: '{{ $cadangan->kapasitas_id }}', total: '{{ $cadangan->total }}' })"
                                                class="p-2 text-blue-500 hover:bg-blue-50 rounded-lg transition-colors">
                                                <i class="ph-bold ph-pencil-simple text-lg"></i>
                                            </button>
                                            <form action="/master-data/apar-cadangan/{{ $cadangan->id }}" method="POST"
                                                class="inline"
                                                onsubmit="confirmDelete(event, '{{ __('Are you sure you want to delete this data?') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                                                    <i class="ph-bold ph-trash text-lg"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 px-5 text-center text-sm font-medium text-slate-500">
                                        <div class="flex flex-col items-center justify-center space-y-2">
                                            <i class="ph-bold ph-archive text-3xl text-slate-300"></i>
                                            <p>{{ __('No spare stock data found.') }}</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-[#009B77]/5 text-sm border-t-2 border-[#009B77]/20">
                            <tr class="divide-x divide-[#009B77]/10">
                                <td colspan="4"
                                    class="py-3 px-5 text-left text-[#009B77] font-bold uppercase tracking-wider">Grand
                                    Total</td>
                                <td class="py-3 px-5 text-center font-bold text-[#009B77] bg-[#009B77]/10">
                                    {{ $aparCadangans->sum('total') }}
                                </td>
                                <td class="py-3 px-5"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        @if(auth()->user()->role !== 'Staff')
            <div x-show="activeTab === 'referensi'" x-cloak x-transition:enter="transition ease-out duration-400"
                x-transition:enter-start="opacity-0 translate-y-6" x-transition:enter-end="opacity-100 translate-y-0"
                class="space-y-6" style="display: none;" x-cloak>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <!-- Lokasi Referensi -->
                    <div
                        class="bg-white rounded-3xl shadow-sm border border-slate-200/60 overflow-hidden group hover:border-teal-400/50 hover:shadow-lg hover:shadow-teal-100 transition-all duration-500 flex flex-col h-full">
                        <div
                            class="p-6 border-b border-slate-100 relative overflow-hidden bg-gradient-to-br from-teal-50 to-white">
                            <div
                                class="absolute -right-6 -top-6 w-24 h-24 bg-teal-200 rounded-full mix-blend-multiply opacity-50 blur-xl">
                            </div>
                            <div class="flex justify-between items-start relative z-10">
                                <div class="flex flex-col gap-1">
                                    <div
                                        class="w-12 h-12 rounded-2xl bg-gradient-to-br from-teal-400 to-[#009B77] text-white flex items-center justify-center shadow-lg shadow-teal-500/30 mb-3">
                                        <i class="ph-fill ph-map-pin text-2xl"></i>
                                    </div>
                                    <h3 class="font-black text-slate-800 text-lg tracking-wide uppercase">{{ __('LOCATION') }}
                                    </h3>
                                    <p class="text-xs font-semibold text-slate-500">{{ __('Placement List') }}</p>
                                </div>
                                <div class="flex flex-col gap-2 2xl:flex-row">
                                    <button @click="showModalGedung = true"
                                        class="bg-white/80 backdrop-blur border border-indigo-200 text-indigo-600 hover:bg-indigo-500 hover:text-white hover:border-indigo-500 rounded-xl px-3 py-2 flex items-center gap-1.5 transition-all shadow-sm">
                                        <i class="ph-bold ph-buildings text-base"></i>
                                        <span class="text-xs font-bold whitespace-nowrap">{{ __('Add Building') }}</span>
                                    </button>
                                    <button @click="showModalLokasi = true"
                                        class="bg-white/80 backdrop-blur border border-teal-200 text-teal-600 hover:bg-teal-500 hover:text-white hover:border-teal-500 rounded-xl px-3 py-2 flex items-center gap-1.5 transition-all shadow-sm">
                                        <i class="ph-bold ph-plus text-base"></i>
                                        <span class="text-xs font-bold whitespace-nowrap">{{ __('Add Location') }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="p-4 bg-slate-50/50 flex-1 overflow-y-auto max-h-[400px]">
                            <div class="space-y-4">
                                @forelse($gedungs as $gedung)
                                    <div x-data="{ expanded: false }" class="flex flex-col gap-2">
                                        <!-- Gedung Header -->
                                        <div @click="expanded = !expanded"
                                            class="bg-white border border-indigo-100 p-3 rounded-xl flex items-center justify-between shadow-[0_2px_10px_rgba(0,0,0,0.02)] group/gedung cursor-pointer hover:border-indigo-300 transition-colors">
                                            <div class="flex items-center gap-2">
                                                <div
                                                    class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                                                    <i class="ph-bold ph-buildings text-sm"></i>
                                                </div>
                                                <span
                                                    class="font-extrabold text-slate-800 text-sm tracking-wide">{{ $gedung->nama }}</span>
                                                <i class="ph-bold ph-caret-down text-slate-400 text-xs transition-transform duration-300 ml-1"
                                                    :class="expanded ? 'rotate-180' : ''"></i>
                                            </div>
                                            <div
                                                class="flex items-center gap-1 opacity-0 group-hover/gedung:opacity-100 transition-opacity">
                                                <button
                                                    @click.stop="editGedung = { id: {{ $gedung->id }}, nama: '{{ addslashes($gedung->nama) }}' }; originalEditGedung = JSON.stringify(editGedung); showEditGedung = true"
                                                    class="w-7 h-7 rounded bg-amber-50 text-amber-500 hover:bg-amber-500 hover:text-white flex items-center justify-center transition-colors"
                                                    title="Edit Gedung"><i class="ph-bold ph-pencil-simple text-sm"></i></button>
                                                <form action="/master-data/gedung/{{ $gedung->id }}" method="POST" class="inline"
                                                    onsubmit="confirmDelete(event, '{{ __('Are you sure you want to delete this building? All locations inside it will also be deleted!') }}');">
                                                    @csrf @method('DELETE')
                                                    <button @click.stop type="submit"
                                                        class="w-7 h-7 rounded bg-red-50 text-red-500 hover:bg-red-500 hover:text-white flex items-center justify-center transition-colors"
                                                        title="Hapus Gedung"><i class="ph-bold ph-trash text-sm"></i></button>
                                                </form>
                                            </div>
                                        </div>

                                        <!-- Child Lokasi List -->
                                        <div x-show="expanded" x-collapse
                                            class="flex flex-col pl-6 space-y-2 border-l-2 border-slate-200/60 ml-4 relative">
                                            @php
                                                $lokasiByGedung = $lokasis->where('gedung_id', $gedung->id);
                                            @endphp

                                            @forelse($lokasiByGedung as $lok)
                                                <div
                                                    class="bg-white/60 border border-slate-100/80 p-2.5 rounded-xl flex items-center justify-between hover:bg-white hover:border-teal-200 hover:shadow-sm transition-all group/item relative">
                                                    <!-- Connection line -->
                                                    <div class="absolute -left-6 top-1/2 w-4 h-px bg-slate-200/60"></div>

                                                    <div class="flex items-center gap-2">
                                                        <i class="ph-bold ph-map-pin text-slate-400 text-sm"></i>
                                                        <span class="font-bold text-slate-600 text-sm">{{ $lok->nama }}</span>
                                                    </div>
                                                    <div
                                                        class="flex items-center gap-1 opacity-0 group-hover/item:opacity-100 transition-opacity">
                                                        <button
                                                            @click="editLokasi = { id: {{ $lok->id }}, nama: '{{ addslashes($lok->nama) }}', gedung_id: '{{ $lok->gedung_id }}' }; originalEditLokasi = JSON.stringify(editLokasi); showEditLokasi = true"
                                                            class="w-7 h-7 rounded-lg text-slate-400 hover:text-amber-500 hover:bg-amber-50 flex items-center justify-center transition-colors"><i
                                                                class="ph-bold ph-pencil-simple text-sm"></i></button>
                                                        <form action="/master-data/lokasi/{{ $lok->id }}" method="POST" class="inline"
                                                            onsubmit="confirmDelete(event, '{{ __('Are you sure you want to delete this location?') }}');">
                                                            @csrf @method('DELETE')
                                                            <button type="submit"
                                                                class="w-7 h-7 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 flex items-center justify-center transition-colors"><i
                                                                    class="ph-bold ph-trash text-sm"></i></button>
                                                        </form>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="text-xs font-semibold text-slate-400 italic py-1 pl-2 relative">
                                                    <!-- Connection line -->
                                                    <div class="absolute -left-6 top-1/2 w-4 h-px bg-slate-200/60"></div>
                                                    Belum ada lokasi
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>
                                @empty
                                    <div class="flex flex-col items-center justify-center py-8 text-center">
                                        <div
                                            class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                                            <i class="ph-duotone ph-buildings text-2xl"></i>
                                        </div>
                                        <h4 class="font-bold text-slate-700 text-sm">Belum ada data</h4>
                                        <p class="text-xs text-slate-500 mt-1 max-w-[200px]">Tambahkan gedung dan lokasi pertama
                                            Anda</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- {{ __('PFE Type') }} -->
                    <div
                        class="bg-white rounded-3xl shadow-sm border border-slate-200/60 overflow-hidden group hover:border-amber-400/50 hover:shadow-lg hover:shadow-amber-100 transition-all duration-500 flex flex-col h-full">
                        <div
                            class="p-6 border-b border-slate-100 relative overflow-hidden bg-gradient-to-br from-amber-50 to-white">
                            <div
                                class="absolute -right-6 -top-6 w-24 h-24 bg-amber-200 rounded-full mix-blend-multiply opacity-50 blur-xl">
                            </div>
                            <div class="flex justify-between items-start relative z-10">
                                <div class="flex flex-col gap-1">
                                    <div
                                        class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-400 to-amber-600 text-white flex items-center justify-center shadow-lg shadow-amber-500/30 mb-3">
                                        <i class="ph-fill ph-fire-extinguisher text-2xl"></i>
                                    </div>
                                    <h3 class="font-black text-slate-800 text-lg tracking-wide uppercase">{{ __('Type') }} APAR
                                    </h3>
                                    <p class="text-xs font-semibold text-slate-500">Tipe Serbuk/Gas</p>
                                </div>
                                <button @click="showModalJenis = true"
                                    class="bg-white/80 backdrop-blur border border-amber-200 text-amber-600 hover:bg-amber-500 hover:text-white hover:border-amber-500 rounded-xl px-3 py-2 flex items-center gap-1.5 transition-all shadow-sm">
                                    <i class="ph-bold ph-plus text-base"></i>
                                    <span class="text-xs font-bold whitespace-nowrap">{{ __('Add Type') }}</span>
                                </button>
                            </div>
                        </div>
                        <div class="p-4 bg-slate-50/50 flex-1 overflow-y-auto max-h-[400px]">
                            <div class="space-y-2">
                                @forelse($jenisApars as $jenis)
                                    <div
                                        class="bg-white border border-slate-100 p-3.5 rounded-2xl flex items-center justify-between hover:border-amber-200 hover:shadow-sm transition-all group/item">
                                        <span class="font-bold text-slate-700 text-sm">{{ $jenis->nama }}</span>
                                        <div
                                            class="flex items-center gap-1 opacity-0 group-hover/item:opacity-100 transition-opacity">
                                            <button
                                                @click="editJenis = { id: {{ $jenis->id }}, nama: '{{ addslashes($jenis->nama) }}' }; originalEditJenis = JSON.stringify(editJenis); showEditJenis = true"
                                                class="w-8 h-8 rounded-lg text-slate-400 hover:text-amber-500 hover:bg-amber-50 flex items-center justify-center transition-colors"><i
                                                    class="ph-bold ph-pencil-simple text-base"></i></button>
                                            <form action="/master-data/jenis/{{ $jenis->id }}" method="POST" class="inline"
                                                onsubmit="confirmDelete(event, '{{ __('Are you sure you want to delete this PFE type?') }}');">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                    class="w-8 h-8 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 flex items-center justify-center transition-colors"><i
                                                        class="ph-bold ph-trash text-base"></i></button>
                                            </form>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-4 text-xs font-semibold text-slate-400">Belum ada data</div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Kapasitas -->
                    <div
                        class="bg-white rounded-3xl shadow-sm border border-slate-200/60 overflow-hidden group hover:border-purple-400/50 hover:shadow-lg hover:shadow-purple-100 transition-all duration-500 flex flex-col h-full">
                        <div
                            class="p-6 border-b border-slate-100 relative overflow-hidden bg-gradient-to-br from-purple-50 to-white">
                            <div
                                class="absolute -right-6 -top-6 w-24 h-24 bg-purple-200 rounded-full mix-blend-multiply opacity-50 blur-xl">
                            </div>
                            <div class="flex justify-between items-start relative z-10">
                                <div class="flex flex-col gap-1">
                                    <div
                                        class="w-12 h-12 rounded-2xl bg-gradient-to-br from-purple-400 to-purple-600 text-white flex items-center justify-center shadow-lg shadow-purple-500/30 mb-3">
                                        <i class="ph-fill ph-scales text-2xl"></i>
                                    </div>
                                    <h3 class="font-black text-slate-800 text-lg tracking-wide uppercase">{{ __('Capacity') }}
                                    </h3>
                                    <p class="text-xs font-semibold text-slate-500">Ukuran Berat/Volume</p>
                                </div>
                                <button @click="showModalKapasitas = true"
                                    class="bg-white/80 backdrop-blur border border-purple-200 text-purple-600 hover:bg-purple-500 hover:text-white hover:border-purple-500 rounded-xl px-3 py-2 flex items-center gap-1.5 transition-all shadow-sm">
                                    <i class="ph-bold ph-plus text-base"></i>
                                    <span class="text-xs font-bold whitespace-nowrap">{{ __('Add Capacity') }}</span>
                                </button>
                            </div>
                        </div>
                        <div class="p-4 bg-slate-50/50 flex-1 overflow-y-auto max-h-[400px]">
                            <div class="space-y-2">
                                @forelse($kapasitasApars as $kap)
                                    <div
                                        class="bg-white border border-slate-100 p-3.5 rounded-2xl flex items-center justify-between hover:border-purple-200 hover:shadow-sm transition-all group/item">
                                        <span class="font-bold text-slate-700 text-sm">{{ $kap->ukuran }}</span>
                                        <div
                                            class="flex items-center gap-1 opacity-0 group-hover/item:opacity-100 transition-opacity">
                                            <button
                                                @click="editKapasitas = { id: {{ $kap->id }}, ukuran: '{{ addslashes($kap->ukuran) }}' }; originalEditKapasitas = JSON.stringify(editKapasitas); showEditKapasitas = true"
                                                class="w-8 h-8 rounded-lg text-slate-400 hover:text-amber-500 hover:bg-amber-50 flex items-center justify-center transition-colors"><i
                                                    class="ph-bold ph-pencil-simple text-base"></i></button>
                                            <form action="/master-data/kapasitas/{{ $kap->id }}" method="POST" class="inline"
                                                onsubmit="confirmDelete(event, '{{ __('Are you sure you want to delete this capacity?') }}');">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                    class="w-8 h-8 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 flex items-center justify-center transition-colors"><i
                                                        class="ph-bold ph-trash text-base"></i></button>
                                            </form>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-4 text-xs font-semibold text-slate-400">Belum ada data</div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        @endif

        <!-- Modal Tambah Lokasi -->
        <div x-show="showModalLokasi" style="display: none;" class="relative z-[100]" aria-labelledby="modal-title"
            role="dialog" aria-modal="true" x-cloak>
            <!-- Background overlay -->
            <div x-show="showModalLokasi" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-slate-900/40 transition-opacity" x-cloak></div>

            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <!-- Modal panel -->
                    <div x-show="showModalLokasi" x-transition:enter="ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        class="relative transform overflow-visible rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-100"
                        x-cloak>

                        <form action="/master-data/lokasi" method="POST"
                            @submit="isDuplicate || nama.trim() === '' || gedung_id === '' ? $event.preventDefault() : true"
                            x-data="{ nama: '', gedung_id: '', existing: [ @foreach(\App\Models\Lokasi::all() as $l) { nama: '{{ strtolower(addslashes($l->nama)) }}', gedung_id: '{{ $l->gedung_id }}' }, @endforeach ], get isDuplicate() { return this.nama.trim() !== '' && this.existing.some(e => e.nama === this.nama.toLowerCase().trim() && e.gedung_id == this.gedung_id); } }">
                            <input type="hidden" name="form_type" value="tambah_lokasi">
                            <div class="bg-white rounded-t-3xl px-6 pb-6 pt-6 sm:p-8">
                                @csrf
                                <div class="flex items-center justify-between mb-6">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center">
                                            <i class="ph-bold ph-map-pin text-xl"></i>
                                        </div>
                                        <h3 class="text-lg font-bold leading-6 text-slate-800" id="modal-title">
                                            {{ __('Add Location Data') }}
                                        </h3>
                                    </div>
                                    <button @click="showModalLokasi = false"
                                        class="text-slate-400 hover:text-slate-500 transition-colors">
                                        <i class="ph-bold ph-x text-xl"></i>
                                    </button>
                                </div>

                                <div class="space-y-5">
                                    <div>
                                        <label
                                            class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('Location Name') }}</label>
                                        <input type="text" name="nama" x-model="nama"
                                            @keydown.enter="if(isDuplicate || nama.trim() === '' || gedung_id === '') { $event.preventDefault(); showModalLokasi = false; }"
                                            required placeholder="{{ __('Example: Server Room 3rd Floor') }}"
                                            class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 px-4 text-sm font-medium text-slate-700 focus:bg-white focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none">
                                        <p x-show="isDuplicate" x-cloak class="text-xs text-red-500 font-medium mt-2"><i
                                                class="ph-bold ph-warning-circle mr-1"></i>{{ __('Location with this name already exists in the selected building.') }}
                                        </p>
                                    </div>

                                    <div x-data="{ 
                                                open: false, 
                                                selectedId: '', 
                                                selectedName: '{{ __('Select Building') }}',
                                                options: [
                                                    @foreach($gedungs as $ged)
                                                        { id: '{{ $ged->id }}', name: '{{ addslashes($ged->nama) }}' },
                                                    @endforeach
                                                ] 
                                            }" class="relative">

                                        <label
                                            class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('Select Building') }}</label>

                                        <!-- Hidden input for form submission -->
                                        <input type="hidden" name="gedung_id" :value="selectedId" required
                                            x-effect="gedung_id = selectedId">

                                        <!-- Trigger -->
                                        <button type="button" @click="open = !open" @click.away="open = false"
                                            class="w-full bg-slate-50 border-2 rounded-xl py-2.5 px-4 text-sm font-medium text-left transition-all flex items-center justify-between"
                                            :class="open ? 'bg-white border-[#009B77] ring-4 ring-[#009B77]/15 text-slate-800' : 'border-slate-200 text-slate-700 hover:border-slate-300'">
                                            <span x-text="selectedName"
                                                :class="selectedId === '' ? 'text-slate-400' : ''"></span>
                                            <i class="ph-bold ph-caret-down text-slate-400 transition-transform duration-300"
                                                :class="open ? 'rotate-180 text-[#009B77]' : ''"></i>
                                        </button>

                                        <!-- Dropdown Menu -->
                                        <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200"
                                            x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                                            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                            x-transition:leave="transition ease-in duration-100"
                                            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                            x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                                            class="absolute z-50 w-full mt-2 bg-white rounded-xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.1)] border border-slate-100 py-2 max-h-60 overflow-y-auto">

                                            <template x-for="option in options" :key="option.id">
                                                <button type="button"
                                                    @click="selectedId = option.id; selectedName = option.name; open = false"
                                                    class="w-full text-left px-4 py-2.5 text-sm font-medium transition-colors flex items-center justify-between"
                                                    :class="selectedId === option.id ? 'bg-teal-50 text-[#009B77]' : 'text-slate-600 hover:bg-slate-50'">
                                                    <span x-text="option.name" class="font-bold"></span>
                                                    <i class="ph-bold ph-check text-[#009B77]"
                                                        x-show="selectedId === option.id" x-cloak></i>
                                                </button>
                                            </template>

                                            <div x-show="options.length === 0" x-cloak
                                                class="px-4 py-3 text-sm text-slate-400 text-center italic">
                                                {{ __('No building data available') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="bg-slate-50 px-6 py-4 sm:flex sm:flex-row-reverse sm:px-8 border-t border-slate-100 rounded-b-3xl">
                                <button type="submit" :disabled="isDuplicate || nama.trim() === ''"
                                    :class="isDuplicate || nama.trim() === '' ? 'opacity-50 cursor-not-allowed' : 'hover:bg-[#008264]'"
                                    class="inline-flex w-full justify-center rounded-xl bg-[#009B77] px-5 py-2.5 text-sm font-bold text-white shadow-sm sm:ml-3 sm:w-auto transition-colors shadow-[#009B77]/20">{{ __('Save') }}</button>
                                <button type="button" @click="showModalLokasi = false"
                                    class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-5 py-2.5 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors">{{ __('Cancel') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Tambah Gedung -->
        <div x-show="showModalGedung" style="display: none;" class="relative z-[100]" aria-labelledby="modal-title"
            role="dialog" aria-modal="true" x-cloak>
            <!-- Background overlay -->
            <div x-show="showModalGedung" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-slate-900/40 transition-opacity" x-cloak></div>

            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <!-- Modal panel -->
                    <div x-show="showModalGedung" x-transition:enter="ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        class="relative transform overflow-visible rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-100"
                        x-cloak>

                        <form action="/master-data/gedung" method="POST"
                            @submit="isDuplicate || nama.trim() === '' ? $event.preventDefault() : true"
                            x-data="{ nama: '', existing: [ @foreach(\App\Models\Gedung::all() as $g) '{{ strtolower(addslashes($g->nama)) }}', @endforeach ], get isDuplicate() { return this.nama.trim() !== '' && this.existing.includes(this.nama.toLowerCase().trim()); } }">
                            <input type="hidden" name="form_type" value="tambah_gedung">
                            <div class="bg-white rounded-t-3xl px-6 pb-6 pt-6 sm:p-8">
                                @csrf
                                <div class="flex items-center justify-between mb-6">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                                            <i class="ph-bold ph-buildings text-xl"></i>
                                        </div>
                                        <h3 class="text-lg font-bold leading-6 text-slate-800" id="modal-title">
                                            {{ __('Add Building Data') }}
                                        </h3>
                                    </div>
                                    <button @click="showModalGedung = false"
                                        class="text-slate-400 hover:text-slate-500 transition-colors">
                                        <i class="ph-bold ph-x text-xl"></i>
                                    </button>
                                </div>

                                <div class="space-y-5">
                                    <div>
                                        <label
                                            class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('Building Name') }}</label>
                                        <input type="text" name="nama" x-model="nama"
                                            @keydown.enter="if(isDuplicate || nama.trim() === '') { $event.preventDefault(); showModalGedung = false; }"
                                            required placeholder="{{ __('Example: BUR Building') }}"
                                            class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 px-4 text-sm font-medium text-slate-700 focus:bg-white focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none">
                                        <p x-show="isDuplicate" x-cloak class="text-xs text-red-500 font-medium mt-2"><i
                                                class="ph-bold ph-warning-circle mr-1"></i>{{ __('This building name is already registered.') }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="bg-slate-50 px-6 py-4 sm:flex sm:flex-row-reverse sm:px-8 border-t border-slate-100 rounded-b-3xl">
                                <button type="submit" :disabled="isDuplicate || nama.trim() === ''"
                                    :class="isDuplicate || nama.trim() === '' ? 'opacity-50 cursor-not-allowed' : 'hover:bg-indigo-700'"
                                    class="inline-flex w-full justify-center rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm sm:ml-3 sm:w-auto transition-colors shadow-indigo-600/20">{{ __('Save') }}</button>
                                <button type="button" @click="showModalGedung = false"
                                    class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-5 py-2.5 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors">{{ __('Cancel') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal {{ __('Add PFE Type') }} -->
        <div x-show="showModalJenis" style="display: none;" class="relative z-[100]" aria-labelledby="modal-title"
            role="dialog" aria-modal="true" x-cloak>
            <div x-show="showModalJenis" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-slate-900/40 transition-opacity" x-cloak></div>

            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div x-show="showModalJenis" x-transition:enter="ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        class="relative transform overflow-visible rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-100"
                        x-cloak>

                        <form action="/master-data/jenis" method="POST"
                            @submit="isDuplicate || nama.trim() === '' ? $event.preventDefault() : true"
                            x-data="{ nama: '', existing: [ @foreach(\App\Models\JenisApar::all() as $j) '{{ strtolower(addslashes($j->nama)) }}', @endforeach ], get isDuplicate() { return this.nama.trim() !== '' && this.existing.includes(this.nama.toLowerCase().trim()); } }">
                            <input type="hidden" name="form_type" value="tambah_jenis">
                            <div class="bg-white rounded-t-3xl px-6 pb-6 pt-6 sm:p-8">
                                @csrf
                                <div class="flex items-center justify-between mb-6">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                                            <i class="ph-fill ph-fire-extinguisher text-xl"></i>
                                        </div>
                                        <h3 class="text-lg font-bold leading-6 text-slate-800" id="modal-title">
                                            {{ __('Add PFE Type') }}
                                        </h3>
                                    </div>
                                    <button @click="showModalJenis = false"
                                        class="text-slate-400 hover:text-slate-500 transition-colors">
                                        <i class="ph-bold ph-x text-xl"></i>
                                    </button>
                                </div>

                                <div class="space-y-5">
                                    <div>
                                        <label
                                            class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('Type Name') }}</label>
                                        <input type="text" name="nama" x-model="nama"
                                            @keydown.enter="if(isDuplicate || nama.trim() === '') { $event.preventDefault(); showModalJenis = false; }"
                                            required placeholder="{{ __('Example: ABC Powder') }}"
                                            class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 px-4 text-sm font-medium text-slate-700 focus:bg-white focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none">
                                        <p x-show="isDuplicate" x-cloak class="text-xs text-red-500 font-medium mt-2"><i
                                                class="ph-bold ph-warning-circle mr-1"></i>{{ __('This PFE Type is already registered.') }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="bg-slate-50 px-6 py-4 sm:flex sm:flex-row-reverse sm:px-8 border-t border-slate-100 rounded-b-3xl">
                                <button type="submit" :disabled="isDuplicate || nama.trim() === ''"
                                    :class="isDuplicate || nama.trim() === '' ? 'opacity-50 cursor-not-allowed' : 'hover:bg-amber-600'"
                                    class="inline-flex w-full justify-center rounded-xl bg-amber-500 px-5 py-2.5 text-sm font-bold text-white shadow-sm sm:ml-3 sm:w-auto transition-colors shadow-amber-500/20">{{ __('Save') }}</button>
                                <button type="button" @click="showModalJenis = false"
                                    class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-5 py-2.5 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors">{{ __('Cancel') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Tambah Kapasitas -->
        <div x-show="showModalKapasitas" style="display: none;" class="relative z-[100]" aria-labelledby="modal-title"
            role="dialog" aria-modal="true" x-cloak>
            <div x-show="showModalKapasitas" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-slate-900/40 transition-opacity" x-cloak></div>

            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div x-show="showModalKapasitas" x-transition:enter="ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        class="relative transform overflow-visible rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-100"
                        x-cloak>

                        <form action="/master-data/kapasitas" method="POST"
                            @submit="isDuplicate || ukuran.trim() === '' ? $event.preventDefault() : true"
                            x-data="{ ukuran: '', existing: [ @foreach(\App\Models\KapasitasApar::all() as $k) '{{ strtolower(addslashes($k->ukuran)) }}', @endforeach ], get isDuplicate() { return this.ukuran.trim() !== '' && this.existing.includes(this.ukuran.trim() + ' kg'); } }">
                            <input type="hidden" name="form_type" value="tambah_kapasitas">
                            <div class="bg-white rounded-t-3xl px-6 pb-6 pt-6 sm:p-8">
                                @csrf
                                <div class="flex items-center justify-between mb-6">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                                            <i class="ph-fill ph-scales text-xl"></i>
                                        </div>
                                        <h3 class="text-lg font-bold leading-6 text-slate-800" id="modal-title">
                                            {{ __('Add Capacity') }}
                                        </h3>
                                    </div>
                                    <button @click="showModalKapasitas = false"
                                        class="text-slate-400 hover:text-slate-500 transition-colors">
                                        <i class="ph-bold ph-x text-xl"></i>
                                    </button>
                                </div>

                                <div class="space-y-5">
                                    <div>
                                        <label
                                            class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('Capacity') }}</label>
                                        <div class="relative flex items-center">
                                            <input type="number" step="any" name="ukuran" x-model="ukuran"
                                                @keydown="['e', 'E', '+', '-'].includes($event.key) && $event.preventDefault()"
                                                @keydown.enter="if(isDuplicate || ukuran.trim() === '') { $event.preventDefault(); showModalKapasitas = false; }"
                                                required placeholder="{{ __('Example: 3') }}"
                                                class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 pl-4 pr-12 text-sm font-medium text-slate-700 focus:bg-white focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none">
                                            <span class="absolute right-4 font-bold text-slate-400">Kg</span>
                                        </div>
                                        <p x-show="isDuplicate" x-cloak class="text-xs text-red-500 font-medium mt-2"><i
                                                class="ph-bold ph-warning-circle mr-1"></i>{{ __('This capacity is already registered.') }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="bg-slate-50 px-6 py-4 sm:flex sm:flex-row-reverse sm:px-8 border-t border-slate-100 rounded-b-3xl">
                                <button type="submit" :disabled="isDuplicate || ukuran.trim() === ''"
                                    :class="isDuplicate || ukuran.trim() === '' ? 'opacity-50 cursor-not-allowed' : 'hover:bg-purple-700'"
                                    class="inline-flex w-full justify-center rounded-xl bg-purple-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm sm:ml-3 sm:w-auto transition-colors shadow-purple-600/20">{{ __('Save') }}</button>
                                <button type="button" @click="showModalKapasitas = false"
                                    class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-5 py-2.5 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors">{{ __('Cancel') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Tambah APAR -->
        <div x-show="showModalApar" style="display: none;" class="relative z-[100]" aria-labelledby="modal-title"
            role="dialog" aria-modal="true" x-cloak>
            <!-- Background overlay -->
            <div x-show="showModalApar" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-slate-900/40 transition-opacity" x-cloak></div>

            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <!-- Modal panel -->
                    <div x-show="showModalApar" x-transition:enter="ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        class="relative transform overflow-visible rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-slate-100"
                        x-cloak>
                        <form action="/master-data/apar" method="POST">
                            <input type="hidden" name="form_type" value="tambah_apar">

                            @csrf
                            <div class="bg-white rounded-t-3xl px-6 pb-6 pt-6 sm:p-8">
                                <div class="flex items-center justify-between mb-6">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-xl bg-[#009B77]/10 text-[#009B77] flex items-center justify-center">
                                            <i class="ph-bold ph-fire-extinguisher text-xl"></i>
                                        </div>
                                        <h3 class="text-lg font-bold leading-6 text-slate-800" id="modal-title">
                                            {{ __('Add PFE Data') }}
                                        </h3>
                                    </div>
                                    <button type="button" @click="showModalApar = false"
                                        class="text-slate-400 hover:text-slate-500 transition-colors">
                                        <i class="ph-bold ph-x text-xl"></i>
                                    </button>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5" x-data="{
                                            gedungId: '{{ old('gedung_id') }}',
                                            lokasiName: '{{ old('lokasi') }}',
                                            gunakanCadangan: {{ old('gunakan_cadangan') ? 'true' : 'false' }},
                                            cadanganId: '{{ old('cadangan_id') }}',
                                            cadanganOptions: [
                                                @foreach($aparCadangans->where('total', '>', 0) as $c)
                                                    { id: '{{ $c->id }}', name: '{{ addslashes($c->gedung->nama ?? '') }} - {{ addslashes($c->jenis->nama ?? '') }} {{ addslashes($c->kapasitas->ukuran ?? '') }} (Stock: {{ $c->total }})', gedung: '{{ addslashes($c->gedung->nama ?? '') }}', jenis: '{{ addslashes($c->jenis->nama ?? '') }}', kapasitas: '{{ addslashes($c->kapasitas->ukuran ?? '') }}', sisa: {{ $c->total }}, jenis_id: '{{ $c->jenis_id }}', kapasitas_id: '{{ $c->kapasitas_id }}' },
                                                @endforeach
                                            ],
                                            lokasiOptions: [
                                                @foreach($lokasis as $lok)
                                                    { id: '{{ $lok->id }}', nama: '{{ addslashes($lok->nama) }}', gedung_id: '{{ $lok->gedung_id }}' },
                                                @endforeach
                                            ],
                                            get filteredLokasi() {
                                                if (!this.gedungId) return [];
                                                return this.lokasiOptions.filter(l => l.gedung_id == this.gedungId);
                                            }
                                        }">
                                    <!-- Stok Cadangan Option (Full Width) -->
                                    <div class="sm:col-span-2 rounded-xl p-4 mb-2 transition-colors border"
                                        :class="gunakanCadangan ? 'bg-[#009B77]/5 border-[#009B77]/30' : 'bg-slate-50 border-slate-200'">
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="checkbox" name="gunakan_cadangan" value="1"
                                                x-model="gunakanCadangan"
                                                class="w-5 h-5 rounded border-slate-300 text-[#009B77] focus:ring-[#009B77] accent-[#009B77] cursor-pointer transition-all">
                                            <span class="text-sm font-bold text-slate-700">{{ __('Take from Backup Stock') }}</span>
                                        </label>

                                        <div x-show="gunakanCadangan" x-collapse class="mt-4">
                                            <label
                                                class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('Select Stock Source') }}</label>
                                            <div class="relative" :class="openCadangan ? 'z-[60]' : 'z-10'" x-data="{
                                                        openCadangan: false,
                                                        searchCadangan: '',
                                                        get selectedCadanganName() {
                                                            let sel = cadanganOptions.find(o => o.id == cadanganId);
                                                            return sel ? sel.name : '{{ __('Select Stock') }}';
                                                        },
                                                        get selectedCadangan() {
                                                            return cadanganOptions.find(o => o.id == cadanganId);
                                                        },
                                                        get filteredCadangan() {
                                                            if (this.searchCadangan === '') return cadanganOptions;
                                                            return cadanganOptions.filter(o => o.name.toLowerCase().includes(this.searchCadangan.toLowerCase()));
                                                        }
                                                    }">
                                                <input type="hidden" name="cadangan_id" :value="cadanganId">
                                                <i class="ph-bold ph-archive absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg transition-colors z-10"
                                                    :class="openCadangan ? 'text-[#009B77]' : ''"></i>
                                                <button type="button" @click="openCadangan = !openCadangan"
                                                    @click.away="openCadangan = false"
                                                    class="w-full bg-white border-2 border-slate-200 rounded-xl py-2.5 pl-10 pr-4 text-sm font-medium text-slate-700 focus:bg-white focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none cursor-pointer text-left flex items-center justify-between"
                                                    :class="openCadangan ? 'bg-white border-[#009B77] ring-4 ring-[#009B77]/15' : ''">
                                                    <div x-show="cadanganId" class="flex flex-col text-left truncate flex-grow">
                                                        <span x-text="selectedCadangan?.gedung + ' - ' + selectedCadangan?.jenis" class="font-bold text-slate-700 text-sm truncate"></span>
                                                        <div class="flex items-center gap-2 mt-0.5">
                                                            <span class="text-[10px] font-bold text-slate-500" x-text="selectedCadangan?.kapasitas"></span>
                                                            <span class="text-[10px] font-bold" :class="selectedCadangan?.sisa > 10 ? 'text-[#009B77]' : 'text-amber-500'" x-text="'Stock: ' + selectedCadangan?.sisa"></span>
                                                        </div>
                                                    </div>
                                                    <span x-show="!cadanganId" class="text-slate-400 font-medium truncate block flex-grow">{{ __('Select Stock') }}</span>
                                                    <i class="ph-bold ph-caret-down text-slate-400 transition-transform duration-200"
                                                        :class="openCadangan ? 'rotate-180 text-[#009B77]' : ''"></i>
                                                </button>
                                                <div x-show="openCadangan" x-cloak x-transition:enter="transition ease-out duration-200"
                                                    x-transition:enter-start="opacity-0 scale-95"
                                                    x-transition:enter-end="opacity-100 scale-100"
                                                    x-transition:leave="transition ease-in duration-100"
                                                    x-transition:leave-start="opacity-100 scale-100"
                                                    x-transition:leave-end="opacity-0 scale-95"
                                                    class="absolute left-0 z-50 w-full mt-2 bg-white rounded-xl shadow-[0_10px_40px_rgba(0,0,0,0.1)] border border-slate-100 pb-2 pt-0 max-h-60 overflow-y-auto origin-top">
                                                    <div class="sticky top-0 bg-white p-2 border-b border-slate-100 z-10 rounded-t-xl">
                                                        <div class="relative">
                                                            <i
                                                                class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                                            <input type="text" x-model="searchCadangan"
                                                                placeholder="Cari stok..."
                                                                class="w-full bg-slate-50 border border-slate-200 rounded-lg py-2 pl-9 pr-3 text-sm font-medium text-slate-700 focus:outline-none focus:border-[#009B77] focus:ring-2 focus:ring-[#009B77]/20 transition-all"
                                                                @click.stop>
                                                        </div>
                                                    </div>
                                                    <div class="py-1">
                                                        <template x-for="option in filteredCadangan" :key="option.id">
                                                            <button type="button"
                                                                @click="cadanganId = option.id; openCadangan = false; $dispatch('cadangan-selected', { jenis_id: option.jenis_id, kapasitas_id: option.kapasitas_id })"
                                                                class="w-full text-left px-4 py-3 text-sm font-medium transition-colors flex items-center justify-between"
                                                                :class="cadanganId == option.id ? 'text-[#009B77] bg-[#009B77]/5' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'">
                                                                <div class="flex flex-col gap-1.5">
                                                                    <span x-text="option.gedung + ' - ' + option.jenis" class="font-bold"></span>
                                                                    <div class="flex items-center gap-2">
                                                                        <span class="text-[11px] text-slate-500 bg-slate-100 px-2 py-0.5 rounded-md border border-slate-200" x-text="option.kapasitas"></span>
                                                                        <span class="text-[11px] font-bold px-2 py-0.5 rounded-md" :class="option.sisa > 10 ? 'text-[#009B77] bg-[#009B77]/10' : 'text-amber-500 bg-amber-500/10'" x-text="'Stock: ' + option.sisa"></span>
                                                                    </div>
                                                                </div>
                                                                <i class="ph-bold ph-check text-[#009B77] text-lg"
                                                                    x-show="cadanganId == option.id"></i>
                                                            </button>
                                                        </template>
                                                        <div x-show="filteredCadangan.length === 0"
                                                            class="py-3 px-4 text-center text-sm text-slate-500">Stok tidak
                                                            ditemukan</div>
                                                    </div>
                                                </div>
                                            </div>
                                            @error('cadangan_id') <span
                                            class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                        </div>
                                    </div>

                                    <!-- {{ __('PFE NUMBER') }} -->
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2"
                                            style="white-space: nowrap;">{{ __('PFE NUMBER') }} <span
                                                class="text-[10px] text-slate-400 font-medium normal-case">{{ __('Auto ID Letters') }}</span></label>
                                        <div class="relative">
                                            <i
                                                class="ph-bold ph-hash absolute left-3.5 top-1/2 -translate-y-1/2 text-[#009B77] text-lg"></i>
                                            <input type="number" name="nomor_apar"
                                                placeholder="{{ __('Enter number (e.g. 12)') }}" required
                                                value="{{ old('nomor_apar') }}"
                                                onkeydown="if(['e', 'E', '+', '-', '.'].includes(event.key)) event.preventDefault();"
                                                class="w-full bg-white border-2 border-slate-200 rounded-xl py-2.5 pl-10 pr-4 text-sm font-medium text-slate-700 focus:outline-none focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all">
                                        </div>
                                        @error('nomor_apar') <span
                                        class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Nomor Ruangan -->
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2"
                                            style="white-space: nowrap;">{{ __('ROOM NO') }} <span
                                                class="text-[10px] text-slate-400 font-medium normal-case">({{ __('Optional') }})</span></label>
                                        <div class="relative">
                                            <i
                                                class="ph-bold ph-door absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg transition-colors z-10 peer-focus:text-[#009B77]"></i>
                                            <input type="text" name="nomor_ruangan"
                                                placeholder="{{ __('Example: R-101') }}"
                                                value="{{ old('nomor_ruangan') }}"
                                                class="w-full peer bg-white border-2 border-slate-200 rounded-xl py-2.5 pl-10 pr-4 text-sm font-medium text-slate-700 focus:outline-none focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none">
                                        </div>
                                        @error('nomor_ruangan') <span
                                        class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
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
                                let sel = this.options.find(o => o.id == gedungId);
                                return sel ? sel.name : '{{ __('Select Building') }}';
                            },
                            get filteredOptions() {
                                if (this.search === '') return this.options;
                                return this.options.filter(o => o.name.toLowerCase().includes(this.search.toLowerCase()));
                            }
                        }">
                                        <label
                                            class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('BUILDING') }}</label>
                                        <div class="relative">
                                            <input type="hidden" name="gedung_id" :value="gedungId" required>
                                            <i class="ph-bold ph-buildings absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg transition-colors z-10"
                                                :class="open ? 'text-[#009B77]' : 'peer-focus:text-[#009B77]'"></i>
                                            <button type="button" @click="open = !open" @click.away="open = false"
                                                class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 pl-10 pr-4 text-sm font-medium text-slate-700 focus:bg-white focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none cursor-pointer text-left flex items-center justify-between"
                                                :class="open ? 'bg-white border-[#009B77] ring-4 ring-[#009B77]/15' : ''">
                                                <span x-text="selectedName"
                                                    :class="gedungId ? 'font-bold text-slate-700' : 'text-slate-400 font-medium'"
                                                    class="truncate block"></span>
                                                <i class="ph-bold ph-caret-down text-slate-400 transition-transform duration-200 flex-shrink-0"
                                                    :class="open ? 'rotate-180 text-[#009B77]' : ''"></i>
                                            </button>
                                            <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200"
                                                x-transition:enter-start="opacity-0 scale-95"
                                                x-transition:enter-end="opacity-100 scale-100"
                                                x-transition:leave="transition ease-in duration-100"
                                                x-transition:leave-start="opacity-100 scale-100"
                                                x-transition:leave-end="opacity-0 scale-95"
                                                class="absolute left-0 z-50 w-full mt-2 bg-white rounded-xl shadow-[0_10px_40px_rgba(0,0,0,0.1)] border border-slate-100 py-2 max-h-60 overflow-y-auto origin-top">
                                                <div class="sticky top-0 bg-white p-2 border-b border-slate-100 z-10">
                                                    <div class="relative">
                                                        <i
                                                            class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                                        <input type="text" x-model="search"
                                                            placeholder="{{ __('Search...') }}"
                                                            class="w-full bg-slate-50 border border-slate-200 rounded-lg py-2 pl-9 pr-3 text-sm font-medium text-slate-700 focus:outline-none focus:border-[#009B77] focus:ring-2 focus:ring-[#009B77]/20 transition-all"
                                                            @click.stop @keydown.enter.prevent>
                                                    </div>
                                                </div>
                                                <div class="py-1">
                                                    <template x-for="option in filteredOptions" :key="option.id">
                                                        <button type="button" @click="gedungId = option.id; open = false"
                                                            class="w-full text-left px-4 py-2.5 text-sm font-medium transition-colors flex items-center justify-between"
                                                            :class="gedungId == option.id ? 'text-[#009B77] bg-[#009B77]/5' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'">
                                                            <span x-text="option.name" class="font-bold"></span>
                                                            <i class="ph-bold ph-check text-[#009B77]"
                                                                x-show="gedungId == option.id" x-cloak></i>
                                                        </button>
                                                    </template>
                                                    <div x-show="filteredOptions.length === 0"
                                                        class="py-3 px-4 text-center text-sm font-medium text-slate-500">
                                                        {{ __('No results found') }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @error('gedung_id') <span
                                        class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Lokasi -->
                                    <div>
                                        <label
                                            class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('LOCATION NAME') }}</label>
                                        <div class="relative">
                                            <input type="hidden" name="lokasi" :value="lokasiName">
                                            <i
                                                class="ph-bold ph-map-pin absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg transition-colors z-10 peer-focus:text-[#009B77]"></i>
                                            <input type="text" x-model="lokasiName"
                                                placeholder="{{ __('Example: Corridor') }}" required
                                                :disabled="!gedungId"
                                                :class="!gedungId ? 'bg-slate-100 cursor-not-allowed opacity-70' : 'bg-white focus:border-[#009B77] focus:ring-[#009B77]/15'"
                                                class="w-full peer border-2 border-slate-200 rounded-xl py-2.5 pl-10 pr-4 text-sm font-medium text-slate-700 focus:ring-4 transition-all outline-none">
                                        </div>
                                        @error('lokasi') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- {{ __('PFE Type') }} -->
                                    <div>
                                        <label
                                            class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('Type') }}
                                            APAR</label>
                                        <div x-data="{
                                    open: false,
                                    search: '',
                                    selectedId: '{{ old('jenis_id') }}',
                                    options: [
                                        @foreach($jenisApars as $jenis)
                                            { id: '{{ $jenis->id }}', name: '{{ addslashes($jenis->nama) }}' },
                                        @endforeach
                                    ],
                                    get selectedName() {
                                        let sel = this.options.find(o => o.id == this.selectedId);
                                        return sel ? sel.name : '{{ __('Select Type') }}';
                                    },
                                    get filteredOptions() {
                                        if (this.search === '') return this.options;
                                        return this.options.filter(o => o.name.toLowerCase().includes(this.search.toLowerCase()));
                                    }
                                }" @cadangan-selected.window="if(gunakanCadangan) selectedId = $event.detail.jenis_id"
                                            class="relative"
                                            :class="gunakanCadangan ? 'opacity-70 pointer-events-none' : ''">
                                            <input type="hidden" name="jenis_id" :value="selectedId" required>
                                            <i class="ph-bold ph-fire-extinguisher absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg transition-colors z-10"
                                                :class="open ? 'text-[#009B77]' : 'peer-focus:text-[#009B77]'"></i>
                                            <button type="button" @click="open = !open" @click.away="open = false"
                                                class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 pl-10 pr-4 text-sm font-medium text-slate-700 focus:bg-white focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none cursor-pointer text-left flex items-center justify-between"
                                                :class="open ? 'bg-white border-[#009B77] ring-4 ring-[#009B77]/15' : ''">
                                                <span x-text="selectedName"
                                                    :class="selectedId ? 'font-bold text-slate-700' : 'text-slate-400 font-medium'"
                                                    class="truncate block"></span>
                                                <i class="ph-bold ph-caret-down text-slate-400 transition-transform duration-200 flex-shrink-0"
                                                    :class="open ? 'rotate-180 text-[#009B77]' : ''"></i>
                                            </button>

                                            <!-- Dropdown Menu -->
                                            <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200"
                                                x-transition:enter-start="opacity-0 scale-95"
                                                x-transition:enter-end="opacity-100 scale-100"
                                                x-transition:leave="transition ease-in duration-100"
                                                x-transition:leave-start="opacity-100 scale-100"
                                                x-transition:leave-end="opacity-0 scale-95"
                                                class="absolute left-0 z-50 w-full mt-2 bg-white rounded-xl shadow-[0_10px_40px_rgba(0,0,0,0.1)] border border-slate-100 py-2 max-h-60 overflow-y-auto origin-top">
                                                <div class="sticky top-0 bg-white p-2 border-b border-slate-100 z-10">
                                                    <div class="relative">
                                                        <i
                                                            class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                                        <input type="text" x-model="search"
                                                            placeholder="{{ __('Search...') }}"
                                                            class="w-full bg-slate-50 border border-slate-200 rounded-lg py-2 pl-9 pr-3 text-sm font-medium text-slate-700 focus:outline-none focus:border-[#009B77] focus:ring-2 focus:ring-[#009B77]/20 transition-all"
                                                            @click.stop @keydown.enter.prevent>
                                                    </div>
                                                </div>
                                                <div class="py-1">
                                                    <template x-for="option in filteredOptions" :key="option.id">
                                                        <button type="button" @click="selectedId = option.id; open = false"
                                                            class="w-full text-left px-4 py-2.5 text-sm font-medium transition-colors flex items-center justify-between"
                                                            :class="selectedId == option.id ? 'text-[#009B77] bg-[#009B77]/5' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'">
                                                            <span x-text="option.name" class="font-bold"></span>



                                                            <i class="ph-bold ph-check text-[#009B77]"
                                                                x-show="selectedId == option.id" x-cloak></i>
                                                        </button>
                                                    </template>
                                                    <div x-show="filteredOptions.length === 0"
                                                        class="py-3 px-4 text-center text-sm font-medium text-slate-500">
                                                        {{ __('No results found') }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @error('jenis_id') <span
                                        class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Kapasitas -->
                                    <div>
                                        <label
                                            class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('Capacity') }}</label>
                                        <div x-data="{
                                    open: false,
                                    search: '',
                                    selectedId: '{{ old('kapasitas_id') }}',
                                    options: [
                                        @foreach($kapasitasApars as $kap)
                                            { id: '{{ $kap->id }}', name: '{{ addslashes($kap->ukuran) }}' },
                                        @endforeach
                                    ],
                                    get selectedName() {
                                        let sel = this.options.find(o => o.id == this.selectedId);
                                        return sel ? sel.name : '{{ __('Select Capacity') }}';
                                    },
                                    get filteredOptions() {
                                        if (this.search === '') return this.options;
                                        return this.options.filter(o => o.name.toLowerCase().includes(this.search.toLowerCase()));
                                    }
                                }" @cadangan-selected.window="if(gunakanCadangan) selectedId = $event.detail.kapasitas_id"
                                            class="relative"
                                            :class="gunakanCadangan ? 'opacity-70 pointer-events-none' : ''">
                                            <input type="hidden" name="kapasitas_id" :value="selectedId" required>
                                            <i class="ph-bold ph-scales absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg transition-colors z-10"
                                                :class="open ? 'text-[#009B77]' : 'peer-focus:text-[#009B77]'"></i>
                                            <button type="button" @click="open = !open" @click.away="open = false"
                                                class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 pl-10 pr-4 text-sm font-medium text-slate-700 focus:bg-white focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none cursor-pointer text-left flex items-center justify-between"
                                                :class="open ? 'bg-white border-[#009B77] ring-4 ring-[#009B77]/15' : ''">
                                                <span x-text="selectedName"
                                                    :class="selectedId ? 'font-bold text-slate-700' : 'text-slate-400 font-medium'"
                                                    class="truncate block"></span>
                                                <i class="ph-bold ph-caret-down text-slate-400 transition-transform duration-200 flex-shrink-0"
                                                    :class="open ? 'rotate-180 text-[#009B77]' : ''"></i>
                                            </button>

                                            <!-- Dropdown Menu -->
                                            <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200"
                                                x-transition:enter-start="opacity-0 scale-95"
                                                x-transition:enter-end="opacity-100 scale-100"
                                                x-transition:leave="transition ease-in duration-100"
                                                x-transition:leave-start="opacity-100 scale-100"
                                                x-transition:leave-end="opacity-0 scale-95"
                                                class="absolute left-0 z-50 w-full mt-2 bg-white rounded-xl shadow-[0_10px_40px_rgba(0,0,0,0.1)] border border-slate-100 py-2 max-h-60 overflow-y-auto origin-top">
                                                <div class="sticky top-0 bg-white p-2 border-b border-slate-100 z-10">
                                                    <div class="relative">
                                                        <i
                                                            class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                                        <input type="text" x-model="search"
                                                            placeholder="{{ __('Search...') }}"
                                                            class="w-full bg-slate-50 border border-slate-200 rounded-lg py-2 pl-9 pr-3 text-sm font-medium text-slate-700 focus:outline-none focus:border-[#009B77] focus:ring-2 focus:ring-[#009B77]/20 transition-all"
                                                            @click.stop @keydown.enter.prevent>
                                                    </div>
                                                </div>
                                                <div class="py-1">
                                                    <template x-for="option in filteredOptions" :key="option.id">
                                                        <button type="button" @click="selectedId = option.id; open = false"
                                                            class="w-full text-left px-4 py-2.5 text-sm font-medium transition-colors flex items-center justify-between"
                                                            :class="selectedId == option.id ? 'text-[#009B77] bg-[#009B77]/5' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'">
                                                            <span x-text="option.name" class="font-bold"></span>



                                                            <i class="ph-bold ph-check text-[#009B77]"
                                                                x-show="selectedId == option.id" x-cloak></i>
                                                        </button>
                                                    </template>
                                                    <div x-show="filteredOptions.length === 0"
                                                        class="py-3 px-4 text-center text-sm font-medium text-slate-500">
                                                        {{ __('No results found') }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @error('kapasitas_id') <span
                                        class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Qty -->
                                    <div>
                                        <label
                                            class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Qty</label>
                                        <div class="relative">
                                            <i
                                                class="ph-bold ph-hash absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
                                            <input type="number" name="qty" min="0" placeholder="{{ __('Enter quantity') }}"
                                                value="{{ old('qty', 1) }}"
                                                onkeydown="if(['e', 'E', '+', '-', '.'].includes(event.key)) event.preventDefault();"
                                                class="w-full bg-white border-2 border-slate-200 rounded-xl py-2.5 pl-12 pr-4 text-sm font-medium text-slate-700 focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none">
                                        </div>
                                        @error('qty') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- {{ __('EXPIRED DATE') }} -->
                                    <div>
                                        <label
                                            class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('EXPIRED DATE') }}</label>
                                        <div class="relative">
                                            <i
                                                class="ph-bold ph-calendar-blank absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg z-10"></i>
                                            <input type="text" name="tgl_kedaluwarsa"
                                                placeholder="{{ __('Select Date...') }}"
                                                value="{{ old('tgl_kedaluwarsa') }}" @input.debounce.100ms="if($event.target.value) { 
                                                            let d = new Date($event.target.value); 
                                                            if(!isNaN(d)) { 
                                                                d.setFullYear(d.getFullYear() - 2); 
                                                                let prev = d.toISOString().split('T')[0];
                                                                let input2 = $event.target.closest('form').querySelector('input[name=tgl_isi_ulang]');
                                                                if(input2._flatpickr) input2._flatpickr.setDate(prev, false);
                                                                else input2.value = prev;
                                                            } 
                                                        }"
                                                class="datepicker w-full bg-white border-2 border-slate-200 rounded-xl py-2.5 pl-10 pr-10 text-sm font-medium text-slate-700 focus:outline-none focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 appearance-none cursor-pointer">
                                            <i
                                                class="ph-bold ph-caret-down absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                                        </div>
                                        @error('tgl_kedaluwarsa') <span
                                        class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- {{ __('Last Refill') }} -->
                                    <div>
                                        <label
                                            class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('Last Refill') }}</label>
                                        <div class="relative">
                                            <i
                                                class="ph-bold ph-calendar-blank absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg z-10"></i>
                                            <input type="text" name="tgl_isi_ulang" placeholder="{{ __('Select Date...') }}"
                                                value="{{ old('tgl_isi_ulang') }}" @input.debounce.100ms="if($event.target.value) { 
                                                            let d = new Date($event.target.value); 
                                                            if(!isNaN(d)) { 
                                                                d.setFullYear(d.getFullYear() + 2); 
                                                                let next = d.toISOString().split('T')[0];
                                                                let input1 = $event.target.closest('form').querySelector('input[name=tgl_kedaluwarsa]');
                                                                if(input1._flatpickr) input1._flatpickr.setDate(next, false);
                                                                else input1.value = next;
                                                            } 
                                                        }"
                                                class="datepicker w-full bg-white border-2 border-slate-200 rounded-xl py-2.5 pl-10 pr-10 text-sm font-medium text-slate-700 focus:outline-none focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 appearance-none cursor-pointer">
                                            <i
                                                class="ph-bold ph-caret-down absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                                        </div>
                                        @error('tgl_isi_ulang') <span
                                        class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            <div
                                class="bg-slate-50 px-6 py-4 sm:flex sm:flex-row-reverse sm:px-8 border-t border-slate-100 rounded-b-3xl">
                                <button type="submit"
                                    class="inline-flex w-full justify-center rounded-xl bg-[#009B77] px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-[#008264] sm:ml-3 sm:w-auto transition-colors shadow-[#009B77]/20">{{ __('Save Data') }}</button>
                                <button type="button" @click="showModalApar = false"
                                    class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-5 py-2.5 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors">{{ __('Cancel') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Edit APAR -->
        <div x-show="showModalEditApar" style="display: none;" class="relative z-[100]" aria-labelledby="modal-title"
            role="dialog" aria-modal="true" x-cloak>
            <!-- Background overlay -->
            <div x-show="showModalEditApar" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-slate-900/40 transition-opacity" x-cloak></div>

            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <!-- Modal panel -->
                    <div x-show="showModalEditApar" x-transition:enter="ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        class="relative transform overflow-visible rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-slate-100"
                        x-cloak>
                        <form :action="`/master-data/apar/${editApar.id}`" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="bg-white rounded-t-3xl px-6 pb-6 pt-6 sm:p-8">
                                <div class="flex items-center justify-between mb-6">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center">
                                            <i class="ph-bold ph-pencil-simple text-xl"></i>
                                        </div>
                                        <h3 class="text-lg font-bold leading-6 text-slate-800" id="modal-title">
                                            {{ __('Edit PFE Data') }}
                                        </h3>
                                    </div>
                                    <button type="button" @click="showModalEditApar = false"
                                        class="text-slate-400 hover:text-slate-500 transition-colors">
                                        <i class="ph-bold ph-x text-xl"></i>
                                    </button>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5" x-data="{
                                            cadanganOptions: [
                                                @foreach($aparCadangans->where('total', '>', 0) as $c)
                                                    { id: '{{ $c->id }}', name: '{{ addslashes($c->gedung->nama ?? '') }} - {{ addslashes($c->jenis->nama ?? '') }} {{ addslashes($c->kapasitas->ukuran ?? '') }} (Stock: {{ $c->total }})', gedung: '{{ addslashes($c->gedung->nama ?? '') }}', jenis: '{{ addslashes($c->jenis->nama ?? '') }}', kapasitas: '{{ addslashes($c->kapasitas->ukuran ?? '') }}', sisa: {{ $c->total }}, jenis_id: '{{ $c->jenis_id }}', kapasitas_id: '{{ $c->kapasitas_id }}' },
                                                @endforeach
                                            ],
                                            lokasiOptions: [
                                                @foreach($lokasis as $lok)
                                                    { id: '{{ $lok->id }}', nama: '{{ addslashes($lok->nama) }}', gedung_id: '{{ $lok->gedung_id }}' },
                                                @endforeach
                                            ],
                                            get filteredLokasi() {
                                                if (!editApar.gedung_id) return [];
                                                return this.lokasiOptions.filter(l => l.gedung_id == editApar.gedung_id);
                                            }
                                        }">
                                    <!-- Stok Cadangan Option (Full Width) -->
                                    <div class="sm:col-span-2 rounded-xl p-4 mb-2 transition-colors border"
                                        :class="editApar.gunakanCadangan ? 'bg-amber-50 border-amber-300' : 'bg-slate-50 border-slate-200'">
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="checkbox" name="gunakan_cadangan" value="1"
                                                x-model="editApar.gunakanCadangan"
                                                class="w-5 h-5 rounded border-slate-300 text-amber-500 focus:ring-amber-500 accent-amber-500 cursor-pointer transition-all">
                                            <span class="text-sm font-bold text-slate-700">{{ __('Replace from Backup Stock') }}</span>
                                        </label>

                                        <div x-show="editApar.gunakanCadangan" x-collapse class="mt-4">
                                            <label
                                                class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('Select Stock Source') }}</label>
                                            <div class="relative" :class="openCadangan ? 'z-[60]' : 'z-10'" x-data="{
                                                        openCadangan: false,
                                                        searchCadangan: '',
                                                        get selectedCadanganName() {
                                                            let sel = cadanganOptions.find(o => o.id == editApar.cadanganId);
                                                            return sel ? sel.name : '{{ __('Select Stock') }}';
                                                        },
                                                        get selectedCadangan() {
                                                            return cadanganOptions.find(o => o.id == editApar.cadanganId);
                                                        },
                                                        get filteredCadangan() {
                                                            if (this.searchCadangan === '') return cadanganOptions;
                                                            return cadanganOptions.filter(o => o.name.toLowerCase().includes(this.searchCadangan.toLowerCase()));
                                                        }
                                                    }">
                                                <input type="hidden" name="cadangan_id" :value="editApar.cadanganId">
                                                <i class="ph-bold ph-archive absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg transition-colors z-10"
                                                    :class="openCadangan ? 'text-amber-500' : ''"></i>
                                                <button type="button" @click="openCadangan = !openCadangan"
                                                    @click.away="openCadangan = false"
                                                    class="w-full bg-white border-2 border-slate-200 rounded-xl py-2.5 pl-10 pr-4 text-sm font-medium text-slate-700 focus:bg-white focus:border-amber-500 focus:ring-4 focus:ring-amber-500/15 transition-all outline-none cursor-pointer text-left flex items-center justify-between"
                                                    :class="openCadangan ? 'bg-white border-amber-500 ring-4 ring-amber-500/15' : ''">
                                                    <div x-show="editApar.cadanganId" class="flex flex-col text-left truncate flex-grow">
                                                        <span x-text="selectedCadangan?.gedung + ' - ' + selectedCadangan?.jenis" class="font-bold text-slate-700 text-sm truncate"></span>
                                                        <div class="flex items-center gap-2 mt-0.5">
                                                            <span class="text-[10px] font-bold text-slate-500" x-text="selectedCadangan?.kapasitas"></span>
                                                            <span class="text-[10px] font-bold" :class="selectedCadangan?.sisa > 10 ? 'text-[#009B77]' : 'text-amber-500'" x-text="'Stock: ' + selectedCadangan?.sisa"></span>
                                                        </div>
                                                    </div>
                                                    <span x-show="!editApar.cadanganId" class="text-slate-400 font-medium truncate block flex-grow">{{ __('Select Stock') }}</span>
                                                    <i class="ph-bold ph-caret-down text-slate-400 transition-transform duration-200"
                                                        :class="openCadangan ? 'rotate-180 text-amber-500' : ''"></i>
                                                </button>
                                                <div x-show="openCadangan" x-cloak x-transition:enter="transition ease-out duration-200"
                                                    x-transition:enter-start="opacity-0 scale-95"
                                                    x-transition:enter-end="opacity-100 scale-100"
                                                    x-transition:leave="transition ease-in duration-100"
                                                    x-transition:leave-start="opacity-100 scale-100"
                                                    x-transition:leave-end="opacity-0 scale-95"
                                                    class="absolute left-0 z-50 w-full mt-2 bg-white rounded-xl shadow-[0_10px_40px_rgba(0,0,0,0.1)] border border-slate-100 pb-2 pt-0 max-h-60 overflow-y-auto origin-top">
                                                    <div class="sticky top-0 bg-white p-2 border-b border-slate-100 z-10 rounded-t-xl">
                                                        <div class="relative">
                                                            <i
                                                                class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                                            <input type="text" x-model="searchCadangan"
                                                                placeholder="Cari stok..."
                                                                class="w-full bg-slate-50 border border-slate-200 rounded-lg py-2 pl-9 pr-3 text-sm font-medium text-slate-700 focus:outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 transition-all"
                                                                @click.stop>
                                                        </div>
                                                    </div>
                                                    <div class="py-1">
                                                        <template x-for="option in filteredCadangan" :key="option.id">
                                                            <button type="button"
                                                                @click="editApar.cadanganId = option.id; openCadangan = false; $dispatch('cadangan-selected-edit', { jenis_id: option.jenis_id, kapasitas_id: option.kapasitas_id })"
                                                                class="w-full text-left px-4 py-3 text-sm font-medium transition-colors flex items-center justify-between"
                                                                :class="editApar.cadanganId == option.id ? 'text-amber-500 bg-amber-500/5' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'">
                                                                <div class="flex flex-col gap-1.5">
                                                                    <span x-text="option.gedung + ' - ' + option.jenis" class="font-bold"></span>
                                                                    <div class="flex items-center gap-2">
                                                                        <span class="text-[11px] text-slate-500 bg-slate-100 px-2 py-0.5 rounded-md border border-slate-200" x-text="option.kapasitas"></span>
                                                                        <span class="text-[11px] font-bold px-2 py-0.5 rounded-md" :class="option.sisa > 10 ? 'text-[#009B77] bg-[#009B77]/10' : 'text-amber-500 bg-amber-500/10'" x-text="'Stock: ' + option.sisa"></span>
                                                                    </div>
                                                                </div>
                                                                <i class="ph-bold ph-check text-amber-500 text-lg"
                                                                    x-show="editApar.cadanganId == option.id"></i>
                                                            </button>
                                                        </template>
                                                        <div x-show="filteredCadangan.length === 0"
                                                            class="py-3 px-4 text-center text-sm text-slate-500">Stok tidak
                                                            ditemukan</div>
                                                    </div>
                                                </div>
                                            </div>
                                            @error('cadangan_id') <span
                                            class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                        </div>
                                    </div>

                                    <!-- {{ __('PFE NUMBER') }} -->
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2"
                                            style="white-space: nowrap;">{{ __('PFE NUMBER') }} <span
                                                class="text-[10px] text-slate-400 font-medium normal-case">{{ __('Auto ID Letters') }}</span></label>
                                        <div class="relative">
                                            <i
                                                class="ph-bold ph-hash absolute left-3.5 top-1/2 -translate-y-1/2 text-amber-500 text-lg"></i>
                                            <input type="number" name="nomor_apar"
                                                placeholder="{{ __('Enter sequence number...') }}" required
                                                x-model="editApar.nomor_apar"
                                                onkeydown="if(['e', 'E', '+', '-', '.'].includes(event.key)) event.preventDefault();"
                                                class="w-full bg-white border-2 border-slate-200 rounded-xl py-2.5 pl-10 pr-4 text-sm font-medium text-slate-700 focus:border-amber-500 focus:ring-4 focus:ring-amber-500/15 transition-all outline-none">
                                        </div>
                                        @error('nomor_apar') <span
                                        class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Nomor Ruangan -->
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2"
                                            style="white-space: nowrap;">{{ __('ROOM NO') }} <span
                                                class="text-[10px] text-slate-400 font-medium normal-case">({{ __('Optional') }})</span></label>
                                        <div class="relative">
                                            <i
                                                class="ph-bold ph-door absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg transition-colors z-10 peer-focus:text-amber-500"></i>
                                            <input type="text" name="nomor_ruangan"
                                                placeholder="{{ __('Example: R-101') }}"
                                                x-model="editApar.nomor_ruangan"
                                                class="w-full peer bg-white border-2 border-slate-200 rounded-xl py-2.5 pl-10 pr-4 text-sm font-medium text-slate-700 focus:border-amber-500 focus:ring-4 focus:ring-amber-500/15 transition-all outline-none">
                                        </div>
                                        @error('nomor_ruangan') <span
                                        class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
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
                                        <label
                                            class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('BUILDING') }}</label>
                                        <div class="relative">
                                            <input type="hidden" name="gedung_id" :value="editApar.gedung_id" required>
                                            <i class="ph-bold ph-buildings absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg transition-colors z-10"
                                                :class="open ? 'text-amber-500' : 'peer-focus:text-amber-500'"></i>
                                            <button type="button" @click="open = !open" @click.away="open = false"
                                                class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 pl-10 pr-4 text-sm font-medium text-slate-700 focus:bg-white focus:border-amber-500 focus:ring-4 focus:ring-amber-500/15 transition-all outline-none cursor-pointer text-left flex items-center justify-between"
                                                :class="open ? 'bg-white border-amber-500 ring-4 ring-amber-500/15' : ''">
                                                <span x-text="selectedName"
                                                    :class="editApar.gedung_id ? 'font-bold text-slate-700' : 'text-slate-400 font-medium'"
                                                    class="truncate block"></span>
                                                <i class="ph-bold ph-caret-down text-slate-400 transition-transform duration-200 flex-shrink-0"
                                                    :class="open ? 'rotate-180 text-amber-500' : ''"></i>
                                            </button>
                                            <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200"
                                                x-transition:enter-start="opacity-0 scale-95"
                                                x-transition:enter-end="opacity-100 scale-100"
                                                x-transition:leave="transition ease-in duration-100"
                                                x-transition:leave-start="opacity-100 scale-100"
                                                x-transition:leave-end="opacity-0 scale-95"
                                                class="absolute left-0 z-50 w-full mt-2 bg-white rounded-xl shadow-[0_10px_40px_rgba(0,0,0,0.1)] border border-slate-100 py-2 max-h-60 overflow-y-auto origin-top">
                                                <div class="sticky top-0 bg-white p-2 border-b border-slate-100 z-10">
                                                    <div class="relative">
                                                        <i
                                                            class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                                        <input type="text" x-model="search"
                                                            placeholder="{{ __('Search...') }}"
                                                            class="w-full bg-slate-50 border border-slate-200 rounded-lg py-2 pl-9 pr-3 text-sm font-medium text-slate-700 focus:outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 transition-all"
                                                            @click.stop @keydown.enter.prevent>
                                                    </div>
                                                </div>
                                                <div class="py-1">
                                                    <template x-for="option in filteredOptions" :key="option.id">
                                                        <button type="button"
                                                            @click="editApar.gedung_id = option.id; open = false"
                                                            class="w-full text-left px-4 py-2.5 text-sm font-medium transition-colors flex items-center justify-between"
                                                            :class="editApar.gedung_id == option.id ? 'text-amber-500 bg-amber-500/5' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'">
                                                            <span x-text="option.name" class="font-bold"></span>
                                                            <i class="ph-bold ph-check text-amber-500"
                                                                x-show="editApar.gedung_id == option.id" x-cloak></i>
                                                        </button>
                                                    </template>
                                                    <div x-show="filteredOptions.length === 0"
                                                        class="py-3 px-4 text-center text-sm font-medium text-slate-500">
                                                        {{ __('No results found') }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @error('gedung_id') <span
                                        class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
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
                                        <label
                                            class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('LOCATION') }}<span
                                                class="text-[10px] text-slate-400 font-medium normal-case">({{ __('Select / Type New') }})</span></label>
                                        <div class="relative">
                                            <input type="hidden" name="lokasi" :value="editApar.lokasi">
                                            <i class="ph-bold ph-map-pin absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg transition-colors z-10"
                                                :class="open ? 'text-amber-500' : 'peer-focus:text-amber-500'"></i>
                                            <input type="text" x-model="editApar.lokasi" @focus="open = true"
                                                @click.away="open = false" placeholder="{{ __('Example: Corridor') }}"
                                                required
                                                :disabled="!editApar.gedung_id"
                                                :class="[!editApar.gedung_id ? 'bg-slate-100 cursor-not-allowed opacity-70' : 'bg-white focus:border-amber-500 focus:ring-amber-500/15', open ? 'bg-white border-amber-500 ring-4 ring-amber-500/15' : '']"
                                                class="w-full border-2 border-slate-200 rounded-xl py-2.5 pl-10 pr-4 text-sm font-medium text-slate-700 focus:ring-4 transition-all outline-none">
                                            <div x-show="open && (!editApar.gedung_id || filtered.length > 0)" x-cloak
                                                x-transition:enter="transition ease-out duration-200"
                                                x-transition:enter-start="opacity-0 scale-95"
                                                x-transition:enter-end="opacity-100 scale-100"
                                                x-transition:leave="transition ease-in duration-100"
                                                x-transition:leave-start="opacity-100 scale-100"
                                                x-transition:leave-end="opacity-0 scale-95"
                                                class="absolute left-0 z-50 w-full mt-2 bg-white rounded-xl shadow-[0_10px_40px_rgba(0,0,0,0.1)] border border-slate-100 py-2 max-h-60 overflow-y-auto origin-top">
                                                <div class="py-1">
                                                    <div x-show="!editApar.gedung_id"
                                                        class="py-3 px-4 text-center text-sm font-medium text-amber-500">
                                                        <i
                                                            class="ph-bold ph-warning-circle mr-1"></i>{{ __('Pilih Gedung terlebih dahulu') }}
                                                    </div>
                                                    <template x-for="lok in filtered" :key="lok.id">
                                                        <button type="button"
                                                            @click="editApar.lokasi = lok.nama; open = false"
                                                            class="w-full text-left px-4 py-2.5 text-sm font-medium transition-colors flex items-center justify-between"
                                                            :class="editApar.lokasi == lok.nama ? 'text-amber-500 bg-amber-500/5' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'">
                                                            <span x-text="lok.nama" class="font-bold"></span>
                                                            <i class="ph-bold ph-check text-amber-500"
                                                                x-show="editApar.lokasi == lok.nama" x-cloak></i>
                                                        </button>
                                                    </template>

                                                </div>
                                            </div>
                                        </div>
                                        @error('lokasi') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- {{ __('PFE Type') }} -->
                                    <div>
                                        <label
                                            class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('Type') }}
                                            APAR</label>
                                        <div x-data="{
                                    open: false,
                                    search: '',

                                    options: [
                                        @foreach($jenisApars as $jenis)
                                            { id: '{{ $jenis->id }}', name: '{{ addslashes($jenis->nama) }}' },
                                        @endforeach
                                    ],
                                    get selectedName() {
                                        let sel = this.options.find(o => o.id == this.editApar.jenis_id);
                                        return sel ? sel.name : '{{ __('Select Type') }}';
                                    },
                                    get filteredOptions() {
                                        if (this.search === '') return this.options;
                                        return this.options.filter(o => o.name.toLowerCase().includes(this.search.toLowerCase()));
                                    }
                                }" @cadangan-selected-edit.window="if(editApar.gunakanCadangan) editApar.jenis_id = $event.detail.jenis_id"
                                            class="relative"
                                            :class="editApar.gunakanCadangan ? 'opacity-70 pointer-events-none' : ''">
                                            <input type="hidden" name="jenis_id" :value="editApar.jenis_id" required>
                                            <i class="ph-bold ph-fire-extinguisher absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg transition-colors z-10"
                                                :class="open ? 'text-amber-500' : 'peer-focus:text-amber-500'"></i>
                                            <button type="button" @click="open = !open" @click.away="open = false"
                                                class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 pl-10 pr-4 text-sm font-medium text-slate-700 focus:bg-white focus:border-amber-500 focus:ring-4 focus:ring-amber-500/15 transition-all outline-none cursor-pointer text-left flex items-center justify-between"
                                                :class="open ? 'bg-white border-amber-500 ring-4 ring-amber-500/15' : ''">
                                                <span x-text="selectedName"
                                                    :class="editApar.jenis_id ? 'font-bold text-slate-700' : 'text-slate-400 font-medium'"
                                                    class="truncate block"></span>
                                                <i class="ph-bold ph-caret-down text-slate-400 transition-transform duration-200 flex-shrink-0"
                                                    :class="open ? 'rotate-180 text-[#009B77]' : ''"></i>
                                            </button>

                                            <!-- Dropdown Menu -->
                                            <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200"
                                                x-transition:enter-start="opacity-0 scale-95"
                                                x-transition:enter-end="opacity-100 scale-100"
                                                x-transition:leave="transition ease-in duration-100"
                                                x-transition:leave-start="opacity-100 scale-100"
                                                x-transition:leave-end="opacity-0 scale-95"
                                                class="absolute left-0 z-50 w-full mt-2 bg-white rounded-xl shadow-[0_10px_40px_rgba(0,0,0,0.1)] border border-slate-100 py-2 max-h-60 overflow-y-auto origin-top">
                                                <div class="sticky top-0 bg-white p-2 border-b border-slate-100 z-10">
                                                    <div class="relative">
                                                        <i
                                                            class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                                        <input type="text" x-model="search"
                                                            placeholder="{{ __('Search...') }}"
                                                            class="w-full bg-slate-50 border border-slate-200 rounded-lg py-2 pl-9 pr-3 text-sm font-medium text-slate-700 focus:outline-none focus:border-[#009B77] focus:ring-2 focus:ring-[#009B77]/20 transition-all"
                                                            @click.stop @keydown.enter.prevent>
                                                    </div>
                                                </div>
                                                <div class="py-1">
                                                    <template x-for="option in filteredOptions" :key="option.id">
                                                        <button type="button"
                                                            @click="editApar.jenis_id = option.id; open = false"
                                                            class="w-full text-left px-4 py-2.5 text-sm font-medium transition-colors flex items-center justify-between"
                                                            :class="editApar.jenis_id == option.id ? 'text-amber-500 bg-amber-50' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'">
                                                            <span x-text="option.name" class="font-bold"></span>



                                                            <i class="ph-bold ph-check text-[#009B77]"
                                                                x-show="editApar.jenis_id == option.id" x-cloak></i>
                                                        </button>
                                                    </template>
                                                    <div x-show="filteredOptions.length === 0"
                                                        class="py-3 px-4 text-center text-sm font-medium text-slate-500">
                                                        {{ __('No results found') }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @error('jenis_id') <span
                                        class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Kapasitas -->
                                    <div>
                                        <label
                                            class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('Capacity') }}</label>
                                        <div x-data="{
                                    open: false,
                                    search: '',

                                    options: [
                                        @foreach($kapasitasApars as $kap)
                                            { id: '{{ $kap->id }}', name: '{{ addslashes($kap->ukuran) }}' },
                                        @endforeach
                                    ],
                                    get selectedName() {
                                        let sel = this.options.find(o => o.id == this.editApar.kapasitas_id);
                                        return sel ? sel.name : '{{ __('Select Capacity') }}';
                                    },
                                    get filteredOptions() {
                                        if (this.search === '') return this.options;
                                        return this.options.filter(o => o.name.toLowerCase().includes(this.search.toLowerCase()));
                                    }
                                }" @cadangan-selected-edit.window="if(editApar.gunakanCadangan) editApar.kapasitas_id = $event.detail.kapasitas_id"
                                            class="relative"
                                            :class="editApar.gunakanCadangan ? 'opacity-70 pointer-events-none' : ''">
                                            <input type="hidden" name="kapasitas_id" :value="editApar.kapasitas_id"
                                                required>
                                            <i class="ph-bold ph-scales absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg transition-colors z-10"
                                                :class="open ? 'text-amber-500' : 'peer-focus:text-amber-500'"></i>
                                            <button type="button" @click="open = !open" @click.away="open = false"
                                                class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 pl-10 pr-4 text-sm font-medium text-slate-700 focus:bg-white focus:border-amber-500 focus:ring-4 focus:ring-amber-500/15 transition-all outline-none cursor-pointer text-left flex items-center justify-between"
                                                :class="open ? 'bg-white border-amber-500 ring-4 ring-amber-500/15' : ''">
                                                <span x-text="selectedName"
                                                    :class="editApar.kapasitas_id ? 'font-bold text-slate-700' : 'text-slate-400 font-medium'"
                                                    class="truncate block"></span>
                                                <i class="ph-bold ph-caret-down text-slate-400 transition-transform duration-200 flex-shrink-0"
                                                    :class="open ? 'rotate-180 text-[#009B77]' : ''"></i>
                                            </button>

                                            <!-- Dropdown Menu -->
                                            <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200"
                                                x-transition:enter-start="opacity-0 scale-95"
                                                x-transition:enter-end="opacity-100 scale-100"
                                                x-transition:leave="transition ease-in duration-100"
                                                x-transition:leave-start="opacity-100 scale-100"
                                                x-transition:leave-end="opacity-0 scale-95"
                                                class="absolute left-0 z-50 w-full mt-2 bg-white rounded-xl shadow-[0_10px_40px_rgba(0,0,0,0.1)] border border-slate-100 py-2 max-h-60 overflow-y-auto origin-top">
                                                <div class="sticky top-0 bg-white p-2 border-b border-slate-100 z-10">
                                                    <div class="relative">
                                                        <i
                                                            class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                                        <input type="text" x-model="search"
                                                            placeholder="{{ __('Search...') }}"
                                                            class="w-full bg-slate-50 border border-slate-200 rounded-lg py-2 pl-9 pr-3 text-sm font-medium text-slate-700 focus:outline-none focus:border-[#009B77] focus:ring-2 focus:ring-[#009B77]/20 transition-all"
                                                            @click.stop @keydown.enter.prevent>
                                                    </div>
                                                </div>
                                                <div class="py-1">
                                                    <template x-for="option in filteredOptions" :key="option.id">
                                                        <button type="button"
                                                            @click="editApar.kapasitas_id = option.id; open = false"
                                                            class="w-full text-left px-4 py-2.5 text-sm font-medium transition-colors flex items-center justify-between"
                                                            :class="editApar.kapasitas_id == option.id ? 'text-amber-500 bg-amber-50' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'">
                                                            <span x-text="option.name" class="font-bold"></span>



                                                            <i class="ph-bold ph-check text-[#009B77]"
                                                                x-show="editApar.kapasitas_id == option.id" x-cloak></i>
                                                        </button>
                                                    </template>
                                                    <div x-show="filteredOptions.length === 0"
                                                        class="py-3 px-4 text-center text-sm font-medium text-slate-500">
                                                        {{ __('No results found') }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @error('kapasitas_id') <span
                                        class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Qty -->
                                    <div>
                                        <label
                                            class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Qty</label>
                                        <div class="relative">
                                            <i
                                                class="ph-bold ph-hash absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
                                            <input type="number" name="qty" min="0" placeholder="{{ __('Enter quantity') }}"
                                                x-model="editApar.qty" required
                                                onkeydown="if(['e', 'E', '+', '-', '.'].includes(event.key)) event.preventDefault();"
                                                class="w-full bg-white border-2 border-slate-200 rounded-xl py-2.5 pl-12 pr-4 text-sm font-medium text-slate-700 focus:border-amber-500 focus:ring-4 focus:ring-amber-500/15 transition-all outline-none">
                                        </div>
                                        @error('qty') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- {{ __('EXPIRED DATE') }} -->
                                    <div>
                                        <label
                                            class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('EXPIRED DATE') }}</label>
                                        <div class="relative">
                                            <i
                                                class="ph-bold ph-calendar-blank absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg z-10"></i>
                                            <input type="text" name="tgl_kedaluwarsa"
                                                placeholder="{{ __('Select Date...') }}" x-model="editApar.tgl_kedaluwarsa"
                                                @input.debounce.100ms="if($event.target.value) { 
                                                            let d = new Date($event.target.value); 
                                                            if(!isNaN(d)) { 
                                                                d.setFullYear(d.getFullYear() - 2); 
                                                                let prev = d.toISOString().split('T')[0];
                                                                editApar.tgl_isi_ulang = prev;
                                                                let input2 = $event.target.closest('form').querySelector('input[name=tgl_isi_ulang]');
                                                                if(input2 && input2._flatpickr) input2._flatpickr.setDate(prev, false);
                                                            } 
                                                        }" required
                                                class="datepicker w-full bg-white border-2 border-slate-200 rounded-xl py-2.5 pl-10 pr-10 text-sm font-medium text-slate-700 focus:outline-none focus:border-amber-500 focus:ring-4 focus:ring-amber-500/15 appearance-none cursor-pointer">
                                            <i
                                                class="ph-bold ph-caret-down absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                                        </div>
                                        @error('tgl_kedaluwarsa') <span
                                        class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- {{ __('Last Refill') }} -->
                                    <div>
                                        <label
                                            class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('Last Refill') }}</label>
                                        <div class="relative">
                                            <i
                                                class="ph-bold ph-calendar-blank absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg z-10"></i>
                                            <input type="text" name="tgl_isi_ulang" placeholder="{{ __('Select Date...') }}"
                                                x-model="editApar.tgl_isi_ulang" @input.debounce.100ms="if($event.target.value) { 
                                                            let d = new Date($event.target.value); 
                                                            if(!isNaN(d)) { 
                                                                d.setFullYear(d.getFullYear() + 2); 
                                                                let next = d.toISOString().split('T')[0];
                                                                editApar.tgl_kedaluwarsa = next;
                                                                let input1 = $event.target.closest('form').querySelector('input[name=tgl_kedaluwarsa]');
                                                                if(input1 && input1._flatpickr) input1._flatpickr.setDate(next, false);
                                                            } 
                                                        }"
                                                class="datepicker w-full bg-white border-2 border-slate-200 rounded-xl py-2.5 pl-10 pr-10 text-sm font-medium text-slate-700 focus:outline-none focus:border-amber-500 focus:ring-4 focus:ring-amber-500/15 appearance-none cursor-pointer">
                                            <i
                                                class="ph-bold ph-caret-down absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                                        </div>
                                        @error('tgl_isi_ulang') <span
                                        class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            <div
                                class="bg-slate-50 px-6 py-4 sm:flex sm:flex-row-reverse sm:px-8 border-t border-slate-100 rounded-b-3xl">
                                <button type="submit"
                                    :disabled="JSON.stringify(editApar) === originalEditApar"
                                    :class="(JSON.stringify(editApar) === originalEditApar) ? 'opacity-50 cursor-not-allowed' : 'hover:bg-amber-600 shadow-amber-500/20'"
                                    class="inline-flex w-full justify-center rounded-xl bg-amber-50 px-5 py-2.5 text-sm font-bold text-amber-600 sm:ml-3 sm:w-auto transition-colors" style="background-color: rgb(245 158 11); color: white;">{{ __('Save Changes') }}</button>
                                <button type="button" @click="showModalEditApar = false"
                                    class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-5 py-2.5 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors">{{ __('Cancel') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 4: Resume -->
        <div x-show="activeTab === 'resume'" x-cloak x-transition:enter="transition ease-out duration-400"
            x-transition:enter-start="opacity-0 translate-y-6" x-transition:enter-end="opacity-100 translate-y-0"
            class="space-y-6" style="display: none;" x-cloak>

            <!-- Resume APAR Table Matrix Layout -->
            @if(isset($resumeColumns) && count($resumeColumns) > 0)
                <div class="bg-white rounded-2xl shadow-sm border border-[#009B77]/20 overflow-hidden">
                    <div class="px-5 py-3 border-b border-[#009B77]/10 flex items-center justify-center bg-[#009B77]/5">
                        <h2 class="text-base font-bold text-[#009B77] uppercase tracking-wider flex items-center gap-2">
                            <i class="ph-bold ph-table"></i>
                            {{ __('Resume Data APAR') }}
                        </h2>
                    </div>
                    <div class="overflow-x-auto p-4">
                        <table class="w-full text-left border-collapse border border-[#009B77]/20">
                            <thead>
                                <tr class="bg-[#009B77] text-white">
                                    <th rowspan="2"
                                        class="px-4 py-3 text-xs font-bold uppercase tracking-wider border border-[#007f60] text-center w-12">
                                        No</th>
                                    <th rowspan="2"
                                        class="px-4 py-3 text-xs font-bold uppercase tracking-wider border border-[#007f60] text-center whitespace-nowrap w-56">
                                        {{ __('Keterangan') }}
                                    </th>
                                    @foreach($resumeColumns as $jenis => $kapasitasList)
                                        <th colspan="{{ count($kapasitasList) }}"
                                            class="px-4 py-3 text-xs font-bold uppercase tracking-wider border border-[#007f60] text-center">
                                            {{ $jenis }}
                                        </th>
                                    @endforeach
                                    <th rowspan="2"
                                        class="px-4 py-3 text-xs font-bold uppercase tracking-wider border border-[#007f60] text-center w-24">
                                        {{ __('Total') }}
                                    </th>
                                </tr>
                                <tr class="bg-[#009B77] text-white">
                                    @foreach($resumeColumns as $jenis => $kapasitasList)
                                        @foreach($kapasitasList as $kapasitas)
                                            <th
                                                class="px-4 py-3 text-xs font-bold uppercase tracking-wider border border-[#007f60] text-center whitespace-nowrap">
                                                {{ $kapasitas }}
                                            </th>
                                        @endforeach
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @foreach($resumeTable as $index => $row)
                                    <tr class="hover:bg-[#009B77]/5 transition-colors">
                                        <td
                                            class="px-4 py-3 text-sm font-semibold text-slate-700 border border-slate-200 text-center">
                                            {{ $index + 1 }}
                                        </td>
                                        <td
                                            class="px-4 py-3 text-sm font-semibold text-slate-700 border border-slate-200 whitespace-nowrap">
                                            {{ __($row['name']) }}
                                        </td>
                                        @foreach($resumeColumns as $jenis => $kapasitasList)
                                            @foreach($kapasitasList as $kapasitas)
                                                <td class="px-4 py-3 text-sm text-slate-600 border border-slate-200 text-center">
                                                    {{ $row['data'][$jenis][$kapasitas] ?? 0 }}
                                                </td>
                                            @endforeach
                                        @endforeach
                                        <td
                                            class="px-4 py-3 text-sm font-bold text-[#009B77] border border-slate-200 text-center bg-[#009B77]/5">
                                            {{ $row['total'] }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-12 text-center">
                    <i class="ph-bold ph-empty text-6xl text-slate-300 mb-4 inline-block"></i>
                    <h3 class="text-lg font-bold text-slate-700">{{ __('Belum Ada Data') }}</h3>
                    <p class="text-slate-500 mt-2">{{ __('Data resume akan muncul otomatis setelah data APAR diinput.') }}</p>
                </div>
            @endif
        </div>

        <!-- Modal Edit Gedung -->
        <div x-show="showEditGedung" style="display: none;" class="relative z-[100]" aria-labelledby="modal-title"
            role="dialog" aria-modal="true" x-cloak>
            <div x-show="showEditGedung" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-slate-900/40 transition-opacity" x-cloak></div>
            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div x-show="showEditGedung" x-transition:enter="ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        class="relative transform overflow-visible rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-100"
                        x-cloak>
                        <form :action="'/master-data/gedung/' + editGedung.id" method="POST">
                            <input type="hidden" name="form_type" value="edit_gedung">
                            <input type="hidden" name="id" :value="editGedung.id">

                            @csrf
                            @method('PUT')
                            <div class="bg-white rounded-t-3xl px-6 pb-6 pt-6 sm:p-8">
                                <div class="flex items-center justify-between mb-6">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center">
                                            <i class="ph-bold ph-pencil-simple text-xl"></i>
                                        </div>
                                        <h3 class="text-lg font-bold leading-6 text-slate-800" id="modal-title">
                                            {{ __('Edit Building Data') }}
                                        </h3>
                                    </div>
                                    <button type="button" @click="showEditGedung = false"
                                        class="text-slate-400 hover:text-slate-500 transition-colors"><i
                                            class="ph-bold ph-x text-xl"></i></button>
                                </div>
                                <div class="space-y-5">
                                    <div>
                                        <label
                                            class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('Building Name') }}</label>
                                        <input type="text" name="nama" x-model="editGedung.nama" required
                                            class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 px-4 text-sm font-medium text-slate-700 focus:bg-white focus:border-amber-500 focus:ring-4 focus:ring-amber-500/15 transition-all outline-none">
                                        @if(old('form_type') == 'edit_gedung') @error('nama')
                                                <p class="text-xs text-red-500 font-medium mt-2"><i
                                            class="ph-bold ph-warning-circle mr-1"></i>{{ $message }}</p> @enderror
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div
                                class="bg-slate-50 px-6 py-4 sm:flex sm:flex-row-reverse sm:px-8 border-t border-slate-100 rounded-b-3xl">
                                <button type="submit"
                                    :disabled="JSON.stringify(editGedung) === originalEditGedung"
                                    :class="(JSON.stringify(editGedung) === originalEditGedung) ? 'opacity-50 cursor-not-allowed' : 'hover:bg-amber-600 shadow-amber-500/20'"
                                    class="inline-flex w-full justify-center rounded-xl bg-amber-50 px-5 py-2.5 text-sm font-bold text-amber-600 sm:ml-3 sm:w-auto transition-colors" style="background-color: rgb(245 158 11); color: white;">{{ __('Save Changes') }}</button>
                                <button type="button" @click="showEditGedung = false"
                                    class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-5 py-2.5 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors">{{ __('Cancel') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Edit Lokasi -->
        <div x-show="showEditLokasi" style="display: none;" class="relative z-[100]" aria-labelledby="modal-title"
            role="dialog" aria-modal="true" x-cloak>
            <div x-show="showEditLokasi" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-slate-900/40 transition-opacity" x-cloak></div>
            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div x-show="showEditLokasi" x-transition:enter="ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        class="relative transform overflow-visible rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-100"
                        x-cloak>
                        <form :action="'/master-data/lokasi/' + editLokasi.id" method="POST">
                            <input type="hidden" name="form_type" value="edit_lokasi">
                            <input type="hidden" name="id" :value="editLokasi.id">

                            @csrf
                            @method('PUT')
                            <div class="bg-white rounded-t-3xl px-6 pb-6 pt-6 sm:p-8">
                                <div class="flex items-center justify-between mb-6">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center">
                                            <i class="ph-bold ph-pencil-simple text-xl"></i>
                                        </div>
                                        <h3 class="text-lg font-bold leading-6 text-slate-800" id="modal-title">
                                            {{ __('Edit Location Data') }}
                                        </h3>
                                    </div>
                                    <button type="button" @click="showEditLokasi = false"
                                        class="text-slate-400 hover:text-slate-500 transition-colors"><i
                                            class="ph-bold ph-x text-xl"></i></button>
                                </div>
                                <div class="space-y-5">
                                    <div>
                                        <label
                                            class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('Location Name') }}</label>
                                        <input type="text" name="nama" x-model="editLokasi.nama" required
                                            class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 px-4 text-sm font-medium text-slate-700 focus:bg-white focus:border-amber-500 focus:ring-4 focus:ring-amber-500/15 transition-all outline-none">
                                        @if(old('form_type') == 'edit_lokasi') @error('nama')
                                                <p class="text-xs text-red-500 font-medium mt-2"><i
                                            class="ph-bold ph-warning-circle mr-1"></i>{{ $message }}</p> @enderror
                                        @endif
                                    </div>
                                    <div x-data="{ 
                                                    open: false, 
                                                    search: '',
                                                    options: [
                                                        @foreach(\App\Models\Gedung::all() as $ged)
                                                            { id: '{{ $ged->id }}', name: '{{ addslashes($ged->nama) }}' },
                                                        @endforeach
                                                    ],
                                                    get selectedName() {
                                                        let sel = this.options.find(o => o.id == editLokasi.gedung_id);
                                                        return sel ? sel.name : '{{ __('Select Building') }}';
                                                    },
                                                    get filteredOptions() {
                                                        if (this.search === '') return this.options;
                                                        return this.options.filter(o => o.name.toLowerCase().includes(this.search.toLowerCase()));
                                                    }
                                                }" class="relative">

                                        <label
                                            class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('Select Building') }}</label>

                                        <!-- Hidden input for form submission -->
                                        <input type="hidden" name="gedung_id" :value="editLokasi.gedung_id" required>

                                        <!-- Trigger -->
                                        <button type="button" @click="open = !open" @click.away="open = false"
                                            class="w-full bg-slate-50 border-2 rounded-xl py-2.5 px-4 text-sm font-medium text-left transition-all flex items-center justify-between"
                                            :class="open ? 'bg-white border-amber-500 ring-4 ring-amber-500/15 text-slate-800' : 'border-slate-200 text-slate-700 hover:border-slate-300'">
                                            <span x-text="selectedName"
                                                :class="editLokasi.gedung_id === '' ? 'text-slate-400' : ''"></span>
                                            <i class="ph-bold ph-caret-down text-slate-400 transition-transform duration-300"
                                                :class="open ? 'rotate-180 text-amber-500' : ''"></i>
                                        </button>

                                        <!-- Dropdown Menu -->
                                        <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200"
                                            x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                                            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                            x-transition:leave="transition ease-in duration-100"
                                            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                            x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                                            class="absolute z-50 w-full mt-2 bg-white rounded-xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.1)] border border-slate-100 py-2 max-h-60 overflow-y-auto origin-top">

                                            <div class="sticky top-0 bg-white p-2 border-b border-slate-100 z-10">
                                                <div class="relative">
                                                    <i
                                                        class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                                    <input type="text" x-model="search" placeholder="Cari gedung..."
                                                        class="w-full bg-slate-50 border border-slate-200 rounded-lg py-2 pl-9 pr-3 text-sm font-medium text-slate-700 focus:outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 transition-all"
                                                        @click.stop @keydown.enter.prevent>
                                                </div>
                                            </div>

                                            <div class="py-1">
                                                <template x-for="option in filteredOptions" :key="option.id">
                                                    <button type="button"
                                                        @click="editLokasi.gedung_id = option.id; open = false"
                                                        class="w-full text-left px-4 py-2.5 text-sm font-medium transition-colors flex items-center justify-between"
                                                        :class="editLokasi.gedung_id == option.id ? 'bg-amber-50 text-amber-600' : 'text-slate-600 hover:bg-slate-50'">
                                                        <span x-text="option.name" class="font-bold"></span>



                                                        <i class="ph-bold ph-check text-amber-600"
                                                            x-show="editLokasi.gedung_id == option.id" x-cloak></i>
                                                    </button>
                                                </template>

                                                <div x-show="filteredOptions.length === 0" x-cloak
                                                    class="px-4 py-3 text-sm text-slate-400 text-center italic">
                                                    {{ __('No results found') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="bg-slate-50 px-6 py-4 sm:flex sm:flex-row-reverse sm:px-8 border-t border-slate-100 rounded-b-3xl">
                                <button type="submit"
                                    :disabled="JSON.stringify(editLokasi) === originalEditLokasi"
                                    :class="(JSON.stringify(editLokasi) === originalEditLokasi) ? 'opacity-50 cursor-not-allowed' : 'hover:bg-amber-600 shadow-amber-500/20'"
                                    class="inline-flex w-full justify-center rounded-xl bg-amber-50 px-5 py-2.5 text-sm font-bold text-amber-600 sm:ml-3 sm:w-auto transition-colors" style="background-color: rgb(245 158 11); color: white;">{{ __('Save Changes') }}</button>
                                <button type="button" @click="showEditLokasi = false"
                                    class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-5 py-2.5 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors">{{ __('Cancel') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Edit Jenis -->
        <div x-show="showEditJenis" style="display: none;" class="relative z-[100]" aria-labelledby="modal-title"
            role="dialog" aria-modal="true" x-cloak>
            <div x-show="showEditJenis" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-slate-900/40 transition-opacity" x-cloak></div>
            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div x-show="showEditJenis" x-transition:enter="ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        class="relative transform overflow-visible rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-100"
                        x-cloak>
                        <form :action="'/master-data/jenis/' + editJenis.id" method="POST">
                            <input type="hidden" name="form_type" value="edit_jenis">
                            <input type="hidden" name="id" :value="editJenis.id">

                            @csrf
                            @method('PUT')
                            <div class="bg-white rounded-t-3xl px-6 pb-6 pt-6 sm:p-8">
                                <div class="flex items-center justify-between mb-6">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center">
                                            <i class="ph-bold ph-pencil-simple text-xl"></i>
                                        </div>
                                        <h3 class="text-lg font-bold leading-6 text-slate-800" id="modal-title">
                                            {{ __('Edit PFE Type') }}
                                        </h3>
                                    </div>
                                    <button type="button" @click="showEditJenis = false"
                                        class="text-slate-400 hover:text-slate-500 transition-colors"><i
                                            class="ph-bold ph-x text-xl"></i></button>
                                </div>
                                <div class="space-y-5">
                                    <div>
                                        <label
                                            class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('Type Name') }}</label>
                                        <input type="text" name="nama" x-model="editJenis.nama" required
                                            class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 px-4 text-sm font-medium text-slate-700 focus:bg-white focus:border-amber-500 focus:ring-4 focus:ring-amber-500/15 transition-all outline-none">
                                        @if(old('form_type') == 'edit_jenis') @error('nama')
                                                <p class="text-xs text-red-500 font-medium mt-2"><i
                                            class="ph-bold ph-warning-circle mr-1"></i>{{ $message }}</p> @enderror
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div
                                class="bg-slate-50 px-6 py-4 sm:flex sm:flex-row-reverse sm:px-8 border-t border-slate-100 rounded-b-3xl">
                                <button type="submit"
                                    :disabled="JSON.stringify(editJenis) === originalEditJenis"
                                    :class="(JSON.stringify(editJenis) === originalEditJenis) ? 'opacity-50 cursor-not-allowed' : 'hover:bg-amber-600 shadow-amber-500/20'"
                                    class="inline-flex w-full justify-center rounded-xl bg-amber-50 px-5 py-2.5 text-sm font-bold text-amber-600 sm:ml-3 sm:w-auto transition-colors" style="background-color: rgb(245 158 11); color: white;">{{ __('Save Changes') }}</button>
                                <button type="button" @click="showEditJenis = false"
                                    class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-5 py-2.5 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors">{{ __('Cancel') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Edit Kapasitas -->
        <div x-show="showEditKapasitas" style="display: none;" class="relative z-[100]" aria-labelledby="modal-title"
            role="dialog" aria-modal="true" x-cloak>
            <div x-show="showEditKapasitas" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-slate-900/40 transition-opacity" x-cloak></div>
            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div x-show="showEditKapasitas" x-transition:enter="ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        class="relative transform overflow-visible rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-100"
                        x-cloak>
                        <form :action="'/master-data/kapasitas/' + editKapasitas.id" method="POST">
                            <input type="hidden" name="form_type" value="edit_kapasitas">
                            <input type="hidden" name="id" :value="editKapasitas.id">

                            @csrf
                            @method('PUT')
                            <div class="bg-white rounded-t-3xl px-6 pb-6 pt-6 sm:p-8">
                                <div class="flex items-center justify-between mb-6">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center">
                                            <i class="ph-bold ph-pencil-simple text-xl"></i>
                                        </div>
                                        <h3 class="text-lg font-bold leading-6 text-slate-800" id="modal-title">
                                            {{ __('Edit Capacity') }}
                                        </h3>
                                    </div>
                                    <button type="button" @click="showEditKapasitas = false"
                                        class="text-slate-400 hover:text-slate-500 transition-colors"><i
                                            class="ph-bold ph-x text-xl"></i></button>
                                </div>
                                <div class="space-y-5">
                                    <div>
                                        <label
                                            class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('Capacity') }}</label>
                                        <div class="relative flex items-center">
                                            <!-- Remove ' Kg' from the model value for display, but keep the input name as ukuran. 
                                                         We use a computed property or just x-effect to strip Kg when modal opens, but easier is just allowing Alpine to display the raw value, 
                                                         wait, editKapasitas.ukuran already has " Kg" in it from the backend. 
                                                         Let's strip it using x-bind:value and x-on:input, or just let the backend handle the 'Kg' part. 
                                                         Actually, if we just set x-model="editKapasitas.ukuran_num" and populate it on click. -->
                                            <input type="number" step="any" name="ukuran"
                                                :value="editKapasitas.ukuran.replace(/[^0-9.]/g, '')"
                                                @keydown="['e', 'E', '+', '-'].includes($event.key) && $event.preventDefault()"
                                                required
                                                class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 pl-4 pr-12 text-sm font-medium text-slate-700 focus:bg-white focus:border-amber-500 focus:ring-4 focus:ring-amber-500/15 transition-all outline-none">
                                            <span class="absolute right-4 font-bold text-slate-400">Kg</span>
                                        </div>
                                        @if(old('form_type') == 'edit_kapasitas') @error('ukuran')
                                                <p class="text-xs text-red-500 font-medium mt-2"><i
                                            class="ph-bold ph-warning-circle mr-1"></i>{{ $message }}</p> @enderror
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div
                                class="bg-slate-50 px-6 py-4 sm:flex sm:flex-row-reverse sm:px-8 border-t border-slate-100 rounded-b-3xl">
                                <button type="submit"
                                    :disabled="JSON.stringify(editKapasitas) === originalEditKapasitas"
                                    :class="(JSON.stringify(editKapasitas) === originalEditKapasitas) ? 'opacity-50 cursor-not-allowed' : 'hover:bg-amber-600 shadow-amber-500/20'"
                                    class="inline-flex w-full justify-center rounded-xl bg-amber-50 px-5 py-2.5 text-sm font-bold text-amber-600 sm:ml-3 sm:w-auto transition-colors" style="background-color: rgb(245 158 11); color: white;">{{ __('Save Changes') }}</button>
                                <button type="button" @click="showEditKapasitas = false"
                                    class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-5 py-2.5 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors">{{ __('Cancel') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal QR Code -->
        <div x-show="showQrModal" style="display: none;" class="relative z-[100]" aria-labelledby="modal-title"
            role="dialog" aria-modal="true" x-cloak>
            <div x-show="showQrModal" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-slate-900/40 transition-opacity" x-cloak></div>
            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                    <div x-show="showQrModal" x-transition:enter="ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:w-full sm:max-w-sm border border-slate-100"
                        x-cloak>

                        <div class="bg-gradient-to-br from-[#009B77] to-[#007b5e] p-6 text-center relative overflow-hidden">
                            <div class="absolute -right-6 -top-6 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
                            <div class="absolute -left-6 -bottom-6 w-24 h-24 bg-white/10 rounded-full blur-xl"></div>
                            <h3 class="text-xl font-black text-white mb-1 relative z-10">Scan APAR</h3>
                            <p class="text-white/80 text-sm font-medium relative z-10" x-text="qrData.kode"></p>
                        </div>

                        <div class="p-8 flex flex-col items-center justify-center bg-white relative">
                            <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 mb-6"
                                x-html="qrData.svg">
                                <!-- SVG QR goes here -->
                            </div>
                            <p class="text-sm font-semibold text-slate-500 text-center mb-2">Gunakan kamera ponsel Anda
                                untuk menscan QR Code ini.</p>

                            <div class="w-full grid grid-cols-3 gap-2 mt-4">
                                <button @click="showQrModal = false"
                                    class="py-2.5 rounded-xl bg-slate-100 text-slate-600 font-bold hover:bg-slate-200 transition-colors text-xs flex items-center justify-center">{{ __('Close') }}</button>
                                <a :href="'/master-data/apar/' + qrData.id + '/download-qr'" target="_blank"
                                    class="py-2.5 rounded-xl bg-white border border-slate-200 text-slate-700 font-bold hover:bg-slate-50 transition-colors text-xs flex items-center justify-center gap-1.5 shadow-sm">
                                    <i class="ph-bold ph-download-simple text-sm text-[#009B77]"></i> Simpan
                                </a>
                                <a :href="'/master-data/apar/' + qrData.id + '/print-qr'" target="_blank"
                                    class="py-2.5 rounded-xl bg-[#009B77] text-white font-bold hover:bg-[#008264] transition-colors text-xs flex items-center justify-center gap-1.5 shadow-sm shadow-[#009B77]/20">
                                    <i class="ph-bold ph-printer text-sm"></i> Cetak
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal History -->
    <div x-data="{ 
                    show: false, 
                    loading: false, 
                    htmlContent: '', 
                    aparKode: '',
                    openModal(e) { 
                        this.show = true; 
                        this.loading = true;
                        this.aparKode = e.detail.kode;
                        this.htmlContent = '';
                        fetch('/master-data/apar/' + e.detail.id + '/history')
                            .then(res => res.text())
                            .then(html => {
                                this.htmlContent = html;
                                this.loading = false;
                            });
                    }
                }" @open-history.window="openModal($event)" x-show="show" class="fixed inset-0 z-[100] overflow-y-auto"
        x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div x-show="show" class="fixed inset-0 bg-slate-900/40" @click="show = false"></div>

            <div x-show="show"
                class="relative inline-block w-full max-w-4xl p-6 overflow-hidden text-left align-middle bg-white shadow-2xl rounded-3xl sm:my-8 border border-slate-100">

                <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-500 flex items-center justify-center border border-indigo-100">
                            <i class="ph-bold ph-clock-counter-clockwise text-2xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-slate-800 tracking-tight">{{ __('Data Change History') }}
                            </h3>
                            <p class="text-sm font-semibold text-slate-500 mt-0.5">{{ __('Activity timeline for PFE') }}
                                <span class="text-indigo-600 font-bold" x-text="aparKode"></span>
                            </p>
                        </div>
                    </div>
                    <button @click="show = false"
                        class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-xl">
                        <i class="ph-bold ph-x text-lg"></i>
                    </button>
                </div>

                <div x-show="loading" class="py-16 flex flex-col justify-center items-center gap-3">
                    <p class="text-sm font-bold text-slate-500">{{ __('Loading data history...') }}</p>
                </div>

                <div x-show="!loading" x-html="htmlContent" class="max-h-[60vh] overflow-y-auto pr-2 custom-scrollbar">
                </div>

                <div class="mt-6 flex justify-end pt-5 border-t border-slate-100">
                    <button @click="show = false"
                        class="px-6 py-2.5 text-sm font-bold text-slate-600 bg-white border-2 border-slate-200/60 hover:bg-slate-50 hover:border-slate-300 hover:text-slate-900 rounded-xl shadow-sm">
                        {{ __('Close') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Cadangan (Add/Edit) -->
    <div x-data="{ 
                    show: {{ (old('form_type') == 'tambah_cadangan' || old('form_type') == 'edit_cadangan') && $errors->any() ? 'true' : 'false' }}, 
                    formType: '{{ old('form_type') ?: '' }}', 
                    cadanganAction: '{{ old('cadangan_action') ?: '' }}',
                    cadanganData: {
                        gedung_id: '{{ old('form_type') == 'tambah_cadangan' || old('form_type') == 'edit_cadangan' ? old('gedung_id') : '' }}',
                        jenis_id: '{{ old('form_type') == 'tambah_cadangan' || old('form_type') == 'edit_cadangan' ? old('jenis_id') : '' }}',
                        kapasitas_id: '{{ old('form_type') == 'tambah_cadangan' || old('form_type') == 'edit_cadangan' ? old('kapasitas_id') : '' }}',
                        total: '{{ old('form_type') == 'tambah_cadangan' || old('form_type') == 'edit_cadangan' ? old('total') : '' }}'
                    },
                    originalCadanganData: ''
                 }" @open-cadangan.window="
                    show = true; 
                    formType = $event.detail.type; 
                    cadanganAction = $event.detail.action;
                    if ($event.detail.type === 'edit_cadangan') {
                        cadanganData.gedung_id = $event.detail.gedung;
                        cadanganData.jenis_id = $event.detail.jenis;
                        cadanganData.kapasitas_id = $event.detail.kapasitas;
                        cadanganData.total = $event.detail.total;
                        originalCadanganData = JSON.stringify(cadanganData);
                    } else {
                        cadanganData.gedung_id = '';
                        cadanganData.jenis_id = '';
                        cadanganData.kapasitas_id = '';
                        cadanganData.total = '';
                    }
                 " x-show="show" style="display: none;" class="relative z-[100]" aria-labelledby="modal-title"
        role="dialog" aria-modal="true" x-cloak>
        <!-- Background overlay -->
        <div x-show="show" class="fixed inset-0 bg-slate-900/40 transition-opacity" x-cloak></div>

        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <!-- Modal panel -->
                <div x-show="show"
                    class="relative transform overflow-visible rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-slate-100"
                    x-cloak>

                    <div class="px-6 pb-6 pt-6 sm:p-8 border-b border-slate-100 flex items-center justify-between bg-white rounded-t-3xl">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-[#009B77]/10 text-[#009B77] flex items-center justify-center">
                                <i class="ph-bold text-xl" :class="formType === 'edit_cadangan' ? 'ph-pencil-simple' : 'ph-plus'"></i>
                            </div>
                            <h3 class="text-xl font-bold leading-6 text-slate-800"
                                x-text="formType === 'edit_cadangan' ? '{{ __('Edit Spare PFE') }}' : '{{ __('Add Spare PFE') }}'">
                            </h3>
                        </div>
                        <button type="button" @click="show = false"
                            class="text-slate-400 hover:text-slate-500 transition-colors">
                            <i class="ph-bold ph-x text-xl"></i>
                        </button>
                    </div>

                    <form :action="cadanganAction" method="POST" id="edit-cadangan-form">
                        @csrf
                        <input type="hidden" name="_method" value="PUT" x-bind:disabled="formType !== 'edit_cadangan'">
                        <input type="hidden" name="form_type" :value="formType">
                        <input type="hidden" name="cadangan_action" :value="cadanganAction">
                        <div class="p-6 space-y-5">

                            <div>
                                <div x-data="{ 
                                                open: false, 
                                                search: '',
                                                options: [
                                                    @foreach($gedungs as $gedung)
                                                        { id: '{{ $gedung->id }}', name: '{{ addslashes($gedung->nama) }}' },
                                                    @endforeach
                                                ],
                                                get selectedName() {
                                                    let sel = this.options.find(o => o.id == cadanganData.gedung_id);
                                                    return sel ? sel.name : '{{ __('Select Building') }}';
                                                },
                                                get filteredOptions() {
                                                    if (this.search === '') return this.options;
                                                    return this.options.filter(o => o.name.toLowerCase().includes(this.search.toLowerCase()));
                                                }
                                            }" class="relative">

                                    <label
                                        class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('Building') }}
                                        <span class="text-red-500">*</span></label>

                                    <!-- Hidden input for form submission -->
                                    <input type="hidden" name="gedung_id" :value="cadanganData.gedung_id" required>

                                    <!-- Trigger -->
                                    <button type="button" @click="open = !open" @click.away="open = false"
                                        class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 px-4 text-sm font-medium text-left transition-all flex items-center justify-between"
                                        :class="open ? 'bg-white border-[#009B77] ring-4 ring-[#009B77]/15 text-slate-800' : 'text-slate-700 hover:border-slate-300'">
                                        <span x-text="selectedName"
                                            :class="cadanganData.gedung_id === '' ? 'text-slate-400' : ''"></span>
                                        <i class="ph-bold ph-caret-down text-slate-400 transition-transform duration-300"
                                            :class="open ? 'rotate-180 text-[#009B77]' : ''"></i>
                                    </button>

                                    <!-- Dropdown Menu -->
                                    <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                                        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                        x-transition:leave="transition ease-in duration-100"
                                        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                        x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                                        class="absolute z-50 w-full mt-2 bg-white rounded-xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.1)] border border-slate-100 py-2 max-h-60 overflow-y-auto origin-top">

                                        <div class="sticky top-0 bg-white p-2 border-b border-slate-100 z-10">
                                            <div class="relative">
                                                <i
                                                    class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                                <input type="text" x-model="search" placeholder="Search building..."
                                                    class="w-full bg-slate-50 border border-slate-200 rounded-lg py-2 pl-9 pr-3 text-sm font-medium text-slate-700 focus:outline-none focus:border-[#009B77] focus:ring-2 focus:ring-[#009B77]/20 transition-all"
                                                    @click.stop @keydown.enter.prevent>
                                            </div>
                                        </div>

                                        <div class="py-1">
                                            <template x-for="option in filteredOptions" :key="option.id">
                                                <button type="button"
                                                    @click="cadanganData.gedung_id = option.id; open = false"
                                                    class="w-full text-left px-4 py-2.5 text-sm font-medium transition-colors flex items-center justify-between"
                                                    :class="cadanganData.gedung_id == option.id ? 'bg-[#009B77]/10 text-[#009B77]' : 'text-slate-600 hover:bg-slate-50'">
                                                    <span x-text="option.name" class="font-bold"></span>
                                                    <i class="ph-bold ph-check text-[#009B77]"
                                                        x-show="cadanganData.gedung_id == option.id" x-cloak></i>
                                                </button>
                                            </template>
                                            <div x-show="filteredOptions.length === 0"
                                                class="py-3 px-4 text-center text-sm font-medium text-slate-500">
                                                {{ __('No results found') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @error('gedung_id')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <div x-data="{ 
                                                open: false, 
                                                search: '',
                                                options: [
                                                    @foreach($jenisApars as $jenis)
                                                        { id: '{{ $jenis->id }}', name: '{{ addslashes($jenis->nama) }}' },
                                                    @endforeach
                                                ],
                                                get selectedName() {
                                                    let sel = this.options.find(o => o.id == cadanganData.jenis_id);
                                                    return sel ? sel.name : '{{ __('Select Type') }}';
                                                },
                                                get filteredOptions() {
                                                    if (this.search === '') return this.options;
                                                    return this.options.filter(o => o.name.toLowerCase().includes(this.search.toLowerCase()));
                                                }
                                            }" class="relative">

                                    <label
                                        class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('Type') }}
                                        <span class="text-red-500">*</span></label>

                                    <!-- Hidden input for form submission -->
                                    <input type="hidden" name="jenis_id" :value="cadanganData.jenis_id" required>

                                    <!-- Trigger -->
                                    <button type="button" @click="open = !open" @click.away="open = false"
                                        class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 px-4 text-sm font-medium text-left transition-all flex items-center justify-between"
                                        :class="open ? 'bg-white border-[#009B77] ring-4 ring-[#009B77]/15 text-slate-800' : 'text-slate-700 hover:border-slate-300'">
                                        <span x-text="selectedName"
                                            :class="cadanganData.jenis_id === '' ? 'text-slate-400' : ''"></span>
                                        <i class="ph-bold ph-caret-down text-slate-400 transition-transform duration-300"
                                            :class="open ? 'rotate-180 text-[#009B77]' : ''"></i>
                                    </button>

                                    <!-- Dropdown Menu -->
                                    <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                                        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                        x-transition:leave="transition ease-in duration-100"
                                        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                        x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                                        class="absolute z-50 w-full mt-2 bg-white rounded-xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.1)] border border-slate-100 py-2 max-h-60 overflow-y-auto origin-top">

                                        <div class="sticky top-0 bg-white p-2 border-b border-slate-100 z-10">
                                            <div class="relative">
                                                <i
                                                    class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                                <input type="text" x-model="search" placeholder="Search type..."
                                                    class="w-full bg-slate-50 border border-slate-200 rounded-lg py-2 pl-9 pr-3 text-sm font-medium text-slate-700 focus:outline-none focus:border-[#009B77] focus:ring-2 focus:ring-[#009B77]/20 transition-all"
                                                    @click.stop @keydown.enter.prevent>
                                            </div>
                                        </div>

                                        <div class="py-1">
                                            <template x-for="option in filteredOptions" :key="option.id">
                                                <button type="button"
                                                    @click="cadanganData.jenis_id = option.id; open = false"
                                                    class="w-full text-left px-4 py-2.5 text-sm font-medium transition-colors flex items-center justify-between"
                                                    :class="cadanganData.jenis_id == option.id ? 'bg-[#009B77]/10 text-[#009B77]' : 'text-slate-600 hover:bg-slate-50'">
                                                    <span x-text="option.name" class="font-bold"></span>
                                                    <i class="ph-bold ph-check text-[#009B77]"
                                                        x-show="cadanganData.jenis_id == option.id" x-cloak></i>
                                                </button>
                                            </template>
                                            <div x-show="filteredOptions.length === 0"
                                                class="py-3 px-4 text-center text-sm font-medium text-slate-500">
                                                {{ __('No results found') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @error('jenis_id')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <div x-data="{ 
                                                open: false, 
                                                search: '',
                                                options: [
                                                    @foreach($kapasitasApars as $kapasitas)
                                                        { id: '{{ $kapasitas->id }}', name: '{{ addslashes($kapasitas->ukuran) }}' },
                                                    @endforeach
                                                ],
                                                get selectedName() {
                                                    let sel = this.options.find(o => o.id == cadanganData.kapasitas_id);
                                                    return sel ? sel.name : '{{ __('Select Capacity') }}';
                                                },
                                                get filteredOptions() {
                                                    if (this.search === '') return this.options;
                                                    return this.options.filter(o => o.name.toLowerCase().includes(this.search.toLowerCase()));
                                                }
                                            }" class="relative">

                                    <label
                                        class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('Capacity') }}
                                        <span class="text-red-500">*</span></label>

                                    <!-- Hidden input for form submission -->
                                    <input type="hidden" name="kapasitas_id" :value="cadanganData.kapasitas_id" required>

                                    <!-- Trigger -->
                                    <button type="button" @click="open = !open" @click.away="open = false"
                                        class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 px-4 text-sm font-medium text-left transition-all flex items-center justify-between"
                                        :class="open ? 'bg-white border-[#009B77] ring-4 ring-[#009B77]/15 text-slate-800' : 'text-slate-700 hover:border-slate-300'">
                                        <span x-text="selectedName"
                                            :class="cadanganData.kapasitas_id === '' ? 'text-slate-400' : ''"></span>
                                        <i class="ph-bold ph-caret-down text-slate-400 transition-transform duration-300"
                                            :class="open ? 'rotate-180 text-[#009B77]' : ''"></i>
                                    </button>

                                    <!-- Dropdown Menu -->
                                    <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                                        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                        x-transition:leave="transition ease-in duration-100"
                                        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                        x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                                        class="absolute z-50 w-full mt-2 bg-white rounded-xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.1)] border border-slate-100 py-2 max-h-60 overflow-y-auto origin-top">

                                        <div class="sticky top-0 bg-white p-2 border-b border-slate-100 z-10">
                                            <div class="relative">
                                                <i
                                                    class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                                <input type="text" x-model="search" placeholder="Search capacity..."
                                                    class="w-full bg-slate-50 border border-slate-200 rounded-lg py-2 pl-9 pr-3 text-sm font-medium text-slate-700 focus:outline-none focus:border-[#009B77] focus:ring-2 focus:ring-[#009B77]/20 transition-all"
                                                    @click.stop @keydown.enter.prevent>
                                            </div>
                                        </div>

                                        <div class="py-1">
                                            <template x-for="option in filteredOptions" :key="option.id">
                                                <button type="button"
                                                    @click="cadanganData.kapasitas_id = option.id; open = false"
                                                    class="w-full text-left px-4 py-2.5 text-sm font-medium transition-colors flex items-center justify-between"
                                                    :class="cadanganData.kapasitas_id == option.id ? 'bg-[#009B77]/10 text-[#009B77]' : 'text-slate-600 hover:bg-slate-50'">
                                                    <span x-text="option.name" class="font-bold"></span>
                                                    <i class="ph-bold ph-check text-[#009B77]"
                                                        x-show="cadanganData.kapasitas_id == option.id" x-cloak></i>
                                                </button>
                                            </template>
                                            <div x-show="filteredOptions.length === 0"
                                                class="py-3 px-4 text-center text-sm font-medium text-slate-500">
                                                {{ __('No results found') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @error('kapasitas_id')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('Stock Quantity') }}
                                    <span class="text-red-500">*</span></label>
                                <input type="number" name="total" min="0" required x-model="cadanganData.total"
                                    onkeydown="if(['e', 'E', '+', '-', '.'].includes(event.key)) event.preventDefault();"
                                    class="w-full bg-white border-2 border-slate-200 rounded-xl py-2.5 px-4 text-sm font-medium text-slate-700 focus:outline-none focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all">
                                @error('total')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                        </div>
                        <div
                            class="bg-slate-50 px-6 py-4 sm:flex sm:flex-row-reverse sm:px-8 border-t border-slate-100 rounded-b-3xl">
                            <button type="submit"
                                :disabled="formType === 'edit_cadangan' && JSON.stringify(cadanganData) === originalCadanganData"
                                :class="(formType === 'edit_cadangan' && JSON.stringify(cadanganData) === originalCadanganData) ? 'opacity-50 cursor-not-allowed' : 'hover:bg-[#007b5e] shadow-[#009B77]/20 shadow-sm'"
                                class="inline-flex w-full justify-center rounded-xl bg-[#009B77] px-5 py-2.5 text-sm font-bold text-white sm:ml-3 sm:w-auto transition-colors">
                                {{ __('Save Changes') }}
                            </button>
                            <button type="button" @click="show = false"
                                class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-5 py-2.5 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors">
                                {{ __('Cancel') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <x-excel-preview-modal />

    <!-- Seamless AJAX Form Submission for Master Data -->
    <script>
        // Override confirmDelete to dispatch an event instead of calling form.submit() directly
        // so our AJAX interceptor can catch it.
        window.confirmDelete = function (event, message) {
            event.preventDefault();
            const form = event.target || event.currentTarget;
            if (form.dataset.confirmed === 'true') return;

            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: message || "Data ini akan dihapus secara permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                customClass: {
                    popup: 'rounded-3xl shadow-[0_10px_40px_rgba(0,0,0,0.1)] border border-slate-100',
                    title: 'text-slate-800 font-bold',
                    htmlContainer: 'text-slate-500 font-semibold',
                    confirmButton: 'rounded-xl font-bold px-6 py-2.5 transition-transform hover:-translate-y-0.5',
                    cancelButton: 'rounded-xl font-bold px-6 py-2.5 transition-transform hover:-translate-y-0.5'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Dispatch a submit event that can be caught by our listener
                    const submitEvent = new Event('submit', { cancelable: true, bubbles: true });
                    // Flag it so we know it's already confirmed
                    form.dataset.confirmed = 'true';
                    form.dispatchEvent(submitEvent);
                }
            });
        };

        document.addEventListener('submit', async function (e) {
            const form = e.target;

            // Pengecualian untuk form logout (biarkan berjalan normal)
            if (form.action && form.action.includes('/logout')) {
                return;
            }

            // Cek apakah ini form pencarian/filter (GET) atau form biasa
            if (form.tagName === 'FORM' && form.method.toUpperCase() === 'POST') {
                // Jika form membutuhkan konfirmasi (onsubmit="confirmDelete...") tapi belum dikonfirmasi
                if (form.hasAttribute('onsubmit') && form.getAttribute('onsubmit').includes('confirmDelete') && form.dataset.confirmed !== 'true') {
                    // Biarkan confirmDelete yang menangani
                    return;
                }

                e.preventDefault();

                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn ? submitBtn.innerHTML : '';
                if (submitBtn) {
                    if (submitBtn.classList.contains('w-8') || submitBtn.classList.contains('w-10')) {
                        submitBtn.innerHTML = '<i class="ph-bold ph-spinner animate-spin"></i>';
                    } else {
                        submitBtn.innerHTML = '<i class="ph-bold ph-spinner animate-spin mr-2"></i> {{ __("Proses...") }}';
                    }
                    submitBtn.disabled = true;
                }

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: new FormData(form),
                        headers: {
                            'Accept': 'text/html' // Kita minta HTML kembali (karena Laravel akan redirect back())
                        }
                    });

                    if (response.ok) {
                        const html = await response.text();
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');

                        // Simpan posisi scroll dari container yang bisa di-scroll (karena window tidak di-scroll)
                        const currentMain = document.querySelector('main');
                        const scrollContainer = currentMain ? currentMain.querySelector('.overflow-y-auto') : null;
                        const scrollTop = scrollContainer ? scrollContainer.scrollTop : window.scrollY;

                        // KUNCI TINGGI ELEMEN MAIN KARENA DIA TIDAK DI-REPLACE SECARA UTUH (HANYA INNERHTML-NYA)
                        if (currentMain) {
                            currentMain.style.minHeight = currentMain.clientHeight + 'px';
                        }

                        // 1. Ganti konten utama (main)
                        const newMain = doc.querySelector('main');
                        if (currentMain && newMain) {
                            currentMain.innerHTML = newMain.innerHTML;
                        }

                        // Kembalikan posisi scroll seketika pada container BARU
                        const newScrollContainer = document.querySelector('main .overflow-y-auto');
                        if (newScrollContainer) {
                            newScrollContainer.scrollTop = scrollTop;
                        } else {
                            window.scrollTo(0, scrollTop);
                        }

                        // Kembalikan posisi scroll setelah Alpine merender modal tertutup dan lepaskan kuncian tinggi MAIN
                        setTimeout(() => {
                            const finalContainer = document.querySelector('main .overflow-y-auto');
                            if (finalContainer) {
                                finalContainer.scrollTop = scrollTop;
                            } else {
                                window.scrollTo(0, scrollTop);
                            }

                            if (currentMain) {
                                currentMain.style.minHeight = '';
                            }
                        }, 50);

                        // 2. Tampilkan notifikasi toast jika ada
                        const newToast = doc.querySelector('[x-data*="type: \'success\'"], [x-data*="type: \'error\'"]');
                        if (newToast) {
                            // Hapus toast lama jika ada
                            const oldToast = document.querySelector('[x-data*="type: \'success\'"], [x-data*="type: \'error\'"]');
                            if (oldToast) oldToast.remove();

                            document.body.appendChild(newToast);
                            // Eksekusi skrip Alpine yang mungkin ada di dalam elemen baru
                            if (window.Alpine) {
                                window.Alpine.initTree(newToast);
                            }
                        }

                        // Re-inisialisasi flatpickr jika perlu
                        if (typeof flatpickr !== 'undefined' && document.querySelector('.datepicker')) {
                            flatpickr(".datepicker", { dateFormat: "Y-m-d", allowInput: true, static: true });
                        }
                    } else {
                        throw new Error('Network response was not ok');
                    }
                } catch (error) {
                    console.error('AJAX form submission failed:', error);
                    // Fallback normal
                    form.submit();
                } finally {
                    if (submitBtn) {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }
                    if (form) {
                        form.dataset.confirmed = 'false';
                    }
                }
            }
        });
    </script>
@endsection