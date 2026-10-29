<?php

$indexFile = __DIR__.'/resources/views/master-data/index.blade.php';
$content = file_get_contents($indexFile);

function generateDropdown($label, $icon, $name, $selectedIdBind, $optionsLoop, $emptyLabel)
{
    return <<<HTML
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">$label</label>
                <div x-data="{
                        open: false,
                        search: '',
                        selectedId: $selectedIdBind,
                        options: [
$optionsLoop
                        ],
                        get selectedName() {
                            let sel = this.options.find(o => o.id == this.selectedId);
                            return sel ? sel.name : '$emptyLabel';
                        },
                        get filteredOptions() {
                            if (this.search === '') return this.options;
                            return this.options.filter(o => o.name.toLowerCase().includes(this.search.toLowerCase()));
                        }
                    }" class="relative">
                    <input type="hidden" name="$name" :value="selectedId" required>
                    <i class="ph-bold ph-$icon absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg transition-colors z-10" :class="open ? 'text-[#009B77]' : 'peer-focus:text-[#009B77]'"></i>
                    <button type="button" @click="open = !open" @click.away="open = false" 
                            class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 pl-10 pr-4 text-sm font-medium text-slate-700 focus:bg-white focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none cursor-pointer text-left flex items-center justify-between"
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
                        <div class="sticky top-0 bg-white p-2 border-b border-slate-100 z-10">
                            <div class="relative">
                                <i class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                <input type="text" x-model="search" placeholder="Cari..." class="w-full bg-slate-50 border border-slate-200 rounded-lg py-2 pl-9 pr-3 text-sm font-medium text-slate-700 focus:outline-none focus:border-[#009B77] focus:ring-2 focus:ring-[#009B77]/20 transition-all" @click.stop @keydown.enter.prevent>
                            </div>
                        </div>
                        <div class="py-1">
                            <template x-for="option in filteredOptions" :key="option.id">
                            <button type="button" @click="selectedId = option.id; open = false" class="w-full text-left px-4 py-2.5 text-sm font-medium transition-colors flex items-center justify-between" :class="selectedId == option.id ? 'text-[#009B77] bg-[#009B77]/5' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'">
                                <span x-text="option.name"></span>
                                <i class="ph-bold ph-check text-[#009B77]" x-show="selectedId == option.id" x-cloak></i>
                            </button>
                            </template>
                            <div x-show="filteredOptions.length === 0" class="py-3 px-4 text-center text-sm font-medium text-slate-500">
                                Pencarian tidak ditemukan
                            </div>
                        </div>
                    </div>
                </div>
                @error('$name') <span class="text-xs text-red-500 mt-1 block">{{ \$message }}</span> @enderror
            </div>
HTML;
}

$lokasiLoop = <<<'HTML'
                            @foreach($lokasis as $lokasi)
                            { id: '{{ $lokasi->id }}', name: '{{ addslashes($lokasi->nama) }} - {{ addslashes($lokasi->gedung->nama) }}' },
                            @endforeach
HTML;

$jenisLoop = <<<'HTML'
                            @foreach($jenisApars as $jenis)
                            { id: '{{ $jenis->id }}', name: '{{ addslashes($jenis->nama) }}' },
                            @endforeach
HTML;

$kapasitasLoop = <<<'HTML'
                            @foreach($kapasitasApars as $kap)
                            { id: '{{ $kap->id }}', name: '{{ addslashes($kap->ukuran) }}' },
                            @endforeach
HTML;

// 1. Fix Tambah Form
$tambahLokasi = "{generateDropdown('Lokasi Penempatan', 'map-pin', 'lokasi_id', \"'{{ old('lokasi_id') }}'\", \n".$lokasiLoop."\n, 'Pilih Lokasi')}";
$tambahJenis = "{generateDropdown('Jenis APAR', 'fire-extinguisher', 'jenis_id', \"'{{ old('jenis_id') }}'\", \n".$jenisLoop."\n, 'Pilih Jenis')}";
$tambahKapasitas = "{generateDropdown('Kapasitas', 'scales', 'kapasitas_id', \"'{{ old('kapasitas_id') }}'\", \n".$kapasitasLoop."\n, 'Pilih Kapasitas')}";

// If the regex above doesn't exactly match whitespace, we can use a simpler regex
$content = preg_replace('/\{generateDropdown\(\'Lokasi Penempatan\', \'map-pin\', \'lokasi_id\'.*?\'Pilih Lokasi\'\)\}/s', generateDropdown('Lokasi Penempatan', 'map-pin', 'lokasi_id', "'{{ old('lokasi_id') }}'", $lokasiLoop, 'Pilih Lokasi'), $content);

$content = preg_replace('/\{generateDropdown\(\'Jenis APAR\', \'fire-extinguisher\', \'jenis_id\'.*?\'Pilih Jenis\'\)\}/s', generateDropdown('Jenis APAR', 'fire-extinguisher', 'jenis_id', "'{{ old('jenis_id') }}'", $jenisLoop, 'Pilih Jenis'), $content);

$content = preg_replace('/\{generateDropdown\(\'Kapasitas\', \'scales\', \'kapasitas_id\'.*?\'Pilih Kapasitas\'\)\}/s', generateDropdown('Kapasitas', 'scales', 'kapasitas_id', "'{{ old('kapasitas_id') }}'", $kapasitasLoop, 'Pilih Kapasitas'), $content);

// 2. We should also check Edit form. From the screenshot, it looks like Tambah Form was broken, but Edit Form might also have these broken strings.
// Let's replace editApar ones too just in case.
$editLokasi = generateDropdown('Lokasi Penempatan', 'map-pin', 'lokasi_id', 'editApar.lokasi_id', $lokasiLoop, 'Pilih Lokasi');
$editJenis = generateDropdown('Jenis APAR', 'fire-extinguisher', 'jenis_id', 'editApar.jenis_id', $jenisLoop, 'Pilih Jenis');
$editKapasitas = generateDropdown('Kapasitas', 'scales', 'kapasitas_id', 'editApar.kapasitas_id', $kapasitasLoop, 'Pilih Kapasitas');

// Re-apply amber focus styling for Edit Form dropdowns
$editLokasi = str_replace('focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15', 'focus:border-amber-500 focus:ring-4 focus:ring-amber-500/15', $editLokasi);
$editLokasi = str_replace("open ? 'text-[#009B77]' : 'peer-focus:text-[#009B77]'", "open ? 'text-amber-500' : 'peer-focus:text-amber-500'", $editLokasi);
$editLokasi = str_replace('bg-white border-[#009B77] ring-4 ring-[#009B77]/15', 'bg-white border-amber-500 ring-4 ring-amber-500/15', $editLokasi);
$editLokasi = str_replace('text-[#009B77] bg-[#009B77]/5', 'text-amber-500 bg-amber-50', $editLokasi);

$editJenis = str_replace('focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15', 'focus:border-amber-500 focus:ring-4 focus:ring-amber-500/15', $editJenis);
$editJenis = str_replace("open ? 'text-[#009B77]' : 'peer-focus:text-[#009B77]'", "open ? 'text-amber-500' : 'peer-focus:text-amber-500'", $editJenis);
$editJenis = str_replace('bg-white border-[#009B77] ring-4 ring-[#009B77]/15', 'bg-white border-amber-500 ring-4 ring-amber-500/15', $editJenis);
$editJenis = str_replace('text-[#009B77] bg-[#009B77]/5', 'text-amber-500 bg-amber-50', $editJenis);

$editKapasitas = str_replace('focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15', 'focus:border-amber-500 focus:ring-4 focus:ring-amber-500/15', $editKapasitas);
$editKapasitas = str_replace("open ? 'text-[#009B77]' : 'peer-focus:text-[#009B77]'", "open ? 'text-amber-500' : 'peer-focus:text-amber-500'", $editKapasitas);
$editKapasitas = str_replace('bg-white border-[#009B77] ring-4 ring-[#009B77]/15', 'bg-white border-amber-500 ring-4 ring-amber-500/15', $editKapasitas);
$editKapasitas = str_replace('text-[#009B77] bg-[#009B77]/5', 'text-amber-500 bg-amber-50', $editKapasitas);

$content = preg_replace('/\{generateDropdown\(\'Lokasi Penempatan\', \'map-pin\', \'lokasi_id\', "editApar.*?\'Pilih Lokasi\'\)\}/s', $editLokasi, $content);
$content = preg_replace('/\{generateDropdown\(\'Jenis APAR\', \'fire-extinguisher\', \'jenis_id\', "editApar.*?\'Pilih Jenis\'\)\}/s', $editJenis, $content);
$content = preg_replace('/\{generateDropdown\(\'Kapasitas\', \'scales\', \'kapasitas_id\', "editApar.*?\'Pilih Kapasitas\'\)\}/s', $editKapasitas, $content);

file_put_contents($indexFile, $content);
echo "Fixed literal generateDropdown strings.\n";
