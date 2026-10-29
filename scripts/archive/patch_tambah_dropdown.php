<?php

$file = __DIR__.'/resources/views/master-data/tambah.blade.php';
$content = file_get_contents($file);

// Replace form action
$content = str_replace('<form action="#" method="POST" class="p-8 space-y-8">', '<form action="/master-data/apar" method="POST" class="p-8 space-y-8">', $content);
// Add csrf
$content = preg_replace('/(<form[^>]+>)/', '$1'."\n".'            @csrf', $content);

// 1. Lokasi Penempatan
$lokasiDropdown = <<<'HTML'
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Lokasi Penempatan</label>
                    <div x-data="{ 
                            open: false, 
                            selectedId: '{{ old('lokasi_id') }}',
                            options: [
                                @foreach($lokasis as $lok)
                                { id: '{{ $lok->id }}', name: '{{ addslashes($lok->nama) }} ({{ addslashes($lok->gedung->nama ?? '-') }})' },
                                @endforeach
                            ],
                            get selectedName() {
                                let sel = this.options.find(o => o.id == this.selectedId);
                                return sel ? sel.name : 'Pilih Lokasi';
                            }
                        }" class="relative">
                        <input type="hidden" name="lokasi_id" :value="selectedId" required>
                        <i class="ph-bold ph-map-pin absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-lg transition-colors z-10" :class="open ? 'text-[#009B77]' : ''"></i>
                        <button type="button" @click="open = !open" @click.away="open = false" 
                                class="w-full bg-slate-50/50 border-2 border-slate-200 rounded-xl py-3 pl-11 pr-4 text-sm font-bold text-slate-800 focus:bg-white focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none cursor-pointer text-left flex items-center justify-between"
                                :class="open ? 'bg-white border-[#009B77] ring-4 ring-[#009B77]/15' : ''">
                            <span x-text="selectedName" :class="!selectedId ? 'text-slate-400 font-medium' : ''" class="truncate block"></span>
                            <i class="ph-bold ph-caret-down text-slate-400 transition-transform duration-200 flex-shrink-0" :class="open ? 'rotate-180 text-[#009B77]' : ''"></i>
                        </button>
                        
                        <!-- Dropdown Menu -->
                        <div x-show="open" x-cloak
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute left-0 z-50 w-full mt-2 bg-white rounded-xl shadow-[0_10px_40px_rgba(0,0,0,0.1)] border border-slate-100 py-2 max-h-60 overflow-y-auto origin-top">
                            <template x-for="option in options" :key="option.id">
                                <button type="button" @click="selectedId = option.id; open = false" class="w-full text-left px-4 py-2.5 text-sm font-bold transition-colors flex items-center justify-between" :class="selectedId == option.id ? 'text-[#009B77] bg-[#009B77]/5' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'">
                                    <span x-text="option.name"></span>
                                    <i class="ph-bold ph-check text-[#009B77]" x-show="selectedId == option.id" x-cloak></i>
                                </button>
                            </template>
                        </div>
                    </div>
HTML;
$content = preg_replace('/<label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Lokasi Penempatan<\/label>.*?<\/div>\s*<\/div>\s*<\/div>/s', $lokasiDropdown."\n                </div>", $content, 1);

// 2. Jenis APAR
$jenisDropdown = <<<'HTML'
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Jenis APAR</label>
                    <div x-data="{ 
                            open: false, 
                            selectedId: '{{ old('jenis_id') }}',
                            options: [
                                @foreach($jenisApars as $jenis)
                                { id: '{{ $jenis->id }}', name: '{{ addslashes($jenis->nama) }}' },
                                @endforeach
                            ],
                            get selectedName() {
                                let sel = this.options.find(o => o.id == this.selectedId);
                                return sel ? sel.name : 'Pilih Jenis';
                            }
                        }" class="relative">
                        <input type="hidden" name="jenis_id" :value="selectedId" required>
                        <i class="ph-bold ph-fire-extinguisher absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-lg transition-colors z-10" :class="open ? 'text-[#009B77]' : ''"></i>
                        <button type="button" @click="open = !open" @click.away="open = false" 
                                class="w-full bg-slate-50/50 border-2 border-slate-200 rounded-xl py-3 pl-11 pr-4 text-sm font-bold text-slate-800 focus:bg-white focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none cursor-pointer text-left flex items-center justify-between"
                                :class="open ? 'bg-white border-[#009B77] ring-4 ring-[#009B77]/15' : ''">
                            <span x-text="selectedName" :class="!selectedId ? 'text-slate-400 font-medium' : ''" class="truncate block"></span>
                            <i class="ph-bold ph-caret-down text-slate-400 transition-transform duration-200 flex-shrink-0" :class="open ? 'rotate-180 text-[#009B77]' : ''"></i>
                        </button>
                        
                        <!-- Dropdown Menu -->
                        <div x-show="open" x-cloak
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute left-0 z-50 w-full mt-2 bg-white rounded-xl shadow-[0_10px_40px_rgba(0,0,0,0.1)] border border-slate-100 py-2 max-h-60 overflow-y-auto origin-top">
                            <template x-for="option in options" :key="option.id">
                                <button type="button" @click="selectedId = option.id; open = false" class="w-full text-left px-4 py-2.5 text-sm font-bold transition-colors flex items-center justify-between" :class="selectedId == option.id ? 'text-[#009B77] bg-[#009B77]/5' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'">
                                    <span x-text="option.name"></span>
                                    <i class="ph-bold ph-check text-[#009B77]" x-show="selectedId == option.id" x-cloak></i>
                                </button>
                            </template>
                        </div>
                    </div>
HTML;
$content = preg_replace('/<label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Jenis APAR<\/label>.*?<\/div>\s*<\/div>\s*<\/div>/s', $jenisDropdown."\n                </div>", $content, 1);

// 3. Kapasitas
$kapasitasDropdown = <<<'HTML'
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Kapasitas</label>
                    <div x-data="{ 
                            open: false, 
                            selectedId: '{{ old('kapasitas_id') }}',
                            options: [
                                @foreach($kapasitasApars as $kap)
                                { id: '{{ $kap->id }}', name: '{{ addslashes($kap->ukuran) }}' },
                                @endforeach
                            ],
                            get selectedName() {
                                let sel = this.options.find(o => o.id == this.selectedId);
                                return sel ? sel.name : 'Pilih Kapasitas';
                            }
                        }" class="relative">
                        <input type="hidden" name="kapasitas_id" :value="selectedId" required>
                        <i class="ph-bold ph-scales absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-lg transition-colors z-10" :class="open ? 'text-[#009B77]' : ''"></i>
                        <button type="button" @click="open = !open" @click.away="open = false" 
                                class="w-full bg-slate-50/50 border-2 border-slate-200 rounded-xl py-3 pl-11 pr-4 text-sm font-bold text-slate-800 focus:bg-white focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none cursor-pointer text-left flex items-center justify-between"
                                :class="open ? 'bg-white border-[#009B77] ring-4 ring-[#009B77]/15' : ''">
                            <span x-text="selectedName" :class="!selectedId ? 'text-slate-400 font-medium' : ''" class="truncate block"></span>
                            <i class="ph-bold ph-caret-down text-slate-400 transition-transform duration-200 flex-shrink-0" :class="open ? 'rotate-180 text-[#009B77]' : ''"></i>
                        </button>
                        
                        <!-- Dropdown Menu -->
                        <div x-show="open" x-cloak
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute left-0 z-50 w-full mt-2 bg-white rounded-xl shadow-[0_10px_40px_rgba(0,0,0,0.1)] border border-slate-100 py-2 max-h-60 overflow-y-auto origin-top">
                            <template x-for="option in options" :key="option.id">
                                <button type="button" @click="selectedId = option.id; open = false" class="w-full text-left px-4 py-2.5 text-sm font-bold transition-colors flex items-center justify-between" :class="selectedId == option.id ? 'text-[#009B77] bg-[#009B77]/5' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'">
                                    <span x-text="option.name"></span>
                                    <i class="ph-bold ph-check text-[#009B77]" x-show="selectedId == option.id" x-cloak></i>
                                </button>
                            </template>
                        </div>
                    </div>
HTML;
$content = preg_replace('/<label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Kapasitas<\/label>.*?<\/div>\s*<\/div>\s*<\/div>/s', $kapasitasDropdown."\n                </div>", $content, 1);

// Add missing name attributes for text inputs in tambah.blade.php
$content = str_replace('<input type="text" placeholder="Contoh: APAR-046"', '<input type="text" name="kode" value="{{ old(\'kode\') }}" required placeholder="Contoh: APAR-046"', $content);
$content = str_replace('<input type="text" placeholder="Nama perusahaan vendor..."', '<input type="text" name="vendor" value="{{ old(\'vendor\') }}" placeholder="Nama perusahaan vendor..."', $content);
$content = str_replace('<input type="date"', '<input type="date" name="tgl_kedaluwarsa" value="{{ old(\'tgl_kedaluwarsa\') }}"', $content);

file_put_contents($file, $content);
echo "Tambah patched.\n";
