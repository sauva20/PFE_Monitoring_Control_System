<?php

$file = __DIR__.'/resources/views/master-data/index.blade.php';
$content = file_get_contents($file);

// 1. Tambah Gedung Input
$content = preg_replace(
    '/(<form action="\/master-data\/gedung"[\s\S]*?)<input type="text" name="nama" x-model="nama" required placeholder="Contoh: Gedung BUR"/m',
    '$1<input type="text" name="nama" x-model="nama" @keydown.enter="if(isDuplicate || nama.trim() === \'\') { $event.preventDefault(); showModalGedung = false; }" required placeholder="Contoh: Gedung BUR"',
    $content
);

// 2. Tambah Lokasi Input
$content = preg_replace(
    '/(<form action="\/master-data\/lokasi"[\s\S]*?)<input type="text" name="nama" x-model="nama" required placeholder="Contoh: Ruang Server Lt 3"/m',
    '$1<input type="text" name="nama" x-model="nama" @keydown.enter="if(isDuplicate || nama.trim() === \'\' || gedung_id === \'\') { $event.preventDefault(); showModalLokasi = false; }" required placeholder="Contoh: Ruang Server Lt 3"',
    $content
);

// 3. Tambah Jenis Input
$content = preg_replace(
    '/(<form action="\/master-data\/jenis"[\s\S]*?)<input type="text" name="nama" x-model="nama" required placeholder="Contoh: ABC Powder"/m',
    '$1<input type="text" name="nama" x-model="nama" @keydown.enter="if(isDuplicate || nama.trim() === \'\') { $event.preventDefault(); showModalJenis = false; }" required placeholder="Contoh: ABC Powder"',
    $content
);

// 4. Tambah Kapasitas Input
$content = preg_replace(
    '/(<form action="\/master-data\/kapasitas"[\s\S]*?)<input type="text" name="ukuran" x-model="ukuran" required placeholder="Contoh: 3 Kg"/m',
    '$1<input type="text" name="ukuran" x-model="ukuran" @keydown.enter="if(isDuplicate || ukuran.trim() === \'\') { $event.preventDefault(); showModalKapasitas = false; }" required placeholder="Contoh: 3 Kg"',
    $content
);

file_put_contents($file, $content);
echo 'Enter key patched.';
