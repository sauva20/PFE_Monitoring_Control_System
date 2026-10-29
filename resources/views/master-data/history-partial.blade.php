<div class="space-y-6">
    @forelse($activities as $activity)
        <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-{{ $activity->event === 'created' ? 'blue' : ($activity->event === 'deleted' ? 'red' : 'amber') }}-50 text-{{ $activity->event === 'created' ? 'blue' : ($activity->event === 'deleted' ? 'red' : 'amber') }}-500 flex items-center justify-center border border-{{ $activity->event === 'created' ? 'blue' : ($activity->event === 'deleted' ? 'red' : 'amber') }}-100">
                        <i class="ph-bold ph-{{ $activity->event === 'created' ? 'plus' : ($activity->event === 'deleted' ? 'trash' : 'pencil-simple') }} text-lg"></i>
                    </div>
                    <div>
                        <p class="text-sm font-extrabold text-slate-800 tracking-wide">{{ strtoupper($activity->event) }}</p>
                        <p class="text-xs font-semibold text-slate-500 mt-0.5">{{ __('By:') }} {{ $activity->causer->name ?? __('System') }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-xs font-bold text-slate-700">{{ $activity->created_at->format('d M Y') }}</p>
                    <p class="text-[11px] font-semibold text-slate-400">{{ $activity->created_at->format('H:i:s') }}</p>
                </div>
            </div>
            
            @if($activity->changes()->count() > 0)
                <div class="overflow-x-auto rounded-lg border border-slate-100">
                    <table class="w-full text-left text-sm border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 text-slate-500">
                                <th class="py-2.5 px-4 font-bold text-xs uppercase tracking-wider w-1/3">{{ __('Data') }}</th>
                                @if($activity->event === 'updated')
                                    <th class="py-2.5 px-4 font-bold text-xs uppercase tracking-wider w-1/3 text-red-500 border-l border-slate-100">{{ __('Before') }}</th>
                                    <th class="py-2.5 px-4 font-bold text-xs uppercase tracking-wider w-1/3 text-[#009B77] border-l border-slate-100">{{ __('After') }}</th>
                                @elseif($activity->event === 'created')
                                    <th class="py-2.5 px-4 font-bold text-xs uppercase tracking-wider w-2/3 text-[#009B77] border-l border-slate-100">{{ __('New Data Entered') }}</th>
                                @elseif($activity->event === 'deleted')
                                    <th class="py-2.5 px-4 font-bold text-xs uppercase tracking-wider w-2/3 text-red-500 border-l border-slate-100">{{ __('Deleted Data') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @php
                                $changes = $activity->changes();
                                $attributes = $changes['attributes'] ?? [];
                                $old = $changes['old'] ?? [];
                                $keys = array_unique(array_merge(array_keys($attributes), array_keys($old)));
                                
                                function formatHistoryLabel($key) {
                                    $map = [
                                        'jenis_id' => __('PFE Type'),
                                        'lokasi_id' => __('Location'),
                                        'kapasitas_id' => __('Capacity'),
                                        'pic_id' => __('PIC'),
                                        'tgl_kedaluwarsa' => __('Expired Date'),
                                        'tgl_isi_ulang' => __('Last Refill'),
                                        'kode' => __('PFE ID'),
                                        'qty' => __('Qty'),
                                        'vendor' => __('Vendor'),
                                        'foto' => __('Photo')
                                    ];
                                    return $map[$key] ?? ucwords(str_replace('_', ' ', $key));
                                }

                                function formatHistoryValue($key, $value) {
                                    if (is_null($value) || $value === '') return '-';
                                    
                                    if ($key === 'jenis_id') {
                                        return \App\Models\JenisApar::find($value)->nama ?? $value;
                                    }
                                    if ($key === 'lokasi_id') {
                                        $lok = \App\Models\Lokasi::find($value);
                                        return $lok ? $lok->nama : $value;
                                    }
                                    if ($key === 'kapasitas_id') {
                                        return \App\Models\KapasitasApar::find($value)->ukuran ?? $value;
                                    }
                                    if ($key === 'pic_id') {
                                        return \App\Models\User::find($value)->name ?? $value;
                                    }
                                    if (in_array($key, ['tgl_kedaluwarsa', 'tgl_isi_ulang'])) {
                                        try {
                                            return \Carbon\Carbon::parse($value)->format('d M Y');
                                        } catch(\Exception $e) {
                                            return $value;
                                        }
                                    }
                                    if ($key === 'foto') {
                                        return __('(Image File Updated)');
                                    }
                                    return $value;
                                }

                                function formatGedungValue($lokasiId) {
                                    if (is_null($lokasiId) || $lokasiId === '') return '-';
                                    $lok = \App\Models\Lokasi::with('gedung')->find($lokasiId);
                                    return $lok && $lok->gedung ? $lok->gedung->nama : '-';
                                }
                            @endphp
                            @foreach($keys as $key)
                                @if(in_array($key, ['created_at', 'updated_at', 'id', 'vendor'])) @continue @endif
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="py-2.5 px-4 font-bold text-slate-700 capitalize text-xs">
                                        {{ formatHistoryLabel($key) }}
                                    </td>
                                    @if($activity->event === 'updated')
                                        <td class="py-2.5 px-4 text-xs font-semibold text-red-500/80 line-through decoration-red-300 border-l border-slate-100">{{ formatHistoryValue($key, $old[$key] ?? null) }}</td>
                                        <td class="py-2.5 px-4 text-xs font-bold text-[#009B77] border-l border-slate-100 bg-teal-50/30">{{ formatHistoryValue($key, $attributes[$key] ?? null) }}</td>
                                    @elseif($activity->event === 'created')
                                        <td class="py-2.5 px-4 text-xs font-bold text-[#009B77] border-l border-slate-100 bg-teal-50/30">{{ formatHistoryValue($key, $attributes[$key] ?? null) }}</td>
                                    @elseif($activity->event === 'deleted')
                                        <td class="py-2.5 px-4 text-xs font-semibold text-red-500/80 border-l border-slate-100">{{ formatHistoryValue($key, $old[$key] ?? null) }}</td>
                                    @endif
                                </tr>
                                @if($key === 'lokasi_id')
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="py-2.5 px-4 font-bold text-slate-700 capitalize text-xs">{{ __('Building') }}</td>
                                    @if($activity->event === 'updated')
                                        <td class="py-2.5 px-4 text-xs font-semibold text-red-500/80 line-through decoration-red-300 border-l border-slate-100">{{ formatGedungValue($old[$key] ?? null) }}</td>
                                        <td class="py-2.5 px-4 text-xs font-bold text-[#009B77] border-l border-slate-100 bg-teal-50/30">{{ formatGedungValue($attributes[$key] ?? null) }}</td>
                                    @elseif($activity->event === 'created')
                                        <td class="py-2.5 px-4 text-xs font-bold text-[#009B77] border-l border-slate-100 bg-teal-50/30">{{ formatGedungValue($attributes[$key] ?? null) }}</td>
                                    @elseif($activity->event === 'deleted')
                                        <td class="py-2.5 px-4 text-xs font-semibold text-red-500/80 border-l border-slate-100">{{ formatGedungValue($old[$key] ?? null) }}</td>
                                    @endif
                                </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-xs font-semibold text-slate-400 italic bg-slate-50 p-3 rounded-lg border border-slate-100/50 text-center">{{ __('Recorded data details are not available.') }}</p>
            @endif
        </div>
    @empty
        <div class="text-center py-12 bg-slate-50/50 rounded-2xl border border-slate-100 border-dashed">
            <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center shadow-sm mx-auto mb-3">
                <i class="ph-duotone ph-clock text-3xl text-slate-400"></i>
            </div>
            <h3 class="font-bold text-slate-700 text-sm mb-1">{{ __('No History Yet') }}</h3>
            <p class="text-xs text-slate-500 font-medium max-w-[200px] mx-auto">{{ __('This PFE has no activity records or data changes yet.') }}</p>
        </div>
    @endforelse
</div>
