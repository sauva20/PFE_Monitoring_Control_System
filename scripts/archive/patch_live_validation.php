<?php

$file = __DIR__.'/resources/views/master-data/index.blade.php';
$content = file_get_contents($file);

// 1. Tambah Gedung
$content = str_replace(
    '<form action="/master-data/gedung" method="POST">',
    '<form action="/master-data/gedung" method="POST" x-data="{ nama: \'\', existing: [ @foreach(\App\Models\Gedung::all() as $g) \'{{ strtolower(addslashes($g->nama)) }}\', @endforeach ], get isDuplicate() { return this.nama.trim() !== \'\' && this.existing.includes(this.nama.toLowerCase().trim()); } }">',
    $content
);
$content = preg_replace(
    '/<input type="text" name="nama" value="\{\{ old\(\'form_type\'\) == \'tambah_gedung\' \? old\(\'nama\'\) : \'\' \}\}" required class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2\.5 px-4 text-sm font-medium text-slate-700 focus:bg-white focus:border-\[\#009B77\] focus:ring-4 focus:ring-\[\#009B77\]\/15 transition-all outline-none">(\s*)@if\(old\(\'form_type\'\) == \'tambah_gedung\'\).*?@endif/s',
    '<input type="text" name="nama" x-model="nama" required class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 px-4 text-sm font-medium text-slate-700 focus:bg-white focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none">'."\n                                    <p x-show=\"isDuplicate\" x-cloak class=\"text-xs text-red-500 font-medium mt-2\"><i class=\"ph-bold ph-warning-circle mr-1\"></i>Nama gedung ini sudah terdaftar.</p>",
    $content
);
// Button
$content = preg_replace(
    '/<button type="submit" class="inline-flex w-full justify-center rounded-xl bg-\[\#009B77\] px-5 py-2\.5 text-sm font-bold text-white shadow-sm hover:bg-\[\#008264\] sm:ml-3 sm:w-auto transition-colors shadow-\[\#009B77\]\/20">Simpan<\/button>/',
    '<button type="submit" :disabled="isDuplicate || nama.trim() === \'\'" :class="isDuplicate || nama.trim() === \'\' ? \'opacity-50 cursor-not-allowed\' : \'hover:bg-[#008264]\'" class="inline-flex w-full justify-center rounded-xl bg-[#009B77] px-5 py-2.5 text-sm font-bold text-white shadow-sm sm:ml-3 sm:w-auto transition-colors shadow-[#009B77]/20">Simpan</button>',
    $content
);

// 2. Tambah Lokasi
$content = str_replace(
    '<form action="/master-data/lokasi" method="POST">',
    '<form action="/master-data/lokasi" method="POST" x-data="{ nama: \'\', gedung_id: \'\', existing: [ @foreach(\App\Models\Lokasi::all() as $l) { nama: \'{{ strtolower(addslashes($l->nama)) }}\', gedung_id: \'{{ $l->gedung_id }}\' }, @endforeach ], get isDuplicate() { return this.nama.trim() !== \'\' && this.existing.some(e => e.nama === this.nama.toLowerCase().trim() && e.gedung_id == this.gedung_id); } }">',
    $content
);
$content = preg_replace(
    '/<input type="text" name="nama" value="\{\{ old\(\'form_type\'\) == \'tambah_lokasi\' \? old\(\'nama\'\) : \'\' \}\}" required class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2\.5 px-4 text-sm font-medium text-slate-700 focus:bg-white focus:border-\[\#009B77\] focus:ring-4 focus:ring-\[\#009B77\]\/15 transition-all outline-none">(\s*)@if\(old\(\'form_type\'\) == \'tambah_lokasi\'\).*?@endif/s',
    '<input type="text" name="nama" x-model="nama" required class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 px-4 text-sm font-medium text-slate-700 focus:bg-white focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none">'."\n                                    <p x-show=\"isDuplicate\" x-cloak class=\"text-xs text-red-500 font-medium mt-2\"><i class=\"ph-bold ph-warning-circle mr-1\"></i>Lokasi dengan nama ini sudah ada di gedung yang dipilih.</p>",
    $content
);
// Dropdown trigger in Lokasi (we need to bind gedung_id)
$content = preg_replace(
    '/<input type="hidden" name="gedung_id" :value="selectedId" required>/',
    '<input type="hidden" name="gedung_id" :value="selectedId" required x-effect="gedung_id = selectedId">',
    $content, 1
);

// 3. Tambah Jenis
$content = str_replace(
    '<form action="/master-data/jenis" method="POST">',
    '<form action="/master-data/jenis" method="POST" x-data="{ nama: \'\', existing: [ @foreach(\App\Models\JenisApar::all() as $j) \'{{ strtolower(addslashes($j->nama)) }}\', @endforeach ], get isDuplicate() { return this.nama.trim() !== \'\' && this.existing.includes(this.nama.toLowerCase().trim()); } }">',
    $content
);
$content = preg_replace(
    '/<input type="text" name="nama" value="\{\{ old\(\'form_type\'\) == \'tambah_jenis\' \? old\(\'nama\'\) : \'\' \}\}" required class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2\.5 px-4 text-sm font-medium text-slate-700 focus:bg-white focus:border-\[\#009B77\] focus:ring-4 focus:ring-\[\#009B77\]\/15 transition-all outline-none">(\s*)@if\(old\(\'form_type\'\) == \'tambah_jenis\'\).*?@endif/s',
    '<input type="text" name="nama" x-model="nama" required class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 px-4 text-sm font-medium text-slate-700 focus:bg-white focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none">'."\n                                    <p x-show=\"isDuplicate\" x-cloak class=\"text-xs text-red-500 font-medium mt-2\"><i class=\"ph-bold ph-warning-circle mr-1\"></i>Jenis APAR ini sudah terdaftar.</p>",
    $content
);

// 4. Tambah Kapasitas
$content = str_replace(
    '<form action="/master-data/kapasitas" method="POST">',
    '<form action="/master-data/kapasitas" method="POST" x-data="{ ukuran: \'\', existing: [ @foreach(\App\Models\KapasitasApar::all() as $k) \'{{ strtolower(addslashes($k->ukuran)) }}\', @endforeach ], get isDuplicate() { return this.ukuran.trim() !== \'\' && this.existing.includes(this.ukuran.toLowerCase().trim()); } }">',
    $content
);
$content = preg_replace(
    '/<input type="text" name="ukuran" value="\{\{ old\(\'form_type\'\) == \'tambah_kapasitas\' \? old\(\'ukuran\'\) : \'\' \}\}" required class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2\.5 px-4 text-sm font-medium text-slate-700 focus:bg-white focus:border-\[\#009B77\] focus:ring-4 focus:ring-\[\#009B77\]\/15 transition-all outline-none">(\s*)@if\(old\(\'form_type\'\) == \'tambah_kapasitas\'\).*?@endif/s',
    '<input type="text" name="ukuran" x-model="ukuran" required class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 px-4 text-sm font-medium text-slate-700 focus:bg-white focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none">'."\n                                    <p x-show=\"isDuplicate\" x-cloak class=\"text-xs text-red-500 font-medium mt-2\"><i class=\"ph-bold ph-warning-circle mr-1\"></i>Kapasitas APAR ini sudah terdaftar.</p>",
    $content
);

file_put_contents($file, $content);
echo 'Live validation patched.';
