@extends('layouts.app')

@section('title', 'Audit Trail - PFE Monitoring Control System')

@section('content')
<div class="space-y-6">

    <!-- Header Area -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200/60">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-[#009B77]/10 border border-[#009B77]/20 shadow-sm flex items-center justify-center text-[#009B77]">
                <i class="ph-bold ph-clock-counter-clockwise text-xl"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-base font-bold text-[#007A5E] leading-tight">Audit Trail</h2>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#009B77]/10 text-[#009B77] border border-[#009B77]/20">
                        <i class="ph-bold ph-calendar"></i> {{ $currentPeriod ?? \Carbon\Carbon::now()->translatedFormat('F Y') }}
                    </span>
                </div>
                <p class="text-xs font-semibold text-slate-500 mt-0.5">
                    {{ __('History of user actions and system activity') }}
                    <span class="text-slate-400 font-normal ml-1">({{ __('Log is cleaned & renewed at each month turnaround') }})</span>
                </p>
            </div>
        </div>
        
        <div class="flex flex-wrap items-center gap-2">
            <!-- Preview Excel -->
            <button type="button"
                @click="$dispatch('open-excel-preview', { url: '{{ route('activity-log.preview-excel') }}', downloadUrl: '{{ route('activity-log.export-excel') }}', title: 'Audit Trail' })"
                class="flex items-center gap-1.5 px-3.5 py-2 text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition-colors shadow-2xs">
                <i class="ph-bold ph-table text-base text-[#009B77]"></i>
                {{ __('Preview Excel') }}
            </button>

            <!-- Export Excel -->
            <a href="{{ route('activity-log.export-excel') }}" class="flex items-center gap-1.5 px-3.5 py-2 text-xs bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200/60 font-bold rounded-xl transition-colors shadow-2xs">
                <i class="ph-bold ph-file-csv text-base"></i>
                {{ __('Export Excel') }}
            </a>

            <!-- Export PDF -->
            <a href="{{ route('activity-log.export-pdf') }}" target="_blank" class="flex items-center gap-1.5 px-3.5 py-2 text-xs bg-red-50 text-red-600 hover:bg-red-100 border border-red-200/60 font-bold rounded-xl transition-colors shadow-2xs">
                <i class="ph-bold ph-file-pdf text-base"></i>
                {{ __('Export PDF') }}
            </a>
        </div>
    </div>

    <!-- Table Section -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#009B77] text-white divide-x divide-white/20 text-center">
                        <th class="py-3.5 px-4 text-xs font-bold uppercase tracking-wider w-36 text-left">{{ __('Time') }}</th>
                        <th class="py-3.5 px-4 text-xs font-bold uppercase tracking-wider text-left">{{ __('User') }}</th>
                        <th class="py-3.5 px-4 text-xs font-bold uppercase tracking-wider w-32">{{ __('Action') }}</th>
                        <th class="py-3.5 px-4 text-xs font-bold uppercase tracking-wider w-40 text-left">{{ __('Module') }}</th>
                        <th class="py-3.5 px-4 text-xs font-bold uppercase tracking-wider text-left">{{ __('Description') }}</th>
                    </tr>
                </thead>
                <tbody class="text-xs divide-y divide-slate-100">
                    @forelse($activities as $activity)
                    @php
                        $moduleName = class_basename($activity->subject_type);
                        $moduleLabel = match ($moduleName) {
                            'Apar' => 'Master Data APAR',
                            'Inspeksi' => 'Inspeksi APAR',
                            'JadwalInspeksi' => 'Jadwal Inspeksi',
                            'User' => 'Manajemen User',
                            'AparCadangan' => 'Stok Cadangan',
                            'Lokasi' => 'Data Lokasi',
                            default => preg_replace('/([a-z])([A-Z])/s', '$1 $2', $moduleName ?: 'System'),
                        };

                        $eventBadge = match ($activity->event) {
                            'created' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'label' => __('CREATED'), 'icon' => 'ph-plus-circle'],
                            'updated' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'border' => 'border-amber-200', 'label' => __('UPDATED'), 'icon' => 'ph-pencil-simple'],
                            'deleted' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'border' => 'border-rose-200', 'label' => __('DELETED'), 'icon' => 'ph-trash'],
                            default => ['bg' => 'bg-slate-100', 'text' => 'text-slate-700', 'border' => 'border-slate-200', 'label' => strtoupper($activity->event), 'icon' => 'ph-activity'],
                        };
                    @endphp
                    <tr class="hover:bg-slate-50/80 transition-colors divide-x divide-slate-100">
                        <td class="py-3 px-4">
                            <span class="text-xs font-bold text-slate-700 block">{{ $activity->created_at->format('d M Y') }}</span>
                            <span class="text-[11px] font-mono text-slate-400">{{ $activity->created_at->format('H:i:s') }}</span>
                        </td>
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 flex-shrink-0 text-xs">
                                    <i class="ph-fill ph-user"></i>
                                </div>
                                <span class="text-xs font-bold text-slate-700">{{ $activity->causer->name ?? __('System') }}</span>
                            </div>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <span class="inline-flex items-center gap-1 text-[10.5px] font-bold px-2 py-0.5 rounded-md border {{ $eventBadge['bg'] }} {{ $eventBadge['text'] }} {{ $eventBadge['border'] }}">
                                <i class="ph-bold {{ $eventBadge['icon'] }}"></i>
                                {{ $eventBadge['label'] }}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            <span class="text-xs font-bold text-[#009B77] bg-[#009B77]/10 px-2 py-0.5 rounded-md border border-[#009B77]/20">
                                {{ __($moduleLabel) }}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            <div class="space-y-1">
                                <p class="text-xs text-slate-700 font-semibold leading-relaxed">
                                    {{ \App\Http\Controllers\ActivityLogController::translateDescription($activity->description) }}
                                </p>
                                @if(isset($activity->properties['attributes']) && is_array($activity->properties['attributes']) && count($activity->properties['attributes']) > 0)
                                    <div class="flex flex-wrap gap-1 pt-0.5">
                                        @foreach(array_slice($activity->properties['attributes'], 0, 4) as $key => $val)
                                            @if(!in_array($key, ['password', 'remember_token', 'updated_at', 'created_at', 'foto']))
                                                <span class="inline-flex items-center text-[9.5px] font-mono bg-slate-100 text-slate-600 px-1.5 py-0.2 rounded border border-slate-200">
                                                    {{ $key }}: <strong class="ml-0.5 text-slate-800">{{ is_array($val) ? json_encode($val) : \Illuminate\Support\Str::limit((string)$val, 20) }}</strong>
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-12 text-center text-slate-500 font-semibold">
                            <div class="flex flex-col items-center justify-center space-y-2">
                                <i class="ph-bold ph-clock-counter-clockwise text-3xl text-slate-300"></i>
                                <p class="text-sm font-bold text-slate-600">{{ __('No activity log for this month yet.') }}</p>
                                <p class="text-xs text-slate-400">{{ __('Audit trail log is automatically refreshed at the beginning of each month.') }}</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-slate-100">
            {{ $activities->onEachSide(0)->links() }}
        </div>
    </div>
</div>
    <x-excel-preview-modal />
@endsection
