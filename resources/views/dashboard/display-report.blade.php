@extends('layouts.display')

@section('title', 'Dashboard Overview - APAR Monitoring System')

@section('content')
<style>
@media print {
    @page { size: landscape; margin: 10mm; }
    html, body { 
        background: white !important; 
        -webkit-print-color-adjust: exact !important; 
        print-color-adjust: exact !important; 
        height: auto !important;
        overflow: visible !important;
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
    .space-y-4 { margin: 0 !important; padding: 0 !important; zoom: 0.85; transform-origin: top center; display: flex; flex-direction: column; gap: 1rem; }
    
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

<div class="flex flex-col flex-1 h-full gap-3">
    
    <!-- Header Area -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 print:hidden bg-[#009B77]/10 p-3 rounded-2xl border border-[#009B77]/20">
        <div class="flex items-center gap-3 px-1">
            <div class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center text-[#009B77]">
                <i class="ph-bold ph-calendar-blank text-xl"></i>
            </div>
            <div>
                <h2 class="text-sm font-bold text-emerald-900">{{ __('Today\'s Statistics') }}</h2>
                <p class="text-[11px] font-semibold text-emerald-700">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</p>
            </div>
        </div>

        @if(isset($guestDisplay))
        <div class="flex items-center gap-2 px-1">
            <div class="flex items-center gap-2 bg-white/80 backdrop-blur-md px-3 py-1.5 rounded-xl border border-emerald-200 shadow-sm text-xs text-slate-700">
                <i class="ph-bold ph-identification-badge text-[#009B77] text-base"></i>
                <span class="font-bold text-slate-800">{{ $guestDisplay->name }}</span>
                <span class="text-slate-500 font-medium hidden sm:inline">({{ $guestDisplay->institution }})</span>
                <span class="text-[11px] text-emerald-700 font-semibold bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">
                    Aktif s.d. {{ \Carbon\Carbon::parse($guestDisplay->valid_until)->translatedFormat('d M Y') }}
                </span>
            </div>
        </div>
        @endif
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
                    <h2 id="stat-totalStock" class="text-2xl font-extrabold text-amber-300 group-hover:text-amber-200 transition-colors">{{ $totalStock ?? 0 }}</h2>
                </div>
            </div>
        </div>

        <!-- Card 2: {{ __('GOOD CONDITION') }} -->
        <div class="bg-white rounded-2xl p-4 shadow-sm hover:shadow-[0_8px_30px_rgba(0,0,0,0.04)] hover:-translate-y-1 border border-[#009B77]/40 flex flex-col justify-between transition-all duration-300 group cursor-pointer report-card relative overflow-hidden">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-teal-500/5 rounded-full blur-2xl group-hover:bg-teal-500/10 transition-colors"></div>
            <div class="flex items-center justify-between mb-3 relative z-10">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('GOOD CONDITION') }}</h3>
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

        <!-- Card 3: {{ __('BROKEN / SERVICE') }} -->
        <div class="bg-white rounded-2xl p-4 shadow-sm hover:shadow-[0_8px_30px_rgba(0,0,0,0.04)] hover:-translate-y-1 border border-[#009B77]/40 flex flex-col justify-between transition-all duration-300 group cursor-pointer report-card relative overflow-hidden">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-red-500/5 rounded-full blur-2xl group-hover:bg-red-500/10 transition-colors"></div>
            <div class="flex items-center justify-between mb-3 relative z-10">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('BROKEN / SERVICE') }}</h3>
                <div class="w-8 h-8 rounded-lg bg-red-50 group-hover:bg-red-100 group-hover:scale-110 transition-all duration-300 flex items-center justify-center text-red-500">
                    <i class="ph-fill ph-wrench text-lg"></i>
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
                    <i class="ph-bold ph-calendar-blank"></i> &lt; 30 hr
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

        <!-- Card 6: {{ __('EMPTY PFE') }} -->
        <a href="{{ route('master-data.index', ['qty_status' => 'empty']) }}" class="bg-white rounded-2xl p-4 shadow-sm hover:shadow-[0_8px_30px_rgba(0,0,0,0.04)] hover:-translate-y-1 border border-[#009B77]/40 flex flex-col justify-between transition-all duration-300 group cursor-pointer report-card relative overflow-hidden block text-left">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-rose-500/5 rounded-full blur-2xl group-hover:bg-rose-500/10 transition-colors"></div>
            <div class="flex items-center justify-between mb-3 relative z-10">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('EMPTY PFE') }}</h3>
                <div class="w-8 h-8 rounded-lg bg-rose-50 group-hover:bg-rose-100 group-hover:scale-110 transition-all duration-300 flex items-center justify-center text-rose-500">
                    <i class="ph-bold ph-warning-octagon text-lg"></i>
                </div>
            </div>
            <div class="flex items-end justify-between relative z-10">
                <h2 id="stat-aparKosong" class="text-2xl font-extrabold text-rose-500 transition-transform group-hover:scale-105 origin-left">{{ $aparKosong }}</h2>
                @if($aparKosong > 0)
                <span class="inline-flex items-center gap-1 text-[10px] font-bold text-rose-600 bg-rose-50 border border-rose-100 px-2 py-1 rounded-lg mb-1.5 uppercase tracking-wider">
                    <i class="ph-bold ph-warning"></i> {{ __('Empty') }}
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

                <!-- Tabs removed for Display mode -->
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
                            <span class="text-2xl font-extrabold text-rose-200">{{ __('Days') }} {{ __('Last!') }}</span>
                        @elseif($endOfMonth->isPast())
                            <span class="text-xl font-extrabold text-emerald-100">{{ __('Month Ended') }}</span>
                        @else
                            <span class="text-xl font-extrabold text-emerald-100">{{ __('Coming Soon') }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Tables removed for Display mode -->
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 flex-1 min-h-0 pb-1">
        <!-- Monthly Inspections Chart -->
        <div class="lg:col-span-2 print-col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200/60 p-4 flex flex-col h-full chart-container">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h3 class="text-lg font-bold text-slate-800">{{ __('Inspection Trend Year') }} {{ $selectedYear }}</h3>
                    <p class="text-xs text-slate-500 mt-1">{{ __('Comparison of INSPECTED and UNINSPECTED PFE every month') }}</p>
                </div>
                <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-500">
                    <i class="ph-bold ph-chart-bar text-lg"></i>
                </div>
            </div>
            <div class="flex-1 w-full relative min-h-0">
                <div id="monthlyChart" class="absolute inset-0"></div>
            </div>
        </div>

        <!-- APAR by Type Donut Chart -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-4 flex flex-col h-full chart-container">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h3 class="text-lg font-bold text-slate-800">{{ __('PFE Type Distribution') }}</h3>
                    <p class="text-xs font-semibold text-slate-500">{{ __('Based on media / agent') }}</p>
                </div>
                <div class="w-8 h-8 rounded-lg bg-purple-50 flex items-center justify-center text-purple-500">
                    <i class="ph-bold ph-chart-pie-slice text-lg"></i>
                </div>
            </div>
            <div class="flex-1 w-full relative min-h-0 flex items-center justify-center">
                <div id="typeChart" class="w-full absolute inset-0 flex items-center justify-center"></div>
            </div>
        </div>
    </div>

        <!-- Recent Inspections Table Section Removed for Display mode -->
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
                height: '100%',
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
                height: '100%',
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
            dataLabels: { enabled: false },
            stroke: {
                show: true,
                colors: '#ffffff',
                width: 3
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
                show: false,
                width: 0
            },
            legend: { show: false },
            tooltip: {
                theme: 'light',
                fillSeriesColor: false,
                y: {
                    formatter: function (val) {
                        return val + " APAR"
                    }
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
            
            @if(isset($guestDisplay))
            const pollingUrl = `{{ route('display.access.view', $guestDisplay->access_token) }}?month=${month}&year=${year}`;
            @else
            const pollingUrl = `/dashboard?month=${month}&year=${year}`;
            @endif

            fetch(pollingUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (response.status === 401 || response.status === 403) {
                    window.location.href = '{{ isset($guestDisplay) ? route("display.access", $guestDisplay->access_token) : route("login") }}';
                    return null;
                }
                return response.json();
            })
            .then(data => {
                if (!data) return;
                if (data.redirect) {
                    window.location.href = data.redirect;
                    return;
                }

                // Update stats with super cool animations
                updateStatWithAnimation('stat-totalApar', data.stats.totalApar);
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

