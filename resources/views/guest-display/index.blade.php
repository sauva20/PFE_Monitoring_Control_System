@extends('layouts.app')

@section('title', 'Akses Viewer Display - PFE Monitoring Control System')

@section('content')
<div class="space-y-6" x-data="{
    showAddModal: {{ old('form_type') === 'add' && $errors->any() ? 'true' : 'false' }},
    showEditModal: {{ old('form_type') === 'edit' && $errors->any() ? 'true' : 'false' }},
    showDeleteModal: false,
    deleteUrl: '',
    deleteGuestName: '',
    copiedToken: null,
    editGuest: {
        id: '{{ old('form_type') === 'edit' ? old('id') : '' }}',
        name: '{{ old('form_type') === 'edit' ? old('name') : '' }}',
        institution: '{{ old('form_type') === 'edit' ? old('institution') : '' }}',
        email: '{{ old('form_type') === 'edit' ? old('email') : '' }}',
        pin: '{{ old('form_type') === 'edit' ? old('pin') : '' }}',
        valid_until: '{{ old('form_type') === 'edit' ? old('valid_until') : '' }}',
        is_active: {{ old('form_type') === 'edit' ? (old('is_active') ? 'true' : 'false') : 'true' }}
    },
    newGuest: {
        pin: '{{ old('form_type') === 'add' ? old('pin') : '' }}'
    },
    generatePin(target) {
        const randomPin = Math.floor(1000 + Math.random() * 9000).toString();
        if (target === 'new') {
            this.newGuest.pin = randomPin;
        } else if (target === 'edit') {
            this.editGuest.pin = randomPin;
        }
    },
    copyToClipboard(text, id) {
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(() => {
                this.copiedToken = id;
                setTimeout(() => this.copiedToken = null, 2500);
            });
        } else {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            textArea.style.top = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            try {
                document.execCommand('copy');
                this.copiedToken = id;
                setTimeout(() => this.copiedToken = null, 2500);
            } catch (err) {
                console.error('Gagal menyalin tautan:', err);
            }
            textArea.remove();
        }
    },
    openEdit(guest) {
        this.editGuest = {
            id: guest.id,
            name: guest.name,
            institution: guest.institution,
            email: guest.email,
            pin: guest.pin,
            valid_until: guest.valid_until ? guest.valid_until.substring(0, 10) : '',
            is_active: Boolean(guest.is_active)
        };
        this.showEditModal = true;
    },
    openDelete(url, name) {
        this.deleteUrl = url;
        this.deleteGuestName = name;
        this.showDeleteModal = true;
    }
}">

    <!-- Header Area -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200/60">
        <!-- Left: Page Context -->
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-[#009B77]/10 border border-[#009B77]/20 shadow-sm flex items-center justify-center text-[#009B77]">
                <i class="ph-bold ph-identification-card text-xl"></i>
            </div>
            <div>
                <h2 class="text-base font-bold text-[#007A5E] leading-tight">Akses Viewer Display</h2>
                <p class="text-xs font-semibold text-slate-500 mt-0.5">Kelola kredensial PIN 4 digit dan durasi masa aktif tampilan laporan untuk Viewer eksternal & auditor</p>
            </div>
        </div>

        <!-- Right: Actions -->
        <div class="flex items-center gap-3 w-full sm:w-auto">
            <a href="{{ url('/display-report') }}" target="_blank" class="w-full sm:w-auto justify-center bg-white border border-slate-200/80 text-slate-700 hover:text-[#009B77] hover:border-[#009B77]/40 hover:bg-[#009B77]/5 font-bold py-2.5 px-4 rounded-xl shadow-sm transition-all flex items-center gap-2 text-sm hover:-translate-y-0.5">
                <i class="ph-bold ph-presentation-chart text-lg text-slate-500"></i>
                <span>{{ __('Live Display (Kiosk)') }}</span>
                <i class="ph-bold ph-arrow-square-out text-xs text-slate-400"></i>
            </a>

            <button @click="showAddModal = true; if(!newGuest.pin) generatePin('new')" class="w-full sm:w-auto justify-center btn-smooth-ring bg-[#009B77] hover:bg-[#008264] text-white font-bold py-2.5 px-5 rounded-xl shadow-[0_4px_12px_rgba(0,155,119,0.25)] transition-all flex items-center gap-2 text-sm hover:-translate-y-0.5">
                <i class="ph-bold ph-plus-circle text-lg"></i>
                <span>Tambah Akses Viewer</span>
            </button>
        </div>
    </div>

    <!-- Stats Quick Cards -->
    @php
        $totalGuestCount = \App\Models\GuestDisplay::count();
        $activeGuestCount = \App\Models\GuestDisplay::where('is_active', true)->whereDate('valid_until', '>=', \Carbon\Carbon::today())->count();
        $inactiveOrExpiredGuestCount = \App\Models\GuestDisplay::where(function($q) {
            $q->where('is_active', false)->orWhereDate('valid_until', '<', \Carbon\Carbon::today());
        })->count();
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/60 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 flex-shrink-0">
                <i class="ph-bold ph-users text-2xl"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Akses Viewer</p>
                <h3 class="text-2xl font-black text-slate-800 mt-0.5">{{ $totalGuestCount }}</h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/60 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 flex-shrink-0">
                <i class="ph-bold ph-check-circle text-2xl"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Viewer Aktif</p>
                <h3 class="text-2xl font-black text-emerald-700 mt-0.5">{{ $activeGuestCount }}</h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/60 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-500 flex-shrink-0">
                <i class="ph-bold ph-prohibit text-2xl"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Viewer Nonaktif</p>
                <h3 class="text-2xl font-black text-slate-700 mt-0.5">{{ $inactiveOrExpiredGuestCount }}</h3>
            </div>
        </div>
    </div>

    <!-- Filters & Search -->
    <form action="{{ route('display-access.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Search -->
        <div class="md:col-span-2">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <i class="ph-bold ph-magnifying-glass text-slate-400 text-lg"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama viewer, instansi, atau email..." class="w-full pl-11 pr-10 py-2.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all shadow-sm"
                       @input.debounce.750ms="$el.form.submit()">
                @if(request('search'))
                <a href="{{ route('display-access.index', ['status' => request('status')]) }}" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600">
                    <i class="ph-bold ph-x-circle text-lg"></i>
                </a>
                @endif
            </div>
        </div>

        <!-- Filter Status -->
        <div>
            <div x-data="{ open: false, selected: '{{ request('status') === 'active' ? 'Hanya Aktif' : (request('status') === 'expired' ? 'Nonaktif / Kedaluwarsa' : 'Semua Status') }}' }" class="relative">
                <input type="hidden" name="status" value="{{ request('status') }}" x-ref="status_input">
                <button type="button" @click="open = !open" @click.away="open = false" class="w-full flex items-center justify-between py-2.5 px-4 bg-white border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all cursor-pointer hover:border-slate-300 shadow-sm">
                    <div class="flex items-center gap-2 truncate">
                        <i class="ph-bold ph-funnel text-slate-400"></i>
                        <span x-text="selected" class="truncate font-medium"></span>
                    </div>
                    <i class="ph-bold ph-caret-down text-slate-400 transition-transform duration-200 flex-shrink-0" :class="open ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="open" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     style="display: none;" 
                     class="absolute right-0 z-50 w-full mt-2 bg-white rounded-xl shadow-[0_10px_40px_rgba(0,0,0,0.1)] border border-slate-100 py-2 origin-top" x-cloak>
                     
                    <button type="button" @click="selected = 'Semua Status'; open = false; $refs.status_input.value = ''; $refs.status_input.form.submit();" class="w-full text-left px-4 py-2 text-sm transition-colors flex items-center justify-between hover:bg-slate-50" :class="selected === 'Semua Status' ? 'text-[#009B77] font-bold bg-[#009B77]/5' : 'text-slate-600'">
                        <span>Semua Status</span>
                        <i class="ph-bold ph-check text-[#009B77]" x-show="selected === 'Semua Status'"></i>
                    </button>
                    <button type="button" @click="selected = 'Hanya Aktif'; open = false; $refs.status_input.value = 'active'; $refs.status_input.form.submit();" class="w-full text-left px-4 py-2 text-sm transition-colors flex items-center justify-between hover:bg-slate-50" :class="selected === 'Hanya Aktif' ? 'text-emerald-600 font-bold bg-emerald-50' : 'text-slate-600'">
                        <span>Hanya Aktif</span>
                        <i class="ph-bold ph-check text-emerald-600" x-show="selected === 'Hanya Aktif'"></i>
                    </button>
                    <button type="button" @click="selected = 'Nonaktif / Kedaluwarsa'; open = false; $refs.status_input.value = 'expired'; $refs.status_input.form.submit();" class="w-full text-left px-4 py-2 text-sm transition-colors flex items-center justify-between hover:bg-slate-50" :class="selected === 'Nonaktif / Kedaluwarsa' ? 'text-slate-700 font-bold bg-slate-100' : 'text-slate-600'">
                        <span>Nonaktif / Kedaluwarsa</span>
                        <i class="ph-bold ph-check text-slate-700" x-show="selected === 'Nonaktif / Kedaluwarsa'"></i>
                    </button>
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
                        <th class="py-4 px-4 text-xs font-bold uppercase tracking-wider w-12">No</th>
                        <th class="py-4 px-5 text-xs font-bold uppercase tracking-wider text-left">Viewer / Instansi</th>
                        <th class="py-4 px-4 text-xs font-bold uppercase tracking-wider text-left">Email</th>
                        <th class="py-4 px-4 text-xs font-bold uppercase tracking-wider">PIN (4 Digit)</th>
                        <th class="py-4 px-4 text-xs font-bold uppercase tracking-wider">Masa Berlaku</th>
                        <th class="py-4 px-4 text-xs font-bold uppercase tracking-wider">Status</th>
                        <th class="py-4 px-4 text-xs font-bold uppercase tracking-wider">Akses Terakhir</th>
                        <th class="py-4 px-5 text-xs font-bold uppercase tracking-wider w-44">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-100">
                    @forelse($guests as $index => $guest)
                    @php
                        $isExpired = $guest->isExpired();
                        $isAccessible = $guest->isAccessible();
                        $validDate = \Carbon\Carbon::parse($guest->valid_until);
                        $today = \Carbon\Carbon::today();
                        $daysDiff = $today->diffInDays($validDate, false);
                    @endphp
                    <tr class="hover:bg-slate-50/80 transition-colors divide-x divide-slate-100">
                        <td class="py-4 px-4 text-center text-slate-500 font-medium">{{ $guests->firstItem() + $index }}</td>
                        
                        <!-- Guest & Institution -->
                        <td class="py-4 px-5">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-sm shadow-sm flex-shrink-0">
                                    {{ strtoupper(substr($guest->name, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="font-bold text-slate-800 text-sm truncate">{{ $guest->name }}</div>
                                    <div class="text-xs font-semibold text-slate-500 flex items-center gap-1.5 mt-0.5">
                                        <i class="ph-bold ph-buildings text-slate-400"></i>
                                        <span class="truncate">{{ $guest->institution }}</span>
                                    </div>
                                </div>
                            </div>
                        </td>

                        <!-- Email -->
                        <td class="py-4 px-4 text-slate-600 font-medium">
                            <a href="mailto:{{ $guest->email }}" class="hover:text-[#009B77] transition-colors flex items-center gap-1.5 text-xs">
                                <i class="ph-bold ph-envelope-simple text-slate-400"></i>
                                <span>{{ $guest->email }}</span>
                            </a>
                        </td>

                        <!-- PIN -->
                        <td class="py-4 px-4 text-center" x-data="{ showPin: false }">
                            <div class="inline-flex items-center gap-1.5 bg-slate-100 px-2.5 py-1 rounded-lg border border-slate-200">
                                <span class="font-mono font-bold tracking-wider text-xs" :class="showPin ? 'text-amber-800' : 'text-slate-600'" x-text="showPin ? '{{ $guest->pin }}' : '••••'"></span>
                                <button type="button" @click="showPin = !showPin" class="text-slate-400 hover:text-slate-700 transition-colors text-xs" :title="showPin ? 'Sembunyikan PIN' : 'Tampilkan PIN'">
                                    <i class="ph-bold" :class="showPin ? 'ph-eye-slash' : 'ph-eye'"></i>
                                </button>
                            </div>
                        </td>

                        <!-- Valid Until -->
                        <td class="py-4 px-4 text-center">
                            <div class="text-xs font-bold text-slate-800">
                                {{ $validDate->translatedFormat('d M Y') }}
                            </div>
                            <div class="mt-1">
                                @if(!$guest->is_active)
                                    <span class="inline-block text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 border border-slate-200">
                                        Nonaktif
                                    </span>
                                @elseif($isExpired)
                                    <span class="inline-block text-[10px] font-bold px-2 py-0.5 rounded-full bg-rose-50 text-rose-600 border border-rose-200">
                                        Kedaluwarsa
                                    </span>
                                @elseif($daysDiff === 0)
                                    <span class="inline-block text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200">
                                        Berakhir Hari Ini
                                    </span>
                                @elseif($daysDiff <= 3)
                                    <span class="inline-block text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200">
                                        Sisa {{ $daysDiff }} hari
                                    </span>
                                @else
                                    <span class="inline-block text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Sisa {{ $daysDiff }} hari
                                    </span>
                                @endif
                            </div>
                        </td>

                        <!-- Status Badge -->
                        <td class="py-4 px-4 text-center">
                            @if($isAccessible)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100/70 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <span>Aktif</span>
                                </span>
                            @elseif(!$guest->is_active)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                    <span>Nonaktif</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-100/70 text-rose-700 border border-rose-200">
                                    <i class="ph-bold ph-clock-countdown"></i>
                                    <span>Kedaluwarsa</span>
                                </span>
                            @endif
                        </td>

                        <!-- Last Accessed -->
                        <td class="py-4 px-4 text-center text-xs">
                            @if($guest->last_accessed_at)
                                <span class="font-bold text-slate-700">{{ \Carbon\Carbon::parse($guest->last_accessed_at)->translatedFormat('d M Y') }}</span>
                                <span class="block text-[10px] text-slate-400 font-semibold">{{ \Carbon\Carbon::parse($guest->last_accessed_at)->format('H:i') }} WIB</span>
                            @else
                                <span class="text-slate-400 font-medium italic">Belum Pernah</span>
                            @endif
                        </td>

                        <!-- Actions -->
                        <td class="py-4 px-5 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <!-- Copy Access Link -->
                                <button type="button" 
                                        @click="copyToClipboard('{{ $guest->getAccessUrl() }}', {{ $guest->id }})" 
                                        class="w-8 h-8 rounded-lg border border-slate-200 flex items-center justify-center transition-all shadow-sm"
                                        :class="copiedToken === {{ $guest->id }} ? 'bg-emerald-500 text-white border-emerald-500' : 'bg-white text-slate-600 hover:text-indigo-600 hover:border-indigo-300 hover:bg-indigo-50'"
                                        :title="copiedToken === {{ $guest->id }} ? 'Tautan berhasil disalin!' : 'Salin tautan akses viewer'">
                                    <i class="ph-bold" :class="copiedToken === {{ $guest->id }} ? 'ph-check' : 'ph-link-simple'"></i>
                                </button>

                                <!-- Resend Email -->
                                <form action="{{ route('display-access.resend', $guest) }}" method="POST" class="inline" onsubmit="return confirm('Kirim ulang email kredensial ke {{ $guest->email }}?')">
                                    @csrf
                                    <button type="submit" class="w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-600 hover:text-[#009B77] hover:border-[#009B77]/30 hover:bg-[#009B77]/5 flex items-center justify-center transition-all shadow-sm" title="{{ __('Resend Email') }}">
                                        <i class="ph-bold ph-paper-plane-tilt"></i>
                                    </button>
                                </form>

                                <!-- Edit -->
                                <button type="button" @click="openEdit({{ json_encode($guest) }})" class="w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-600 hover:text-amber-600 hover:border-amber-300 hover:bg-amber-50 flex items-center justify-center transition-all shadow-sm" title="{{ __('Edit') }}">
                                    <i class="ph-bold ph-pencil-simple"></i>
                                </button>

                                <!-- Delete -->
                                <button type="button" @click="openDelete('{{ route('display-access.destroy', $guest) }}', '{{ addslashes($guest->name) }}')" class="w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-600 hover:text-red-600 hover:border-red-300 hover:bg-red-50 flex items-center justify-center transition-all shadow-sm" title="{{ __('Delete') }}">
                                    <i class="ph-bold ph-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-slate-400">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-300 mb-3">
                                    <i class="ph-bold ph-identification-card text-3xl"></i>
                                </div>
                                <h4 class="text-sm font-bold text-slate-600">Belum Ada Akses Viewer</h4>
                                <p class="text-xs text-slate-400 mt-1 max-w-sm">Klik tombol "Tambah Akses Viewer" di atas untuk memberikan akses pemantauan kepada pihak eksternal atau auditor.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($guests->hasPages())
        <div class="p-4 border-t border-slate-100 bg-slate-50/50">
            {{ $guests->links() }}
        </div>
        @endif
    </div>

    <!-- MODAL 1: TAMBAH AKSES VIEWER -->
    <template x-teleport="body">
        <div x-show="showAddModal" 
             class="fixed inset-0 z-[100] overflow-y-auto"
             style="display: none;"
             x-cloak>
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="showAddModal = false"></div>

            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl border border-slate-100 overflow-hidden transform transition-all my-8"
                     @click.outside="showAddModal = false">
                
                <!-- Modal Header -->
                <div class="bg-gradient-to-r from-[#009B77] to-[#008264] px-6 py-4 text-white flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-white/10 flex items-center justify-center backdrop-blur-sm">
                            <i class="ph-bold ph-user-plus text-xl text-white"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-base">Buat Akses Viewer Baru</h3>
                            <p class="text-xs text-white/80 font-medium">Kirim kredensial tautan & 4 digit PIN via email otomatis</p>
                        </div>
                    </div>
                    <button type="button" @click="showAddModal = false" class="text-white/70 hover:text-white transition-colors">
                        <i class="ph-bold ph-x text-xl"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <form action="{{ route('display-access.store') }}" method="POST" class="p-6 space-y-4">
                    @csrf
                    <input type="hidden" name="form_type" value="add">

                    <!-- Nama Viewer -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama Viewer <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="ph-bold ph-user text-base"></i>
                            </div>
                            <input type="text" name="name" value="{{ old('name') }}" required placeholder="cth: Bpk. Bambang Sutrisno" class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all @error('name') border-rose-500 @enderror">
                        </div>
                        @error('name')
                            <p class="text-rose-500 text-xs mt-1 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Instansi / Lembaga -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Instansi / Perusahaan <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="ph-bold ph-buildings text-base"></i>
                            </div>
                            <input type="text" name="institution" value="{{ old('institution') }}" required placeholder="cth: Auditor HSE / PT Sucofindo" class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all @error('institution') border-rose-500 @enderror">
                        </div>
                        @error('institution')
                            <p class="text-rose-500 text-xs mt-1 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email Viewer -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Email Viewer <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="ph-bold ph-envelope-simple text-base"></i>
                            </div>
                            <input type="email" name="email" value="{{ old('email') }}" required placeholder="cth: viewer@perusahaan.co.id" class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all @error('email') border-rose-500 @enderror">
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1 font-medium">Tautan akses dan 4 digit PIN akan dikirimkan langsung ke email ini.</p>
                        @error('email')
                            <p class="text-rose-500 text-xs mt-1 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- PIN & Batas Tanggal -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- PIN Akses -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">PIN Akses (4 Digit) <span class="text-rose-500">*</span></label>
                                <button type="button" @click="generatePin('new')" class="text-[11px] font-bold text-[#009B77] hover:underline flex items-center gap-1">
                                    <i class="ph-bold ph-arrows-clockwise"></i> Acak
                                </button>
                            </div>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <i class="ph-bold ph-key text-base"></i>
                                </div>
                                <input type="text" name="pin" x-model="newGuest.pin" required maxlength="4" pattern="[0-9]{4}" placeholder="1234" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 4)" class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono font-bold tracking-widest focus:bg-white focus:outline-none focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all @error('pin') border-rose-500 @enderror">
                            </div>
                            @error('pin')
                                <p class="text-rose-500 text-xs mt-1 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Batas Tanggal Berlaku -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Batas Berlaku <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <i class="ph-bold ph-calendar text-base"></i>
                                </div>
                                <input type="date" name="valid_until" value="{{ old('valid_until', date('Y-m-d', strtotime('+7 days'))) }}" min="{{ date('Y-m-d') }}" required class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all @error('valid_until') border-rose-500 @enderror">
                            </div>
                            @error('valid_until')
                                <p class="text-rose-500 text-xs mt-1 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Notice Box -->
                    <div class="bg-emerald-50/70 border border-emerald-100 rounded-xl p-3 text-xs text-emerald-800 flex items-start gap-2.5">
                        <i class="ph-fill ph-info text-base text-emerald-600 mt-0.5 flex-shrink-0"></i>
                        <span class="leading-relaxed">Viewer akan menerima tautan akses langsung dengan autentikasi 4 digit PIN. Masa akses otomatis berakhir setelah pukul 23:59 WIB pada tanggal yang ditentukan.</span>
                    </div>

                    <!-- Modal Footer -->
                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                        <button type="button" @click="showAddModal = false" class="px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
                            Batal
                        </button>
                        <button type="submit" class="btn-smooth-ring bg-[#009B77] hover:bg-[#008264] text-white font-bold py-2.5 px-6 rounded-xl shadow-[0_4px_12px_rgba(0,155,119,0.25)] transition-all flex items-center gap-2 text-sm">
                            <i class="ph-bold ph-paper-plane-tilt"></i>
                            <span>Simpan & Kirim Email</span>
                        </button>
                    </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    </template>

    <!-- MODAL 2: EDIT AKSES VIEWER -->
    <template x-teleport="body">
        <div x-show="showEditModal" 
             class="fixed inset-0 z-[100] overflow-y-auto"
             style="display: none;"
             x-cloak>
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="showEditModal = false"></div>

            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl border border-slate-100 overflow-hidden transform transition-all my-8"
                     @click.outside="showEditModal = false">
                
                <!-- Modal Header -->
                <div class="bg-slate-800 px-6 py-4 text-white flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-white/10 flex items-center justify-center backdrop-blur-sm">
                            <i class="ph-bold ph-pencil-simple text-xl text-white"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-base">Edit Akses Viewer</h3>
                            <p class="text-xs text-slate-300 font-medium">Perbarui data kredensial, tanggal aktif, atau status viewer</p>
                        </div>
                    </div>
                    <button type="button" @click="showEditModal = false" class="text-white/70 hover:text-white transition-colors">
                        <i class="ph-bold ph-x text-xl"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <form :action="'/guest-display/' + editGuest.id" method="POST" class="p-6 space-y-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="form_type" value="edit">

                    <!-- Nama Viewer -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama Viewer <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" x-model="editGuest.name" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all">
                    </div>

                    <!-- Instansi / Lembaga -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Instansi / Perusahaan <span class="text-rose-500">*</span></label>
                        <input type="text" name="institution" x-model="editGuest.institution" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all">
                    </div>

                    <!-- Email Viewer -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Email Viewer <span class="text-rose-500">*</span></label>
                        <input type="email" name="email" x-model="editGuest.email" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all">
                    </div>

                    <!-- PIN & Batas Tanggal -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">PIN Akses (4 Digit) <span class="text-rose-500">*</span></label>
                                <button type="button" @click="generatePin('edit')" class="text-[11px] font-bold text-[#009B77] hover:underline flex items-center gap-1">
                                    <i class="ph-bold ph-arrows-clockwise"></i> Acak
                                </button>
                            </div>
                            <input type="text" name="pin" x-model="editGuest.pin" required maxlength="4" pattern="[0-9]{4}" placeholder="1234" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 4)" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono font-bold tracking-widest focus:bg-white focus:outline-none focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Batas Berlaku <span class="text-rose-500">*</span></label>
                            <input type="date" name="valid_until" x-model="editGuest.valid_until" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all">
                        </div>
                    </div>

                    <!-- Toggle Status Aktif -->
                    <div class="pt-2">
                        <label class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200 cursor-pointer hover:bg-slate-100/70 transition-colors">
                            <input type="checkbox" name="is_active" value="1" x-model="editGuest.is_active" class="w-4 h-4 text-[#009B77] border-slate-300 rounded focus:ring-[#009B77]">
                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-slate-800">Aktifkan Akses Viewer</span>
                                <span class="text-[11px] text-slate-400 font-medium">Hapus centang untuk langsung memblokir akses viewer ini ke display report.</span>
                            </div>
                        </label>
                    </div>

                    <!-- Modal Footer -->
                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                        <button type="button" @click="showEditModal = false" class="px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
                            Batal
                        </button>
                        <button type="submit" class="btn-smooth-ring bg-slate-800 hover:bg-slate-900 text-white font-bold py-2.5 px-6 rounded-xl shadow-md transition-all flex items-center gap-2 text-sm">
                            <i class="ph-bold ph-floppy-disk"></i>
                            <span>Simpan Perubahan</span>
                        </button>
                    </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    </template>

    <!-- MODAL 3: KONFIRMASI HAPUS -->
    <template x-teleport="body">
        <div x-show="showDeleteModal" 
             class="fixed inset-0 z-[100] overflow-y-auto"
             style="display: none;"
             x-cloak>
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="showDeleteModal = false"></div>

            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl border border-slate-100 overflow-hidden p-6 text-center my-8"
                     @click.outside="showDeleteModal = false">
                
                <div class="w-14 h-14 rounded-2xl bg-rose-50 border border-rose-100 text-rose-500 mx-auto flex items-center justify-center mb-4">
                    <i class="ph-bold ph-trash text-2xl"></i>
                </div>

                <h3 class="text-base font-bold text-slate-800">Hapus Akses Viewer?</h3>
                <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                    Apakah Anda yakin ingin menghapus akses display untuk viewer <strong class="text-slate-700" x-text="deleteGuestName"></strong>? Tautan dan PIN viewer ini tidak akan dapat digunakan lagi.
                </p>

                <form :action="deleteUrl" method="POST" class="mt-6 flex items-center justify-center gap-3">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="showDeleteModal = false" class="px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors w-full">
                        {{ __('Batal') }}
                    </button>
                    <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white font-bold py-2.5 px-4 rounded-xl shadow-sm transition-all w-full">
                        {{ __('Ya, Hapus') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
    </template>

</div>
@endsection
