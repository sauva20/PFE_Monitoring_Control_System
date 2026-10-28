@extends('layouts.app')

@section('title', 'Dashboard Overview - APAR Monitoring System')

@section('content')
<style>
@media print {
    @page { size: landscape; margin: 5mm; }
    html, body { 
        background: white !important; 
        -webkit-print-color-adjust: exact !important; 
        print-color-adjust: exact !important; 
        height: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        overflow: hidden !important;
    }
    
    * { overflow: visible !important; }

    /* Hide UI elements and tables */
    .sidebar, header, nav, .btn-smooth-ring { display: none !important; }
    
    /* Target only the tables */
    [x-show="tab === 'belum'"], 
    [x-show="tab === 'sudah'"],
    #recentInspectionsTable {
        display: none !important;
    }

    main { 
        padding: 0 !important; 
        margin: 0 !important; 
        height: auto !important; 
        overflow: visible !important;
    }
    
    /* Frame scale for 1 page fit (landscape) */
    .space-y-4 { margin: 0 !important; padding: 0 !important; display: flex; flex-direction: column; gap: 0.25rem !important; }
    
    /* Reduce vertical spacing in print mode to fit 1 page natively */
    .report-card, .chart-container { padding: 0.5rem 1rem !important; }
    .mb-4 { margin-bottom: 0.25rem !important; }
    .p-4 { padding: 0.5rem 1rem !important; }
    .p-5 { padding: 0.75rem 1rem !important; }
    h2.text-2xl { font-size: 1.25rem !important; }
    #progressPieChart { transform: scale(0.85); transform-origin: center; margin: -10px 0 !important; }
    
    /* Force desktop grid layouts for print */
    .grid-cols-1.xl\:grid-cols-7 {
        grid-template-columns: repeat(7, minmax(0, 1fr)) !important;
    }
    .xl\:col-span-2 {
        grid-column: span 2 / span 2 !important;
    }
    .grid-cols-1.lg\:grid-cols-3 {
        grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
    }
    .print-col-span-2 {
        grid-column: span 2 / span 2 !important;
    }
    
    /* Force green box grid layout for print */
    .md\:grid-cols-12 {
        grid-template-columns: repeat(12, minmax(0, 1fr)) !important;
    }
    .md\:col-span-3 {
        grid-column: span 3 / span 3 !important;
    }
    .md\:col-span-6 {
        grid-column: span 6 / span 6 !important;
    }
    
    .report-card, .chart-container {
        page-break-inside: avoid !important;
        break-inside: avoid !important;
    }

    /* Hide the tabs dropdown in print */
    .max-w-md, [x-data="{ tab: null }"] > div:nth-child(4) { display: none !important; }
}
</style>

<!-- Include ApexCharts CDN -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<style>
@keyframes pop-highlight {
    0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(0, 155, 119, 0.4); }
    50% { transform: scale(1.02); box-shadow: 0 0 0 15px rgba(0, 155, 119, 0); }
    100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(0, 155, 119, 0); }
}
.animate-pop-highlight {
    animation: pop-highlight 1s cubic-bezier(0.4, 0, 0.2, 1);
}
</style>

<div class="space-y-4">

    <!-- Custom Print Header -->
    <div class="hidden print:flex flex-col mb-4">
        <div class="text-center border-b-2 border-[#009B77] pb-2 mb-2">
            <h1 class="text-lg font-extrabold text-[#009B77] uppercase tracking-wider">{{ __('DASHBOARD PFE MONITORING CONTROL SYSTEM') }}</h1>
        </div>
        <div class="flex justify-between items-center w-full px-2">
            <div class="text-[11px] font-bold text-slate-500">{{ __('Identification Date:') }} {{ \Carbon\Carbon::now()->locale(app()->getLocale())->translatedFormat('d F Y H:i:s') }}</div>
            <div class="text-[11px] font-bold text-slate-500">{{ __('Period:') }} {{ __(\Carbon\Carbon::create()->month((int)$selectedMonth)->format('F')) }} {{ $selectedYear }}</div>
        </div>
    </div>
    
    <!-- Header Area -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 print:hidden">
        <!-- Left: Date Context -->
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-white border border-slate-200/60 shadow-sm flex items-center justify-center text-[#009B77]">
                <i class="ph-bold ph-calendar-blank text-lg"></i>
            </div>
            <div>
                @php
                    $isCurrentMonthYear = ((int)$selectedMonth == (int)now()->month && (int)$selectedYear == (int)now()->year);
                    $selectedDateObj = \Carbon\Carbon::createFromDate((int)$selectedYear, (int)$selectedMonth, 1)->locale(app()->getLocale());
                @endphp
                <h2 class="text-base font-bold text-slate-800 leading-tight">
                    {{ $isCurrentMonthYear ? __('Today\'s Statistics') : __('Monthly Statistics') }}
                </h2>
                <p class="text-xs font-semibold text-slate-500 mt-0.5">
                    {{ $isCurrentMonthYear ? \Carbon\Carbon::now()->locale(app()->getLocale())->translatedFormat('l, d F Y') : $selectedDateObj->translatedFormat('F Y') }}
                </p>
            </div>
        </div>

        <!-- Right: Filter & Actions -->
        <div class="flex items-center gap-3 flex-wrap sm:flex-nowrap justify-end">
            <!-- Filter Form -->
            <form method="GET" action="/dashboard" class="flex items-center gap-2 print:hidden" id="filterForm">
                
                <!-- Custom Month Dropdown -->
                <div x-data="{ open: false, val: '{{ $selectedMonth }}' }" class="relative" @click.away="open = false">
                    <input type="hidden" name="month" :value="val">
                    <button type="button" @click="open = !open" class="flex items-center justify-between gap-2 w-36 bg-white border border-slate-200/60 rounded-xl py-2 px-3 text-sm font-bold text-slate-700 shadow-sm hover:border-[#009B77]/50 focus:border-[#009B77] focus:ring-2 focus:ring-[#009B77]/20 outline-none transition-all">
                        <span class="truncate text-left flex-1" x-text="
                            @foreach([
                                1 => __('January'), 2 => __('February'), 3 => __('March'), 
                                4 => __('April'), 5 => __('May'), 6 => __('June'), 
                                7 => __('July'), 8 => __('August'), 9 => __('September'), 
                                10 => __('October'), 11 => __('November'), 12 => __('December')
                            ] as $m => $monthName)
                                val == '{{ $m }}' ? '{{ $monthName }}' :
                            @endforeach ''
                        "></span>
                        <i class="ph-bold ph-caret-down text-slate-400"></i>
                    </button>
                    
                    <div x-show="open" style="display: none;" class="absolute top-full left-0 mt-1.5 w-full bg-white border border-slate-100 rounded-xl shadow-xl z-50 py-1.5 max-h-60 overflow-y-auto custom-scrollbar">
                        @foreach([
                            1 => __('January'), 2 => __('February'), 3 => __('March'), 
                            4 => __('April'), 5 => __('May'), 6 => __('June'), 
                            7 => __('July'), 8 => __('August'), 9 => __('September'), 
                            10 => __('October'), 11 => __('November'), 12 => __('December')
                        ] as $m => $monthName)
                            @php 
                                $isDisabled = ($selectedYear == now()->year && $m > now()->month);
                            @endphp
                            @if($isDisabled)
                                <div class="px-3 py-2 text-sm text-slate-300 font-medium cursor-not-allowed">
                                    {{ $monthName }}
                                </div>
                            @else
                                <button type="button" @click="val = '{{ $m }}'; open = false; $nextTick(() => document.getElementById('filterForm').submit())" class="w-full text-left px-3 py-2 text-sm font-bold transition-colors" :class="val == '{{ $m }}' ? 'bg-[#009B77]/10 text-[#009B77]' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'">
                                    {{ $monthName }}
                                </button>
                            @endif
                        @endforeach
                    </div>
                </div>

                <!-- Custom Year Dropdown -->
                <div x-data="{ open: false, val: '{{ $selectedYear }}' }" class="relative" @click.away="open = false">
                    <input type="hidden" name="year" :value="val">
                    <button type="button" @click="open = !open" class="flex items-center justify-between gap-2 w-24 bg-white border border-slate-200/60 rounded-xl py-2 px-3 text-sm font-bold text-slate-700 shadow-sm hover:border-[#009B77]/50 focus:border-[#009B77] focus:ring-2 focus:ring-[#009B77]/20 outline-none transition-all">
                        <span x-text="val"></span>
                        <i class="ph-bold ph-caret-down text-slate-400"></i>
                    </button>
                    
                    <div x-show="open" style="display: none;" class="absolute top-full right-0 mt-1.5 w-full bg-white border border-slate-100 rounded-xl shadow-xl z-50 py-1.5 max-h-60 overflow-y-auto custom-scrollbar">
                        @foreach(range(now()->year - 2, now()->year) as $y)
                            <button type="button" @click="val = '{{ $y }}'; open = false; $nextTick(() => document.getElementById('filterForm').submit())" class="w-full text-left px-3 py-2 text-sm font-bold transition-colors" :class="val == '{{ $y }}' ? 'bg-[#009B77]/10 text-[#009B77]' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'">
                                {{ $y }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </form>

            <button onclick="window.print()" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-xl shadow-md hover:shadow-lg shadow-red-600/20 transition-all flex items-center gap-2 text-sm">
                <i class="ph-bold ph-file-pdf text-lg"></i>
                <span class="hidden sm:inline">{{ __('Export PDF') }}</span>
            </button>
        </div>
    </div>


    
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-7 gap-3">
        
        <!-- Card 1: {{ __('TOTAL PFE') }} -->
        <div class="bg-gradient-to-br from-[#009B77] to-[#007b5e] text-white rounded-2xl p-5 shadow-lg shadow-[#009B77]/20 hover:shadow-xl hover:-translate-y-1 border border-[#009B77] flex flex-col justify-between transition-all duration-300 group cursor-pointer report-card relative overflow-hidden xl:col-span-2">
            <!-- Background Decorations -->
            <div class="absolute -right-6 -top-6 w-32 h-32 bg-white/10 rounded-full blur-2xl group-hover:bg-white/20 transition-colors"></div>
            <div class="absolute right-[5%] bottom-[-10%] text-white/5 transform -rotate-12 scale-150 pointer-events-none transition-transform group-hover:scale-125 duration-500">
                <i class="ph-fill ph-fire-extinguisher" style="font-size: 150px;"></i>
            </div>
            
            <div class="relative z-10 flex h-full gap-4 items-center">
                <!-- Left: TOTAL PFE -->
                <div class="flex-1 flex items-center gap-4 h-full pl-2">
                    <div class="w-12 h-12 rounded-xl bg-white/20 backdrop-blur-md group-hover:bg-white/30 group-hover:scale-110 transition-all duration-300 flex items-center justify-center text-white shadow-inner flex-shrink-0">
                        <i class="ph-fill ph-fire-extinguisher text-2xl"></i>
                    </div>
                    <div class="flex flex-col justify-center">
                        <h3 class="text-xs font-bold text-white/90 uppercase tracking-wider mb-1">{{ __('TOTAL PFE') }}</h3>
                        <h2 id="stat-totalApar" class="text-4xl font-extrabold text-white leading-none">{{ $totalApar }}</h2>
                    </div>
                </div>

                <!-- Right: TOTAL STOCK (Mini Card) -->
                <div class="w-32 bg-black/30 backdrop-blur-sm border border-white/10 rounded-xl p-3 flex flex-col items-center justify-center shadow-inner group-hover:bg-black/40 transition-colors">
                    <div class="flex items-center gap-1.5 mb-1 text-white">
                        <i class="ph-bold ph-archive text-sm"></i>
                        <h3 class="text-[10px] font-bold uppercase tracking-wider">{{ __('STOK') }}</h3>
                    </div>
                    <h2 id="stat-totalStock" class="text-2xl font-extrabold text-amber-300 group-hover:text-amber-200 transition-colors">{{ $totalStock }}</h2>
                </div>
            </div>
        </div>

        <!-- Card 2: {{ __('SESUAI') }} -->
        <div class="bg-white rounded-2xl p-4 shadow-sm hover:shadow-[0_8px_30px_rgba(0,0,0,0.04)] hover:-translate-y-1 border border-[#009B77]/40 flex flex-col justify-between transition-all duration-300 group cursor-pointer report-card relative overflow-hidden">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-teal-500/5 rounded-full blur-2xl group-hover:bg-teal-500/10 transition-colors"></div>
            <div class="flex items-center justify-between mb-3 relative z-10">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('SESUAI') }}</h3>
                <div class="w-8 h-8 rounded-lg bg-teal-50 group-hover:bg-teal-100 group-hover:scale-110 transition-all duration-300 flex items-center justify-center text-teal-600">
                    <i class="ph-bold ph-check-circle text-lg"></i>
                </div>
            </div>
            <div class="relative z-10">
                <div class="flex items-end justify-between mb-2">
                    <h2 id="stat-kondisiBaik" class="text-2xl font-extrabold text-slate-800 transition-colors group-hover:text-teal-600">{{ $kondisiBaik }}</h2>
                    <span id="stat-kondisiBaikPercentage" class="text-xs font-bold text-teal-600 mb-1.5">{{ $totalApar > 0 ? round(($kondisiBaik / $totalApar) * 100) : 0 }}%</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                    <div id="stat-kondisiBaikBar" class="bg-teal-500 h-1.5 rounded-full transition-all duration-1000" style="width: {{ $totalApar > 0 ? round(($kondisiBaik / $totalApar) * 100) : 0 }}%"></div>
                </div>
            </div>
        </div>

        <!-- Card 3: {{ __('TIDAK SESUAI') }} -->
        <div class="bg-white rounded-2xl p-4 shadow-sm hover:shadow-[0_8px_30px_rgba(0,0,0,0.04)] hover:-translate-y-1 border border-[#009B77]/40 flex flex-col justify-between transition-all duration-300 group cursor-pointer report-card relative overflow-hidden">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-red-500/5 rounded-full blur-2xl group-hover:bg-red-500/10 transition-colors"></div>
            <div class="flex items-center justify-between mb-3 relative z-10">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('TIDAK SESUAI') }}</h3>
                <div class="w-8 h-8 rounded-lg bg-red-50 group-hover:bg-red-100 group-hover:scale-110 transition-all duration-300 flex items-center justify-center text-red-500">
                    <i class="ph-bold ph-warning-circle text-lg"></i>
                </div>
            </div>
            <div class="flex items-end justify-between relative z-10">
                <h2 id="stat-rusakServis" class="text-2xl font-extrabold text-red-500 transition-transform group-hover:scale-105 origin-left">{{ $rusakServis }}</h2>
                @if($rusakServis > 0)
                <span class="inline-flex items-center gap-1 text-[10px] font-bold text-red-600 bg-red-50 border border-red-100 px-2 py-1 rounded-lg mb-1.5 uppercase tracking-wider">
                    <i class="ph-bold ph-warning"></i> {{ __('Immediate') }}
                </span>
                @endif
            </div>
        </div>

        <!-- Card 4: {{ __('EXPIRING SOON') }} -->
        <div class="bg-white rounded-2xl p-4 shadow-sm hover:shadow-[0_8px_30px_rgba(0,0,0,0.04)] hover:-translate-y-1 border border-[#009B77]/40 flex flex-col justify-between transition-all duration-300 group cursor-pointer report-card relative overflow-hidden">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-amber-500/5 rounded-full blur-2xl group-hover:bg-amber-500/10 transition-colors"></div>
            <div class="flex items-center justify-between mb-3 relative z-10">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('EXPIRING SOON') }}</h3>
                <div class="w-8 h-8 rounded-lg bg-amber-50 group-hover:bg-amber-100 group-hover:scale-110 transition-all duration-300 flex items-center justify-center text-amber-500">
                    <i class="ph-bold ph-hourglass-high text-lg"></i>
                </div>
            </div>
            <div class="flex items-end justify-between relative z-10">
                <h2 id="stat-akanKedaluwarsa" class="text-2xl font-extrabold text-amber-500 transition-transform group-hover:scale-105 origin-left">{{ $akanKedaluwarsa }}</h2>
                @if($akanKedaluwarsa > 0)
                <span class="inline-flex items-center gap-1 text-[10px] font-bold text-amber-600 bg-amber-50 border border-amber-100 px-2 py-1 rounded-lg mb-1.5 uppercase tracking-wider">
                    <i class="ph-bold ph-calendar-blank"></i> &lt; 30 {{ __('days') }}
                </span>
                @endif
            </div>
        </div>

        <!-- Card 5: Sudah {{ __('EXPIRED') }} -->
        <div class="bg-white rounded-2xl p-4 shadow-sm hover:shadow-[0_8px_30px_rgba(0,0,0,0.04)] hover:-translate-y-1 border border-[#009B77]/40 flex flex-col justify-between transition-all duration-300 group cursor-pointer report-card relative overflow-hidden">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-red-600/5 rounded-full blur-2xl group-hover:bg-red-600/10 transition-colors"></div>
            <div class="flex items-center justify-between mb-3 relative z-10">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('EXPIRED') }}</h3>
                <div class="w-8 h-8 rounded-lg bg-red-50 group-hover:bg-red-100 group-hover:scale-110 transition-all duration-300 flex items-center justify-center text-red-600">
                    <i class="ph-bold ph-x-circle text-lg"></i>
                </div>
            </div>
            <div class="flex items-end justify-between relative z-10">
                <h2 id="stat-sudahKedaluwarsa" class="text-2xl font-extrabold text-red-600 transition-transform group-hover:scale-105 origin-left">{{ $sudahKedaluwarsa }}</h2>
                @if($sudahKedaluwarsa > 0)
                <span class="inline-flex items-center gap-1 text-[10px] font-bold text-red-700 bg-red-50 border border-red-100 px-2 py-1 rounded-lg mb-1.5 uppercase tracking-wider">
                    <i class="ph-bold ph-warning"></i> {{ __('Danger') }}
                </span>
                @endif
            </div>
        </div>

        <!-- Card 6: {{ __('PERLU REFILL') }} -->
        <a href="{{ route('master-data.index', ['qty_status' => 'empty']) }}" class="bg-white rounded-2xl p-4 shadow-sm hover:shadow-[0_8px_30px_rgba(0,0,0,0.04)] hover:-translate-y-1 border border-[#009B77]/40 flex flex-col justify-between transition-all duration-300 group cursor-pointer report-card relative overflow-hidden block text-left">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-amber-500/5 rounded-full blur-2xl group-hover:bg-amber-500/10 transition-colors"></div>
            <div class="flex items-center justify-between mb-3 relative z-10">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('PERLU REFILL') }}</h3>
                <div class="w-8 h-8 rounded-lg bg-amber-50 group-hover:bg-amber-100 group-hover:scale-110 transition-all duration-300 flex items-center justify-center text-amber-500">
                    <i class="ph-bold ph-arrows-clockwise text-lg"></i>
                </div>
            </div>
            <div class="flex items-end justify-between relative z-10">
                <h2 id="stat-aparKosong" class="text-2xl font-extrabold text-amber-500 transition-transform group-hover:scale-105 origin-left">{{ $aparKosong }}</h2>
                @if($aparKosong > 0)
                <span class="inline-flex items-center gap-1 text-[10px] font-bold text-amber-600 bg-amber-50 border border-amber-100 px-2 py-1 rounded-lg mb-1.5 uppercase tracking-wider">
                    <i class="ph-bold ph-arrows-clockwise"></i> {{ __('Refill') }}
                </span>
                @endif
            </div>
        </a>

    </div>

    <!-- Progress Inspeksi Section -->
    <div class="bg-[#009B77] rounded-2xl shadow-[0_8px_30px_rgba(0,155,119,0.2)] mb-4 overflow-hidden" x-data="{ tab: null }">
        <div class="p-4 grid grid-cols-1 md:grid-cols-12 gap-5 items-center border-b border-[#008264]">
            <!-- Left: Pie Chart -->
            <div class="md:col-span-3 lg:col-span-3 flex justify-center">
                <div id="progressPieChart" class="w-full max-w-[160px]"></div>
            </div>
            
            <!-- Middle: Info & Tabs -->
            <div class="md:col-span-6 lg:col-span-6">
                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 mb-4">
                    <div>
                        <h3 class="text-lg font-extrabold text-white mb-0.5">{{ __('Inspection Progress for') }} {{ __(\Carbon\Carbon::createFromDate($selectedYear, $selectedMonth, 1)->format('F')) }} {{ $selectedYear }}</h3>
                        <p class="text-[13px] font-medium text-emerald-100">{{ __('Monitor monthly inspection task completion') }}</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-5 mb-5">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center text-white">
                            <i class="ph-bold ph-check-circle text-lg"></i>
                        </div>
                        <div>
                            <p class="text-[9px] font-bold text-emerald-100 uppercase tracking-wider">{{ __('INSPECTED') }}</p>
                            <p class="text-lg font-extrabold text-white"><span id="stat-sudahDiinspeksi">{{ $sudahDiinspeksiBulanIni }}</span> <span class="text-xs font-semibold text-emerald-100">APAR</span></p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-emerald-100">
                            <i class="ph-bold ph-clock text-lg"></i>
                        </div>
                        <div>
                            <p class="text-[9px] font-bold text-emerald-100 uppercase tracking-wider">{{ __('UNINSPECTED') }}</p>
                            <p class="text-lg font-extrabold text-white"><span id="stat-belumDiinspeksi">{{ $belumDiinspeksiBulanIni }}</span> <span class="text-xs font-semibold text-emerald-100">APAR</span></p>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="flex space-x-2 w-full max-w-sm print:hidden">
                    <button @click="tab = tab === 'belum' ? null : 'belum'" :class="tab === 'belum' ? 'bg-white text-[#009B77] shadow-md' : 'bg-white/10 border border-white/20 text-white hover:bg-white/20'" class="flex-1 py-2 px-3 text-xs font-bold rounded-xl transition-all outline-none flex items-center justify-center gap-2">
                        {{ __('Uninspected List') }}
                        <i class="ph-bold transition-transform duration-300" :class="tab === 'belum' ? 'ph-caret-up' : 'ph-caret-down'"></i>
                    </button>
                    <button @click="tab = tab === 'sudah' ? null : 'sudah'" :class="tab === 'sudah' ? 'bg-white text-[#009B77] shadow-md' : 'bg-white/10 border border-white/20 text-white hover:bg-white/20'" class="flex-1 py-2 px-3 text-xs font-bold rounded-xl transition-all outline-none flex items-center justify-center gap-2">
                        {{ __('Inspected List') }}
                        <i class="ph-bold transition-transform duration-300" :class="tab === 'sudah' ? 'ph-caret-up' : 'ph-caret-down'"></i>
                    </button>
                </div>
            </div>

            <!-- Right: Sisa Waktu Box -->
            <div class="md:col-span-3 lg:col-span-3 flex h-full items-center">
                @php
                    $now = \Carbon\Carbon::now();
                    $endOfMonth = \Carbon\Carbon::createFromDate($selectedYear, $selectedMonth, 1)->endOfMonth();
                    $isCurrentMonth = $now->format('Y-m') === $endOfMonth->format('Y-m');
                    
                    if ($isCurrentMonth) {
                        $daysLeft = intval(floor($now->diffInDays($endOfMonth, false)));
                    } elseif ($endOfMonth->isPast()) {
                        $daysLeft = 0;
                    } else {
                        $daysLeft = $endOfMonth->daysInMonth;
                    }
                @endphp
                
                <div class="bg-transparent rounded-2xl p-5 border-2 border-white/50 w-full min-h-[140px] flex flex-col justify-center relative overflow-hidden transition-colors duration-300 hover:bg-white/5">
                    <div class="flex items-center justify-between mb-3 relative z-10">
                        <h3 class="text-[11px] font-bold text-emerald-100 uppercase tracking-wider">{{ __('MONTHLY INSPECTION DEADLINE') }}</h3>
                        <div class="w-10 h-10 rounded-xl bg-white/10 border border-white/20 text-white flex items-center justify-center">
                            <i class="ph-bold ph-hourglass-high text-xl"></i>
                        </div>
                    </div>
                    
                    <div class="relative z-10">
                        @if($isCurrentMonth && $daysLeft > 0)
                            <div class="flex items-baseline gap-1">
                                <span class="text-5xl font-extrabold text-white tracking-tight">{{ $daysLeft }}</span>
                                <span class="text-sm font-semibold text-emerald-100">{{ __('Days') }}</span>
                            </div>
                        @elseif($isCurrentMonth && $daysLeft === 0)
                            <div class="flex items-center gap-1.5">
                                <span class="text-2xl font-extrabold text-white">{{ __('Last Day!') }}</span>
                                <i class="ph-fill ph-warning-circle text-rose-400 text-2xl"></i>
                            </div>
                        @elseif($endOfMonth->isPast())
                            <span class="text-xl font-extrabold text-emerald-100">{{ __('Month Ended') }}</span>
                        @else
                            <span class="text-xl font-extrabold text-emerald-100">{{ __('Coming Soon') }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="p-4 md:p-6 bg-slate-50 print:hidden" x-show="tab !== null" x-cloak>

        <!-- Table {{ __('UNINSPECTED') }} -->
        <div x-show="tab === 'belum'" class="overflow-x-auto bg-white rounded-xl border border-slate-200 mb-4" id="table-uninspected-container">
            @include('dashboard.partials.uninspected-table')
        </div>

        <!-- Table {{ __('INSPECTED') }} -->
        <div x-show="tab === 'sudah'" class="overflow-x-auto bg-white rounded-xl border border-slate-200 mb-4" id="table-inspected-container">
            @include('dashboard.partials.inspected-table')
        </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 items-stretch">
        <!-- Monthly Inspections Chart -->
        <div class="lg:col-span-2 print-col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200/60 p-5 flex flex-col justify-between h-full chart-container">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-800 leading-tight">{{ __('Inspection Trend Year') }} {{ $selectedYear }}</h3>
                    <p class="text-xs font-semibold text-slate-500 mt-1">{{ __('Comparison of INSPECTED and UNINSPECTED PFE every month') }}</p>
                </div>
                <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-500 flex-shrink-0">
                    <i class="ph-bold ph-chart-bar text-lg"></i>
                </div>
            </div>
            <div class="flex-1 w-full relative min-h-[250px] flex items-center justify-center">
                <div id="monthlyChart" class="w-full"></div>
            </div>
        </div>

        <!-- APAR by Type Donut Chart -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-5 flex flex-col justify-between h-full chart-container">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-800 leading-tight">{{ __('PFE Type Distribution') }}</h3>
                    <p class="text-xs font-semibold text-slate-500 mt-1">{{ __('Based on media / agent') }}</p>
                </div>
                <div class="w-8 h-8 rounded-lg bg-purple-50 flex items-center justify-center text-purple-500 flex-shrink-0">
                    <i class="ph-bold ph-chart-pie-slice text-lg"></i>
                </div>
            </div>
            <div class="flex-1 w-full relative min-h-[250px] flex items-center justify-center">
                <div id="typeChart" class="w-full"></div>
            </div>
        </div>
    </div>

    <!-- Recent Inspections Table Section -->
    <div id="recentInspectionsTable" class="bg-white rounded-2xl shadow-sm border border-slate-200/60 overflow-hidden">
        <div class="p-6 bg-[#009B77] border-b border-white/20 flex items-center justify-between">
            <div class="flex items-center gap-3 text-white">
                <div class="w-8 h-8 rounded-lg bg-white/20 backdrop-blur flex items-center justify-center border border-white/30">
                    <i class="ph-bold ph-clipboard-text text-lg"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold">{{ __('Recent Inspections') }}</h3>
                    <p class="text-xs font-medium text-white/80 mt-1">{{ __('History of the 5 most recent inspections') }}</p>
                </div>
            </div>
        </div>


        <div class="overflow-x-auto">
            
        </div>
        
        <div class="overflow-x-auto" id="table-recent-container">
            @include('dashboard.partials.recent-inspections')
        </div>
    </div>
</div>

<!-- Script to initialize ApexCharts -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        
        // Data for Monthly Inspections Chart
        const yearlyData = @json($yearlyInspections);
        const monthCategories = yearlyData.map(item => item.month);
        const monthSudah = yearlyData.map(item => item.sudah);
        const monthBelum = yearlyData.map(item => item.belum);

        const monthlyOptions = {
            series: [{
                name: '{{ __('INSPECTED') }}',
                data: monthSudah
            }, {
                name: '{{ __('UNINSPECTED') }}',
                data: monthBelum
            }],
            chart: {
                type: 'bar',
                height: 240,
                fontFamily: 'inherit',
                toolbar: { show: false },
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 800,
                    animateGradually: {
                        enabled: true,
                        delay: 150
                    },
                    dynamicAnimation: {
                        enabled: true,
                        speed: 350
                    }
                }
            },
            colors: ['#009B77', '#F59E0B'],
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '55%',
                    borderRadius: 4
                },
            },
            dataLabels: { enabled: false },
            stroke: {
                show: true,
                width: 2,
                colors: ['transparent']
            },
            xaxis: {
                categories: monthCategories,
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: {
                    style: {
                        colors: '#64748b',
                        fontWeight: 600,
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: '#64748b',
                        fontWeight: 600,
                    }
                }
            },
            grid: {
                borderColor: '#cbd5e1',
                strokeDashArray: 4,
                yaxis: { lines: { show: true } },
                padding: { top: 0, right: 0, bottom: 0, left: 10 }
            },
            legend: {
                position: 'top',
                horizontalAlign: 'right',
                markers: { radius: 12 }
            },
            fill: {
                opacity: 1
            },
            tooltip: {
                theme: 'light',
                y: { formatter: function (val) { return val + " APAR" } }
            }
        };

        if(document.getElementById("monthlyChart") && monthCategories.length > 0) {
            window.monthlyChart = new ApexCharts(document.querySelector("#monthlyChart"), monthlyOptions);
            window.monthlyChart.render();
        } else if (document.getElementById("monthlyChart")) {
            document.getElementById("monthlyChart").innerHTML = '<div class="flex items-center justify-center h-full text-slate-400 font-semibold text-sm">{{ __('No chart data available') }}</div>';
        }

        // Data for APAR Types Donut Chart
        const typesData = @json($jenisData);
        const typeLabels = Object.keys(typesData);
        const typeSeries = Object.values(typesData);
        
        // Elegant Color Palette
        const colors = ['#009B77', '#8b5cf6', '#f59e0b', '#3b82f6', '#ef4444', '#14b8a6'];

        const typeOptions = {
            series: typeSeries,
            chart: {
                type: 'pie',
                height: 240,
                fontFamily: 'inherit',
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 800,
                    animateGradually: {
                        enabled: true,
                        delay: 150
                    },
                    dynamicAnimation: {
                        enabled: true,
                        speed: 350
                    }
                }
            },
            labels: typeLabels,
            colors: colors,
            plotOptions: {
                pie: {
                    expandOnClick: false
                }
            },
            dataLabels: { enabled: false },
            stroke: {
                show: true,
                colors: '#ffffff',
                width: 3
            },
            states: {
                hover: {
                    filter: {
                        type: 'lighten',
                        value: 0.15
                    }
                },
                active: {
                    filter: {
                        type: 'none'
                    }
                }
            },
            legend: {
                position: 'bottom',
                horizontalAlign: 'center',
                markers: { radius: 12 },
                itemMargin: { horizontal: 10, vertical: 5 },
                formatter: function(seriesName, opts) {
                    return ["<span style='font-weight: 600; color: #475569;'>", seriesName, "</span> <span style='font-weight: 800; color: #1e293b; margin-left: 4px;'>", opts.w.globals.seriesTotals[opts.seriesIndex], "</span>"].join('')
                }
            },
            tooltip: {
                theme: 'light',
                fillSeriesColor: false
            }
        };

        if(document.getElementById("typeChart") && typeSeries.length > 0) {
            window.typeChart = new ApexCharts(document.querySelector("#typeChart"), typeOptions);
            window.typeChart.render();
        } else if (document.getElementById("typeChart")) {
            document.getElementById("typeChart").innerHTML = '<div class="flex items-center justify-center h-full text-slate-400 font-semibold text-sm">{{ __('No PFE data available') }}</div>';
        }

        // Data for Progress Donut Chart
        const progressSudah = {{ $sudahDiinspeksiBulanIni }};
        const progressBelum = {{ $belumDiinspeksiBulanIni }};
        const totalProgress = progressSudah + progressBelum;
        const progressPercentage = totalProgress > 0 ? Math.round((progressSudah / totalProgress) * 100) : 0;

        const progressOptions = {
            series: [progressSudah, progressBelum],
            chart: {
                type: 'donut',
                height: 160,
                fontFamily: 'inherit',
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 800,
                    animateGradually: {
                        enabled: true,
                        delay: 150
                    },
                    dynamicAnimation: {
                        enabled: true,
                        speed: 350
                    }
                }
            },
            labels: ['{{ __('INSPECTED') }}', '{{ __('UNINSPECTED') }}'],
            colors: ['#ffffff', '#a7f3d0'],
            plotOptions: {
                pie: {
                    expandOnClick: false,
                    donut: {
                        size: '75%',
                        labels: {
                            show: true,
                            name: {
                                show: true,
                                fontSize: '10px',
                                fontWeight: 700,
                                color: '#a7f3d0',
                                offsetY: -5
                            },
                            value: {
                                show: true,
                                fontSize: '22px',
                                fontWeight: 800,
                                color: '#ffffff',
                                offsetY: 5,
                                formatter: function (val) {
                                    return progressPercentage + "%";
                                }
                            },
                            total: {
                                show: true,
                                showAlways: true,
                                label: '{{ __('PROGRESS') }}',
                                fontSize: '10px',
                                fontWeight: 700,
                                color: '#a7f3d0',
                                formatter: function (w) {
                                    return progressPercentage + "%";
                                }
                            }
                        }
                    }
                }
            },
            dataLabels: { enabled: false },
            stroke: {
                show: true,
                colors: ['#009B77'],
                width: 2.5
            },
            states: {
                hover: {
                    filter: {
                        type: 'lighten',
                        value: 0.2
                    }
                },
                active: {
                    allowMultipleDataPointsSelection: false,
                    filter: {
                        type: 'none'
                    }
                }
            },
            legend: { show: false },
            tooltip: {
                enabled: true,
                theme: 'dark',
                custom: function({ series, seriesIndex, w }) {
                    const label = w.globals.labels[seriesIndex];
                    const val = series[seriesIndex];
                    const percent = totalProgress > 0 ? Math.round((val / totalProgress) * 100) : 0;
                    const isInspected = seriesIndex === 0;
                    const dotColor = isInspected ? '#10b981' : '#38bdf8';
                    
                    return '<div style="background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); border: 1px solid rgba(255, 255, 255, 0.18); border-radius: 12px; padding: 7px 12px; box-shadow: 0 12px 30px -4px rgba(0,0,0,0.4); display: flex; align-items: center; gap: 8px; font-family: inherit; pointer-events: none;">' +
                        '<span style="width: 8px; height: 8px; border-radius: 9999px; background: ' + dotColor + '; box-shadow: 0 0 8px ' + dotColor + ';"></span>' +
                        '<span style="font-weight: 700; color: #e2e8f0; font-size: 11px;">' + label + ':</span>' +
                        '<span style="font-weight: 800; color: #ffffff; font-size: 12px;">' + val + ' <span style="font-size: 10px; font-weight: 600; opacity: 0.85;">APAR</span></span>' +
                        '<span style="font-size: 10px; font-weight: 800; padding: 2px 6px; border-radius: 6px; background: rgba(255,255,255,0.12); color: #a7f3d0; margin-left: 2px;">' + percent + '%</span>' +
                    '</div>';
                }
            }
        };

        if(document.getElementById("progressPieChart")) {
            window.progressChart = new ApexCharts(document.querySelector("#progressPieChart"), progressOptions);
            window.progressChart.render();
        }

        // --- Animation Helpers ---
        function animateValue(obj, start, end, duration) {
            let startTimestamp = null;
            const step = (timestamp) => {
                if (!startTimestamp) startTimestamp = timestamp;
                const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                const easeOutQuart = 1 - Math.pow(1 - progress, 4);
                obj.innerHTML = Math.floor(easeOutQuart * (end - start) + start);
                if (progress < 1) {
                    window.requestAnimationFrame(step);
                } else {
                    obj.innerHTML = end;
                }
            };
            window.requestAnimationFrame(step);
        }

        function updateStatWithAnimation(id, newValue, isPercentage = false) {
            const el = document.getElementById(id);
            if (!el) return;
            const currentText = el.innerText.replace('%', '');
            const currentValue = parseInt(currentText) || 0;
            
            if (currentValue !== newValue) {
                // Animate Number
                let startTimestamp = null;
                const duration = 1200; // 1.2s animation
                const step = (timestamp) => {
                    if (!startTimestamp) startTimestamp = timestamp;
                    const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                    const easeOutQuart = 1 - Math.pow(1 - progress, 4);
                    let val = Math.floor(easeOutQuart * (newValue - currentValue) + currentValue);
                    el.innerHTML = val + (isPercentage ? '%' : '');
                    if (progress < 1) {
                        window.requestAnimationFrame(step);
                    } else {
                        el.innerHTML = newValue + (isPercentage ? '%' : '');
                    }
                };
                window.requestAnimationFrame(step);

                // Add Highlight Pop Effect to the Card
                const card = el.closest('.report-card') || el.closest('.bg-white') || el.parentElement;
                if(card) {
                    card.classList.remove('animate-pop-highlight');
                    void card.offsetWidth; // trigger reflow
                    card.classList.add('animate-pop-highlight');
                }
            }
        }

        function updateTableWithAnimation(id, newHtml) {
            const container = document.getElementById(id);
            if (!container) return;
            if (container.innerHTML.trim() !== newHtml.trim()) {
                // Fade out
                container.style.transition = 'opacity 0.3s ease';
                container.style.opacity = '0';
                setTimeout(() => {
                    container.innerHTML = newHtml;
                    // Fade in
                    container.style.opacity = '1';
                    
                    // Add a subtle flash effect to the table rows
                    const rows = container.querySelectorAll('tbody tr');
                    rows.forEach(row => {
                        row.classList.add('bg-emerald-50/50');
                        setTimeout(() => row.classList.remove('bg-emerald-50/50'), 2000);
                    });
                }, 300);
            }
        }

        // Start polling for real-time updates every 15 seconds
        setInterval(function() {
            const urlParams = new URLSearchParams(window.location.search);
            const month = urlParams.get('month') || '{{ $selectedMonth }}';
            const year = urlParams.get('year') || '{{ $selectedYear }}';
            
            fetch(`/dashboard?month=${month}&year=${year}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                // Update stats with super cool animations
                updateStatWithAnimation('stat-totalApar', data.stats.totalApar);
                updateStatWithAnimation('stat-totalStock', data.stats.totalStock);
                updateStatWithAnimation('stat-kondisiBaik', data.stats.kondisiBaik);
                updateStatWithAnimation('stat-kondisiBaikPercentage', data.stats.kondisiBaikPercentage, true);
                
                const bar = document.getElementById('stat-kondisiBaikBar');
                if(bar) bar.style.width = data.stats.kondisiBaikPercentage + '%';
                
                updateStatWithAnimation('stat-rusakServis', data.stats.rusakServis);
                updateStatWithAnimation('stat-akanKedaluwarsa', data.stats.akanKedaluwarsa);
                updateStatWithAnimation('stat-sudahKedaluwarsa', data.stats.sudahKedaluwarsa);
                updateStatWithAnimation('stat-aparKosong', data.stats.aparKosong);
                updateStatWithAnimation('stat-sudahDiinspeksi', data.stats.sudahDiinspeksiBulanIni);
                updateStatWithAnimation('stat-belumDiinspeksi', data.stats.belumDiinspeksiBulanIni);
                
                // Update Progress Donut Chart
                const totalProgress = data.stats.sudahDiinspeksiBulanIni + data.stats.belumDiinspeksiBulanIni;
                const progPct = totalProgress > 0 ? Math.round((data.stats.sudahDiinspeksiBulanIni / totalProgress) * 100) : 0;
                
                if (window.progressChart) {
                    window.progressChart.updateSeries([data.stats.sudahDiinspeksiBulanIni, data.stats.belumDiinspeksiBulanIni]);
                    window.progressChart.updateOptions({
                        plotOptions: {
                            pie: { donut: { labels: { 
                                value: { formatter: function() { return progPct + "%"; } },
                                total: { formatter: function() { return progPct + "%"; } }
                            }}}
                        }
                    });
                }
                
                // Update Monthly Chart
                if (window.monthlyChart) {
                    const monthSudah = data.charts.yearlyInspections.map(item => item.sudah);
                    const monthBelum = data.charts.yearlyInspections.map(item => item.belum);
                    window.monthlyChart.updateSeries([
                        { name: '{{ __('INSPECTED') }}', data: monthSudah },
                        { name: '{{ __('UNINSPECTED') }}', data: monthBelum }
                    ]);
                }
                
                // Update Types Chart
                if (window.typeChart) {
                    window.typeChart.updateSeries(Object.values(data.charts.jenisData));
                    window.typeChart.updateOptions({ labels: Object.keys(data.charts.jenisData) });
                }
                
                // Update Tables with cross-fade animation
                updateTableWithAnimation('table-recent-container', data.html.recentInspections);
                updateTableWithAnimation('table-uninspected-container', data.html.uninspectedTable);
                updateTableWithAnimation('table-inspected-container', data.html.inspectedTable);
            })
            .catch(error => console.error('Error fetching real-time dashboard data:', error));
        }, 15000);
    });
</script>
@endsection

