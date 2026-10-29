<?php

$file = __DIR__.'/resources/views/master-data/index.blade.php';
$content = file_get_contents($file);

// 1. Update x-data
$content = str_replace(
    '<div class="space-y-6" x-data="{ activeTab: \'data\', showModalLokasi: false, showModalGedung: false, showModalJenis: false, showModalKapasitas: false }">',
    '<div class="space-y-6" x-data="{ activeTab: \'data\', showModalLokasi: false, showModalGedung: false, showModalJenis: false, showModalKapasitas: false, showEditGedung: false, editGedung: { id:\'\', nama:\'\' }, showEditLokasi: false, editLokasi: { id:\'\', nama:\'\', gedung_id:\'\' }, showEditJenis: false, editJenis: { id:\'\', nama:\'\' }, showEditKapasitas: false, editKapasitas: { id:\'\', ukuran:\'\' } }">',
    $content
);

// 2. Jenis APAR loop and edit button
$jenisAparOld = <<<'HTML'
                        <div class="bg-white border border-slate-100 p-3.5 rounded-2xl flex items-center justify-between hover:border-amber-200 hover:shadow-sm transition-all group/item">
                            <span class="font-bold text-slate-700 text-sm">ABC Powder</span>
                            <div class="flex items-center gap-1 opacity-0 group-hover/item:opacity-100 transition-opacity">
                                <button class="w-8 h-8 rounded-lg text-slate-400 hover:text-amber-500 hover:bg-amber-50 flex items-center justify-center transition-colors"><i class="ph-bold ph-pencil-simple text-base"></i></button>
                                <button class="w-8 h-8 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 flex items-center justify-center transition-colors"><i class="ph-bold ph-trash text-base"></i></button>
                            </div>
                        </div>
                        <div class="bg-white border border-slate-100 p-3.5 rounded-2xl flex items-center justify-between hover:border-amber-200 hover:shadow-sm transition-all group/item">
                            <span class="font-bold text-slate-700 text-sm">CO2</span>
                            <div class="flex items-center gap-1 opacity-0 group-hover/item:opacity-100 transition-opacity">
                                <button class="w-8 h-8 rounded-lg text-slate-400 hover:text-amber-500 hover:bg-amber-50 flex items-center justify-center transition-colors"><i class="ph-bold ph-pencil-simple text-base"></i></button>
                                <button class="w-8 h-8 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 flex items-center justify-center transition-colors"><i class="ph-bold ph-trash text-base"></i></button>
                            </div>
                        </div>
                        <div class="bg-white border border-slate-100 p-3.5 rounded-2xl flex items-center justify-between hover:border-amber-200 hover:shadow-sm transition-all group/item">
                            <span class="font-bold text-slate-700 text-sm">Foam</span>
                            <div class="flex items-center gap-1 opacity-0 group-hover/item:opacity-100 transition-opacity">
                                <button class="w-8 h-8 rounded-lg text-slate-400 hover:text-amber-500 hover:bg-amber-50 flex items-center justify-center transition-colors"><i class="ph-bold ph-pencil-simple text-base"></i></button>
                                <button class="w-8 h-8 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 flex items-center justify-center transition-colors"><i class="ph-bold ph-trash text-base"></i></button>
                            </div>
                        </div>
HTML;
$jenisAparNew = <<<'HTML'
                        @forelse($jenisApars as $jenis)
                        <div class="bg-white border border-slate-100 p-3.5 rounded-2xl flex items-center justify-between hover:border-amber-200 hover:shadow-sm transition-all group/item">
                            <span class="font-bold text-slate-700 text-sm">{{ $jenis->nama }}</span>
                            <div class="flex items-center gap-1 opacity-0 group-hover/item:opacity-100 transition-opacity">
                                <button @click="editJenis = { id: {{ $jenis->id }}, nama: '{{ addslashes($jenis->nama) }}' }; showEditJenis = true" class="w-8 h-8 rounded-lg text-slate-400 hover:text-amber-500 hover:bg-amber-50 flex items-center justify-center transition-colors"><i class="ph-bold ph-pencil-simple text-base"></i></button>
                                <form action="/master-data/jenis/{{ $jenis->id }}" method="POST" class="inline" onsubmit="confirmDelete(event, 'Yakin hapus jenis APAR ini?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="w-8 h-8 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 flex items-center justify-center transition-colors"><i class="ph-bold ph-trash text-base"></i></button>
                                </form>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-4 text-xs font-semibold text-slate-400">Belum ada data</div>
                        @endforelse
HTML;
$content = str_replace($jenisAparOld, $jenisAparNew, $content);

// 3. Edit button for Lokasi
$lokasiEditOld = '<button class="w-8 h-8 rounded-lg text-slate-400 hover:text-amber-500 hover:bg-amber-50 flex items-center justify-center transition-colors"><i class="ph-bold ph-pencil-simple text-base"></i></button>';
$lokasiEditNew = '<button @click="editLokasi = { id: {{ $lok->id }}, nama: \'{{ addslashes($lok->nama) }}\', gedung_id: \'{{ $lok->gedung_id }}\' }; showEditLokasi = true" class="w-8 h-8 rounded-lg text-slate-400 hover:text-amber-500 hover:bg-amber-50 flex items-center justify-center transition-colors"><i class="ph-bold ph-pencil-simple text-base"></i></button>';
// only first occurrence since others are Kapasitas and Jenis
// Wait, I will use str_replace for all. Kapasitas doesn't use $lok, it uses $kap. So I'll do regex or precise target.
// I will target the exact line in Lokasi loop
$content = preg_replace(
    '/(<div class="flex items-center gap-1 opacity-0 group-hover\/item:opacity-100 transition-opacity">\s*)<button class="w-8 h-8 rounded-lg text-slate-400 hover:text-amber-500 hover:bg-amber-50 flex items-center justify-center transition-colors"><i class="ph-bold ph-pencil-simple text-base"><\/i><\/button>(\s*<form action="\/master-data\/lokasi)/',
    '$1<button @click="editLokasi = { id: {{ $lok->id }}, nama: \'{{ addslashes($lok->nama) }}\', gedung_id: \'{{ $lok->gedung_id }}\' }; showEditLokasi = true" class="w-8 h-8 rounded-lg text-slate-400 hover:text-amber-500 hover:bg-amber-50 flex items-center justify-center transition-colors"><i class="ph-bold ph-pencil-simple text-base"></i></button>$2',
    $content
);

// 4. Edit button for Kapasitas
$content = preg_replace(
    '/(<div class="flex items-center gap-1 opacity-0 group-hover\/item:opacity-100 transition-opacity">\s*)<button class="w-8 h-8 rounded-lg text-slate-400 hover:text-amber-500 hover:bg-amber-50 flex items-center justify-center transition-colors"><i class="ph-bold ph-pencil-simple text-base"><\/i><\/button>(\s*<form action="\/master-data\/kapasitas)/',
    '$1<button @click="editKapasitas = { id: {{ $kap->id }}, ukuran: \'{{ addslashes($kap->ukuran) }}\' }; showEditKapasitas = true" class="w-8 h-8 rounded-lg text-slate-400 hover:text-amber-500 hover:bg-amber-50 flex items-center justify-center transition-colors"><i class="ph-bold ph-pencil-simple text-base"></i></button>$2',
    $content
);

// 5. Tambah Lokasi Modal wrapper
$content = str_replace(
    '<div class="bg-white px-6 pb-6 pt-6 sm:p-8">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center">
                                    <i class="ph-bold ph-map-pin text-xl"></i>
                                </div>
                                <h3 class="text-lg font-bold leading-6 text-slate-800" id="modal-title">Tambah Data Lokasi</h3>
                            </div>',
    '<form action="/master-data/lokasi" method="POST"><div class="bg-white px-6 pb-6 pt-6 sm:p-8">
                        @csrf
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center">
                                    <i class="ph-bold ph-map-pin text-xl"></i>
                                </div>
                                <h3 class="text-lg font-bold leading-6 text-slate-800" id="modal-title">Tambah Data Lokasi</h3>
                            </div>',
    $content
);
$content = str_replace(
    '<button type="button" @click="showModalLokasi = false" class="inline-flex w-full justify-center rounded-xl bg-[#009B77] px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-[#008264] sm:ml-3 sm:w-auto transition-colors shadow-[#009B77]/20">Simpan</button>',
    '<button type="submit" class="inline-flex w-full justify-center rounded-xl bg-[#009B77] px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-[#008264] sm:ml-3 sm:w-auto transition-colors shadow-[#009B77]/20">Simpan</button>',
    $content
);
$content = str_replace(
    '<button type="button" @click="showModalLokasi = false" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-5 py-2.5 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors">Batal</button>
                    </div>',
    '<button type="button" @click="showModalLokasi = false" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-5 py-2.5 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors">Batal</button>
                    </div></form>',
    $content
);

// Lokasi input names & dynamic dropdown
$content = str_replace(
    '<input type="text" placeholder="Contoh: Ruang Server Lt 3"',
    '<input type="text" name="nama" placeholder="Contoh: Ruang Server Lt 3"',
    $content
);
$lokasiDropdownOld = <<<'HTML'
                                <div x-data="{ openGedung: false, selectedGedung: '', optionsGedung: ['Gedung Utama', 'Gedung Produksi', 'Gudang Logistik'] }" class="relative">
                                    <button type="button" @click="openGedung = !openGedung" @click.away="openGedung = false" 
                                            class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 px-4 text-sm font-semibold text-slate-800 focus:bg-white focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none cursor-pointer text-left flex items-center justify-between"
                                            :class="openGedung ? 'bg-white border-[#009B77] ring-4 ring-[#009B77]/15' : ''">
                                        <div class="flex items-center gap-2">
                                            <i class="ph-bold ph-buildings text-slate-400" :class="openGedung ? 'text-[#009B77]' : ''"></i>
                                            <span x-text="selectedGedung || 'Pilih Gedung'" :class="!selectedGedung ? 'text-slate-400' : ''"></span>
                                        </div>
                                        <i class="ph-bold ph-caret-down text-slate-400 transition-transform duration-200" :class="openGedung ? 'rotate-180 text-[#009B77]' : ''"></i>
                                    </button>
                                    
                                    <div x-show="openGedung" 
                                         x-transition:enter="transition ease-out duration-200"
                                         x-transition:enter-start="opacity-0 scale-95"
                                         x-transition:enter-end="opacity-100 scale-100"
                                         x-transition:leave="transition ease-in duration-100"
                                         x-transition:leave-start="opacity-100 scale-100"
                                         x-transition:leave-end="opacity-0 scale-95"
                                         style="display: none;" 
                                         class="absolute left-0 z-50 w-full mt-2 bg-white rounded-xl shadow-[0_10px_40px_rgba(0,0,0,0.1)] border border-slate-100 py-2 overflow-hidden origin-top">
                                        <template x-for="opt in optionsGedung">
                                            <button type="button" @click="selectedGedung = opt; openGedung = false" class="w-full text-left px-4 py-2.5 text-sm font-semibold transition-colors flex items-center justify-between" :class="selectedGedung === opt ? 'text-[#009B77] bg-[#009B77]/5' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'">
                                                <span x-text="opt"></span>
                                                <i class="ph-bold ph-check text-[#009B77]" x-show="selectedGedung === opt"></i>
                                            </button>
                                        </template>
                                    </div>
                                </div>
HTML;
$lokasiDropdownNew = <<<'HTML'
                                <select name="gedung_id" required class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 px-4 text-sm font-semibold text-slate-800 focus:bg-white focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none appearance-none cursor-pointer">
                                    <option value="">Pilih Gedung</option>
                                    @foreach($gedungs as $ged)
                                        <option value="{{ $ged->id }}">{{ $ged->nama }}</option>
                                    @endforeach
                                </select>
HTML;
$content = str_replace($lokasiDropdownOld, $lokasiDropdownNew, $content);

// 6. Tambah Gedung Modal wrapper
$content = str_replace(
    '<div class="bg-white px-6 pb-6 pt-6 sm:p-8">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                                    <i class="ph-bold ph-buildings text-xl"></i>
                                </div>
                                <h3 class="text-lg font-bold leading-6 text-slate-800" id="modal-title">Tambah Data Gedung</h3>',
    '<form action="/master-data/gedung" method="POST"><div class="bg-white px-6 pb-6 pt-6 sm:p-8">
                        @csrf
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                                    <i class="ph-bold ph-buildings text-xl"></i>
                                </div>
                                <h3 class="text-lg font-bold leading-6 text-slate-800" id="modal-title">Tambah Data Gedung</h3>',
    $content
);
$content = str_replace(
    '<input type="text" placeholder="Contoh: Gedung BUR"',
    '<input type="text" name="nama" required placeholder="Contoh: Gedung BUR"',
    $content
);
$content = preg_replace(
    '/<button type="button" @click="showModalGedung = false" class="inline-flex w-full justify-center rounded-xl bg-indigo-600([^>]+)>Simpan<\/button>/',
    '<button type="submit" class="inline-flex w-full justify-center rounded-xl bg-indigo-600$1>Simpan</button>',
    $content
);
$content = str_replace(
    '<button type="button" @click="showModalGedung = false" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-5 py-2.5 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors">Batal</button>
                    </div>',
    '<button type="button" @click="showModalGedung = false" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-5 py-2.5 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors">Batal</button>
                    </div></form>',
    $content
);

// 7. Tambah Jenis Modal wrapper
$content = str_replace(
    '<div class="bg-white px-6 pb-6 pt-6 sm:p-8">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                                    <i class="ph-fill ph-fire-extinguisher text-xl"></i>
                                </div>
                                <h3 class="text-lg font-bold leading-6 text-slate-800" id="modal-title">Tambah Jenis APAR</h3>',
    '<form action="/master-data/jenis" method="POST"><div class="bg-white px-6 pb-6 pt-6 sm:p-8">
                        @csrf
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                                    <i class="ph-fill ph-fire-extinguisher text-xl"></i>
                                </div>
                                <h3 class="text-lg font-bold leading-6 text-slate-800" id="modal-title">Tambah Jenis APAR</h3>',
    $content
);
$content = str_replace(
    '<input type="text" placeholder="Contoh: ABC Powder"',
    '<input type="text" name="nama" required placeholder="Contoh: ABC Powder"',
    $content
);
$content = preg_replace(
    '/<button type="button" @click="showModalJenis = false" class="inline-flex w-full justify-center rounded-xl bg-amber-500([^>]+)>Simpan<\/button>/',
    '<button type="submit" class="inline-flex w-full justify-center rounded-xl bg-amber-500$1>Simpan</button>',
    $content
);
$content = str_replace(
    '<button type="button" @click="showModalJenis = false" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-5 py-2.5 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors">Batal</button>
                    </div>',
    '<button type="button" @click="showModalJenis = false" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-5 py-2.5 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors">Batal</button>
                    </div></form>',
    $content
);

// 8. Tambah Kapasitas Modal wrapper
$content = str_replace(
    '<div class="bg-white px-6 pb-6 pt-6 sm:p-8">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                                    <i class="ph-fill ph-scales text-xl"></i>
                                </div>
                                <h3 class="text-lg font-bold leading-6 text-slate-800" id="modal-title">Tambah Kapasitas</h3>',
    '<form action="/master-data/kapasitas" method="POST"><div class="bg-white px-6 pb-6 pt-6 sm:p-8">
                        @csrf
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                                    <i class="ph-fill ph-scales text-xl"></i>
                                </div>
                                <h3 class="text-lg font-bold leading-6 text-slate-800" id="modal-title">Tambah Kapasitas</h3>',
    $content
);
$content = str_replace(
    '<input type="text" placeholder="Contoh: 3 Kg"',
    '<input type="text" name="ukuran" required placeholder="Contoh: 3 Kg"',
    $content
);
$content = preg_replace(
    '/<button type="button" @click="showModalKapasitas = false" class="inline-flex w-full justify-center rounded-xl bg-purple-600([^>]+)>Simpan<\/button>/',
    '<button type="submit" class="inline-flex w-full justify-center rounded-xl bg-purple-600$1>Simpan</button>',
    $content
);
$content = str_replace(
    '<button type="button" @click="showModalKapasitas = false" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-5 py-2.5 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors">Batal</button>
                    </div>',
    '<button type="button" @click="showModalKapasitas = false" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-5 py-2.5 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors">Batal</button>
                    </div></form>',
    $content
);

// 9. Append Edit Modals
$editModals = <<<'HTML'

    <!-- EDIT MODALS -->
    <!-- Modal Edit Lokasi -->
    <div x-show="showEditModalLokasi" style="display: none;" class="relative z-50">
        <div x-show="showEditModalLokasi" class="fixed inset-0 bg-slate-900/40"></div>
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div x-show="showEditModalLokasi" @click.away="showEditModalLokasi = false" class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-100">
                    <form :action="'/master-data/lokasi/' + editLokasi.id" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="bg-white px-6 pb-6 pt-6 sm:p-8">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-lg font-bold text-slate-800">Edit Data Lokasi</h3>
                                <button type="button" @click="showEditModalLokasi = false" class="text-slate-400 hover:text-slate-500"><i class="ph-bold ph-x text-xl"></i></button>
                            </div>
                            <div class="space-y-5">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 mb-2">Nama Lokasi</label>
                                    <input type="text" name="nama" x-model="editLokasi.nama" required class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 px-4 text-sm font-semibold text-slate-800 focus:bg-white focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 mb-2">Pilih Gedung</label>
                                    <select name="gedung_id" x-model="editLokasi.gedung_id" required class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 px-4 text-sm font-semibold text-slate-800 focus:bg-white focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 outline-none">
                                        @foreach($gedungs as $ged)
                                            <option value="{{ $ged->id }}">{{ $ged->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-50 px-6 py-4 sm:flex sm:flex-row-reverse sm:px-8 border-t border-slate-100">
                            <button type="submit" class="inline-flex w-full justify-center rounded-xl bg-[#009B77] px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-[#008264] sm:ml-3 sm:w-auto">Simpan</button>
                            <button type="button" @click="showEditModalLokasi = false" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-5 py-2.5 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit Jenis -->
    <div x-show="showEditJenis" style="display: none;" class="relative z-50">
        <div x-show="showEditJenis" class="fixed inset-0 bg-slate-900/40"></div>
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div x-show="showEditJenis" @click.away="showEditJenis = false" class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-100">
                    <form :action="'/master-data/jenis/' + editJenis.id" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="bg-white px-6 pb-6 pt-6 sm:p-8">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-lg font-bold text-slate-800">Edit Jenis APAR</h3>
                                <button type="button" @click="showEditJenis = false" class="text-slate-400 hover:text-slate-500"><i class="ph-bold ph-x text-xl"></i></button>
                            </div>
                            <div class="space-y-5">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 mb-2">Nama Jenis</label>
                                    <input type="text" name="nama" x-model="editJenis.nama" required class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 px-4 text-sm font-semibold text-slate-800 focus:bg-white focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 outline-none">
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-50 px-6 py-4 sm:flex sm:flex-row-reverse sm:px-8 border-t border-slate-100">
                            <button type="submit" class="inline-flex w-full justify-center rounded-xl bg-amber-500 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-amber-600 sm:ml-3 sm:w-auto">Simpan</button>
                            <button type="button" @click="showEditJenis = false" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-5 py-2.5 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit Kapasitas -->
    <div x-show="showEditKapasitas" style="display: none;" class="relative z-50">
        <div x-show="showEditKapasitas" class="fixed inset-0 bg-slate-900/40"></div>
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div x-show="showEditKapasitas" @click.away="showEditKapasitas = false" class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-100">
                    <form :action="'/master-data/kapasitas/' + editKapasitas.id" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="bg-white px-6 pb-6 pt-6 sm:p-8">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-lg font-bold text-slate-800">Edit Kapasitas APAR</h3>
                                <button type="button" @click="showEditKapasitas = false" class="text-slate-400 hover:text-slate-500"><i class="ph-bold ph-x text-xl"></i></button>
                            </div>
                            <div class="space-y-5">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 mb-2">Kapasitas</label>
                                    <input type="text" name="ukuran" x-model="editKapasitas.ukuran" required class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 px-4 text-sm font-semibold text-slate-800 focus:bg-white focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 outline-none">
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-50 px-6 py-4 sm:flex sm:flex-row-reverse sm:px-8 border-t border-slate-100">
                            <button type="submit" class="inline-flex w-full justify-center rounded-xl bg-purple-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-purple-700 sm:ml-3 sm:w-auto">Simpan</button>
                            <button type="button" @click="showEditKapasitas = false" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-5 py-2.5 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
HTML;

$content = str_replace('</div>'."\n".'@endsection', $editModals, $content);

file_put_contents($file, $content);
echo "Patched index.blade.php successfully.\n";
