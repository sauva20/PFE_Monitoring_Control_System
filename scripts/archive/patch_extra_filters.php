<?php

$file = __DIR__.'/resources/views/master-data/index.blade.php';
$content = file_get_contents($file);

// Replace grid wrapper
$content = str_replace('<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">', '<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">', $content);

// Add Gedung before Lokasi
$gedungHtml = <<<'HTML'
            <!-- Filter Gedung -->
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Gedung</label>
                <div x-data="{ open: false, selected: '{{ request('gedung_id') ? addslashes($gedungs->firstWhere('id', request('gedung_id'))->nama ?? 'Semua Gedung') : 'Semua Gedung' }}' }" class="relative">
                    <input type="hidden" name="gedung_id" value="{{ request('gedung_id') }}" x-ref="gedung_input">
                    <button type="button" @click="open = !open" @click.away="open = false" class="w-full flex items-center justify-between py-2.5 px-3.5 bg-white border-2 border-slate-200 rounded-xl text-sm font-semibold text-slate-700 focus:outline-none focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all cursor-pointer hover:border-slate-300">
                        <span x-text="selected" class="truncate"></span>
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
                         class="absolute left-0 z-50 w-full mt-2 bg-white rounded-xl shadow-[0_10px_40px_rgba(0,0,0,0.1)] border border-slate-100 py-2 max-h-60 overflow-y-auto origin-top" x-cloak>
                         
                        <button type="button" @click="selected = 'Semua Gedung'; open = false; $refs.gedung_input.value = ''; $refs.gedung_input.form.submit();" class="w-full text-left px-4 py-2.5 text-sm font-medium transition-colors flex items-center justify-between" :class="selected === 'Semua Gedung' ? 'text-[#009B77] bg-[#009B77]/5' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'">
                            <span>Semua Gedung</span>
                            <i class="ph-bold ph-check text-[#009B77]" x-show="selected === 'Semua Gedung'" x-cloak></i>
                        </button>
                        
                        @foreach($gedungs as $ged)
                        <button type="button" @click="selected = '{{ addslashes($ged->nama) }}'; open = false; $refs.gedung_input.value = '{{ $ged->id }}'; $refs.gedung_input.form.submit();" class="w-full text-left px-4 py-2.5 text-sm font-medium transition-colors flex items-center justify-between" :class="selected === '{{ addslashes($ged->nama) }}' ? 'text-[#009B77] bg-[#009B77]/5' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'">
                            <span>{{ $ged->nama }}</span>
                            <i class="ph-bold ph-check text-[#009B77]" x-show="selected === '{{ addslashes($ged->nama) }}'" x-cloak></i>
                        </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Filter Lokasi -->
HTML;
$content = str_replace('<!-- Filter Lokasi -->', $gedungHtml, $content);

// Add Kapasitas after Jenis
$kapasitasHtml = <<<'HTML'
                    </div>
                </div>
            </div>

            <!-- Filter Kapasitas -->
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Kapasitas</label>
                <div x-data="{ open: false, selected: '{{ request('kapasitas_id') ? addslashes($kapasitasApars->firstWhere('id', request('kapasitas_id'))->ukuran ?? 'Semua Kapasitas') : 'Semua Kapasitas' }}' }" class="relative">
                    <input type="hidden" name="kapasitas_id" value="{{ request('kapasitas_id') }}" x-ref="kapasitas_input">
                    <button type="button" @click="open = !open" @click.away="open = false" class="w-full flex items-center justify-between py-2.5 px-3.5 bg-white border-2 border-slate-200 rounded-xl text-sm font-semibold text-slate-700 focus:outline-none focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all cursor-pointer hover:border-slate-300">
                        <span x-text="selected" class="truncate"></span>
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
                         class="absolute left-0 z-50 w-full mt-2 bg-white rounded-xl shadow-[0_10px_40px_rgba(0,0,0,0.1)] border border-slate-100 py-2 max-h-60 overflow-y-auto origin-top" x-cloak>
                         
                        <button type="button" @click="selected = 'Semua Kapasitas'; open = false; $refs.kapasitas_input.value = ''; $refs.kapasitas_input.form.submit();" class="w-full text-left px-4 py-2.5 text-sm font-medium transition-colors flex items-center justify-between" :class="selected === 'Semua Kapasitas' ? 'text-[#009B77] bg-[#009B77]/5' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'">
                            <span>Semua Kapasitas</span>
                            <i class="ph-bold ph-check text-[#009B77]" x-show="selected === 'Semua Kapasitas'" x-cloak></i>
                        </button>
                        
                        @foreach($kapasitasApars as $kap)
                        <button type="button" @click="selected = '{{ addslashes($kap->ukuran) }}'; open = false; $refs.kapasitas_input.value = '{{ $kap->id }}'; $refs.kapasitas_input.form.submit();" class="w-full text-left px-4 py-2.5 text-sm font-medium transition-colors flex items-center justify-between" :class="selected === '{{ addslashes($kap->ukuran) }}' ? 'text-[#009B77] bg-[#009B77]/5' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'">
                            <span>{{ $kap->ukuran }}</span>
                            <i class="ph-bold ph-check text-[#009B77]" x-show="selected === '{{ addslashes($kap->ukuran) }}'" x-cloak></i>
                        </button>
                        @endforeach
                    </div>
                </div>
            </div>
HTML;
// I will replace the end of Jenis filter div to append the Kapasitas filter.
$jenisEndSearch = <<<'HTML'
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
HTML;
$jenisEndReplace = $kapasitasHtml."\n        </div>";
$content = str_replace($jenisEndSearch, $jenisEndReplace, $content);

file_put_contents($file, $content);

$controllerFile = __DIR__.'/app/Http/Controllers/MasterDataController.php';
$controllerContent = file_get_contents($controllerFile);

$controllerSearch = <<<'PHP'
        if ($request->filled('lokasi_id')) {
            $query->where('lokasi_id', $request->lokasi_id);
        }
PHP;

$controllerReplace = <<<'PHP'
        if ($request->filled('gedung_id')) {
            $query->whereHas('lokasi', function($q) use ($request) {
                $q->where('gedung_id', $request->gedung_id);
            });
        }
        
        if ($request->filled('lokasi_id')) {
            $query->where('lokasi_id', $request->lokasi_id);
        }
        
        if ($request->filled('kapasitas_id')) {
            $query->where('kapasitas_id', $request->kapasitas_id);
        }
PHP;

$controllerContent = str_replace($controllerSearch, $controllerReplace, $controllerContent);
file_put_contents($controllerFile, $controllerContent);

echo 'Extra filters patched.';
