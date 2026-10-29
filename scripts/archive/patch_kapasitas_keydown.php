<?php

$file = __DIR__.'/resources/views/master-data/index.blade.php';
$content = file_get_contents($file);

// Tambah Kapasitas
$tambahSearch = <<<'HTML'
                                    <input type="number" step="any" name="ukuran" x-model="ukuran" @keydown.enter="if(isDuplicate || ukuran.trim() === '') { $event.preventDefault(); showModalKapasitas = false; }" required placeholder="Contoh: 3" class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 pl-4 pr-12 text-sm font-medium text-slate-700 focus:bg-white focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none">
HTML;
$tambahReplace = <<<'HTML'
                                    <input type="number" step="any" name="ukuran" x-model="ukuran" @keydown="['e', 'E', '+', '-'].includes($event.key) && $event.preventDefault()" @keydown.enter="if(isDuplicate || ukuran.trim() === '') { $event.preventDefault(); showModalKapasitas = false; }" required placeholder="Contoh: 3" class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 pl-4 pr-12 text-sm font-medium text-slate-700 focus:bg-white focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none">
HTML;
$content = str_replace($tambahSearch, $tambahReplace, $content);

// Edit Kapasitas
$editSearch = <<<'HTML'
                                        <input type="number" step="any" name="ukuran" :value="editKapasitas.ukuran.replace(/[^0-9.]/g, '')" required class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 pl-4 pr-12 text-sm font-medium text-slate-700 focus:bg-white focus:border-amber-500 focus:ring-4 focus:ring-amber-500/15 transition-all outline-none">
HTML;
$editReplace = <<<'HTML'
                                        <input type="number" step="any" name="ukuran" :value="editKapasitas.ukuran.replace(/[^0-9.]/g, '')" @keydown="['e', 'E', '+', '-'].includes($event.key) && $event.preventDefault()" required class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 pl-4 pr-12 text-sm font-medium text-slate-700 focus:bg-white focus:border-amber-500 focus:ring-4 focus:ring-amber-500/15 transition-all outline-none">
HTML;
$content = str_replace($editSearch, $editReplace, $content);

file_put_contents($file, $content);
echo 'Patched.';
