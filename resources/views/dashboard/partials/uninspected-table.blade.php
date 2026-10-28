<table class="w-full text-left border-collapse">
    <thead>
        <tr class="bg-slate-50 border-b border-slate-200 text-slate-500">
            <th class="py-3 px-5 text-xs font-bold uppercase tracking-wider w-12 text-center">No</th>
            <th class="py-3 px-5 text-xs font-bold uppercase tracking-wider">{{ __('PFE ID') }}</th>
            <th class="py-3 px-5 text-xs font-bold uppercase tracking-wider">{{ __('Location') }}</th>
            <th class="py-3 px-5 text-xs font-bold uppercase tracking-wider">{{ __('Building') }}</th>
            <th class="py-3 px-5 text-xs font-bold uppercase tracking-wider text-center">{{ __('Status') }}</th>
        </tr>
    </thead>
    <tbody class="text-sm divide-y divide-slate-100">
        @forelse($belumDiinspeksiApars as $index => $apar)
        <tr class="hover:bg-slate-50/50 transition-colors">
            <td class="py-3 px-5 text-center font-semibold text-slate-400">{{ $index + 1 }}</td>
            <td class="py-3 px-5 font-bold text-slate-700">{{ $apar->kode }}</td>
            <td class="py-3 px-5 font-medium text-slate-600">{{ $apar->lokasi->nama ?? 'n/a' }}</td>
            <td class="py-3 px-5 font-medium text-slate-600">{{ $apar->lokasi->gedung->nama ?? 'n/a' }}</td>
            <td class="py-3 px-5 text-center">
                <span class="inline-flex items-center gap-1 text-xs font-bold text-amber-600 bg-amber-50 px-2.5 py-1 rounded-md border border-amber-200/50">
                    <i class="ph-bold ph-clock"></i> {{ __('Uninspected') }}
                </span>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="5" class="py-8 text-center text-slate-500 font-semibold">{{ __('All PFE have been inspected this month!') }}</td>
        </tr>
        @endforelse
    </tbody>
</table>
