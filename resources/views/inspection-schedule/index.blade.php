@extends('layouts.app')

@section('title', 'Jadwal Inspeksi - PFE Monitoring Control System')

@section('content')
<!-- We inject pertanyaan into a JS variable so Alpine can reference it -->
<script>
    window.pertanyaanApar = @json($pertanyaan);
    window.jadwalsData = @json($jadwals->map(function($j) { return ['tanggal' => \Carbon\Carbon::parse($j->tanggal_inspeksi)->format('Y-m-d'), 'status' => $j->status, 'area_id' => $j->tipe_area == 'gedung' ? 'g_'.$j->gedung_id : 'l_'.$j->lokasi_id]; })->groupBy('tanggal'));
    window.statusTranslations = {
        'sesuai': '{{ __('Sesuai') }}',
        'tidak_sesuai': '{{ __('Tidak Sesuai') }}',
        'perlu_refill': '{{ __('Perlu Refill') }}',
        'layak': '{{ __('Sesuai') }}',
        'perbaikan': '{{ __('Tidak Sesuai') }}',
        'tidak_layak': '{{ __('Tidak Sesuai') }}',
        'isi_ulang': '{{ __('Perlu Refill') }}',
        'rusak': '{{ __('Tidak Sesuai') }}'
    };
</script>

<style>
    /* Prevent the entire page from scrolling, let the right column handle it */
    main { overflow: hidden !important; }
</style>

<div x-data="{ showPanelHasil: false, showPanelDetail: false, showModalBuatJadwal: false, showModalEditJadwal: false, editData: null, originalEditData: null, selectedInspeksi: null }">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200/60 flex-shrink-0">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-[#009B77]/10 border border-[#009B77]/20 shadow-sm flex items-center justify-center text-[#009B77]">
                <i class="ph-bold ph-calendar-check text-xl"></i>
            </div>
            <div>
                <h2 class="text-base font-bold text-[#007A5E] leading-tight">{{ __('Inspection Schedule') }}</h2>
                <p class="text-xs font-semibold text-slate-500 mt-0.5">{{ __('Manage and monitor PFE checking routines') }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            @if(auth()->user()->role !== 'Staff')
            <button @click="showModalBuatJadwal = true" class="btn-smooth-ring bg-[#009B77] hover:bg-[#008264] text-white font-bold py-2.5 px-5 rounded-xl shadow-[0_4px_12px_rgba(0,155,119,0.25)] transition-all flex items-center gap-2 text-sm hover:-translate-y-0.5">
                <i class="ph-bold ph-calendar-plus text-lg"></i>
                <span class="hidden sm:inline">{{ __('Create Schedule') }}</span>
            </button>
            @endif
            
            <div x-data="{ openExport: false }" class="relative z-40">
                <button @click="openExport = !openExport" @click.away="openExport = false" class="btn-smooth-ring bg-white border border-slate-200/60 text-slate-600 hover:text-slate-900 hover:border-slate-300 hover:bg-slate-50 font-bold py-2.5 px-4 rounded-xl shadow-sm transition-all flex items-center gap-2 text-sm hover:-translate-y-0.5">
                    <i class="ph-bold ph-printer text-lg"></i>
                    <span class="hidden sm:inline">{{ __('Export Checklist') }}</span>
                    <i class="ph-bold ph-caret-down text-sm text-slate-400 ml-1 transition-transform" :class="openExport ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="openExport" 
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="transform opacity-0 scale-95"
                     x-transition:enter-end="transform opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="transform opacity-100 scale-100"
                     x-transition:leave-end="transform opacity-0 scale-95"
                     class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg shadow-slate-200/50 border border-slate-100 py-2 max-h-60 overflow-y-auto" x-cloak style="display: none;">
                    
                    <div class="px-4 py-2 text-xs font-bold text-slate-400 uppercase tracking-wider bg-slate-50/50">
                        {{ __('Select Building') }}
                    </div>
                    
                    @foreach($gedungs as $gedung)
                    <a href="{{ route('inspection-schedule.export-checklist', ['gedung_id' => $gedung->id]) }}" target="_blank" class="px-4 py-2 text-sm font-medium text-slate-600 hover:bg-teal-50 hover:text-teal-600 flex items-center gap-2 transition-colors">
                        <i class="ph-fill ph-buildings text-lg text-teal-500"></i> <span class="truncate">{{ $gedung->nama }}</span>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:h-[calc(100vh-230px)]">
        
        <!-- Left Column: Calendar & Filters -->
        <div class="space-y-6 h-full flex flex-col flex-shrink-0">
            <!-- Stats Mini -->
            <div class="bg-[#009B77] rounded-2xl p-5 text-white shadow-[0_8px_30px_rgba(0,155,119,0.2)] relative overflow-hidden flex-shrink-0">
                <div class="absolute -right-10 -top-10 w-32 h-32 rounded-full border-4 border-white/10"></div>
                <div class="absolute -right-4 -top-4 w-16 h-16 rounded-full bg-white/10 blur-xl"></div>
                
                <h3 class="text-sm font-bold text-white/80 uppercase tracking-wider mb-1 relative z-10">{{ __('Total Inspections') }} {{ __('Month') }} {{ strtoupper(\Carbon\Carbon::create()->month($currentMonth)->translatedFormat('F')) }}</h3>
                <h2 class="text-4xl font-extrabold text-white mb-4 relative z-10">{{ $totalApar }}</h2>
                <div class="flex flex-col gap-2 relative z-10">
                    <div class="flex justify-between items-center text-sm">
                        <span class="font-semibold text-white/90">{{ __('Completed') }}</span>
                        <span class="font-bold">{{ $selesai }}</span>
                    </div>
                    <div class="w-full bg-white/20 rounded-full h-1.5">
                        <div class="bg-white h-1.5 rounded-full" style="width: {{ $totalApar > 0 ? ($selesai/$totalApar)*100 : 0 }}%"></div>
                    </div>
                    <div class="flex justify-between items-center text-sm mt-1">
                        <span class="font-semibold text-white/90">{{ __('Waiting') }}</span>
                        <span class="font-bold">{{ $menunggu }}</span>
                    </div>
                </div>
            </div>

            <!-- Calendar Widget -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-5 flex-1 min-h-[250px] flex flex-col"
                 x-data="{
                    currentDate: new Date({{ $currentYear }}, {{ $currentMonth - 1 }}, 1),
                    jadwals: window.jadwalsData,
                    get currentMonthName() {
                        return this.currentDate.toLocaleString('id-ID', { month: 'long', year: 'numeric' });
                    },
                    get daysInMonth() {
                        let year = this.currentDate.getFullYear();
                        let month = this.currentDate.getMonth();
                        let date = new Date(year, month, 1);
                        let days = [];
                        let firstDay = date.getDay();
                        for(let i=0; i<firstDay; i++) {
                            days.push({ empty: true });
                        }
                        while(date.getMonth() === month) {
                            let d = new Date(date);
                            let dateString = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
                            let j = this.jadwals[dateString] || [];
                            let hasJadwal = j.length > 0;
                            let allSelesai = hasJadwal && j.every(x => x.status?.toLowerCase() === 'selesai');
                            let isPast = dateString < '{{ now()->format('Y-m-d') }}';
                            let isTerlewat = hasJadwal && !allSelesai && isPast;
                            let hasProses = hasJadwal && j.some(x => x.status?.toLowerCase() === 'proses');
                            days.push({ 
                                empty: false, 
                                date: d.getDate(), 
                                dateString: dateString,
                                isToday: dateString === '{{ now()->format('Y-m-d') }}',
                                hasJadwal: hasJadwal,
                                allSelesai: allSelesai,
                                isTerlewat: isTerlewat,
                                hasProses: hasProses
                            });
                            date.setDate(date.getDate() + 1);
                        }
                        return days;
                    },
                    nextMonth() {
                        let nextM = this.currentDate.getMonth() + 2;
                        let nextY = this.currentDate.getFullYear();
                        if (nextM > 12) { nextM = 1; nextY++; }
                        window.location.href = `?month=${nextM}&year=${nextY}`;
                    },
                    prevMonth() {
                        let prevM = this.currentDate.getMonth();
                        let prevY = this.currentDate.getFullYear();
                        if (prevM < 1) { prevM = 12; prevY--; }
                        window.location.href = `?month=${prevM}&year=${prevY}`;
                    }
                 }"
            >
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-slate-800 capitalize" x-text="currentMonthName"></h3>
                    <div class="flex gap-1">
                        <button type="button" @click="prevMonth()" class="w-7 h-7 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-400 transition-colors"><i class="ph-bold ph-caret-left"></i></button>
                        <button type="button" @click="nextMonth()" class="w-7 h-7 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-400 transition-colors"><i class="ph-bold ph-caret-right"></i></button>
                    </div>
                </div>
                <div class="grid grid-cols-7 gap-1 text-center text-[10px] font-bold text-slate-400 mb-2 uppercase tracking-wider">
                    <div>{{ __('SUN') }}</div><div>{{ __('MON') }}</div><div>{{ __('TUE') }}</div><div>{{ __('WED') }}</div><div>{{ __('THU') }}</div><div>{{ __('FRI') }}</div><div>{{ __('SAT') }}</div>
                </div>
                <div class="grid grid-cols-7 gap-1 text-center text-sm">
                    <template x-for="(day, index) in daysInMonth" :key="index">
                        <div class="aspect-square flex items-center justify-center p-0.5">
                            <template x-if="day.empty">
                                <div></div>
                            </template>
                            <template x-if="!day.empty">
                                <button type="button" class="w-8 h-8 rounded-full flex flex-col items-center justify-center relative transition-colors cursor-default mx-auto"
                                        :class="{ 
                                            'bg-[#009B77] text-white font-bold shadow-md': day.isToday,
                                            'border-2 border-[#009B77] text-[#009B77] font-bold': day.hasJadwal && !day.isToday && day.allSelesai,
                                            'border-2 border-red-500 text-red-600 font-bold': day.hasJadwal && !day.isToday && !day.allSelesai && day.isTerlewat,
                                            'border-2 border-blue-500 text-blue-600 font-bold': day.hasJadwal && !day.isToday && !day.allSelesai && !day.isTerlewat && day.hasProses,
                                            'border-2 border-amber-500 text-amber-600 font-bold': day.hasJadwal && !day.isToday && !day.allSelesai && !day.isTerlewat && !day.hasProses,
                                            'text-slate-700 font-medium hover:bg-slate-50 border border-transparent': !day.isToday && !day.hasJadwal,
                                        }">
                                    <span x-text="day.date" class="z-10"></span>
                                </button>
                            </template>
                        </div>
                    </template>
                </div>
                
                <!-- Legend -->
                <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-center gap-3 text-[10px] font-medium text-slate-500">
                    <div class="flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-[#009B77]"></span>
                        <span>{{ __('Completed') }}</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                        <span>{{ __('Process') }}</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        <span>{{ __('Waiting') }}</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-red-500"></span>
                        <span>{{ __('Missed') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Inspection List -->
        <div class="lg:col-span-2 flex flex-col space-y-4 lg:overflow-y-auto lg:pr-3 pb-8 custom-scrollbar">
            
            <!-- Section: Hari Ini / {{ __('Month') }} Ini -->
            <div class="flex-shrink-0">
                <h2 class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-4 flex items-center gap-2 sticky top-0 bg-[#F8FAFC] z-10 py-2">
                    <i class="ph-bold ph-chart-line-up text-slate-500"></i> {{ __('Inspection Progress') }} {{ __('Month') }} {{ \Carbon\Carbon::create()->month($currentMonth)->translatedFormat('F') }}
                </h2>
                <div class="space-y-4">
                    
                    @forelse($gedungs as $gedung)
                    @php
                        $aparsInGedung = collect();
                        foreach($gedung->lokasi as $l) {
                            foreach($l->apar as $a) {
                                $aparsInGedung->push($a);
                            }
                        }
                        $countSelesai = $aparsInGedung->filter(fn($a) => $a->inspeksis->isNotEmpty())->count();
                        $countTotal = $aparsInGedung->count();
                    @endphp

                    @if($countTotal > 0)
                    <div x-data="{ expanded: false }" class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-md overflow-hidden transition-all flex-shrink-0">
                        <!-- Header -->
                        <div @click="expanded = !expanded" class="cursor-pointer w-full flex items-center justify-between p-5 bg-white hover:bg-slate-50 transition-colors border-b border-slate-100">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-[#009B77] shadow-sm flex-shrink-0">
                                    <i class="ph-fill ph-buildings text-2xl"></i>
                                </div>
                                <div class="text-left">
                                    <div class="flex items-center gap-2 mb-1">
                                        <h4 class="font-bold text-slate-800 text-base">{{ $gedung->nama }}</h4>
                                    </div>
                                    <div class="flex items-center gap-3 text-xs font-semibold text-slate-500">
                                        <span class="flex items-center gap-1"><i class="ph-bold ph-fire-extinguisher text-slate-400"></i> {{ $countTotal }} APAR</span>
                                        <span class="flex items-center gap-1"><i class="ph-bold ph-check-circle text-[#009B77]"></i> {{ $countSelesai }}/{{ $countTotal }} {{ __('Completed') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 transition-transform duration-300" :class="expanded ? 'rotate-180' : ''">
                                    <i class="ph-bold ph-caret-down text-sm"></i>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Body: Adaptive grouping (only group when multiple data) -->
                        <div x-show="expanded" x-collapse>
                            <div class="p-5 bg-white space-y-3">
                                @foreach($gedung->lokasi as $lokasi)
                                    @php
                                        $lokasiApars = $lokasi->apar;
                                        $lokasiSelesai = $lokasiApars->filter(fn($a) => $a->inspeksis->isNotEmpty())->count();
                                        $lokasiTotal = $lokasiApars->count();
                                        $isMultiData = $gedung->lokasi->count() > 1 && $lokasiTotal > 1;
                                    @endphp
                                    @if($lokasiTotal > 0)
                                        @if($isMultiData)
                                        <!-- Multiple APARs in room: Grouped box -->
                                        <div class="rounded-xl border border-slate-200/90 overflow-hidden bg-slate-50/40">
                                            <!-- Location Subheader -->
                                            <div class="px-4 py-2.5 bg-slate-100/70 border-b border-slate-200/80 flex flex-wrap items-center justify-between gap-2">
                                                <div class="flex items-center gap-2">
                                                    <div class="w-6 h-6 rounded-md bg-white border border-slate-200/90 flex items-center justify-center text-[#009B77] shadow-2xs">
                                                        <i class="ph-bold ph-door text-xs"></i>
                                                    </div>
                                                    <div>
                                                        <span class="font-bold text-slate-800 text-xs sm:text-sm">{{ $lokasi->nama }}</span>
                                                        @if($lokasi->nomor_ruangan)
                                                            <span class="ml-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-white border border-slate-200 text-slate-600">{{ $lokasi->nomor_ruangan }}</span>
                                                        @endif
                                                        @if($lokasi->lantai)
                                                            <span class="ml-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-white border border-slate-200 text-slate-500">{{ __('Floor') }} {{ $lokasi->lantai }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-2 text-xs font-semibold">
                                                    <span class="text-slate-500 text-[11px]">{{ $lokasiTotal }} APAR</span>
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $lokasiSelesai === $lokasiTotal ? 'bg-emerald-50 text-[#009B77] border border-emerald-200' : 'bg-slate-200/70 text-slate-600' }}">
                                                        {{ $lokasiSelesai }}/{{ $lokasiTotal }} {{ __('Completed') }}
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- APAR List in this Location -->
                                            <div class="p-3 bg-white space-y-2.5">
                                                @foreach($lokasiApars as $apar)
                                                    @if($apar->inspeksis->isEmpty())
                                                    <!-- APAR Menunggu -->
                                                    <div class="p-3 rounded-xl border border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:border-slate-200 hover:bg-slate-50/50 transition-colors">
                                                        <div class="flex items-center gap-3">
                                                            <div class="w-9 h-9 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400 flex-shrink-0">
                                                                <i class="ph-fill ph-fire-extinguisher text-lg"></i>
                                                            </div>
                                                            <div>
                                                                <div class="flex items-center gap-2">
                                                                    <h5 class="font-bold text-slate-800 text-sm">{{ $apar->kode }}</h5>
                                                                    <span class="text-[10px] font-medium text-slate-400">({{ $apar->jenis->nama ?? '-' }}, {{ $apar->kapasitas->ukuran ?? '-' }})</span>
                                                                </div>
                                                                @php
                                                                    $aparPics = $lokasi->users->isNotEmpty()
                                                                        ? $lokasi->users
                                                                        : $gedung->users;
                                                                    $aparPicNames = $aparPics->pluck('name')->implode(', ');
                                                                @endphp
                                                                <p class="text-[10px] font-bold {{ $aparPicNames ? 'text-[#009B77]' : 'text-slate-400' }} flex items-center gap-1 mt-0.5"><i class="ph-bold ph-user"></i> {{ __('Assigned to:') }} {{ $aparPicNames ?: __('Anyone') }}</p>
                                                            </div>
                                                        </div>
                                                        <div class="flex items-center gap-2 w-full sm:w-auto">
                                                            <span class="hidden sm:inline-flex px-2 py-1 rounded-md text-[10px] font-bold bg-amber-50 text-amber-500 border border-amber-100">{{ __('Waiting') }}</span>
                                                            <a href="{{ route('inspeksi.pedoman', $apar->id) }}?source=schedule" class="w-full sm:w-auto text-center bg-white border-2 border-slate-200 hover:border-[#009B77] text-slate-600 hover:text-[#009B77] font-bold py-1.5 px-4 rounded-lg transition-colors text-xs flex items-center justify-center gap-1.5 shadow-sm">
                                                                {{ __('Start') }} <i class="ph-bold ph-arrow-right"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    @else
                                                    <!-- APAR Selesai -->
                                                    @php 
                                                        $inspeksi = $apar->inspeksis->first(); 
                                                        $isPic = $gedung->users->contains('id', $inspeksi->user_id) || $lokasi->users->contains('id', $inspeksi->user_id);
                                                    @endphp
                                                    <div class="p-3 rounded-xl border border-[#009B77]/20 bg-[#009B77]/5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 relative overflow-hidden">
                                                        <div class="flex items-center gap-3">
                                                            <div class="w-9 h-9 rounded-lg bg-white border border-[#009B77]/20 flex items-center justify-center text-[#009B77] flex-shrink-0">
                                                                <i class="ph-fill ph-fire-extinguisher text-lg"></i>
                                                            </div>
                                                            <div>
                                                                <div class="flex items-center gap-2">
                                                                    <h5 class="font-bold text-slate-800 text-sm">{{ $apar->kode }}</h5>
                                                                    <span class="text-[10px] font-medium text-slate-500">({{ $apar->jenis->nama ?? '-' }}, {{ $apar->kapasitas->ukuran ?? '-' }})</span>
                                                                </div>
                                                                <p class="text-[10px] font-bold text-[#009B77] flex items-center gap-1 mt-0.5">
                                                                    <i class="ph-bold ph-check-circle"></i> {{ __('Completed by') }} 
                                                                    @if(!$isPic)
                                                                        <span class="text-amber-500 flex items-center gap-0.5" title="{{ __('Not Main PIC for this Area') }}">
                                                                            {{ ucwords(strtolower($inspeksi->user->name ?? 'User')) }} <i class="ph-fill ph-warning-circle text-[10px]"></i>
                                                                        </span>
                                                                    @else
                                                                        {{ ucwords(strtolower($inspeksi->user->name ?? 'User')) }}
                                                                    @endif
                                                                    <span class="text-slate-400 font-medium">({{ $inspeksi->created_at->format('d M, H:i') }})</span>
                                                                </p>
                                                            </div>
                                                        </div>
                                                        @php
                                                            $inspeksiData = [
                                                                'apar_kode' => $apar->kode,
                                                                'lokasi' => ($lokasi->nama ?? 'n/a') . ($lokasi->nomor_ruangan ? ' ('.$lokasi->nomor_ruangan.')' : ''),
                                                                'jenis' => $apar->jenis->nama ?? '-',
                                                                'kapasitas' => $apar->kapasitas->ukuran ?? '-',
                                                                'tgl_isi_ulang' => $apar->tgl_isi_ulang ? $apar->tgl_isi_ulang->translatedFormat('d M Y') : '-',
                                                                'tgl_kedaluwarsa' => $apar->tgl_kedaluwarsa ? $apar->tgl_kedaluwarsa->translatedFormat('d M Y') : '-',
                                                                'waktu' => $inspeksi->created_at->format('d M Y, H:i'),
                                                                'petugas' => ucwords(strtolower($inspeksi->user->name ?? 'User')),
                                                                'is_pic' => $isPic,
                                                                'status' => $inspeksi->status,
                                                                'catatan' => $inspeksi->catatan_tambahan,
                                                                'checklist' => is_string($inspeksi->checklist) ? json_decode($inspeksi->checklist, true) : $inspeksi->checklist,
                                                                'foto' => $apar->foto
                                                            ];
                                                        @endphp
                                                        <div class="flex items-center gap-2 w-full sm:w-auto">
                                                            <button @click='selectedInspeksi = @json($inspeksiData); showPanelHasil = true' class="w-full sm:w-auto text-center bg-white border border-slate-200 hover:border-slate-300 text-slate-600 font-bold py-1.5 px-4 rounded-lg transition-colors text-xs flex items-center justify-center gap-1.5 shadow-sm">
                                                                {{ __('View Results') }}
                                                            </button>
                                                        </div>
                                                    </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                        @else
                                        <!-- Single APAR in room or single room in building: Display flat like usual ("tampilin kyk biasa") -->
                                        @foreach($lokasiApars as $apar)
                                            @if($apar->inspeksis->isEmpty())
                                            <!-- APAR Menunggu -->
                                            <div class="p-4 rounded-xl border border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:border-slate-200 hover:bg-slate-50/50 transition-colors">
                                                <div class="flex items-start gap-3">
                                                    <div class="w-10 h-10 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400 flex-shrink-0">
                                                        <i class="ph-fill ph-fire-extinguisher text-xl"></i>
                                                    </div>
                                                    <div>
                                                        <div class="flex items-center gap-2">
                                                            <h5 class="font-bold text-slate-800 text-sm">{{ $apar->kode }}</h5>
                                                            <span class="text-[10px] font-medium text-slate-400">({{ $apar->jenis->nama ?? '-' }}, {{ $apar->kapasitas->ukuran ?? '-' }})</span>
                                                        </div>
                                                        <p class="text-xs font-semibold text-slate-500 mb-1 flex items-center gap-1.5 mt-0.5">
                                                            <i class="ph-bold ph-door text-slate-400 text-xs"></i> {{ $lokasi->nama ?? 'n/a' }}
                                                            @if($lokasi->nomor_ruangan)
                                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-slate-100 border border-slate-200/80 text-slate-600">{{ $lokasi->nomor_ruangan }}</span>
                                                            @endif
                                                            @if($lokasi->lantai)
                                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-slate-100 border border-slate-200/80 text-slate-500">{{ __('Floor') }} {{ $lokasi->lantai }}</span>
                                                            @endif
                                                        </p>
                                                        @php
                                                            $aparPics = $lokasi->users->isNotEmpty()
                                                                ? $lokasi->users
                                                                : $gedung->users;
                                                            $aparPicNames = $aparPics->pluck('name')->implode(', ');
                                                        @endphp
                                                        <p class="text-[10px] font-bold {{ $aparPicNames ? 'text-[#009B77]' : 'text-slate-400' }} flex items-center gap-1"><i class="ph-bold ph-user"></i> {{ __('Assigned to:') }} {{ $aparPicNames ?: __('Anyone') }}</p>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-2 w-full sm:w-auto">
                                                    <span class="hidden sm:inline-flex px-2 py-1 rounded-md text-[10px] font-bold bg-amber-50 text-amber-500 border border-amber-100">{{ __('Waiting') }}</span>
                                                    <a href="{{ route('inspeksi.pedoman', $apar->id) }}?source=schedule" class="w-full sm:w-auto text-center bg-white border-2 border-slate-200 hover:border-[#009B77] text-slate-600 hover:text-[#009B77] font-bold py-1.5 px-4 rounded-lg transition-colors text-xs flex items-center justify-center gap-1.5 shadow-sm">
                                                        {{ __('Start') }} <i class="ph-bold ph-arrow-right"></i>
                                                    </a>
                                                </div>
                                            </div>
                                            @else
                                            <!-- APAR Selesai -->
                                            @php 
                                                $inspeksi = $apar->inspeksis->first(); 
                                                $isPic = $gedung->users->contains('id', $inspeksi->user_id) || $lokasi->users->contains('id', $inspeksi->user_id);
                                            @endphp
                                            <div class="p-4 rounded-xl border border-[#009B77]/20 bg-[#009B77]/5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 relative overflow-hidden">
                                                <div class="flex items-start gap-3">
                                                    <div class="w-10 h-10 rounded-lg bg-white border border-[#009B77]/20 flex items-center justify-center text-[#009B77] flex-shrink-0">
                                                        <i class="ph-fill ph-fire-extinguisher text-xl"></i>
                                                    </div>
                                                    <div>
                                                        <div class="flex items-center gap-2">
                                                            <h5 class="font-bold text-slate-800 text-sm">{{ $apar->kode }}</h5>
                                                            <span class="text-[10px] font-medium text-slate-500">({{ $apar->jenis->nama ?? '-' }}, {{ $apar->kapasitas->ukuran ?? '-' }})</span>
                                                        </div>
                                                        <p class="text-xs font-semibold text-slate-500 mb-1 flex items-center gap-1.5 mt-0.5">
                                                            <i class="ph-bold ph-door text-slate-400 text-xs"></i> {{ $lokasi->nama ?? 'n/a' }}
                                                            @if($lokasi->nomor_ruangan)
                                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-white border border-slate-200 text-slate-600">{{ $lokasi->nomor_ruangan }}</span>
                                                            @endif
                                                            @if($lokasi->lantai)
                                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-white border border-slate-200 text-slate-500">{{ __('Floor') }} {{ $lokasi->lantai }}</span>
                                                            @endif
                                                        </p>
                                                        <p class="text-[10px] font-bold text-[#009B77] flex items-center gap-1">
                                                            <i class="ph-bold ph-check-circle"></i> {{ __('Completed by') }} 
                                                            @if(!$isPic)
                                                                <span class="text-amber-500 flex items-center gap-0.5" title="{{ __('Not Main PIC for this Area') }}">
                                                                    {{ ucwords(strtolower($inspeksi->user->name ?? 'User')) }} <i class="ph-fill ph-warning-circle text-[10px]"></i>
                                                                </span>
                                                            @else
                                                                {{ ucwords(strtolower($inspeksi->user->name ?? 'User')) }}
                                                            @endif
                                                            <span class="text-slate-400 font-medium">({{ $inspeksi->created_at->format('d M, H:i') }})</span>
                                                        </p>
                                                    </div>
                                                </div>
                                                @php
                                                    $inspeksiData = [
                                                        'apar_kode' => $apar->kode,
                                                        'lokasi' => ($lokasi->nama ?? 'n/a') . ($lokasi->nomor_ruangan ? ' ('.$lokasi->nomor_ruangan.')' : ''),
                                                        'jenis' => $apar->jenis->nama ?? '-',
                                                        'kapasitas' => $apar->kapasitas->ukuran ?? '-',
                                                        'tgl_isi_ulang' => $apar->tgl_isi_ulang ? $apar->tgl_isi_ulang->translatedFormat('d M Y') : '-',
                                                        'tgl_kedaluwarsa' => $apar->tgl_kedaluwarsa ? $apar->tgl_kedaluwarsa->translatedFormat('d M Y') : '-',
                                                        'waktu' => $inspeksi->created_at->format('d M Y, H:i'),
                                                        'petugas' => ucwords(strtolower($inspeksi->user->name ?? 'User')),
                                                        'is_pic' => $isPic,
                                                        'status' => $inspeksi->status,
                                                        'catatan' => $inspeksi->catatan_tambahan,
                                                        'checklist' => is_string($inspeksi->checklist) ? json_decode($inspeksi->checklist, true) : $inspeksi->checklist,
                                                        'foto' => $apar->foto
                                                    ];
                                                @endphp
                                                <div class="flex items-center gap-2 w-full sm:w-auto">
                                                    <button @click='selectedInspeksi = @json($inspeksiData); showPanelHasil = true' class="w-full sm:w-auto text-center bg-white border border-slate-200 hover:border-slate-300 text-slate-600 font-bold py-1.5 px-4 rounded-lg transition-colors text-xs flex items-center justify-center gap-1.5 shadow-sm">
                                                        {{ __('View Results') }}
                                                    </button>
                                                </div>
                                            </div>
                                            @endif
                                        @endforeach
                                        @endif
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif
                    @empty
                    <div class="p-8 text-center bg-white rounded-2xl border border-slate-200 border-dashed text-slate-500 flex-shrink-0">
                        <i class="ph-duotone ph-buildings text-4xl mb-3"></i>
                        <p class="font-bold">{{ __('No PFE or Building data yet.') }}</p>
                    </div>
                    @endforelse
                </div>
                </div>

                <!-- Pembagian Jadwal -->
                @php
                    $jadwalTerlewat = collect();
                    $jadwalHariIni = collect();
                    $jadwalMendatang = collect();
                    $jadwalSelesai = collect();
                    $today = \Carbon\Carbon::today();

                    foreach($jadwals as $jadwal) {
                        if ($jadwal->status === 'selesai') {
                            $jadwalSelesai->push($jadwal);
                            continue;
                        }

                        $jadwalDate = \Carbon\Carbon::parse($jadwal->tanggal_inspeksi)->startOfDay();
                        
                        if ($jadwalDate->lessThan($today)) {
                            $jadwalTerlewat->push($jadwal);
                        } elseif ($jadwalDate->equalTo($today)) {
                            $jadwalHariIni->push($jadwal);
                        } else {
                            $jadwalMendatang->push($jadwal);
                        }
                    }
                @endphp

                @if($jadwalTerlewat->isNotEmpty())
                <div class="mt-10">
                    <h2 class="text-[11px] font-bold text-red-500 uppercase tracking-wider flex items-center gap-2 mb-4"><i class="ph-bold ph-warning-circle"></i> {{ __('Missed Schedule') }}</h2>
                    
                    <div class="space-y-4">
                        @foreach($jadwalTerlewat as $jadwal)
                            @php
                                $isTerlewat = true;
                            @endphp
                            @include('inspection-schedule.partials.jadwal-card')
                        @endforeach
                    </div>
                </div>
                @endif

                @if($jadwalHariIni->isNotEmpty())
                <div class="mt-10">
                    <h2 class="text-[11px] font-bold text-blue-500 uppercase tracking-wider flex items-center gap-2 mb-4"><i class="ph-bold ph-calendar-star"></i> {{ __('Today\'s Schedule') }}</h2>
                    
                    <div class="space-y-4">
                        @foreach($jadwalHariIni as $jadwal)
                            @php
                                $isTerlewat = false;
                            @endphp
                            @include('inspection-schedule.partials.jadwal-card')
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="mt-10">
                    <h2 class="text-[11px] font-bold text-slate-500 uppercase tracking-wider flex items-center gap-2 mb-4"><i class="ph-bold ph-calendar-check"></i> {{ __('Upcoming Schedule List') }}</h2>
                    
                    <div class="space-y-4">
                        @forelse($jadwalMendatang as $jadwal)
                            @php
                                $isTerlewat = false;
                            @endphp
                            @include('inspection-schedule.partials.jadwal-card')
                        @empty
                        <div class="p-8 text-center bg-white rounded-2xl border border-slate-200 border-dashed text-slate-400 flex-shrink-0">
                            <i class="ph-duotone ph-calendar-x text-4xl mb-3 text-slate-300"></i>
                            <p class="font-bold text-sm">{{ __('No upcoming inspection schedule yet.') }}</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                @if($jadwalSelesai->isNotEmpty())
                <div class="mt-10">
                    <h2 class="text-[11px] font-bold text-[#009B77] uppercase tracking-wider flex items-center gap-2 mb-4"><i class="ph-bold ph-check-circle"></i> {{ __('Completed Schedule') }}</h2>
                    
                    <div class="space-y-4 opacity-80 hover:opacity-100 transition-opacity">
                        @foreach($jadwalSelesai as $jadwal)
                            @php
                                $isTerlewat = false;
                            @endphp
                            @include('inspection-schedule.partials.jadwal-card')
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
    </div>

    <!-- Slide-over Panel: Lihat Hasil -->
    <div x-show="showPanelHasil" class="relative z-[100]" aria-labelledby="slide-over-title" role="dialog" aria-modal="true" style="display: none;">
        <div x-show="showPanelHasil" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/40 transition-opacity" @click="showPanelHasil = false"></div>
        <div class="fixed inset-0 overflow-hidden pointer-events-none">
            <div class="absolute inset-0 overflow-hidden">
                <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10 sm:pl-16">
                    <div x-show="showPanelHasil" @click.away="showPanelHasil = false" x-transition:enter="transform transition ease-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transform transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="pointer-events-auto w-screen max-w-md">
                        <div class="flex h-full flex-col overflow-y-scroll bg-white shadow-2xl" x-data="{ tabs: 'checklist' }">
                            <template x-if="selectedInspeksi">
                                <div>
                                    <div class="p-6 border-b border-slate-100 flex items-center justify-between sticky top-0 bg-white/90 backdrop-blur-md z-10">
                                        <div>
                                            <h2 class="text-lg font-extrabold text-slate-800" id="slide-over-title">{{ __('PFE Inspection Results') }}</h2>
                                            <p class="text-xs font-semibold text-[#009B77] mt-0.5"><i class="ph-bold ph-check-circle mr-1"></i>{{ __('Completed on') }} <span x-text="selectedInspeksi.waktu"></span></p>
                                        </div>
                                        <button type="button" @click="showPanelHasil = false" class="relative rounded-xl w-10 h-10 flex items-center justify-center bg-slate-50 text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                                            <i class="ph-bold ph-x text-lg"></i>
                                        </button>
                                    </div>
                                    <div class="p-6 space-y-6">
                                        <!-- Info Header -->
                                        <div class="flex items-center gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-100">
                                            <div class="w-12 h-12 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-400 flex-shrink-0">
                                                <i class="ph-fill ph-fire-extinguisher text-2xl"></i>
                                            </div>
                                            <div>
                                                <h3 class="font-bold text-slate-800" x-text="selectedInspeksi.apar_kode"></h3>
                                                <p class="text-xs font-semibold text-slate-500" x-text="selectedInspeksi.lokasi"></p>
                                            </div>
                                        </div>

                                        <!-- Final Status -->
                                        <div class="flex flex-col gap-3 p-4 rounded-xl border"
                                             :class="{
                                                 'border-[#009B77]/20 bg-[#009B77]/5': selectedInspeksi.status?.toLowerCase() === 'sesuai' || selectedInspeksi.status?.toLowerCase() === 'layak',
                                                 'border-red-200 bg-red-50': selectedInspeksi.status?.toLowerCase() === 'tidak_sesuai' || selectedInspeksi.status?.toLowerCase() === 'perbaikan' || selectedInspeksi.status?.toLowerCase() === 'rusak' || selectedInspeksi.status?.toLowerCase() === 'tidak_layak',
                                                 'border-amber-200 bg-amber-50': selectedInspeksi.status?.toLowerCase() === 'perlu_refill' || selectedInspeksi.status?.toLowerCase() === 'isi_ulang'
                                             }">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-3">
                                                    <i class="text-2xl" :class="{
                                                        'ph-fill ph-shield-check text-[#009B77]': selectedInspeksi.status?.toLowerCase() === 'sesuai' || selectedInspeksi.status?.toLowerCase() === 'layak',
                                                        'ph-fill ph-warning-circle text-red-500': selectedInspeksi.status?.toLowerCase() === 'tidak_sesuai' || selectedInspeksi.status?.toLowerCase() === 'perbaikan' || selectedInspeksi.status?.toLowerCase() === 'rusak' || selectedInspeksi.status?.toLowerCase() === 'tidak_layak',
                                                        'ph-fill ph-arrows-clockwise text-amber-500': selectedInspeksi.status?.toLowerCase() === 'perlu_refill' || selectedInspeksi.status?.toLowerCase() === 'isi_ulang'
                                                    }"></i>
                                                    <div>
                                                        <span class="block text-[10px] font-bold uppercase tracking-wider" :class="{
                                                            'text-[#009B77]': selectedInspeksi.status?.toLowerCase() === 'sesuai' || selectedInspeksi.status?.toLowerCase() === 'layak',
                                                            'text-red-500': selectedInspeksi.status?.toLowerCase() === 'tidak_sesuai' || selectedInspeksi.status?.toLowerCase() === 'perbaikan' || selectedInspeksi.status?.toLowerCase() === 'rusak' || selectedInspeksi.status?.toLowerCase() === 'tidak_layak',
                                                            'text-amber-600': selectedInspeksi.status?.toLowerCase() === 'perlu_refill' || selectedInspeksi.status?.toLowerCase() === 'isi_ulang'
                                                        }">{{ __('FINAL STATUS') }}</span>
                                                        <span class="font-bold text-slate-800 text-sm capitalize" x-text="window.statusTranslations[selectedInspeksi.status?.toLowerCase()] || selectedInspeksi.status?.replace('_', ' ')"></span>
                                                    </div>
                                                </div>
                                                <div class="text-right">
                                                    <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ __('Inspected By') }}</span>
                                                    <div class="flex items-center justify-end gap-1">
                                                        <span class="font-bold text-sm" :class="selectedInspeksi.is_pic === false ? 'text-amber-500' : 'text-slate-800'" x-text="selectedInspeksi.petugas"></span>
                                                        <template x-if="selectedInspeksi.is_pic === false">
                                                            <i class="ph-fill ph-warning-circle text-amber-500 text-xs" title="{{ __('Not Main PIC for this Area') }}"></i>
                                                        </template>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- {{ __('Inspection Photo') }} -->
                                        <template x-if="selectedInspeksi.foto">
                                            <div class="rounded-xl border border-slate-200 overflow-hidden shadow-sm bg-slate-900 flex items-center justify-center relative group/img cursor-pointer"
                                                 @click="openGlobalPhotoModal(selectedInspeksi)"
                                                 title="{{ __('Klik untuk memperbesar foto inspeksi & watermark') }}">
                                                <img :src="'/storage/' + selectedInspeksi.foto" alt="{{ __('Inspection Photo') }}" class="w-full h-auto max-h-80 object-contain transition-transform duration-300 group-hover/img:scale-105">
                                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover/img:opacity-100 transition-opacity flex items-center justify-center pointer-events-none">
                                                    <div class="px-3.5 py-1.5 rounded-full bg-white/95 text-slate-800 text-xs font-bold flex items-center gap-1.5 shadow-lg backdrop-blur">
                                                        <i class="ph-bold ph-magnifying-glass-plus text-base text-[#009B77]"></i>
                                                        {{ __('Perbesar Foto Watermark') }}
                                                    </div>
                                                </div>
                                                <div class="absolute top-2.5 left-2.5 bg-black/60 backdrop-blur-md border border-white/20 text-white text-[9px] font-bold px-2 py-0.5 rounded-md flex items-center gap-1">
                                                    <i class="ph-bold ph-arrows-out text-xs text-[#009B77]"></i>
                                                    <span>{{ __('Klik untuk Zoom') }}</span>
                                                </div>
                                            </div>
                                        </template>
                                        
                                        <!-- Tabs -->
                                        <div class="flex gap-2 p-1 bg-slate-100 rounded-xl">
                                            <button @click="tabs = 'checklist'" class="flex-1 py-1.5 text-xs font-bold rounded-lg transition-all" :class="tabs === 'checklist' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-700'">{{ __('Checklist') }}</button>
                                            <button @click="tabs = 'catatan'" class="flex-1 py-1.5 text-xs font-bold rounded-lg transition-all" :class="tabs === 'catatan' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-700'">{{ __('Notes & Officer') }}</button>
                                        </div>

                                        <!-- Checklist Results -->
                                        <div x-show="tabs === 'checklist'">
                                            <div class="space-y-6">
                                                
                                                <!-- Step 1: Pemeriksaan Fisik (Indices 14-23) -->
                                                <div>
                                                    <h4 class="font-bold text-slate-800 text-[11px] uppercase tracking-wider mb-3">{{ __('Physical Inspection') }}</h4>
                                                    <div class="space-y-3">
                                                        <template x-for="(item, index) in selectedInspeksi.checklist" :key="index">
                                                            <template x-if="parseInt(index) >= 14">
                                                                <div class="flex items-start gap-3 p-3 rounded-xl border" 
                                                                    x-data="{ 
                                                                        isExpected() { 
                                                                            const isLegacy = selectedInspeksi.checklist && Object.keys(selectedInspeksi.checklist).length >= 24;
                                                                            const karatIdx = isLegacy ? 19 : 18;
                                                                            if (parseInt(index) === karatIdx) return item.jawaban === 'tidak ada';
                                                                            return item.jawaban === 'ada';
                                                                        },
                                                                        getAnswerBadge() {
                                                                            const idx = parseInt(index);
                                                                            const isLegacy = selectedInspeksi.checklist && Object.keys(selectedInspeksi.checklist).length >= 24;
                                                                            if (idx === 14) return selectedInspeksi.jenis || '{{ __('Ada') }}';
                                                                            if (idx === 15) return selectedInspeksi.kapasitas || '{{ __('Ada') }}';
                                                                            if (idx === 16) return selectedInspeksi.tgl_isi_ulang || '{{ __('Ada') }}';
                                                                            if (isLegacy && idx === 17) return selectedInspeksi.tgl_kedaluwarsa || '{{ __('Ada') }}';
                                                                            const karatIdx = isLegacy ? 19 : 18;
                                                                            const padatIdx = isLegacy ? 20 : 19;
                                                                            const lokasiIdx = isLegacy ? 23 : 22;
                                                                            if (idx === karatIdx) return item.jawaban === 'tidak ada' ? '{{ __('Tidak Ada') }}' : '{{ __('Ada') }}';
                                                                            if (idx === padatIdx) return item.jawaban === 'ada' ? '{{ __('Padat') }}' : '{{ __('Tidak Padat') }}';
                                                                            if (idx === lokasiIdx) {
                                                                                if (item.jawaban === 'tidak ada' && item.keterangan) {
                                                                                    return '{{ __('Pindah: ') }}' + item.keterangan;
                                                                                }
                                                                                return selectedInspeksi.lokasi || '{{ __('Sesuai') }}';
                                                                            }
                                                                            const dict = { 'ya': '{{ __('Yes') }}', 'tidak': '{{ __('No') }}', 'ada': '{{ __('Ada') }}', 'tidak ada': '{{ __('Tidak Ada') }}' };
                                                                            return dict[item.jawaban] || item.jawaban;
                                                                        }
                                                                    }"
                                                                    :class="isExpected() ? 'border-slate-100 bg-white' : 'border-red-200 bg-red-50'">
                                                                    <i class="text-xl mt-0.5 flex-shrink-0" :class="isExpected() ? 'ph-fill ph-check-circle text-[#009B77]' : 'ph-fill ph-x-circle text-red-500'"></i>
                                                                    <div class="w-full">
                                                                        <div class="flex justify-between items-start gap-2">
                                                                            <p class="font-bold text-slate-800 text-xs leading-relaxed" x-text="window.pertanyaanApar[index]"></p>
                                                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wider flex-shrink-0 text-center" :class="isExpected() ? 'bg-teal-50 text-teal-600' : 'bg-red-100 text-red-600'" x-text="getAnswerBadge()"></span>
                                                                        </div>
                                                                        <template x-if="item.keterangan && item.keterangan.trim() !== '' && parseInt(index) !== ((selectedInspeksi.checklist && Object.keys(selectedInspeksi.checklist).length >= 24) ? 23 : 22)">
                                                                            <p class="text-xs font-bold mt-1.5 bg-white px-2 py-1 rounded border inline-block" :class="isExpected() ? 'text-slate-600 border-slate-100' : 'text-red-600 border-red-100'">
                                                                                {{ __('Notes:') }} <span class="font-medium" x-text="item.keterangan"></span>
                                                                            </p>
                                                                        </template>
                                                                    </div>
                                                                </div>
                                                            </template>
                                                        </template>
                                                    </div>
                                                </div>

                                                <!-- Step 2: Pemeriksaan Fungsi (Indices 0-13) -->
                                                <div>
                                                    <h4 class="font-bold text-slate-800 text-[11px] uppercase tracking-wider mb-3">{{ __('Function Check') }}</h4>
                                                    <div class="space-y-3">
                                                        <template x-for="(item, index) in selectedInspeksi.checklist" :key="index">
                                                            <template x-if="parseInt(index) < 14">
                                                                <div class="flex items-start gap-3 p-3 rounded-xl border" 
                                                                    x-data="{ 
                                                                        translatedAnswer() {
                                                                            const dict = { 'ya': '{{ __('Yes') }}', 'tidak': '{{ __('No') }}', 'ada': '{{ __('Ada') }}', 'tidak ada': '{{ __('Tidak Ada') }}' };
                                                                            return dict[item.jawaban] || item.jawaban;
                                                                        }
                                                                    }"
                                                                    :class="item.jawaban === 'ya' ? 'border-slate-100 bg-white' : 'border-red-200 bg-red-50'">
                                                                    <i class="text-xl mt-0.5 flex-shrink-0" :class="item.jawaban === 'ya' ? 'ph-fill ph-check-circle text-[#009B77]' : 'ph-fill ph-x-circle text-red-500'"></i>
                                                                    <div class="w-full">
                                                                        <div class="flex justify-between items-start gap-2">
                                                                            <p class="font-bold text-slate-800 text-xs leading-relaxed" x-text="window.pertanyaanApar[index]"></p>
                                                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wider flex-shrink-0" :class="item.jawaban === 'ya' ? 'bg-teal-50 text-teal-600' : 'bg-red-100 text-red-600'" x-text="translatedAnswer()"></span>
                                                                        </div>
                                                                        <template x-if="item.keterangan && item.keterangan.trim() !== ''">
                                                                            <p class="text-xs font-bold mt-1.5 bg-white px-2 py-1 rounded border inline-block" :class="item.jawaban === 'ya' ? 'text-slate-600 border-slate-100' : 'text-red-600 border-red-100'">
                                                                                {{ __('Notes:') }} <span class="font-medium" x-text="item.keterangan"></span>
                                                                            </p>
                                                                        </template>
                                                                    </div>
                                                                </div>
                                                            </template>
                                                        </template>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>

                                        <!-- Inspector Info -->
                                        <div x-show="tabs === 'catatan'" style="display: none;">
                                            <div class="p-4 rounded-2xl border border-slate-100 space-y-4">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-400">
                                                        <i class="ph-fill ph-user text-xl"></i>
                                                    </div>
                                                    <div>
                                                        <p class="font-bold text-slate-800 text-sm" x-text="selectedInspeksi.petugas"></p>
                                                        <p class="text-xs font-medium text-slate-500">{{ __('Checking Officer') }}</p>
                                                    </div>
                                                </div>
                                                <div class="border-t border-slate-100 pt-4">
                                                    <p class="text-xs font-bold text-slate-500 mb-2">{{ __('Additional Notes:') }}</p>
                                                    <p class="text-sm font-medium text-slate-700 bg-slate-50 p-3 rounded-lg border border-slate-100" x-text="selectedInspeksi.catatan || '{{ __('No specific notes.') }}'"></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Buat Jadwal -->
    <div x-show="showModalBuatJadwal" 
         class="fixed inset-0 z-[100] flex items-center justify-center overflow-y-auto overflow-x-hidden p-4 sm:p-6 lg:p-8"
         style="display: none;">
        
        <!-- Backdrop -->
        <div x-show="showModalBuatJadwal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="showModalBuatJadwal = false"
             class="fixed inset-0 bg-slate-900/40 z-[-1]">
        </div>

        <!-- Modal Content -->
        <div x-show="showModalBuatJadwal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative w-full max-w-2xl bg-white rounded-3xl shadow-[0_20px_60px_-15px_rgba(0,0,0,0.1)] border border-slate-100 overflow-hidden my-auto">
             
            <!-- Header Modal -->
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white border border-slate-200/60 shadow-sm flex items-center justify-center text-[#009B77]">
                        <i class="ph-bold ph-calendar-plus text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-800 leading-tight">{{ __('Create Inspection Schedule') }}</h3>
                        <p class="text-xs font-semibold text-slate-500">{{ __('Determine area, date, and inspection officer.') }}</p>
                    </div>
                </div>
                <button @click="showModalBuatJadwal = false" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                    <i class="ph-bold ph-x text-lg"></i>
                </button>
            </div>

            <!-- Body Modal -->
            <form action="/inspection-schedule" method="POST" class="p-6" x-data="{
                formJenis: '',
                formTanggal: '',
                formAreas: [],
                formUserId: '',
                get isFormValid() {
                    if (!this.formJenis) return false;
                    if (!this.formTanggal) return false;
                    if (this.formAreas.length === 0) return false;
                    if (!this.formUserId) return false;
                    return true;
                },
                isAreaScheduledOnDate(areaId) {
                    if (!this.formTanggal) return false;
                    if (!window.jadwalsData) return false;
                    let jadwalsForDate = window.jadwalsData[this.formTanggal];
                    if (!jadwalsForDate) return false;
                    return jadwalsForDate.some(j => j.area_id === areaId);
                }
            }">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
                    <!-- Jenis Jadwal -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">{{ __('Schedule Type') }}</label>
                        <div x-data="{ open: false, options: ['{{ __('Routine Inspection') }}', '{{ __('Special Inspection / Findings') }}'] }" class="relative">
                            <input type="hidden" name="jenis_jadwal" :value="formJenis">
                            <i class="ph-bold ph-calendar-star absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-base transition-colors z-10" :class="open ? 'text-[#009B77]' : ''"></i>
                            <button type="button" @click="open = !open" @click.away="open = false" 
                                    class="w-full bg-white border-2 border-slate-200 rounded-xl py-2.5 pl-10 pr-3 text-sm font-medium text-slate-700 focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none cursor-pointer text-left flex items-center justify-between"
                                    :class="open ? 'border-[#009B77] ring-4 ring-[#009B77]/15' : ''">
                                <span x-text="formJenis || '{{ __('Select Type') }}'" :class="!formJenis ? 'text-slate-400 font-medium' : ''" class="truncate block"></span>
                                <i class="ph-bold ph-caret-down text-slate-400 transition-transform duration-200 flex-shrink-0" :class="open ? 'rotate-180 text-[#009B77]' : ''"></i>
                            </button>
                            <!-- Dropdown -->
                            <div x-show="open" style="display: none;" class="absolute left-0 z-50 w-full mt-1.5 bg-white rounded-xl shadow-lg border border-slate-100 py-1.5 overflow-hidden origin-top">
                                <template x-for="option in options">
                                    <button type="button" @click="formJenis = option; open = false" class="w-full text-left px-4 py-2 text-sm transition-colors flex items-center justify-between" :class="formJenis === option ? 'text-[#009B77] bg-[#009B77]/5 font-bold' : 'text-slate-600 font-medium hover:bg-slate-50'">
                                        <span x-text="option"></span>
                                        <i class="ph-bold ph-check text-[#009B77]" x-show="formJenis === option"></i>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Tanggal Inspeksi -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">{{ __('Inspection Date') }}</label>
                        <div class="relative" x-data x-init="flatpickr($refs.dateInput, { dateFormat: 'Y-m-d', minDate: 'today', onChange: (s, d) => { formTanggal = d; } })">
                            <i class="ph-bold ph-calendar-blank absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-base transition-colors peer-focus:text-[#009B77] z-10"></i>
                            <input x-ref="dateInput" name="tanggal" type="text" placeholder="{{ __('Select Date') }}" required
                                   class="peer w-full bg-white border-2 border-slate-200 rounded-xl py-2.5 pl-10 pr-3 text-sm font-medium text-slate-700 focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none placeholder:text-slate-400 placeholder:font-medium appearance-none cursor-pointer">
                        </div>
                    </div>

                    <!-- Hidden Inputs for formAreas -->
                    <template x-for="a in formAreas" :key="a">
                        <input type="hidden" name="areas[]" :value="a">
                    </template>

                    <!-- Cakupan Lokasi -->
                    <div class="col-span-1 md:col-span-2" x-data="{
                        open: false,
                        search: '',
                        options: [
                            @foreach($gedungs as $gedung)
                                @foreach($gedung->lokasi as $lokasi)
                                @php
                                    $aparCodes = $lokasi->apar->pluck('kode')->implode(', ');
                                @endphp
                                { id: 'l_{{ $lokasi->id }}', nama: '{{ addslashes($lokasi->nama) }}', gedung_nama: '{{ addslashes($gedung->nama) }}', apars: '{{ addslashes($aparCodes) }}', text_search: '{{ addslashes($lokasi->nama . ' ' . $gedung->nama . ' ' . $aparCodes) }}' },
                                @endforeach
                            @endforeach
                        ],
                        toggleArea(id) {
                            if (formAreas.includes(id)) {
                                formAreas = formAreas.filter(x => x !== id);
                            } else {
                                formAreas.push(id);
                            }
                        },
                        get countSelected() {
                            return formAreas.filter(id => id.startsWith('l_')).length;
                        }
                    }">
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">{{ __('Select Location') }}</label>
                        
                        <div class="relative" @click.away="open = false; search = ''">
                            <i class="ph-bold ph-map-pin absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-base transition-colors z-10" :class="open ? 'text-[#009B77]' : ''"></i>
                            <button type="button" @click="open = !open" 
                                    class="w-full bg-white border-2 border-slate-200 rounded-xl py-2.5 pl-10 pr-3 text-sm font-medium text-slate-700 focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none cursor-pointer text-left flex items-center justify-between"
                                    :class="open ? 'border-[#009B77] ring-4 ring-[#009B77]/15' : ''">
                                <span x-text="countSelected > 0 ? countSelected + ' ' + '{{ __('selected locations') }}' : '{{ __('Select Location') }}'" :class="countSelected === 0 ? 'text-slate-400 font-medium' : 'font-medium text-slate-800'" class="truncate block"></span>
                                <i class="ph-bold ph-caret-down text-slate-400 transition-transform duration-200 flex-shrink-0" :class="open ? 'rotate-180 text-[#009B77]' : ''"></i>
                            </button>
                            
                            <div x-show="open" style="display: none;" class="absolute left-0 z-50 w-full mt-1.5 bg-white rounded-xl shadow-lg border border-slate-100 py-1.5 overflow-hidden origin-top max-h-60 flex flex-col">
                                <div class="px-2 pb-1.5 mb-1.5 border-b border-slate-100 flex-shrink-0">
                                    <div class="relative">
                                        <i class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                        <input type="text" x-model="search" placeholder="{{ __('Search location...') }}" class="w-full bg-slate-50 border-none rounded-lg py-1.5 pl-9 pr-3 text-xs font-medium text-slate-700 focus:ring-0 placeholder:text-slate-400" @click.stop>
                                    </div>
                                </div>
                                <div class="overflow-y-auto">
                                    <template x-for="option in options" :key="option.id">
                                        <button type="button" x-show="option.text_search.toLowerCase().includes(search.toLowerCase())" 
                                                @click="toggleArea(option.id)" 
                                                class="w-full text-left px-4 py-2.5 transition-colors flex items-start justify-between cursor-pointer" 
                                                :class="formAreas.includes(option.id) ? 'bg-[#009B77]/5' : 'hover:bg-slate-50'">
                                            <div class="flex flex-col pr-3 py-0.5">
                                                <div class="flex items-center mb-1">
                                                    <span class="text-sm transition-colors" :class="formAreas.includes(option.id) ? 'text-[#009B77] font-bold' : 'text-slate-700 font-medium'" x-text="option.nama"></span>
                                                    <span x-show="isAreaScheduledOnDate(option.id)" class="text-[9px] font-bold text-amber-600 bg-amber-50 border border-amber-200 px-1.5 py-0.5 rounded ml-2 uppercase tracking-wide">{{ __('Scheduled') }}</span>
                                                </div>
                                                <div class="flex flex-wrap items-center gap-1.5">
                                                    <div class="flex items-center gap-1 text-[10px] text-slate-500 font-medium bg-slate-100 px-1.5 py-0.5 rounded">
                                                        <i class="ph-fill ph-buildings"></i>
                                                        <span x-text="option.gedung_nama"></span>
                                                    </div>
                                                    <template x-if="option.apars">
                                                        <div class="flex flex-wrap gap-1">
                                                            <template x-for="apar in option.apars.split(', ')" :key="apar">
                                                                <span class="text-[9px] font-medium text-slate-500 border border-slate-200 bg-white px-1.5 py-0.5 rounded shadow-sm" x-text="apar"></span>
                                                            </template>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                            <div class="w-4 h-4 rounded border flex items-center justify-center transition-all flex-shrink-0 ml-3 mt-1" :class="formAreas.includes(option.id) ? 'bg-[#009B77] border-[#009B77] shadow-sm' : 'border-slate-300 bg-white'">
                                                <i class="ph-bold ph-check text-white text-[10px]" x-show="formAreas.includes(option.id)"></i>
                                            </div>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Petugas Inspeksi -->
                    <div class="md:col-span-2" x-data="{
                        optionsUser: [
                            @foreach($users as $user)
                            { id: {{ $user->id }}, nama: '{{ addslashes($user->name) }} ({{ addslashes($user->role) }})' },
                            @endforeach
                        ],
                        get selectedUserName() {
                            let u = this.optionsUser.find(x => x.id === this.formUserId);
                            return u ? u.nama : '';
                        }
                    }">
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">{{ __('Inspection Officer') }}</label>
                        <div x-data="{ open: false, search: '' }" class="relative">
                            <input type="hidden" name="user_id" :value="formUserId">
                            <i class="ph-bold ph-user-circle absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-base transition-colors z-10" :class="open ? 'text-[#009B77]' : ''"></i>
                            <button type="button" @click="open = !open" @click.away="open = false; search = ''" 
                                    class="w-full bg-white border-2 border-slate-200 rounded-xl py-2.5 pl-10 pr-3 text-sm font-medium text-slate-700 focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none cursor-pointer text-left flex items-center justify-between"
                                    :class="open ? 'border-[#009B77] ring-4 ring-[#009B77]/15' : ''">
                                <span x-text="selectedUserName || '{{ __('Select Officer') }}'" :class="!selectedUserName ? 'text-slate-400 font-medium' : ''" class="truncate block"></span>
                                <i class="ph-bold ph-caret-down text-slate-400 transition-transform duration-200 flex-shrink-0" :class="open ? 'rotate-180 text-[#009B77]' : ''"></i>
                            </button>
                            <div x-show="open" style="display: none;" class="absolute left-0 z-50 w-full mt-1.5 bg-white rounded-xl shadow-lg border border-slate-100 py-1.5 overflow-hidden origin-top max-h-56 flex flex-col">
                                <div class="px-2 pb-1.5 mb-1.5 border-b border-slate-100 flex-shrink-0">
                                    <div class="relative">
                                        <i class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                        <input type="text" x-model="search" placeholder="{{ __('Search officer...') }}" class="w-full bg-slate-50 border-none rounded-lg py-1.5 pl-9 pr-3 text-xs font-medium text-slate-700 focus:ring-0 placeholder:text-slate-400" @click.stop>
                                    </div>
                                </div>
                                <div class="overflow-y-auto">
                                    <template x-for="option in optionsUser" :key="option.id">
                                        <button type="button" x-show="option.nama.toLowerCase().includes(search.toLowerCase())" @click="formUserId = option.id; open = false; search = ''" class="w-full text-left px-4 py-2 text-sm transition-colors flex items-center justify-between" :class="formUserId === option.id ? 'text-[#009B77] bg-[#009B77]/5 font-bold' : 'text-slate-600 font-medium hover:bg-slate-50'">
                                            <span x-text="option.nama"></span>
                                            <i class="ph-bold ph-check text-[#009B77]" x-show="formUserId === option.id"></i>
                                        </button>
                                    </template>
                                    <div x-show="optionsUser.filter(o => o.nama.toLowerCase().includes(search.toLowerCase())).length === 0" class="px-4 py-3 text-sm text-slate-500 font-medium text-center">{{ __('Not found.') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Catatan -->
                    <div class="md:col-span-2">
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">{{ __('Additional Notes (Optional)') }}</label>
                        <div class="relative">
                            <i class="ph-bold ph-note-pencil absolute left-3.5 top-3.5 text-slate-400 text-base transition-colors peer-focus:text-[#009B77]"></i>
                            <textarea name="catatan_tambahan" rows="2" placeholder="{{ __('Focus on area...') }}" class="peer w-full bg-white border-2 border-slate-200 rounded-xl py-2.5 pl-10 pr-3 text-sm font-medium text-slate-700 focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none placeholder:text-slate-400 placeholder:font-medium resize-none"></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="showModalBuatJadwal = false" class="px-5 py-2 rounded-xl font-bold text-slate-600 hover:text-slate-900 bg-slate-50 hover:bg-slate-100 transition-colors text-sm">
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit" 
                            :disabled="!isFormValid"
                            :class="isFormValid ? 'bg-[#009B77] hover:bg-[#008264] text-white shadow-sm' : 'bg-slate-200 text-slate-400 cursor-not-allowed'"
                            class="font-bold py-2.5 px-6 rounded-xl transition-all text-sm flex items-center justify-center gap-2">
                        <i class="ph-bold ph-floppy-disk text-lg"></i>
                        {{ __('Save Schedule') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

        <!-- Modal Edit Jadwal -->
    <div x-show="showModalEditJadwal" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6" x-cloak>
        <!-- Backdrop -->
        <div x-show="showModalEditJadwal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="showModalEditJadwal = false"
             class="fixed inset-0 bg-slate-900/40 z-[-1]">
        </div>

        <!-- Modal Content -->
        <div x-show="showModalEditJadwal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative w-full max-w-2xl bg-white rounded-3xl shadow-[0_20px_60px_-15px_rgba(0,0,0,0.1)] border border-slate-100 overflow-hidden my-auto">
             
            <!-- Header Modal -->
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white border border-slate-200/60 shadow-sm flex items-center justify-center text-[#009B77]">
                        <i class="ph-bold ph-pencil-simple text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-800 leading-tight">{{ __('Edit Inspection Schedule') }}</h3>
                        <p class="text-xs font-semibold text-slate-500">{{ __('Update area, date, and inspection officer.') }}</p>
                    </div>
                </div>
                <button @click="showModalEditJadwal = false" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                    <i class="ph-bold ph-x text-lg"></i>
                </button>
            </div>

            <!-- Body Modal -->
            <form :action="'/inspection-schedule/' + editData?.id" method="POST" class="p-6" x-data="{
                get formJenis() { return editData?.jenis_jadwal || '' },
                set formJenis(val) { if(editData) editData.jenis_jadwal = val },
                get formTanggal() { return editData?.tanggal || '' },
                set formTanggal(val) { if(editData) editData.tanggal = val },
                get formTipeArea() { return editData?.tipe_area || 'gedung' },
                set formTipeArea(val) { if(editData) editData.tipe_area = val },
                get formGedungId() { return editData?.gedung_id || '' },
                set formGedungId(val) { if(editData) editData.gedung_id = val },
                get formLokasiId() { return editData?.lokasi_id || '' },
                set formLokasiId(val) { if(editData) editData.lokasi_id = val },
                get formUserId() { return editData?.user_id || '' },
                set formUserId(val) { if(editData) editData.user_id = val },
                get formCatatan() { return editData?.catatan_tambahan || '' },
                set formCatatan(val) { if(editData) editData.catatan_tambahan = val },
                get isFormValid() {
                    if (!this.formJenis) return false;
                    if (!this.formTanggal) return false;
                    if (this.formTipeArea === 'gedung' && !this.formGedungId) return false;
                    if (this.formTipeArea === 'lokasi' && !this.formLokasiId) return false;
                    if (!this.formUserId) return false;
                    if (JSON.stringify(editData) === originalEditData) return false;
                    return true;
                }
            }">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
                    <!-- Jenis Jadwal -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">{{ __('Schedule Type') }}</label>
                        <div x-data="{ open: false, options: ['{{ __('Routine Inspection') }}', '{{ __('Special Inspection / Findings') }}'], get selected() { return formJenis }, set selected(val) { formJenis = val } }" class="relative">
                            <input type="hidden" name="jenis_jadwal" :value="formJenis">
                            <i class="ph-bold ph-calendar-star absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-base transition-colors z-10" :class="open ? 'text-[#009B77]' : ''"></i>
                            <button type="button" @click="open = !open" @click.away="open = false" 
                                    class="w-full bg-white border-2 border-slate-200 rounded-xl py-2.5 pl-10 pr-3 text-sm font-medium text-slate-700 focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none cursor-pointer text-left flex items-center justify-between"
                                    :class="open ? 'border-[#009B77] ring-4 ring-[#009B77]/15' : ''">
                                <span x-text="formJenis || '{{ __('Select Type') }}'" :class="!formJenis ? 'text-slate-400 font-medium' : ''" class="truncate block"></span>
                                <i class="ph-bold ph-caret-down text-slate-400 transition-transform duration-200 flex-shrink-0" :class="open ? 'rotate-180 text-[#009B77]' : ''"></i>
                            </button>
                            <!-- Dropdown -->
                            <div x-show="open" style="display: none;" class="absolute left-0 z-50 w-full mt-1.5 bg-white rounded-xl shadow-lg border border-slate-100 py-1.5 overflow-hidden origin-top">
                                <template x-for="option in options">
                                    <button type="button" @click="formJenis = option; open = false" class="w-full text-left px-4 py-2 text-sm transition-colors flex items-center justify-between" :class="formJenis === option ? 'text-[#009B77] bg-[#009B77]/5 font-bold' : 'text-slate-600 font-medium hover:bg-slate-50'">
                                        <span x-text="option"></span>
                                        <i class="ph-bold ph-check text-[#009B77]" x-show="formJenis === option"></i>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Tanggal Inspeksi -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">{{ __('Inspection Date') }}</label>
                        <div class="relative" x-data x-init="let fp = flatpickr($refs.dateInput, { dateFormat: 'Y-m-d', minDate: 'today', onChange: function(s, d) { formTanggal = d; } }); $watch('formTanggal', val => { if(val) fp.setDate(val) })">
                            <i class="ph-bold ph-calendar-blank absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-base transition-colors peer-focus:text-[#009B77] z-10"></i>
                            <input x-ref="dateInput" name="tanggal" type="text" placeholder="{{ __('Select Date') }}" required
                                   class="peer w-full bg-white border-2 border-slate-200 rounded-xl py-2.5 pl-10 pr-3 text-sm font-medium text-slate-700 focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none placeholder:text-slate-400 placeholder:font-medium appearance-none cursor-pointer">
                        </div>
                    </div>

                    <!-- Cakupan Lokasi/Gedung -->
                    <div class="col-span-1" x-data="{
                        optionsGedung: [
                            @foreach($gedungs as $gedung)
                            { id: {{ $gedung->id }}, nama: '{{ addslashes($gedung->nama) }}' },
                            @endforeach
                        ],
                        optionsLokasi: [
                            @foreach($gedungs as $gedung)
                                @foreach($gedung->lokasi as $lokasi)
                                @php
                                    $aparCodesEdit = $lokasi->apar->pluck('kode')->implode(', ');
                                @endphp
                                { id: {{ $lokasi->id }}, nama: '{{ addslashes($lokasi->nama) }}', gedung_nama: '{{ addslashes($gedung->nama) }}', gedung_id: {{ $gedung->id }}, apars: '{{ addslashes($aparCodesEdit) }}', text_search: '{{ addslashes($lokasi->nama . ' ' . $gedung->nama . ' ' . $aparCodesEdit) }}' },
                                @endforeach
                            @endforeach
                        ],
                        
                        get selectedGedungName() {
                            let g = this.optionsGedung.find(x => x.id === this.formGedungId);
                            return g ? g.nama : '';
                        },
                        get selectedLokasiName() {
                            let l = this.optionsLokasi.find(x => x.id === this.formLokasiId);
                            return l ? l.nama + ' (' + l.gedung_nama + ')' : '';
                        }
                    }">
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider">{{ __('Location Scope') }}</label>
                            
                            <!-- Toggle Tipe Area -->
                            <div class="flex items-center gap-3">
                                <label class="flex items-center gap-1.5 cursor-pointer group">
                                    <input type="radio" x-model="formTipeArea" value="gedung" name="tipe_area" class="w-3.5 h-3.5 text-[#009B77] border-slate-300 focus:ring-[#009B77] cursor-pointer">
                                    <span class="text-[10px] font-bold text-slate-500 group-hover:text-slate-800 transition-colors uppercase tracking-wider">{{ __('Building') }}</span>
                                </label>
                                <label class="flex items-center gap-1.5 cursor-pointer group">
                                    <input type="radio" x-model="formTipeArea" value="lokasi" name="tipe_area" class="w-3.5 h-3.5 text-[#009B77] border-slate-300 focus:ring-[#009B77] cursor-pointer">
                                    <span class="text-[10px] font-bold text-slate-500 group-hover:text-slate-800 transition-colors uppercase tracking-wider">{{ __('Specific') }}</span>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Dropdown Gedung -->
                        <div x-show="formTipeArea === 'gedung'" x-data="{ open: false, search: '' }" class="relative">
                            <input type="hidden" name="gedung_id" :value="formGedungId">
                            <i class="ph-bold ph-buildings absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-base transition-colors z-10" :class="open ? 'text-[#009B77]' : ''"></i>
                            <button type="button" @click="open = !open" @click.away="open = false; search = ''" 
                                    class="w-full bg-white border-2 border-slate-200 rounded-xl py-2.5 pl-10 pr-3 text-sm font-medium text-slate-700 focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none cursor-pointer text-left flex items-center justify-between"
                                    :class="open ? 'border-[#009B77] ring-4 ring-[#009B77]/15' : ''">
                                <span x-text="selectedGedungName || '{{ __('Select Building') }}'" :class="!selectedGedungName ? 'text-slate-400 font-medium' : ''" class="truncate block"></span>
                                <i class="ph-bold ph-caret-down text-slate-400 transition-transform duration-200 flex-shrink-0" :class="open ? 'rotate-180 text-[#009B77]' : ''"></i>
                            </button>
                            <div x-show="open" style="display: none;" class="absolute left-0 z-50 w-full mt-1.5 bg-white rounded-xl shadow-lg border border-slate-100 py-1.5 overflow-hidden origin-top max-h-56 flex flex-col">
                                <div class="px-2 pb-1.5 mb-1.5 border-b border-slate-100 flex-shrink-0">
                                    <div class="relative">
                                        <i class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                        <input type="text" x-model="search" placeholder="{{ __('Search building...') }}" class="w-full bg-slate-50 border-none rounded-lg py-1.5 pl-9 pr-3 text-xs font-medium text-slate-700 focus:ring-0 placeholder:text-slate-400" @click.stop>
                                    </div>
                                </div>
                                <div class="overflow-y-auto">
                                    <template x-if="optionsGedung.length === 0">
                                        <div class="px-4 py-3 text-sm text-slate-500 font-medium text-center">{{ __('No building yet.') }}</div>
                                    </template>
                                    <template x-for="option in optionsGedung" :key="option.id">
                                        <button type="button" x-show="option.nama.toLowerCase().includes(search.toLowerCase())" @click="formGedungId = option.id; open = false; search = ''" class="w-full text-left px-4 py-2 text-sm transition-colors flex items-center justify-between" :class="formGedungId === option.id ? 'text-[#009B77] bg-[#009B77]/5 font-bold' : 'text-slate-600 font-medium hover:bg-slate-50'">
                                            <span x-text="option.nama"></span>
                                            <i class="ph-bold ph-check text-[#009B77]" x-show="formGedungId === option.id"></i>
                                        </button>
                                    </template>
                                    <div x-show="optionsGedung.length > 0 && optionsGedung.filter(o => o.nama.toLowerCase().includes(search.toLowerCase())).length === 0" class="px-4 py-3 text-sm text-slate-500 font-medium text-center">{{ __('Not found.') }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Dropdown Lokasi -->
                        <div x-show="formTipeArea === 'lokasi'" x-data="{ open: false, search: '' }" class="relative" style="display:none;">
                            <input type="hidden" name="lokasi_id" :value="formLokasiId">
                            <i class="ph-bold ph-map-pin absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-base transition-colors z-10" :class="open ? 'text-[#009B77]' : ''"></i>
                            <button type="button" @click="open = !open" @click.away="open = false; search = ''" 
                                    class="w-full bg-white border-2 border-slate-200 rounded-xl py-2.5 pl-10 pr-3 text-sm font-medium text-slate-700 focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none cursor-pointer text-left flex items-center justify-between"
                                    :class="open ? 'border-[#009B77] ring-4 ring-[#009B77]/15' : ''">
                                <span x-text="selectedLokasiName || '{{ __('Select Location') }}'" :class="!selectedLokasiName ? 'text-slate-400 font-medium' : ''" class="truncate block"></span>
                                <i class="ph-bold ph-caret-down text-slate-400 transition-transform duration-200 flex-shrink-0" :class="open ? 'rotate-180 text-[#009B77]' : ''"></i>
                            </button>
                            <div x-show="open" style="display: none;" class="absolute left-0 z-50 w-full mt-1.5 bg-white rounded-xl shadow-lg border border-slate-100 py-1.5 overflow-hidden origin-top max-h-60 flex flex-col">
                                <div class="px-2 pb-1.5 mb-1.5 border-b border-slate-100 flex-shrink-0">
                                    <div class="relative">
                                        <i class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                        <input type="text" x-model="search" placeholder="{{ __('Search location...') }}" class="w-full bg-slate-50 border-none rounded-lg py-1.5 pl-9 pr-3 text-xs font-medium text-slate-700 focus:ring-0 placeholder:text-slate-400" @click.stop>
                                    </div>
                                </div>
                                <div class="overflow-y-auto">
                                    <template x-if="optionsLokasi.length === 0">
                                        <div class="px-4 py-3 text-sm text-slate-500 font-medium text-center">{{ __('No location yet.') }}</div>
                                    </template>
                                    <template x-for="option in optionsLokasi" :key="option.id">
                                        <button type="button" x-show="option.text_search.toLowerCase().includes(search.toLowerCase())" @click="formLokasiId = option.id; open = false; search = ''" class="w-full text-left px-4 py-2.5 transition-colors flex items-start justify-between" :class="formLokasiId === option.id ? 'bg-[#009B77]/5' : 'hover:bg-slate-50'">
                                            <div class="flex flex-col pr-3 py-0.5">
                                                <span class="text-sm transition-colors mb-1" :class="formLokasiId === option.id ? 'text-[#009B77] font-bold' : 'text-slate-700 font-medium'" x-text="option.nama"></span>
                                                <div class="flex flex-wrap items-center gap-1.5">
                                                    <div class="flex items-center gap-1 text-[10px] text-slate-500 font-medium bg-slate-100 px-1.5 py-0.5 rounded">
                                                        <i class="ph-fill ph-buildings"></i>
                                                        <span x-text="option.gedung_nama"></span>
                                                    </div>
                                                    <template x-if="option.apars">
                                                        <div class="flex flex-wrap gap-1">
                                                            <template x-for="apar in option.apars.split(', ')" :key="apar">
                                                                <span class="text-[9px] font-medium text-slate-500 border border-slate-200 bg-white px-1.5 py-0.5 rounded shadow-sm" x-text="apar"></span>
                                                            </template>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                            <i class="ph-bold ph-check text-[#009B77] mt-1" x-show="formLokasiId === option.id"></i>
                                        </button>
                                    </template>
                                    <div x-show="optionsLokasi.length > 0 && optionsLokasi.filter(o => o.nama.toLowerCase().includes(search.toLowerCase()) || o.gedung_nama.toLowerCase().includes(search.toLowerCase())).length === 0" class="px-4 py-3 text-sm text-slate-500 font-medium text-center">{{ __('Not found.') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Petugas Inspeksi -->
                    <div x-data="{
                        optionsUser: [
                            @foreach($users as $user)
                            { id: {{ $user->id }}, nama: '{{ addslashes($user->name) }} ({{ addslashes($user->role) }})' },
                            @endforeach
                        ],
                        get selectedUserName() {
                            let u = this.optionsUser.find(x => x.id === this.formUserId);
                            return u ? u.nama : '';
                        }
                    }">
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">{{ __('Inspection Officer') }}</label>
                        <div x-data="{ open: false, search: '' }" class="relative">
                            <input type="hidden" name="user_id" :value="formUserId">
                            <i class="ph-bold ph-user-circle absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-base transition-colors z-10" :class="open ? 'text-[#009B77]' : ''"></i>
                            <button type="button" @click="open = !open" @click.away="open = false; search = ''" 
                                    class="w-full bg-white border-2 border-slate-200 rounded-xl py-2.5 pl-10 pr-3 text-sm font-medium text-slate-700 focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none cursor-pointer text-left flex items-center justify-between"
                                    :class="open ? 'border-[#009B77] ring-4 ring-[#009B77]/15' : ''">
                                <span x-text="selectedUserName || '{{ __('Select Officer') }}'" :class="!selectedUserName ? 'text-slate-400 font-medium' : ''" class="truncate block"></span>
                                <i class="ph-bold ph-caret-down text-slate-400 transition-transform duration-200 flex-shrink-0" :class="open ? 'rotate-180 text-[#009B77]' : ''"></i>
                            </button>
                            <div x-show="open" style="display: none;" class="absolute left-0 z-50 w-full mt-1.5 bg-white rounded-xl shadow-lg border border-slate-100 py-1.5 overflow-hidden origin-top max-h-56 flex flex-col">
                                <div class="px-2 pb-1.5 mb-1.5 border-b border-slate-100 flex-shrink-0">
                                    <div class="relative">
                                        <i class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                        <input type="text" x-model="search" placeholder="{{ __('Search officer...') }}" class="w-full bg-slate-50 border-none rounded-lg py-1.5 pl-9 pr-3 text-xs font-medium text-slate-700 focus:ring-0 placeholder:text-slate-400" @click.stop>
                                    </div>
                                </div>
                                <div class="overflow-y-auto">
                                    <template x-for="option in optionsUser" :key="option.id">
                                        <button type="button" x-show="option.nama.toLowerCase().includes(search.toLowerCase())" @click="formUserId = option.id; open = false; search = ''" class="w-full text-left px-4 py-2 text-sm transition-colors flex items-center justify-between" :class="formUserId === option.id ? 'text-[#009B77] bg-[#009B77]/5 font-bold' : 'text-slate-600 font-medium hover:bg-slate-50'">
                                            <span x-text="option.nama"></span>
                                            <i class="ph-bold ph-check text-[#009B77]" x-show="formUserId === option.id"></i>
                                        </button>
                                    </template>
                                    <div x-show="optionsUser.filter(o => o.nama.toLowerCase().includes(search.toLowerCase())).length === 0" class="px-4 py-3 text-sm text-slate-500 font-medium text-center">{{ __('Not found.') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Catatan -->
                    <div class="md:col-span-2">
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">{{ __('Additional Notes (Optional)') }}</label>
                        <div class="relative">
                            <i class="ph-bold ph-note-pencil absolute left-3.5 top-3.5 text-slate-400 text-base transition-colors peer-focus:text-[#009B77]"></i>
                            <textarea name="catatan_tambahan" x-model="formCatatan" rows="2" placeholder="{{ __('Focus on area...') }}" class="peer w-full bg-white border-2 border-slate-200 rounded-xl py-2.5 pl-10 pr-3 text-sm font-medium text-slate-700 focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none placeholder:text-slate-400 placeholder:font-medium resize-none"></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="showModalEditJadwal = false" class="px-5 py-2 rounded-xl font-bold text-slate-600 hover:text-slate-900 bg-slate-50 hover:bg-slate-100 transition-colors text-sm">
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit" 
                            :disabled="!isFormValid"
                            :class="isFormValid ? 'hover:bg-[#008264] shadow-sm' : 'opacity-50 cursor-not-allowed'"
                            class="bg-[#009B77] text-white font-bold py-2.5 px-6 rounded-xl transition-all text-sm flex items-center justify-center gap-2">
                        <i class="ph-bold ph-floppy-disk text-lg"></i>
                        {{ __('Save Changes') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

