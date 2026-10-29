<?php

$ctrlFile = __DIR__.'/app/Http/Controllers/MasterDataController.php';
$ctrlContent = file_get_contents($ctrlFile);

// storeApar
$storeSearch = <<<'PHP'
    public function storeApar(Request $request)
    {
        $request->validate([
            'kode' => 'required|string|unique:apar,kode',
            'lokasi_id' => 'required|exists:lokasi,id',
            'jenis_id' => 'required|exists:jenis_apar,id',
            'kapasitas_id' => 'required|exists:kapasitas_apar,id',
            'tgl_kedaluwarsa' => 'nullable|date',
        ]);

        Apar::create($request->all());
PHP;
$storeReplace = <<<PHP
    public function storeApar(Request \$request)
    {
        \$request->validate([
            'lokasi_id' => 'required|exists:lokasi,id',
            'jenis_id' => 'required|exists:jenis_apar,id',
            'kapasitas_id' => 'required|exists:kapasitas_apar,id',
            'tgl_kedaluwarsa' => 'nullable|date',
        ]);

        \$lokasi = \App\Models\Lokasi::with('gedung')->find(\$request->lokasi_id);
        \$jenis = \App\Models\JenisApar::find(\$request->jenis_id);

        \$gedungNama = \$lokasi->gedung->nama ?? '';
        \$gedungCode = '';
        if (strpos(strtoupper(\$gedungNama), 'BU-') !== false) {
            \$parts = explode('BU-', strtoupper(\$gedungNama));
            \$gedungCode = trim(\$parts[1] ?? '');
        } 
        if (empty(\$gedungCode)) {
            \$gedungCode = substr(strtoupper(\$gedungNama), 0, 1);
        }

        \$jenisCode = 'C';
        \$jenisNamaL = strtolower(\$jenis->nama ?? '');
        if (strpos(\$jenisNamaL, 'dry chemical') !== false || strpos(\$jenisNamaL, 'powder') !== false) {
            \$jenisCode = 'A';
        } elseif (strpos(\$jenisNamaL, 'carbon') !== false || strpos(\$jenisNamaL, 'dioxide') !== false || strpos(\$jenisNamaL, 'co2') !== false) {
            \$jenisCode = 'B';
        } else {
            \$jenisCode = substr(strtoupper(\$jenis->nama ?? 'C'), 0, 1);
        }

        \$prefix = "PFE-{\$gedungCode}-{\$jenisCode}";

        \$lastApar = \App\Models\Apar::where('kode', 'like', "{\$prefix}%")->get();
        \$maxSeq = 0;
        foreach (\$lastApar as \$apar) {
            \$num = (int) substr(\$apar->kode, strlen(\$prefix));
            if (\$num > \$maxSeq) \$maxSeq = \$num;
        }

        \$kode = \$prefix . (\$maxSeq + 1);

        \$data = \$request->except('kode');
        \$data['kode'] = \$kode;

        Apar::create(\$data);
PHP;
$ctrlContent = str_replace($storeSearch, $storeReplace, $ctrlContent);

// updateApar
$updateSearch = <<<'PHP'
    public function updateApar(Request $request, Apar $apar)
    {
        $request->validate([
            'kode' => 'required|string|unique:apar,kode,' . $apar->id,
            'lokasi_id' => 'required|exists:lokasi,id',
            'jenis_id' => 'required|exists:jenis_apar,id',
            'kapasitas_id' => 'required|exists:kapasitas_apar,id',
            'tgl_kedaluwarsa' => 'nullable|date',
        ]);

        $apar->update($request->all());
PHP;
$updateReplace = <<<'PHP'
    public function updateApar(Request $request, Apar $apar)
    {
        $request->validate([
            'lokasi_id' => 'required|exists:lokasi,id',
            'jenis_id' => 'required|exists:jenis_apar,id',
            'kapasitas_id' => 'required|exists:kapasitas_apar,id',
            'tgl_kedaluwarsa' => 'nullable|date',
        ]);

        $apar->update($request->except('kode'));
PHP;
$ctrlContent = str_replace($updateSearch, $updateReplace, $ctrlContent);

file_put_contents($ctrlFile, $ctrlContent);

// Blade
$bladeFile = __DIR__.'/resources/views/master-data/index.blade.php';
$bladeContent = file_get_contents($bladeFile);

// Tambah APAR Input
$bladeTambahSearch = <<<'HTML'
                    <input type="text" name="kode" placeholder="Contoh: APAR-046" value="{{ old('kode') }}" class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 pl-10 pr-4 text-sm font-medium text-slate-700 focus:outline-none focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15">
HTML;
$bladeTambahReplace = <<<'HTML'
                    <input type="text" name="kode" placeholder="ID dibuat otomatis oleh sistem" readonly class="w-full bg-slate-100 border-2 border-slate-200 rounded-xl py-2.5 pl-10 pr-4 text-sm font-medium text-slate-500 cursor-not-allowed focus:outline-none">
HTML;
$bladeContent = str_replace($bladeTambahSearch, $bladeTambahReplace, $bladeContent);

// Edit APAR Input
$bladeEditSearch = <<<'HTML'
                    <input type="text" name="kode" placeholder="Contoh: APAR-046" x-model="editApar.kode" class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 pl-10 pr-4 text-sm font-medium text-slate-700 focus:outline-none focus:border-amber-500 focus:ring-4 focus:ring-amber-500/15">
HTML;
$bladeEditReplace = <<<'HTML'
                    <input type="text" name="kode" readonly x-model="editApar.kode" class="w-full bg-slate-100 border-2 border-slate-200 rounded-xl py-2.5 pl-10 pr-4 text-sm font-medium text-slate-500 cursor-not-allowed focus:outline-none">
HTML;
$bladeContent = str_replace($bladeEditSearch, $bladeEditReplace, $bladeContent);

file_put_contents($bladeFile, $bladeContent);

echo 'Patched.';
