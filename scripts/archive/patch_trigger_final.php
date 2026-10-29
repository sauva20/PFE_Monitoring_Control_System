<?php

$file = __DIR__.'/resources/views/master-data/index.blade.php';
$content = file_get_contents($file);

$jenisSearch = <<<'HTML'
                        <template x-if="selectedName">
                            <span x-text="selectedName.name" class="font-bold text-slate-700 truncate block"></span>
                        </template>
                        <template x-if="!selectedName">
                            <span class="text-slate-400 font-medium truncate block">Pilih Jenis APAR</span>
                        </template>
HTML;

$kapasitasSearch = <<<'HTML'
                        <template x-if="selectedName">
                            <span x-text="selectedName.name" class="font-bold text-slate-700 truncate block"></span>
                        </template>
                        <template x-if="!selectedName">
                            <span class="text-slate-400 font-medium truncate block">Pilih Kapasitas</span>
                        </template>
HTML;

$replace = <<<'HTML'
                        <span x-text="selectedName" :class="selectedId ? 'font-bold text-slate-700' : 'text-slate-400 font-medium'" class="truncate block"></span>
HTML;

$content = str_replace($jenisSearch, $replace, $content);
$content = str_replace($kapasitasSearch, $replace, $content);

file_put_contents($file, $content);
echo 'Patched.';
