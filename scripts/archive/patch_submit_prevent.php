<?php

$file = __DIR__.'/resources/views/master-data/index.blade.php';
$content = file_get_contents($file);

// 1. Tambah Gedung
$content = str_replace(
    '<form action="/master-data/gedung" method="POST" x-data="{ nama: \'\', existing: [',
    '<form action="/master-data/gedung" method="POST" @submit="isDuplicate || nama.trim() === \'\' ? $event.preventDefault() : true" x-data="{ nama: \'\', existing: [',
    $content
);

// 2. Tambah Lokasi
$content = str_replace(
    '<form action="/master-data/lokasi" method="POST" x-data="{ nama: \'\', gedung_id: \'\', existing: [',
    '<form action="/master-data/lokasi" method="POST" @submit="isDuplicate || nama.trim() === \'\' || gedung_id === \'\' ? $event.preventDefault() : true" x-data="{ nama: \'\', gedung_id: \'\', existing: [',
    $content
);

// 3. Tambah Jenis
$content = str_replace(
    '<form action="/master-data/jenis" method="POST" x-data="{ nama: \'\', existing: [',
    '<form action="/master-data/jenis" method="POST" @submit="isDuplicate || nama.trim() === \'\' ? $event.preventDefault() : true" x-data="{ nama: \'\', existing: [',
    $content
);

// 4. Tambah Kapasitas
$content = str_replace(
    '<form action="/master-data/kapasitas" method="POST" x-data="{ ukuran: \'\', existing: [',
    '<form action="/master-data/kapasitas" method="POST" @submit="isDuplicate || ukuran.trim() === \'\' ? $event.preventDefault() : true" x-data="{ ukuran: \'\', existing: [',
    $content
);

file_put_contents($file, $content);
echo 'Submit prevent patched.';
