<table class="w-full text-left border-collapse">
    <thead>
        <tr class="bg-slate-50 text-slate-500 border-b border-slate-200">
            <th class="py-3 px-6 text-xs font-bold uppercase tracking-wider">{{ __('PFE & Location') }}</th>
            <th class="py-3 px-6 text-xs font-bold uppercase tracking-wider">{{ __('Time') }}</th>
            <th class="py-3 px-6 text-xs font-bold uppercase tracking-wider">{{ __('Inspector') }}</th>
            <th class="py-3 px-6 text-xs font-bold uppercase tracking-wider text-center">{{ __('Status') }}</th>
        </tr>
    </thead>
    <tbody class="text-sm divide-y divide-slate-100">
        @forelse($recentInspections as $inspeksi)
        <tr class="hover:bg-slate-50/50 transition-colors">
            <td class="py-4 px-6">
                <div class="flex flex-col">
                    <span class="font-bold text-slate-800">{{ $inspeksi->apar->kode ?? 'n/a' }}</span>
                    <span class="text-xs font-medium text-slate-500">{{ $inspeksi->apar->lokasi->nama ?? 'n/a' }} - {{ $inspeksi->apar->lokasi->gedung->nama ?? 'n/a' }}</span>
                </div>
            </td>
            <td class="py-4 px-6">
                <div class="flex flex-col">
                    <span class="font-semibold text-slate-700">{{ $inspeksi->created_at->format('d M Y') }}</span>
                    <span class="text-xs font-medium text-slate-500">{{ $inspeksi->created_at->format('H:i') }} WIB</span>
                </div>
            </td>
            <td class="py-4 px-6">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-[#009B77]/10 flex items-center justify-center text-[#009B77] text-xs font-bold border border-[#009B77]/20">
                        {{ strtoupper(substr($inspeksi->user->name ?? 'U', 0, 2)) }}
                    </div>
                    <span class="font-semibold text-slate-700">{{ $inspeksi->user->name ?? 'n/a' }}</span>
                </div>
            </td>
            <td class="py-4 px-6 text-center">
                @if(in_array($inspeksi->status, ['sesuai', 'layak']))
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-bold bg-teal-50 text-teal-600 border border-teal-100 uppercase tracking-wider">
                        <i class="ph-fill ph-check-circle"></i> {{ __('Sesuai') }}
                    </span>
                @elseif(in_array($inspeksi->status, ['tidak_sesuai', 'perbaikan', 'rusak', 'tidak_layak']))
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-bold bg-red-50 text-red-600 border border-red-100 uppercase tracking-wider">
                        <i class="ph-fill ph-warning-circle"></i> {{ __('Tidak Sesuai') }}
                    </span>
                @elseif(in_array($inspeksi->status, ['perlu_refill', 'isi_ulang']))
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-bold bg-amber-50 text-amber-600 border border-amber-100 uppercase tracking-wider">
                        <i class="ph-fill ph-arrows-clockwise"></i> {{ __('Perlu Refill') }}
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-bold bg-slate-50 text-slate-600 border border-slate-100 uppercase tracking-wider">
                        <span>{{ str_replace('_', ' ', $inspeksi->status) }}</span>
                    </span>
                @endif
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="4" class="py-8 text-center text-slate-500 font-semibold">{{ __('No inspection activity yet.') }}</td>
        </tr>
        @endforelse
    </tbody>
</table>
