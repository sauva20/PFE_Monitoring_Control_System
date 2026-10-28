@extends('layouts.app')

@section('title', 'Manajemen User - PFE Monitoring Control System')

@section('content')
<div class="space-y-6" x-data="{ 
    showModalUser: {{ old('form_type') == 'tambah_user' && $errors->any() ? 'true' : 'false' }},
    showEditUser: {{ old('form_type') == 'edit_user' && $errors->any() ? 'true' : 'false' }},
    editUser: { 
        id:'{{ old('form_type') == 'edit_user' ? old('id') : '' }}', 
        employee_id:'{{ old('form_type') == 'edit_user' ? old('employee_id') : '' }}',
        name:'{{ old('form_type') == 'edit_user' ? old('name') : '' }}',
        email:'{{ old('form_type') == 'edit_user' ? old('email') : '' }}',
        role:'{{ old('form_type') == 'edit_user' ? old('role') : '' }}',
        jadwal_rutin_tanggal:'{{ old('form_type') == 'edit_user' ? old('jadwal_rutin_tanggal') : '' }}',
        gedungs: [],
        lokasis: []
    },
    originalEditUser: null,
    formRoleTambah: '{{ old('form_type') == 'tambah_user' ? old('role') : '' }}',
    formEmployeeIdTambah: '{{ old('form_type') == 'tambah_user' ? old('employee_id') : '' }}',
    formEmailTambah: '{{ old('form_type') == 'tambah_user' ? old('email') : '' }}',
    formPinTambah: '{{ old('form_type') == 'tambah_user' ? old('pin') : '' }}',
    isPinUnsafeTambah: false,
    isCheckingPinTambah: false,
    editPin: '',
    isPinUnsafeEdit: false,
    isCheckingPinEdit: false,
    pinCheckDebounceTimer: null,
    checkPinUnsafe(pin, userId = null, target = 'tambah') {
        if (this.pinCheckDebounceTimer) clearTimeout(this.pinCheckDebounceTimer);
        if (!pin || pin.length !== 4 || !/^\d{4}$/.test(pin)) {
            if (target === 'tambah') {
                this.isPinUnsafeTambah = false;
                this.isCheckingPinTambah = false;
            }
            if (target === 'edit') {
                this.isPinUnsafeEdit = false;
                this.isCheckingPinEdit = false;
            }
            return;
        }
        if (target === 'tambah') {
            this.isCheckingPinTambah = true;
            this.isPinUnsafeTambah = false;
        }
        if (target === 'edit') {
            this.isCheckingPinEdit = true;
            this.isPinUnsafeEdit = false;
        }
        this.pinCheckDebounceTimer = setTimeout(async () => {
            try {
                let url = `/users/check-pin?pin=${encodeURIComponent(pin)}`;
                if (userId) url += `&user_id=${encodeURIComponent(userId)}`;
                let res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                if (!res.ok) {
                    if (target === 'tambah') this.isCheckingPinTambah = false;
                    if (target === 'edit') this.isCheckingPinEdit = false;
                    return;
                }
                let data = await res.json();
                if (target === 'tambah') {
                    this.isPinUnsafeTambah = Boolean(data && data.safe === false);
                    this.isCheckingPinTambah = false;
                } else {
                    this.isPinUnsafeEdit = Boolean(data && data.safe === false);
                    this.isCheckingPinEdit = false;
                }
            } catch (e) {
                if (target === 'tambah') this.isCheckingPinTambah = false;
                if (target === 'edit') this.isCheckingPinEdit = false;
            }
        }, 200);
    },
    formGedungs: [],
    formLokasis: [],
    expandedGedungTambah: null,
    expandedGedungEdit: null,
    gedungsData: [
        @foreach($gedungs as $g)
            {
                id: '{{ $g->id }}',
                nama: '{{ addslashes($g->nama) }}',
                lokasis: [
                    @foreach($g->lokasi as $l)
                        {
                            id: '{{ $l->id }}',
                            nama: '{{ addslashes($l->nama) }}',
                            nomor_ruangan: '{{ addslashes($l->nomor_ruangan ?? '') }}',
                            lantai: '{{ addslashes($l->lantai ?? '') }}',
                            apar_count: '{{ $l->apar_count ?? $l->apar->count() }}',
                            users: [
                                @foreach($l->users as $u)
                                    { id: '{{ $u->id }}', name: '{{ addslashes($u->name) }}' },
                                @endforeach
                            ]
                        },
                    @endforeach
                ]
            },
        @endforeach
    ],
    takenUsers: {
        @foreach($allUsersForValidation as $u)
            '{{ $u->id }}': { employee_id: '{{ $u->employee_id }}', email: '{{ $u->email }}' },
        @endforeach
    },
    isEmployeeIdTaken(val, currentId = null) {
        if(!val) return false;
        return Object.entries(this.takenUsers).some(([id, data]) => data.employee_id === val && id !== String(currentId));
    },
    isEmailTaken(val, currentId = null) {
        if(!val) return false;
        return Object.entries(this.takenUsers).some(([id, data]) => data.email === val && id !== String(currentId));
    },
    gedungAssignments: {
        @foreach($gedungs as $g)
        '{{ $g->id }}': [
            @foreach($g->users as $u)
                { id: '{{ $u->id }}', name: '{{ addslashes($u->name) }}' },
            @endforeach
        ],
        @endforeach
    },
    getGedungWarning(gedungId, currentUserId = null) {
        let users = this.gedungAssignments[gedungId] || [];
        if (currentUserId) {
            users = users.filter(u => u.id != currentUserId);
        }
        if (users.length > 0) {
            let names = users.map(u => u.name).join(', ');
            return `{{ __('Already assigned to') }}: ${names}`;
        }
        return '';
    },
    getLokasiWarning(lokasiId, currentUserId = null) {
        for (let g of this.gedungsData) {
            let lok = g.lokasis.find(l => l.id == lokasiId);
            if (lok && lok.users) {
                let users = lok.users;
                if (currentUserId) users = users.filter(u => u.id != currentUserId);
                if (users.length > 0) {
                    return `{{ __('Already assigned to') }}: ${users.map(u => u.name).join(', ')}`;
                }
            }
        }
        return '';
    },
    countSelectedLokasiInGedung(gedungId, lokasisList) {
        let g = this.gedungsData.find(item => item.id == gedungId);
        if (!g || !g.lokasis) return 0;
        return g.lokasis.filter(l => lokasisList.map(String).includes(String(l.id))).length;
    },
    removeLokasisInGedung(gedungId, lokasisList) {
        let g = this.gedungsData.find(item => item.id == gedungId);
        if (!g || !g.lokasis) return;
        let lokIdsToRemove = g.lokasis.map(l => String(l.id));
        for (let i = lokasisList.length - 1; i >= 0; i--) {
            if (lokIdsToRemove.includes(String(lokasisList[i]))) {
                lokasisList.splice(i, 1);
            }
        }
    },
    isTambahSubmitDisabled() {
        let pinIncomplete = this.formPinTambah.length > 0 && this.formPinTambah.length < 4;
        return this.isCheckingPinTambah || 
               this.isPinUnsafeTambah || 
               pinIncomplete ||
               this.isEmployeeIdTaken(this.formEmployeeIdTambah) || 
               this.isEmailTaken(this.formEmailTambah);
    },
    isEditSubmitDisabled() {
        let pinIncomplete = this.editPin.length > 0 && this.editPin.length < 4;
        return this.isCheckingPinEdit || 
               this.isPinUnsafeEdit || 
               pinIncomplete ||
               this.isEmployeeIdTaken(this.editUser.employee_id, this.editUser.id) || 
               this.isEmailTaken(this.editUser.email, this.editUser.id) || 
               (JSON.stringify(this.editUser) === this.originalEditUser && !this.editPin);
    }
}">

    <!-- Header Area -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200/60">
            <!-- Left: Page Context -->
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-[#009B77]/10 border border-[#009B77]/20 shadow-sm flex items-center justify-center text-[#009B77]">
                <i class="ph-bold ph-users text-xl"></i>
            </div>
            <div>
                <h2 class="text-base font-bold text-[#007A5E] leading-tight">{{ __('User Management') }}</h2>
                <p class="text-xs font-semibold text-slate-500 mt-0.5">{{ __('Manage access rights and user data') }}</p>
            </div>
        </div>

        <!-- Right: Actions -->
        <div class="flex items-center gap-3">
            <div x-data="{ openExport: false }" class="relative z-40">
                <button @click="openExport = !openExport" @click.away="openExport = false" class="bg-white border border-slate-200/60 text-slate-600 hover:text-slate-900 hover:border-slate-300 hover:bg-slate-50 font-bold py-2.5 px-4 rounded-xl shadow-sm transition-all flex items-center gap-2 text-sm hover:-translate-y-0.5">
                    <i class="ph-bold ph-download-simple text-lg"></i>
                    <span class="hidden sm:inline">{{ __('Export Data') }}</span>
                    <i class="ph-bold ph-caret-down text-slate-400 ml-1 transition-transform" :class="openExport ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="openExport" 
                     x-transition.opacity.duration.200ms
                     class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg shadow-slate-200/50 border border-slate-100 py-2" x-cloak style="display: none;">
                    <a href="{{ route('users.export-pdf', request()->all()) }}" target="_blank" class="px-4 py-2 text-sm font-medium text-slate-600 hover:bg-red-50 hover:text-red-600 flex items-center gap-2 transition-colors">
                        <i class="ph-bold ph-file-pdf text-lg text-red-500"></i> {{ __('Export to PDF') }}
                    </a>
                    <a href="#" @click.prevent="$dispatch('open-excel-preview', { previewUrl: '{{ route('users.export-excel-preview', request()->all()) }}', downloadUrl: '{{ route('users.export-excel', request()->all()) }}' })" class="px-4 py-2 text-sm font-medium text-slate-600 hover:bg-green-50 hover:text-green-600 flex items-center gap-2 transition-colors">
                        <i class="ph-bold ph-file-csv text-lg text-green-500"></i> {{ __('Export to Excel (CSV)') }}
                    </a>
                </div>
            </div>
            <button @click="showModalUser = true; formPinTambah = ''; isPinUnsafeTambah = false; isCheckingPinTambah = false;" class="btn-smooth-ring bg-[#009B77] hover:bg-[#008264] text-white font-bold py-2.5 px-5 rounded-xl shadow-[0_4px_12px_rgba(0,155,119,0.25)] transition-all flex items-center gap-2 text-sm hover:-translate-y-0.5">
                <i class="ph-bold ph-plus text-lg"></i>
                <span>{{ __('Add User') }}</span>
            </button>
        </div>
    </div>

    <!-- Filters & Search -->
    <form action="/users" method="GET" class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Search -->
        <div class="md:col-span-2">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <i class="ph-bold ph-magnifying-glass text-slate-400 text-lg"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search user name or email...') }}" class="w-full pl-11 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all shadow-sm"
                       x-data
                       x-init="if ($el.value) { $nextTick(() => { $el.focus({ preventScroll: true }); let val = $el.value; $el.value = ''; $el.value = val; }) }"
                       @input.debounce.750ms="$el.form.submit()">
                @if(request('search'))
                <a href="/users?role={{ request('role') }}" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600">
                    <i class="ph-bold ph-x-circle text-lg"></i>
                </a>
                @endif
            </div>
        </div>

        <!-- Filter Role -->
        <div>
            <div x-data="{ open: false, selected: '{{ request('role') ? addslashes(request('role')) : __('All Roles') }}' }" class="relative">
                <input type="hidden" name="role" value="{{ request('role') }}" x-ref="role_input">
                <button type="button" @click="open = !open" @click.away="open = false" class="w-full flex items-center justify-between py-2.5 px-4 bg-white border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all cursor-pointer hover:border-slate-300 shadow-sm">
                    <div class="flex items-center gap-2 truncate">
                        <i class="ph-bold ph-funnel text-slate-400"></i>
                        <span x-text="selected" class="truncate"></span>
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
                     class="absolute right-0 z-50 w-full mt-2 bg-white rounded-xl shadow-[0_10px_40px_rgba(0,0,0,0.1)] border border-slate-100 py-2 max-h-60 overflow-y-auto origin-top" x-cloak>
                     
                    <button type="button" @click="selected = '{{ __('All Roles') }}'; open = false; $refs.role_input.value = ''; $refs.role_input.form.submit();" class="w-full text-left px-4 py-2.5 text-sm transition-colors flex items-center justify-between" :class="selected === '{{ __('All Roles') }}' ? 'text-[#009B77] bg-[#009B77]/5' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'">
                        <span>{{ __('All Roles') }}</span>
                        <i class="ph-bold ph-check text-[#009B77]" x-show="selected === '{{ __('All Roles') }}'" x-cloak></i>
                    </button>
                    
                    @foreach($roles as $role)
                    <button type="button" @click="selected = '{{ addslashes($role) }}'; open = false; $refs.role_input.value = '{{ addslashes($role) }}'; $refs.role_input.form.submit();" class="w-full text-left px-4 py-2.5 text-sm transition-colors flex items-center justify-between" :class="selected === '{{ addslashes($role) }}' ? 'text-[#009B77] bg-[#009B77]/5' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'">
                        <span>{{ $role }}</span>
                        <i class="ph-bold ph-check text-[#009B77]" x-show="selected === '{{ addslashes($role) }}'" x-cloak></i>
                    </button>
                    @endforeach
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
                        <th class="py-4 px-5 text-xs font-bold uppercase tracking-wider">User ID</th>
                        <th class="py-4 px-5 text-xs font-bold uppercase tracking-wider">{{ __('Name') }}</th>
                        <th class="py-4 px-5 text-xs font-bold uppercase tracking-wider">Email</th>
                        <th class="py-4 px-5 text-xs font-bold uppercase tracking-wider">{{ __('PIC BUILDING') }}</th>
                        <th class="py-4 px-5 text-xs font-bold uppercase tracking-wider">Role</th>
                        <th class="py-4 px-5 text-xs font-bold uppercase tracking-wider">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-100">
                    @forelse($users as $index => $user)
                    <tr class="hover:bg-slate-50 transition-colors divide-x divide-slate-100">
                        <td class="py-4 px-5 text-center text-slate-500">{{ $users->firstItem() + $index }}</td>
                        <td class="py-4 px-5 text-slate-800 font-medium">{{ $user->employee_id ?? 'n/a' }}</td>
                        <td class="py-4 px-5">
                            <span class="text-slate-800">{{ $user->name }}</span>
                        </td>
                        <td class="py-4 px-5 text-slate-600">{{ $user->email }}</td>
                        <td class="py-4 px-5 text-slate-600">
                            @if($user->role === 'EHSS')
                                <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs font-bold">{{ __('All Buildings') }}</span>
                            @elseif($user->gedungs->count() > 0 || $user->lokasis->count() > 0)
                                <div class="flex flex-wrap gap-1.5 items-center">
                                @foreach($user->gedungs as $gedung)
                                    <span class="bg-emerald-100 text-emerald-800 border border-emerald-200 px-2 py-0.5 rounded text-xs font-bold flex items-center gap-1" title="{{ $gedung->nama }} ({{ __('All Rooms') }})">
                                        <i class="ph-bold ph-buildings text-[11px]"></i>
                                        {{ $gedung->nama }} <span class="text-[9.5px] opacity-75 font-normal">({{ __('All') }})</span>
                                    </span>
                                @endforeach
                                @foreach($user->lokasis as $lok)
                                    <span class="bg-teal-50 text-teal-800 border border-teal-200 px-2 py-0.5 rounded text-xs font-semibold flex items-center gap-1" title="{{ $lok->gedung->nama ?? '' }}: {{ $lok->nama }}">
                                        <i class="ph-bold ph-door text-[11px] text-teal-600"></i>
                                        <span class="font-bold text-[10px] text-teal-700">{{ $lok->gedung->nama ?? '' }}:</span>
                                        @if($lok->nomor_ruangan)
                                            <span class="font-mono text-[10.5px] font-bold text-[#009B77]">{{ $lok->nomor_ruangan }}</span>
                                        @else
                                            <span>{{ \Illuminate\Support\Str::limit($lok->nama, 16) }}</span>
                                        @endif
                                    </span>
                                @endforeach
                                </div>
                                @if($user->jadwal_rutin_tanggal)
                                    <div class="text-xs text-slate-500 mt-1.5 flex items-center gap-1"><i class="ph-bold ph-calendar-blank text-[#009B77]"></i> {{ __('Routine: Date') }} {{ $user->jadwal_rutin_tanggal }}</div>
                                @endif
                            @else
                                <span class="text-slate-400 text-xs italic">{{ __('Unassigned') }}</span>
                            @endif
                        </td>
                        <td class="py-4 px-5 text-center">
                            <span class="text-slate-600">{{ $user->role }}</span>
                        </td>
                        <td class="py-4 px-5">
                            <div class="flex items-center justify-center gap-1">
                                <button @click="editUser = { id: '{{ $user->id }}', employee_id: '{{ addslashes($user->employee_id) }}', name: '{{ addslashes($user->name) }}', email: '{{ addslashes($user->email) }}', role: '{{ addslashes($user->role) }}', jadwal_rutin_tanggal: '{{ $user->jadwal_rutin_tanggal }}', gedungs: {{ json_encode($user->gedungs->pluck('id')->map(fn($id) => (string)$id)) }}, lokasis: {{ json_encode($user->lokasis->pluck('id')->map(fn($id) => (string)$id)) }} }; originalEditUser = JSON.stringify(editUser); editPin = ''; isPinUnsafeEdit = false; isCheckingPinEdit = false; showEditUser = true;" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-slate-400 hover:text-amber-500 hover:bg-amber-50 transition-colors" title="{{ __('Edit') }}">
                                    <i class="ph-bold ph-pencil-simple text-base"></i>
                                </button>
                                <form action="/users/{{ $user->id }}" method="POST" class="inline" onsubmit="confirmDelete(event, '{{ __('This user will be permanently deleted!') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-slate-400 hover:text-red-500 hover:bg-red-50 transition-colors" title="{{ __('Delete') }}">
                                        <i class="ph-bold ph-trash text-base"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-500 font-medium">{{ __('No User data yet.') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($users->hasPages())
        <div class="px-6 py-4 border-t border-slate-200/60 bg-slate-50/50">
            {{ $users->links() }}
        </div>
        @endif
    </div>

    <!-- Modal {{ __('Add User') }} -->
    <div x-show="showModalUser" class="fixed inset-0 z-[100] overflow-y-auto" x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div x-show="showModalUser" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity bg-slate-900/40"></div>

            <div x-show="showModalUser" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative inline-block w-full max-w-2xl p-6 text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl sm:my-8" style="max-height: 85vh; overflow-y: auto;">
                
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">{{ __('Add New User') }}</h3>
                        <p class="text-xs font-semibold text-slate-500 mt-0.5">{{ __('Register a new account to the system') }}</p>
                    </div>
                    <button @click="showModalUser = false" class="text-slate-400 hover:text-slate-600 transition-colors p-2 hover:bg-slate-100 rounded-lg">
                        <i class="ph-bold ph-x text-lg"></i>
                    </button>
                </div>

                <form action="/users" method="POST" class="space-y-4" @submit="if(isTambahSubmitDisabled()){ $event.preventDefault(); return false; }">
                    @csrf
                    <input type="hidden" name="form_type" value="tambah_user">
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">User ID <span class="text-red-500">*</span></label>
                        <input type="text" name="employee_id" x-model="formEmployeeIdTambah" required placeholder="{{ __('Example: 123456') }}" class="w-full bg-slate-50 border border-slate-200/60 text-slate-800 text-sm rounded-xl focus:outline-none focus:ring-[#009B77] focus:border-[#009B77] block p-2.5 transition-colors font-medium" :class="isEmployeeIdTaken(formEmployeeIdTambah) ? 'border-red-300 bg-red-50 focus:border-red-500 focus:ring-red-500/20' : ''">
                        <p x-show="isEmployeeIdTaken(formEmployeeIdTambah)" class="mt-1.5 text-xs text-red-600 font-bold flex items-center gap-1.5" x-cloak><i class="ph-fill ph-warning-circle text-sm shrink-0"></i> <span>{{ __('This User ID is already registered. Please use a different User ID.') }}</span></p>
                        @if(old('form_type') == 'tambah_user') @error('employee_id') <p class="mt-1.5 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror @endif
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">{{ __('Full Name') }} <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('form_type') == 'tambah_user' ? old('name') : '' }}" required placeholder="{{ __('Enter full name') }}" class="w-full bg-slate-50 border border-slate-200/60 text-slate-800 text-sm rounded-xl focus:outline-none focus:ring-[#009B77] focus:border-[#009B77] block p-2.5 transition-colors font-medium">
                        @if(old('form_type') == 'tambah_user') @error('name') <p class="mt-1.5 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror @endif
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" x-model="formEmailTambah" required placeholder="user@ehss.id" class="w-full bg-slate-50 border border-slate-200/60 text-slate-800 text-sm rounded-xl focus:outline-none focus:ring-[#009B77] focus:border-[#009B77] block p-2.5 transition-colors font-medium" :class="isEmailTaken(formEmailTambah) ? 'border-red-300 bg-red-50 focus:border-red-500 focus:ring-red-500/20' : ''">
                        <p x-show="isEmailTaken(formEmailTambah)" class="mt-1.5 text-xs text-red-600 font-bold flex items-center gap-1.5" x-cloak><i class="ph-fill ph-warning-circle text-sm shrink-0"></i> <span>{{ __('This Email is already registered. Please use a different Email.') }}</span></p>
                        @if(old('form_type') == 'tambah_user') @error('email') <p class="mt-1.5 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror @endif
                    </div>

                    <div x-data="{ open: false }" class="relative">
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">{{ __('Role / Position') }} <span class="text-red-500">*</span></label>
                        <!-- Hidden input to submit the value -->
                        <input type="hidden" name="role" x-model="formRoleTambah">
                        
                        <button type="button" @click="open = !open" @click.away="open = false" class="w-full bg-slate-50 border border-slate-200/60 text-slate-800 text-sm rounded-xl focus:outline-none focus:ring-[#009B77] focus:border-[#009B77] p-2.5 transition-colors font-medium flex items-center justify-between">
                            <span x-text="formRoleTambah === '' ? '{{ __('Select Role') }}' : formRoleTambah" :class="formRoleTambah === '' ? 'text-slate-500' : 'text-slate-800'"></span>
                            <i class="ph-bold ph-caret-down text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                        </button>
                        
                        <div x-show="open" x-transition.opacity.duration.200ms class="absolute z-50 w-full mt-1 bg-white border border-slate-200/60 rounded-xl shadow-lg py-1 overflow-hidden" style="display: none;">
                            <div @click="formRoleTambah = 'EHSS'; open = false" class="px-4 py-2 text-sm text-slate-700 hover:bg-[#009B77]/10 hover:text-[#009B77] cursor-pointer font-medium transition-colors flex items-center justify-between group">
                                EHSS
                                <i class="ph-bold ph-check text-[#009B77] opacity-0 group-hover:opacity-100" x-show="formRoleTambah === 'EHSS'"></i>
                            </div>
                            <div @click="formRoleTambah = 'Staff'; open = false" class="px-4 py-2 text-sm text-slate-700 hover:bg-[#009B77]/10 hover:text-[#009B77] cursor-pointer font-medium transition-colors flex items-center justify-between group">
                                Staff
                                <i class="ph-bold ph-check text-[#009B77] opacity-0 group-hover:opacity-100" x-show="formRoleTambah === 'Staff'"></i>
                            </div>
                        </div>
                        @if(old('form_type') == 'tambah_user') @error('role') <p class="mt-1.5 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror @endif
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            {{ __('Inspection PIN') }} (4 {{ __('Digits') }})
                            <span class="text-slate-400 font-normal">({{ __('Optional, auto-generated if left blank') }})</span>
                        </label>
                        <input type="text" 
                               name="pin" 
                               x-model="formPinTambah" 
                               maxlength="4" 
                               pattern="[0-9]*" 
                               inputmode="numeric" 
                               @input="formPinTambah = formPinTambah.replace(/[^0-9]/g, ''); if(formPinTambah.length === 4) { checkPinUnsafe(formPinTambah, null, 'tambah'); } else { isPinUnsafeTambah = false; isCheckingPinTambah = false; }"
                               placeholder="{{ __('Example: 1234') }}" 
                               class="w-full bg-slate-50 border border-slate-200/60 text-slate-800 text-sm rounded-xl focus:outline-none focus:ring-[#009B77] focus:border-[#009B77] block p-2.5 transition-colors font-mono tracking-widest"
                               :class="(isPinUnsafeTambah) ? 'border-red-300 bg-red-50 focus:border-red-500 focus:ring-red-500/20 text-red-600' : ''">
                        <div x-show="isCheckingPinTambah" class="mt-1.5 text-xs text-amber-600 font-semibold flex items-center gap-1.5" x-cloak>
                            <i class="ph-bold ph-spinner animate-spin text-sm"></i>
                            <span>{{ __('Memeriksa keamanan PIN...') }}</span>
                        </div>
                        <p x-show="!isCheckingPinTambah && isPinUnsafeTambah" class="mt-1.5 text-xs text-red-600 font-bold flex items-center gap-1.5" x-cloak>
                            <i class="ph-fill ph-warning-circle text-sm shrink-0"></i>
                            <span>{{ __('PIN yang anda buat berbahaya dan tidak aman') }}</span>
                        </p>
                        @if(old('form_type') == 'tambah_user') @error('pin') <p class="mt-1.5 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror @endif
                    </div>

                    <!-- Pilihan Gedung dan Jadwal Rutin (Khusus Staff) -->
                    <div x-show="formRoleTambah === 'Staff'" x-cloak class="p-4 rounded-xl bg-slate-50 border border-slate-200/60 space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">{{ __('Monthly Inspection Routine Schedule Date (Optional)') }}</label>
                            <input type="number" 
                                   name="jadwal_rutin_tanggal" 
                                   min="1" 
                                   max="31" 
                                   maxlength="2" 
                                   value="{{ old('form_type') == 'tambah_user' ? old('jadwal_rutin_tanggal') : '' }}" 
                                   placeholder="{{ __('Example: 15 (inspected every 15th)') }}" 
                                   oninput="let v = this.value.replace(/[^0-9]/g, ''); if (v.length > 2) v = v.slice(0, 2); if (v !== '' && parseInt(v, 10) > 31) v = '31'; if (v !== '' && parseInt(v, 10) < 1) v = '1'; this.value = v;"
                                   class="w-full bg-white border border-slate-200/60 text-slate-800 text-sm rounded-xl focus:outline-none focus:ring-[#009B77] focus:border-[#009B77] block p-2.5 transition-colors font-medium">
                            <p class="mt-1 text-[11px] text-slate-500">{{ __('Numbers 1-31. H-3 email will be sent automatically.') }}</p>
                            @if(old('form_type') == 'tambah_user') @error('jadwal_rutin_tanggal') <p class="mt-1.5 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror @endif
                        </div>
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-xs font-bold text-slate-700">{{ __('Select Responsibility Area (Building / Specific Rooms)') }}</label>
                                <span class="text-[11px] font-semibold text-slate-400">{{ __('Full building or select rooms') }}</span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 items-start">
                                @foreach($gedungs as $g)
                                <div class="rounded-xl border transition-all overflow-hidden"
                                     :class="formGedungs.includes('{{ $g->id }}') ? 'border-[#009B77] bg-white shadow-sm ring-1 ring-[#009B77]/20' : (countSelectedLokasiInGedung('{{ $g->id }}', formLokasis) > 0 ? 'border-teal-400 bg-white shadow-sm ring-1 ring-teal-400/20' : 'border-slate-200 bg-white hover:border-slate-300')">
                                    <div class="p-2.5 flex items-center justify-between gap-1.5">
                                        <label class="flex items-center gap-2 cursor-pointer select-none flex-1 min-w-0">
                                            <input type="checkbox" name="gedungs[]" value="{{ $g->id }}" x-model="formGedungs"
                                                   @change="if(formGedungs.includes('{{ $g->id }}')) { removeLokasisInGedung('{{ $g->id }}', formLokasis); }"
                                                   class="w-4 h-4 text-[#009B77] bg-slate-100 border-slate-300 rounded focus:ring-[#009B77] focus:ring-2 shrink-0">
                                            <div class="flex items-center gap-1.5 min-w-0">
                                                <span class="text-xs font-bold text-slate-800 truncate">{{ $g->nama }}</span>
                                                <span x-show="formGedungs.includes('{{ $g->id }}')" class="text-[9px] font-extrabold text-[#009B77] bg-[#009B77]/10 px-1.5 py-0.5 rounded uppercase tracking-wider shrink-0" x-cloak>{{ __('All') }}</span>
                                                <span x-show="!formGedungs.includes('{{ $g->id }}') && countSelectedLokasiInGedung('{{ $g->id }}', formLokasis) > 0" class="text-[9.5px] font-extrabold text-teal-700 bg-teal-100 px-1.5 py-0.5 rounded shrink-0" x-cloak>
                                                    <span x-text="countSelectedLokasiInGedung('{{ $g->id }}', formLokasis)"></span>
                                                </span>
                                            </div>
                                        </label>

                                        @if($g->lokasi->count() > 0)
                                        <button type="button" 
                                                @click="expandedGedungTambah = (expandedGedungTambah === '{{ $g->id }}' ? null : '{{ $g->id }}')"
                                                class="px-2 py-1 rounded-lg text-[11px] font-bold flex items-center gap-1 transition-all shrink-0"
                                                :class="expandedGedungTambah === '{{ $g->id }}' ? 'bg-[#009B77] text-white shadow-sm' : (countSelectedLokasiInGedung('{{ $g->id }}', formLokasis) > 0 ? 'bg-teal-50 text-teal-700 hover:bg-teal-100' : 'bg-slate-100 text-slate-600 hover:bg-slate-200')">
                                            <span x-text="countSelectedLokasiInGedung('{{ $g->id }}', formLokasis) > 0 ? countSelectedLokasiInGedung('{{ $g->id }}', formLokasis) + ' {{ __('Rooms') }}' : '{{ __('Rooms') }} ({{ $g->lokasi->count() }})'"></span>
                                            <i class="ph-bold ph-caret-down text-[10px] transition-transform duration-200" :class="expandedGedungTambah === '{{ $g->id }}' ? 'rotate-180' : ''"></i>
                                        </button>
                                        @else
                                        <span class="text-[10.5px] text-slate-400 italic shrink-0">{{ __('No rooms') }}</span>
                                        @endif
                                    </div>

                                    <!-- Warning Gedung already assigned -->
                                    <div x-show="formGedungs.includes('{{ $g->id }}') && getGedungWarning('{{ $g->id }}')" class="px-2.5 pb-2 text-[10px] text-amber-600 font-bold flex items-center gap-1" x-cloak>
                                        <i class="ph-bold ph-warning-circle shrink-0"></i>
                                        <span class="truncate" x-text="getGedungWarning('{{ $g->id }}')"></span>
                                    </div>

                                    @if($g->lokasi->count() > 0)
                                    <div x-show="expandedGedungTambah === '{{ $g->id }}'" x-collapse class="border-t border-slate-100 bg-slate-50/80 p-2 space-y-1.5">
                                        <template x-if="formGedungs.includes('{{ $g->id }}')">
                                            <div class="p-2 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-[11px] font-medium flex items-center gap-1.5">
                                                <i class="ph-bold ph-check-circle text-emerald-600 shrink-0"></i>
                                                <span>{{ __('All rooms in this building are already included.') }}</span>
                                            </div>
                                        </template>

                                        <div x-show="!formGedungs.includes('{{ $g->id }}')" class="space-y-1 max-h-48 overflow-y-auto pr-1">
                                            <div class="flex items-center justify-between pb-1 text-[10.5px]">
                                                <span class="font-bold text-slate-500 uppercase">{{ $g->nama }}:</span>
                                                <span class="text-slate-400 font-semibold" x-text="countSelectedLokasiInGedung('{{ $g->id }}', formLokasis) + ' / {{ $g->lokasi->count() }}'"></span>
                                            </div>
                                            @foreach($g->lokasi as $lok)
                                            <label class="flex items-center justify-between p-1.5 rounded-lg border bg-white cursor-pointer hover:bg-teal-50/30 transition-all select-none"
                                                   :class="formLokasis.includes('{{ $lok->id }}') ? 'border-[#009B77] bg-[#009B77]/5 ring-1 ring-[#009B77]/20' : 'border-slate-200 hover:border-slate-300'">
                                                <div class="flex items-center gap-1.5 min-w-0 flex-1">
                                                    <input type="checkbox" name="lokasis[]" value="{{ $lok->id }}" x-model="formLokasis"
                                                           class="w-3.5 h-3.5 text-[#009B77] bg-slate-100 border-slate-300 rounded focus:ring-[#009B77] shrink-0">
                                                    @if($lok->nomor_ruangan)
                                                        <span class="px-1.5 py-0.5 rounded text-[9.5px] font-bold bg-[#009B77]/10 text-[#009B77] border border-[#009B77]/20 font-mono tracking-tight shrink-0">{{ $lok->nomor_ruangan }}</span>
                                                    @endif
                                                    <span class="text-[11.5px] font-bold text-slate-700 truncate">{{ $lok->nama }}</span>
                                                    @if($lok->lantai)
                                                        <span class="text-[9px] font-medium text-slate-400 bg-slate-100 px-1 py-0.5 rounded shrink-0">Lt. {{ $lok->lantai }}</span>
                                                    @endif
                                                    <span class="text-[9px] font-bold text-slate-500 bg-slate-100 border border-slate-200/80 px-1.5 py-0.5 rounded shrink-0">{{ $lok->apar_count ?? $lok->apar->count() }} APAR</span>
                                                </div>
                                                <div x-show="getLokasiWarning('{{ $lok->id }}')" class="text-[9px] text-amber-600 font-bold ml-1 shrink-0" x-cloak>
                                                    <i class="ph-bold ph-warning-circle" :title="getLokasiWarning('{{ $lok->id }}')"></i>
                                                </div>
                                            </label>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                            @if(old('form_type') == 'tambah_user') @error('gedungs') <p class="mt-1.5 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror @endif
                        </div>
                    </div>

                    <div class="bg-blue-50/80 text-blue-900 p-3 rounded-xl border border-blue-200/70 flex items-center gap-3 mt-4">
                        <div class="w-7 h-7 rounded-lg bg-blue-500/10 text-blue-600 flex items-center justify-center shrink-0">
                            <i class="ph-bold ph-info text-base"></i>
                        </div>
                        <p class="text-xs leading-relaxed font-medium text-blue-800 flex-1">
                            {{ __('PIN and password creation instructions will be sent automatically to the user\'s email after the account is created.') }}
                        </p>
                    </div>

                    <div class="mt-6 flex gap-3 justify-end pt-4 border-t border-slate-100">
                        <button type="button" @click="showModalUser = false" class="px-5 py-2.5 text-sm font-bold text-slate-600 bg-white border border-slate-200/60 hover:bg-slate-50 hover:text-slate-900 rounded-xl transition-colors">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit" :disabled="isTambahSubmitDisabled()" :class="isTambahSubmitDisabled() ? 'bg-slate-400 opacity-50 cursor-not-allowed' : 'bg-[#009B77] hover:bg-[#008264] hover:-translate-y-0.5 shadow-[0_4px_12px_rgba(0,155,119,0.25)]'" class="px-5 py-2.5 text-sm font-bold text-white rounded-xl transition-all flex items-center gap-2">
                            <i x-show="isCheckingPinTambah" class="ph-bold ph-spinner animate-spin text-base" x-cloak></i>
                            <span x-text="isCheckingPinTambah ? '{{ __('Memeriksa PIN...') }}' : '{{ __('Save User') }}'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit User -->
    <div x-show="showEditUser" class="fixed inset-0 z-[100] overflow-y-auto" x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div x-show="showEditUser" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity bg-slate-900/40"></div>

            <div x-show="showEditUser" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative inline-block w-full max-w-2xl p-6 text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl sm:my-8" style="max-height: 85vh; overflow-y: auto;">
                
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">{{ __('Edit User') }}</h3>
                        <p class="text-xs font-semibold text-slate-500 mt-0.5">{{ __('Edit user data') }}</p>
                    </div>
                    <button @click="showEditUser = false" class="text-slate-400 hover:text-slate-600 transition-colors p-2 hover:bg-slate-100 rounded-lg">
                        <i class="ph-bold ph-x text-lg"></i>
                    </button>
                </div>

                <form :action="`/users/${editUser.id}`" method="POST" class="space-y-4" @submit="if(isEditSubmitDisabled()){ $event.preventDefault(); return false; }">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="form_type" value="edit_user">
                    <input type="hidden" name="id" x-model="editUser.id">
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">User ID <span class="text-red-500">*</span></label>
                        <input type="text" name="employee_id" x-model="editUser.employee_id" required class="w-full bg-slate-50 border border-slate-200/60 text-slate-800 text-sm rounded-xl focus:outline-none focus:ring-[#009B77] focus:border-[#009B77] block p-2.5 transition-colors font-medium" :class="isEmployeeIdTaken(editUser.employee_id, editUser.id) ? 'border-red-300 bg-red-50 focus:border-red-500 focus:ring-red-500/20' : ''">
                        <p x-show="isEmployeeIdTaken(editUser.employee_id, editUser.id)" class="mt-1.5 text-xs text-red-600 font-bold flex items-center gap-1.5" x-cloak><i class="ph-fill ph-warning-circle text-sm shrink-0"></i> <span>{{ __('This User ID is already registered. Please use a different User ID.') }}</span></p>
                        @if(old('form_type') == 'edit_user') @error('employee_id') <p class="mt-1.5 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror @endif
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">{{ __('Full Name') }} <span class="text-red-500">*</span></label>
                        <input type="text" name="name" x-model="editUser.name" required placeholder="{{ __('Enter full name') }}" class="w-full bg-slate-50 border border-slate-200/60 text-slate-800 text-sm rounded-xl focus:outline-none focus:ring-[#009B77] focus:border-[#009B77] block p-2.5 transition-colors font-medium">
                        @if(old('form_type') == 'edit_user') @error('name') <p class="mt-1.5 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror @endif
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" x-model="editUser.email" required class="w-full bg-slate-50 border border-slate-200/60 text-slate-800 text-sm rounded-xl focus:outline-none focus:ring-[#009B77] focus:border-[#009B77] block p-2.5 transition-colors font-medium" :class="isEmailTaken(editUser.email, editUser.id) ? 'border-red-300 bg-red-50 focus:border-red-500 focus:ring-red-500/20' : ''">
                        <p x-show="isEmailTaken(editUser.email, editUser.id)" class="mt-1.5 text-xs text-red-600 font-bold leading-tight flex items-start gap-1" x-cloak><i class="ph-fill ph-warning-circle text-sm"></i> <span>{{ __('This Email is already registered. Please use a different Email.') }}</span></p>
                        @if(old('form_type') == 'edit_user') @error('email') <p class="mt-1.5 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror @endif
                    </div>

                    <div x-data="{ open: false }" class="relative">
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">{{ __('Role / Position') }} <span class="text-red-500">*</span></label>
                        <!-- Hidden input to submit the value -->
                        <input type="hidden" name="role" x-model="editUser.role">
                        
                        <button type="button" @click="open = !open" @click.away="open = false" class="w-full bg-slate-50 border border-slate-200/60 text-slate-800 text-sm rounded-xl focus:outline-none focus:ring-[#009B77] focus:border-[#009B77] p-2.5 transition-colors font-medium flex items-center justify-between">
                            <span x-text="editUser.role === '' ? '{{ __('Select Role') }}' : editUser.role" :class="editUser.role === '' ? 'text-slate-500' : 'text-slate-800'"></span>
                            <i class="ph-bold ph-caret-down text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                        </button>
                        
                        <div x-show="open" x-transition.opacity.duration.200ms class="absolute z-50 w-full mt-1 bg-white border border-slate-200/60 rounded-xl shadow-lg py-1 overflow-hidden" style="display: none;">
                            <div @click="editUser.role = 'EHSS'; open = false" class="px-4 py-2 text-sm text-slate-700 hover:bg-[#009B77]/10 hover:text-[#009B77] cursor-pointer font-medium transition-colors flex items-center justify-between group">
                                EHSS
                                <i class="ph-bold ph-check text-[#009B77] opacity-0 group-hover:opacity-100" x-show="editUser.role === 'EHSS'"></i>
                            </div>
                            <div @click="editUser.role = 'Staff'; open = false" class="px-4 py-2 text-sm text-slate-700 hover:bg-[#009B77]/10 hover:text-[#009B77] cursor-pointer font-medium transition-colors flex items-center justify-between group">
                                Staff
                                <i class="ph-bold ph-check text-[#009B77] opacity-0 group-hover:opacity-100" x-show="editUser.role === 'Staff'"></i>
                            </div>
                        </div>
                        @if(old('form_type') == 'edit_user') @error('role') <p class="mt-1.5 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror @endif
                    </div>

                    <!-- Pilihan Gedung dan Jadwal Rutin (Khusus Staff) -->
                    <div x-show="editUser.role === 'Staff'" x-cloak class="p-4 rounded-xl bg-slate-50 border border-slate-200/60 space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">{{ __('Monthly Inspection Routine Schedule Date (Optional)') }}</label>
                            <input type="number" 
                                   name="jadwal_rutin_tanggal" 
                                   min="1" 
                                   max="31" 
                                   maxlength="2" 
                                   x-model="editUser.jadwal_rutin_tanggal" 
                                   placeholder="{{ __('Example: 15 (inspected every 15th)') }}" 
                                   @input="let v = ($event.target.value || '').toString().replace(/[^0-9]/g, ''); if (v.length > 2) v = v.slice(0, 2); if (v !== '' && parseInt(v, 10) > 31) v = '31'; if (v !== '' && parseInt(v, 10) < 1) v = '1'; editUser.jadwal_rutin_tanggal = v; $event.target.value = v;"
                                   class="w-full bg-white border border-slate-200/60 text-slate-800 text-sm rounded-xl focus:outline-none focus:ring-[#009B77] focus:border-[#009B77] block p-2.5 transition-colors font-medium">
                            <p class="mt-1 text-[11px] text-slate-500">{{ __('Numbers 1-31. H-3 email will be sent automatically.') }}</p>
                            @if(old('form_type') == 'edit_user') @error('jadwal_rutin_tanggal') <p class="mt-1.5 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror @endif
                        </div>
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-xs font-bold text-slate-700">{{ __('Select Responsibility Area (Building / Specific Rooms)') }}</label>
                                <span class="text-[11px] font-semibold text-slate-400">{{ __('Full building or select rooms') }}</span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 items-start">
                                @foreach($gedungs as $g)
                                <div class="rounded-xl border transition-all overflow-hidden"
                                     :class="editUser.gedungs.map(String).includes('{{ $g->id }}') ? 'border-[#009B77] bg-white shadow-sm ring-1 ring-[#009B77]/20' : (countSelectedLokasiInGedung('{{ $g->id }}', editUser.lokasis) > 0 ? 'border-teal-400 bg-white shadow-sm ring-1 ring-teal-400/20' : 'border-slate-200 bg-white hover:border-slate-300')">
                                    <div class="p-2.5 flex items-center justify-between gap-1.5">
                                        <label class="flex items-center gap-2 cursor-pointer select-none flex-1 min-w-0">
                                            <input type="checkbox" name="gedungs[]" value="{{ $g->id }}" x-model="editUser.gedungs"
                                                   @change="if(editUser.gedungs.map(String).includes('{{ $g->id }}')) { removeLokasisInGedung('{{ $g->id }}', editUser.lokasis); }"
                                                   class="w-4 h-4 text-[#009B77] bg-slate-100 border-slate-300 rounded focus:ring-[#009B77] focus:ring-2 shrink-0">
                                            <div class="flex items-center gap-1.5 min-w-0">
                                                <span class="text-xs font-bold text-slate-800 truncate">{{ $g->nama }}</span>
                                                <span x-show="editUser.gedungs.map(String).includes('{{ $g->id }}')" class="text-[9px] font-extrabold text-[#009B77] bg-[#009B77]/10 px-1.5 py-0.5 rounded uppercase tracking-wider shrink-0" x-cloak>{{ __('All') }}</span>
                                                <span x-show="!editUser.gedungs.map(String).includes('{{ $g->id }}') && countSelectedLokasiInGedung('{{ $g->id }}', editUser.lokasis) > 0" class="text-[9.5px] font-extrabold text-teal-700 bg-teal-100 px-1.5 py-0.5 rounded shrink-0" x-cloak>
                                                    <span x-text="countSelectedLokasiInGedung('{{ $g->id }}', editUser.lokasis)"></span>
                                                </span>
                                            </div>
                                        </label>

                                        @if($g->lokasi->count() > 0)
                                        <button type="button" 
                                                @click="expandedGedungEdit = (expandedGedungEdit === '{{ $g->id }}' ? null : '{{ $g->id }}')"
                                                class="px-2 py-1 rounded-lg text-[11px] font-bold flex items-center gap-1 transition-all shrink-0"
                                                :class="expandedGedungEdit === '{{ $g->id }}' ? 'bg-[#009B77] text-white shadow-sm' : (countSelectedLokasiInGedung('{{ $g->id }}', editUser.lokasis) > 0 ? 'bg-teal-50 text-teal-700 hover:bg-teal-100' : 'bg-slate-100 text-slate-600 hover:bg-slate-200')">
                                            <span x-text="countSelectedLokasiInGedung('{{ $g->id }}', editUser.lokasis) > 0 ? countSelectedLokasiInGedung('{{ $g->id }}', editUser.lokasis) + ' {{ __('Rooms') }}' : '{{ __('Rooms') }} ({{ $g->lokasi->count() }})'"></span>
                                            <i class="ph-bold ph-caret-down text-[10px] transition-transform duration-200" :class="expandedGedungEdit === '{{ $g->id }}' ? 'rotate-180' : ''"></i>
                                        </button>
                                        @else
                                        <span class="text-[10.5px] text-slate-400 italic shrink-0">{{ __('No rooms') }}</span>
                                        @endif
                                    </div>

                                    <!-- Warning Gedung already assigned -->
                                    <div x-show="editUser.gedungs.map(String).includes('{{ $g->id }}') && getGedungWarning('{{ $g->id }}', editUser.id)" class="px-2.5 pb-2 text-[10px] text-amber-600 font-bold flex items-center gap-1" x-cloak>
                                        <i class="ph-bold ph-warning-circle shrink-0"></i>
                                        <span class="truncate" x-text="getGedungWarning('{{ $g->id }}', editUser.id)"></span>
                                    </div>

                                    @if($g->lokasi->count() > 0)
                                    <div x-show="expandedGedungEdit === '{{ $g->id }}'" x-collapse class="border-t border-slate-100 bg-slate-50/80 p-2 space-y-1.5">
                                        <template x-if="editUser.gedungs.map(String).includes('{{ $g->id }}')">
                                            <div class="p-2 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-[11px] font-medium flex items-center gap-1.5">
                                                <i class="ph-bold ph-check-circle text-emerald-600 shrink-0"></i>
                                                <span>{{ __('All rooms in this building are already included.') }}</span>
                                            </div>
                                        </template>

                                        <div x-show="!editUser.gedungs.map(String).includes('{{ $g->id }}')" class="space-y-1 max-h-48 overflow-y-auto pr-1">
                                            <div class="flex items-center justify-between pb-1 text-[10.5px]">
                                                <span class="font-bold text-slate-500 uppercase">{{ $g->nama }}:</span>
                                                <span class="text-slate-400 font-semibold" x-text="countSelectedLokasiInGedung('{{ $g->id }}', editUser.lokasis) + ' / {{ $g->lokasi->count() }}'"></span>
                                            </div>
                                            @foreach($g->lokasi as $lok)
                                            <label class="flex items-center justify-between p-1.5 rounded-lg border bg-white cursor-pointer hover:bg-teal-50/30 transition-all select-none"
                                                   :class="editUser.lokasis.map(String).includes('{{ $lok->id }}') ? 'border-[#009B77] bg-[#009B77]/5 ring-1 ring-[#009B77]/20' : 'border-slate-200 hover:border-slate-300'">
                                                <div class="flex items-center gap-1.5 min-w-0 flex-1">
                                                    <input type="checkbox" name="lokasis[]" value="{{ $lok->id }}" x-model="editUser.lokasis"
                                                           class="w-3.5 h-3.5 text-[#009B77] bg-slate-100 border-slate-300 rounded focus:ring-[#009B77] shrink-0">
                                                    @if($lok->nomor_ruangan)
                                                        <span class="px-1.5 py-0.5 rounded text-[9.5px] font-bold bg-[#009B77]/10 text-[#009B77] border border-[#009B77]/20 font-mono tracking-tight shrink-0">{{ $lok->nomor_ruangan }}</span>
                                                    @endif
                                                    <span class="text-[11.5px] font-bold text-slate-700 truncate">{{ $lok->nama }}</span>
                                                    @if($lok->lantai)
                                                        <span class="text-[9px] font-medium text-slate-400 bg-slate-100 px-1 py-0.5 rounded shrink-0">Lt. {{ $lok->lantai }}</span>
                                                    @endif
                                                    <span class="text-[9px] font-bold text-slate-500 bg-slate-100 border border-slate-200/80 px-1.5 py-0.5 rounded shrink-0">{{ $lok->apar_count ?? $lok->apar->count() }} APAR</span>
                                                </div>
                                                <div x-show="getLokasiWarning('{{ $lok->id }}', editUser.id)" class="text-[9px] text-amber-600 font-bold ml-1 shrink-0" x-cloak>
                                                    <i class="ph-bold ph-warning-circle" :title="getLokasiWarning('{{ $lok->id }}', editUser.id)"></i>
                                                </div>
                                            </label>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                            @if(old('form_type') == 'edit_user') @error('gedungs') <p class="mt-1.5 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror @endif
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">{{ __('Change Inspection PIN') }}</label>
                        <input type="text" 
                               name="pin" 
                               x-model="editPin" 
                               maxlength="4" 
                               pattern="[0-9]*" 
                               inputmode="numeric" 
                               @input="editPin = editPin.replace(/[^0-9]/g, ''); if(editPin.length === 4) { checkPinUnsafe(editPin, editUser.id, 'edit'); } else { isPinUnsafeEdit = false; isCheckingPinEdit = false; }"
                               placeholder="{{ __('4 digits (Leave blank if not changing PIN)') }}" 
                               class="w-full bg-slate-50 border border-slate-200/60 text-slate-800 text-sm rounded-xl focus:outline-none focus:ring-[#009B77] focus:border-[#009B77] block p-2.5 transition-colors font-mono tracking-widest"
                               :class="(isPinUnsafeEdit) ? 'border-red-300 bg-red-50 focus:border-red-500 focus:ring-red-500/20 text-red-600' : ''">
                        <div x-show="isCheckingPinEdit" class="mt-1.5 text-xs text-amber-600 font-semibold flex items-center gap-1.5" x-cloak>
                            <i class="ph-bold ph-spinner animate-spin text-sm"></i>
                            <span>{{ __('Memeriksa keamanan PIN...') }}</span>
                        </div>
                        <p x-show="!isCheckingPinEdit && isPinUnsafeEdit" class="mt-1.5 text-xs text-red-600 font-bold flex items-center gap-1.5" x-cloak>
                            <i class="ph-fill ph-warning-circle text-sm shrink-0"></i>
                            <span>{{ __('PIN yang anda buat berbahaya dan tidak aman') }}</span>
                        </p>
                        @if(old('form_type') == 'edit_user') @error('pin') <p class="mt-1.5 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror @endif
                    </div>


                    <div class="mt-6 flex gap-3 justify-end pt-4 border-t border-slate-100">
                        <button type="button" @click="showEditUser = false" class="px-5 py-2.5 text-sm font-bold text-slate-600 bg-white border border-slate-200/60 hover:bg-slate-50 hover:text-slate-900 rounded-xl transition-colors">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit" :disabled="isEditSubmitDisabled()" :class="isEditSubmitDisabled() ? 'bg-slate-400 opacity-50 cursor-not-allowed' : 'hover:bg-[#008264] hover:-translate-y-0.5 shadow-[0_4px_12px_rgba(0,155,119,0.25)]'" class="px-5 py-2.5 text-sm font-bold text-white bg-[#009B77] rounded-xl transition-all flex items-center gap-2">
                            <i x-show="isCheckingPinEdit" class="ph-bold ph-spinner animate-spin text-base" x-cloak></i>
                            <span x-text="isCheckingPinEdit ? '{{ __('Memeriksa PIN...') }}' : '{{ __('Save Changes') }}'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
    <x-excel-preview-modal />
@endsection


