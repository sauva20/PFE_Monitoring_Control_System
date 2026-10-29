<?php

$file = __DIR__.'/resources/views/master-data/index.blade.php';
$content = file_get_contents($file);

// Replace Tambah Lokasi select
$oldTambahSelect = <<<'HTML'
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Pilih Gedung</label>
                                <select name="gedung_id" required class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 px-4 text-sm font-semibold text-slate-800 focus:bg-white focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none appearance-none cursor-pointer">
                                    <option value="">Pilih Gedung</option>
                                    @foreach($gedungs as $ged)
                                        <option value="{{ $ged->id }}">{{ $ged->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
HTML;

$newCustomSelectTambah = <<<'HTML'
                            <div x-data="{ 
                                    open: false, 
                                    selectedId: '', 
                                    selectedName: 'Pilih Gedung',
                                    options: [
                                        @foreach($gedungs as $ged)
                                        { id: '{{ $ged->id }}', name: '{{ addslashes($ged->nama) }}' },
                                        @endforeach
                                    ] 
                                }" 
                                class="relative">
                                
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Pilih Gedung</label>
                                
                                <!-- Hidden input for form submission -->
                                <input type="hidden" name="gedung_id" :value="selectedId" required>
                                
                                <!-- Trigger -->
                                <button type="button" @click="open = !open" @click.away="open = false"
                                        class="w-full bg-slate-50 border-2 rounded-xl py-2.5 px-4 text-sm font-semibold text-left transition-all flex items-center justify-between"
                                        :class="open ? 'bg-white border-[#009B77] ring-4 ring-[#009B77]/15 text-slate-800' : 'border-slate-200 text-slate-700 hover:border-slate-300'">
                                    <span x-text="selectedName" :class="selectedId === '' ? 'text-slate-400' : ''"></span>
                                    <i class="ph-bold ph-caret-down text-slate-400 transition-transform duration-300" :class="open ? 'rotate-180 text-[#009B77]' : ''"></i>
                                </button>
                                
                                <!-- Dropdown Menu -->
                                <div x-show="open" x-cloak
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                     x-transition:leave="transition ease-in duration-100"
                                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                     x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                                     class="absolute z-50 w-full mt-2 bg-white rounded-xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.1)] border border-slate-100 py-2 max-h-60 overflow-y-auto">
                                     
                                    <template x-for="option in options" :key="option.id">
                                        <button type="button" @click="selectedId = option.id; selectedName = option.name; open = false"
                                                class="w-full text-left px-4 py-2.5 text-sm font-semibold transition-colors flex items-center justify-between"
                                                :class="selectedId === option.id ? 'bg-teal-50 text-[#009B77]' : 'text-slate-600 hover:bg-slate-50'">
                                            <span x-text="option.name"></span>
                                            <i class="ph-bold ph-check text-[#009B77]" x-show="selectedId === option.id" x-cloak></i>
                                        </button>
                                    </template>
                                    
                                    <div x-show="options.length === 0" x-cloak class="px-4 py-3 text-sm text-slate-400 text-center italic">
                                        Belum ada data gedung
                                    </div>
                                </div>
                            </div>
HTML;

$content = str_replace($oldTambahSelect, $newCustomSelectTambah, $content);

// Replace Edit Lokasi select
$oldEditSelect = <<<'HTML'
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Pilih Gedung</label>
                                <select name="gedung_id" x-model="editLokasi.gedung_id" required class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 px-4 text-sm font-semibold text-slate-800 focus:bg-white focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none appearance-none cursor-pointer">
                                    <option value="">Pilih Gedung</option>
                                    @foreach($gedungs as $ged)
                                        <option value="{{ $ged->id }}">{{ $ged->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
HTML;

$newCustomSelectEdit = <<<'HTML'
                            <div x-data="{ 
                                    open: false,
                                    options: [
                                        @foreach($gedungs as $ged)
                                        { id: '{{ $ged->id }}', name: '{{ addslashes($ged->nama) }}' },
                                        @endforeach
                                    ],
                                    get selectedName() {
                                        let selected = this.options.find(o => o.id == editLokasi.gedung_id);
                                        return selected ? selected.name : 'Pilih Gedung';
                                    }
                                }" 
                                class="relative">
                                
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Pilih Gedung</label>
                                
                                <!-- Hidden input for form submission -->
                                <input type="hidden" name="gedung_id" :value="editLokasi.gedung_id" required>
                                
                                <!-- Trigger -->
                                <button type="button" @click="open = !open" @click.away="open = false"
                                        class="w-full bg-slate-50 border-2 rounded-xl py-2.5 px-4 text-sm font-semibold text-left transition-all flex items-center justify-between"
                                        :class="open ? 'bg-white border-[#009B77] ring-4 ring-[#009B77]/15 text-slate-800' : 'border-slate-200 text-slate-700 hover:border-slate-300'">
                                    <span x-text="selectedName" :class="!editLokasi.gedung_id ? 'text-slate-400' : ''"></span>
                                    <i class="ph-bold ph-caret-down text-slate-400 transition-transform duration-300" :class="open ? 'rotate-180 text-[#009B77]' : ''"></i>
                                </button>
                                
                                <!-- Dropdown Menu -->
                                <div x-show="open" x-cloak
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                     x-transition:leave="transition ease-in duration-100"
                                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                     x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                                     class="absolute z-50 w-full mt-2 bg-white rounded-xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.1)] border border-slate-100 py-2 max-h-60 overflow-y-auto">
                                     
                                    <template x-for="option in options" :key="option.id">
                                        <button type="button" @click="editLokasi.gedung_id = option.id; open = false"
                                                class="w-full text-left px-4 py-2.5 text-sm font-semibold transition-colors flex items-center justify-between"
                                                :class="editLokasi.gedung_id == option.id ? 'bg-teal-50 text-[#009B77]' : 'text-slate-600 hover:bg-slate-50'">
                                            <span x-text="option.name"></span>
                                            <i class="ph-bold ph-check text-[#009B77]" x-show="editLokasi.gedung_id == option.id" x-cloak></i>
                                        </button>
                                    </template>
                                    
                                    <div x-show="options.length === 0" x-cloak class="px-4 py-3 text-sm text-slate-400 text-center italic">
                                        Belum ada data gedung
                                    </div>
                                </div>
                            </div>
HTML;

$content = str_replace($oldEditSelect, $newCustomSelectEdit, $content);

file_put_contents($file, $content);
echo "Dropdowns patched.\n";
