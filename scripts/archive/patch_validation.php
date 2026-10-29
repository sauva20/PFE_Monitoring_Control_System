<?php

$file = __DIR__.'/app/Http/Controllers/MasterDataController.php';
$content = file_get_contents($file);

if (strpos($content, 'use Illuminate\Validation\Rule;') === false) {
    $content = str_replace('namespace App\Http\Controllers;', "namespace App\Http\Controllers;\n\nuse Illuminate\Validation\Rule;", $content);
}

// storeGedung
$content = str_replace(
    "public function storeGedung(Request \$request)\n    {\n        \$request->validate(['nama' => 'required|string|max:255']);",
    "public function storeGedung(Request \$request)\n    {\n        \$request->validate(['nama' => 'required|string|max:255|unique:gedung,nama'], ['nama.unique' => 'Nama gedung ini sudah terdaftar.']);",
    $content
);

// updateGedung
$content = str_replace(
    "public function updateGedung(Request \$request, Gedung \$gedung)\n    {\n        \$request->validate(['nama' => 'required|string|max:255']);",
    "public function updateGedung(Request \$request, Gedung \$gedung)\n    {\n        \$request->validate(['nama' => 'required|string|max:255|unique:gedung,nama,' . \$gedung->id], ['nama.unique' => 'Nama gedung ini sudah terdaftar.']);",
    $content
);

// storeLokasi
$storeLokasiSearch = <<<'PHP'
        $request->validate([
            'nama' => 'required|string|max:255',
            'gedung_id' => 'required|exists:gedung,id'
        ]);
PHP;
$storeLokasiReplace = <<<'PHP'
        $request->validate([
            'nama' => [
                'required',
                'string',
                'max:255',
                Rule::unique('lokasi')->where(fn ($query) => $query->where('gedung_id', $request->gedung_id))
            ],
            'gedung_id' => 'required|exists:gedung,id'
        ], [
            'nama.unique' => 'Lokasi dengan nama ini sudah ada di gedung yang dipilih.'
        ]);
PHP;
$content = str_replace($storeLokasiSearch, $storeLokasiReplace, $content);

// updateLokasi
$updateLokasiSearch = <<<'PHP'
        $request->validate([
            'nama' => 'required|string|max:255',
            'gedung_id' => 'required|exists:gedung,id'
        ]);
PHP;
$updateLokasiReplace = <<<'PHP'
        $request->validate([
            'nama' => [
                'required',
                'string',
                'max:255',
                Rule::unique('lokasi')->ignore($lokasi->id)->where(fn ($query) => $query->where('gedung_id', $request->gedung_id))
            ],
            'gedung_id' => 'required|exists:gedung,id'
        ], [
            'nama.unique' => 'Lokasi dengan nama ini sudah ada di gedung yang dipilih.'
        ]);
PHP;
$content = str_replace($updateLokasiSearch, $updateLokasiReplace, $content);

// storeJenis
$content = str_replace(
    "public function storeJenis(Request \$request)\n    {\n        \$request->validate(['nama' => 'required|string|max:255']);",
    "public function storeJenis(Request \$request)\n    {\n        \$request->validate(['nama' => 'required|string|max:255|unique:jenis_apar,nama'], ['nama.unique' => 'Jenis APAR ini sudah terdaftar.']);",
    $content
);

// updateJenis
$content = str_replace(
    "public function updateJenis(Request \$request, JenisApar \$jenisApar)\n    {\n        \$request->validate(['nama' => 'required|string|max:255']);",
    "public function updateJenis(Request \$request, JenisApar \$jenisApar)\n    {\n        \$request->validate(['nama' => 'required|string|max:255|unique:jenis_apar,nama,' . \$jenisApar->id], ['nama.unique' => 'Jenis APAR ini sudah terdaftar.']);",
    $content
);

// storeKapasitas
$content = str_replace(
    "public function storeKapasitas(Request \$request)\n    {\n        \$request->validate(['ukuran' => 'required|string|max:255']);",
    "public function storeKapasitas(Request \$request)\n    {\n        \$request->validate(['ukuran' => 'required|string|max:255|unique:kapasitas_apar,ukuran'], ['ukuran.unique' => 'Kapasitas APAR ini sudah terdaftar.']);",
    $content
);

// updateKapasitas
$content = str_replace(
    "public function updateKapasitas(Request \$request, KapasitasApar \$kapasitasApar)\n    {\n        \$request->validate(['ukuran' => 'required|string|max:255']);",
    "public function updateKapasitas(Request \$request, KapasitasApar \$kapasitasApar)\n    {\n        \$request->validate(['ukuran' => 'required|string|max:255|unique:kapasitas_apar,ukuran,' . \$kapasitasApar->id], ['ukuran.unique' => 'Kapasitas APAR ini sudah terdaftar.']);",
    $content
);

file_put_contents($file, $content);
echo 'Validation added successfully.';
