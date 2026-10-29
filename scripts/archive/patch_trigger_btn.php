<?php

$file = __DIR__.'/resources/views/master-data/index.blade.php';
$content = file_get_contents($file);

$search1 = <<<'HTML'
                        <template x-if="selectedName">
                            <div class="flex items-center gap-2 truncate">
                                <span x-text="selectedName.lokasi" class="font-bold text-slate-700"></span>
                                <span x-text="selectedName.gedung" class="text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-500 py-0.5 px-2 rounded-md"></span>
                            </div>
                        </template>
                        <template x-if="!selectedName">
                            <span class="text-slate-400 font-medium truncate block">Pilih Lokasi</span>
                        </template>
HTML;

// Find all occurrences of this exact block
$parts = explode($search1, $content);
// There should be 5 occurrences: Lokasi(Tambah), Jenis(Tambah), Kapasitas(Tambah), Jenis(Edit), Kapasitas(Edit). Wait, Lokasi(Edit) was also there?
// Let's check how many times it matches:
echo 'Matched parts: '.count($parts)."\n";

// We know the 1st one is Lokasi Tambah (KEEP IT as Pilih Lokasi, but template stays the same).
// The 2nd is Jenis Tambah.
// The 3rd is Kapasitas Tambah.
// The 4th is Lokasi Edit (KEEP IT).
// The 5th is Jenis Edit.
// The 6th is Kapasitas Edit.

$jenisReplace = <<<'HTML'
                        <template x-if="selectedName">
                            <span x-text="selectedName.name" class="font-bold text-slate-700 truncate block"></span>
                        </template>
                        <template x-if="!selectedName">
                            <span class="text-slate-400 font-medium truncate block">Pilih Jenis APAR</span>
                        </template>
HTML;

$kapasitasReplace = <<<'HTML'
                        <template x-if="selectedName">
                            <span x-text="selectedName.name" class="font-bold text-slate-700 truncate block"></span>
                        </template>
                        <template x-if="!selectedName">
                            <span class="text-slate-400 font-medium truncate block">Pilih Kapasitas</span>
                        </template>
HTML;

if (count($parts) === 7) {
    // parts[0] is before 1st (Lokasi Tambah)
    // parts[1] is after 1st, before 2nd (Jenis Tambah)
    // parts[2] is after 2nd, before 3rd (Kapasitas Tambah)
    // parts[3] is after 3rd, before 4th (Lokasi Edit)
    // parts[4] is after 4th, before 5th (Jenis Edit)
    // parts[5] is after 5th, before 6th (Kapasitas Edit)
    // parts[6] is after 6th

    $newContent = $parts[0].
        $search1.$parts[1].
        $jenisReplace.$parts[2].
        $kapasitasReplace.$parts[3].
        $search1.$parts[4].
        $jenisReplace.$parts[5].
        $kapasitasReplace.$parts[6];

    file_put_contents($file, $newContent);
    echo "Patched successfully.\n";
} else {
    echo 'Unexpected number of parts: '.count($parts)."\n";
}
