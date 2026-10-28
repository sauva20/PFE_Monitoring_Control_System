@php
    if (isset($jadwal->custom_apars)) {
        $apars = $jadwal->custom_apars;
    } elseif ($jadwal->tipe_area === 'gedung' && $jadwal->gedung) {
        $apars = collect();
        foreach($jadwal->gedung->lokasi as $l) {
            foreach($l->apar as $a) {
                $apars->push($a);
            }
        }
    } elseif ($jadwal->tipe_area === 'lokasi' && $jadwal->lokasi) {
        $apars = collect();
        foreach($jadwal->lokasi->apar as $a) {
            $apars->push($a);
        }
    } else {
        $apars = collect();
    }
    $hasMultipleApars = $apars->count() > 1;
@endphp
<div x-data="{ expanded: false }" class="bg-white rounded-2xl border border-slate-200 shadow-sm transition-all hover:shadow-md {{ $isTerlewat ? 'ring-1 ring-red-100' : '' }}">
    <div class="p-5 flex items-start justify-between gap-4 {{ $hasMultipleApars ? 'cursor-pointer' : '' }}" @click="{{ $hasMultipleApars ? 'expanded = !expanded' : '' }}">
        <div class="flex gap-4">
            <div class="w-12 h-12 rounded-xl {{ $isTerlewat ? 'bg-red-50 border-red-200 text-red-500' : 'bg-[#009B77]/5 border-[#009B77]/20 text-[#009B77]' }} border flex items-center justify-center flex-shrink-0">
                <i class="ph-duotone ph-buildings text-2xl" title="{{ __('Inspect Building') }}"></i>
            </div>
            <div>
                <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 tracking-wider uppercase">{{ __($jadwal->jenis_jadwal) }}</span>
                    
                    @php
                        $jadwalDate = \Carbon\Carbon::parse($jadwal->tanggal_inspeksi)->startOfDay();
                        $today = \Carbon\Carbon::today();
                        
                        $isHariIni = $jadwalDate->equalTo($today);
                        
                        $dateBg = 'bg-blue-50 text-blue-600 border-blue-100';
                        $dateText = $jadwalDate->translatedFormat('d M Y');
                        
                        if ($isHariIni && $jadwal->status === 'menunggu') {
                            $dateBg = 'bg-indigo-50 text-indigo-600 border-indigo-200';
                            $dateText = __('TODAY') . ' - ' . $dateText;
                        } elseif ($isTerlewat) {
                            $dateBg = 'bg-red-50 text-red-600 border-red-200';
                            $dateText = __('MISSED') . ' - ' . $dateText;
                        }
                    @endphp
                    
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-md {{ $dateBg }} border flex items-center gap-1 uppercase tracking-wider">
                        <i class="ph-bold ph-calendar-blank"></i> {{ $dateText }}
                    </span>
                </div>
                <h3 class="font-extrabold text-slate-800 text-base">
                    {{ $jadwal->gedung->nama ?? ($jadwal->lokasi->nama ?? __('Deleted Area')) }}
                    <span class="text-xs font-semibold text-slate-400 ml-1">
                        {{ $jadwal->scope_label ?? ($jadwal->tipe_area === 'gedung' ? __('(All Areas)') : '('.($jadwal->lokasi->nama ?? '').')') }}
                    </span>
                </h3>
                <p class="text-[13px] font-medium text-slate-500 mt-1 flex items-center gap-1.5 flex-wrap">
                    <span class="flex items-center gap-1.5"><i class="ph-bold ph-user-circle text-slate-400"></i> {{ __('Officer:') }} {{ $jadwal->user ? $jadwal->user->name : __('Free / Anyone') }}</span>
                    <span class="text-slate-300 mx-1">|</span>
                    <span class="flex items-center gap-1.5"><i class="ph-bold ph-fire-extinguisher text-slate-400"></i> 
                        {{ $apars->count() }} APAR
                    </span>
                </p>
                @if($jadwal->catatan_tambahan)
                <div class="mt-3 bg-slate-50 p-3 rounded-xl border border-slate-100 flex items-start gap-2">
                    <i class="ph-bold ph-note-pencil text-slate-400 mt-0.5 text-sm"></i>
                    <p class="text-xs font-medium text-slate-600 italic leading-relaxed">
                        "{{ __($jadwal->catatan_tambahan) }}"
                    </p>
                </div>
                @endif
            </div>
        </div>
        <div class="flex-shrink-0 flex items-center gap-2">
            @php
                $pct = $jadwal->total_apar > 0 ? round(($jadwal->inspected_apar / $jadwal->total_apar) * 100) : 0;
            @endphp
            @if($isTerlewat && $jadwal->status !== 'selesai')
                <span class="text-[10px] font-bold uppercase tracking-wider px-3 py-1.5 rounded-lg bg-red-50 text-red-600 border border-red-200 flex flex-col items-center">
                    <span>{{ __('MISSED') }}</span>
                    <span class="text-[8px] opacity-80 mt-0.5">{{ $jadwal->inspected_apar }}/{{ $jadwal->total_apar }} ({{ $pct }}%)</span>
                </span>
            @elseif($jadwal->status === 'menunggu')
                <span class="text-[10px] font-bold uppercase tracking-wider px-3 py-1.5 rounded-lg bg-orange-50 text-orange-600 border border-orange-200 flex flex-col items-center">
                    <span>{{ __('WAITING') }}</span>
                    <span class="text-[8px] opacity-80 mt-0.5">0/{{ $jadwal->total_apar }} (0%)</span>
                </span>
            @elseif($jadwal->status === 'proses')
                <span class="text-[10px] font-bold uppercase tracking-wider px-3 py-1.5 rounded-lg bg-blue-50 text-blue-600 border border-blue-200 flex flex-col items-center">
                    <span>{{ __('PROCESS') }}</span>
                    <span class="text-[8px] opacity-80 mt-0.5">{{ $jadwal->inspected_apar }}/{{ $jadwal->total_apar }} ({{ $pct }}%)</span>
                </span>
            @else
                <span class="text-[10px] font-bold uppercase tracking-wider px-3 py-1.5 rounded-lg bg-[#009B77]/10 text-[#009B77] border border-[#009B77]/20 flex flex-col items-center">
                    <span>{{ __('COMPLETED') }}</span>
                    <span class="text-[8px] opacity-80 mt-0.5">{{ $jadwal->total_apar }}/{{ $jadwal->total_apar }} (100%)</span>
                </span>
            @endif
            @if(auth()->user()->role !== 'Staff' && $jadwal->status !== 'selesai' && !$isTerlewat)
                @if(!isset($jadwal->is_rutin_virtual))
                <button type="button" @click.stop="editData = { id: {{ $jadwal->id }}, jenis_jadwal: '{{ addslashes($jadwal->jenis_jadwal) }}', tanggal: '{{ \Carbon\Carbon::parse($jadwal->tanggal_inspeksi)->format('Y-m-d') }}', tipe_area: '{{ $jadwal->tipe_area }}', gedung_id: {{ $jadwal->gedung_id ?? 'null' }}, lokasi_id: {{ $jadwal->lokasi_id ?? 'null' }}, user_id: {{ $jadwal->user_id ?? 'null' }}, catatan_tambahan: '{{ addslashes(str_replace(PHP_EOL, ' ', $jadwal->catatan_tambahan)) }}' }; originalEditData = JSON.stringify(editData); showModalEditJadwal = true" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-blue-500 hover:bg-blue-50 transition-colors" title="{{ __('Edit Schedule') }}">
                    <i class="ph-bold ph-pencil-simple text-lg"></i>
                </button>
                <form action="/inspection-schedule/{{ $jadwal->id }}" method="POST" class="inline-block"
                      onsubmit="event.preventDefault(); Swal.fire({ title: '{{ __('Delete Schedule?') }}', text: '{{ __('Deleted schedule cannot be restored!') }}', icon: 'warning', showCancelButton: true, confirmButtonColor: '#ef4444', cancelButtonColor: '#94a3b8', confirmButtonText: '{{ __('Yes, Delete!') }}', cancelButtonText: '{{ __('Cancel') }}', customClass: { confirmButton: 'rounded-xl', cancelButton: 'rounded-xl' } }).then((result) => { if (result.isConfirmed) { this.submit(); } })">
                    @csrf
                    @method('DELETE')
                    <button type="submit" @click.stop class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-red-500 hover:bg-red-50 transition-colors" title="{{ __('Delete Schedule') }}">
                        <i class="ph-bold ph-trash text-lg"></i>
                    </button>
                </form>
                @endif
            @endif
            
            @if($hasMultipleApars)
            <div class="w-8 h-8 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 transition-transform duration-300 ml-1" :class="expanded ? 'rotate-180' : ''">
                <i class="ph-bold ph-caret-down text-sm"></i>
            </div>
            @endif
        </div>
    </div>

    @if($hasMultipleApars)
    <!-- Dropdown List APAR -->
    <div x-show="expanded" x-collapse style="display: none;">
        <div class="px-5 pb-5 border-t border-slate-100 pt-4 space-y-2.5">
            @php
                $jadwalMonth = \Carbon\Carbon::parse($jadwal->tanggal_inspeksi)->month;
                $jadwalYear = \Carbon\Carbon::parse($jadwal->tanggal_inspeksi)->year;
            @endphp
            
            @if($apars->isEmpty())
                <p class="text-xs text-center text-slate-400 font-medium py-3">{{ __('No PFE in this area.') }}</p>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($apars as $apar)
                        @php
                            $isInspected = $apar->inspeksis()->whereMonth('created_at', $jadwalMonth)->whereYear('created_at', $jadwalYear)->exists();
                        @endphp
                        <div class="flex items-center justify-between p-3 rounded-xl border border-slate-100 {{ $isInspected ? 'bg-emerald-50/50' : 'bg-slate-50 hover:bg-slate-100 transition-colors' }}">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 {{ $isInspected ? 'bg-emerald-100 text-emerald-600' : 'bg-white border border-slate-200 text-slate-400' }}">
                                    <i class="ph-fill ph-fire-extinguisher text-lg"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-slate-700 truncate">{{ $apar->kode }}</p>
                                    <p class="text-[10px] text-slate-500 truncate">{{ $apar->lokasi->nama ?? 'n/a' }}</p>
                                </div>
                            </div>
                            <div class="flex-shrink-0 pl-2">
                                @if($isInspected)
                                    <span class="text-[9px] font-bold text-emerald-600 bg-emerald-100 px-2 py-1 rounded uppercase flex items-center gap-1"><i class="ph-bold ph-check"></i> {{ __('Completed') }}</span>
                                @else
                                    <span class="text-[9px] font-bold text-slate-500 bg-slate-200 px-2 py-1 rounded uppercase">{{ __('Waiting') }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
    @endif
</div>
