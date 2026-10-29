<?php

$file = __DIR__.'/resources/views/master-data/index.blade.php';
$content = file_get_contents($file);

// Replace options
$searchOptions1 = <<<'HTML'
                            @foreach($lokasis as $lokasi)
                            { id: '{{ $lokasi->id }}', name: '{{ addslashes($lokasi->nama) }} • {{ addslashes($lokasi->gedung->nama) }}' },
                            @endforeach
HTML;
$replaceOptions1 = <<<'HTML'
                            @foreach($lokasis as $lokasi)
                            { id: '{{ $lokasi->id }}', name: '{{ addslashes($lokasi->nama) }} {{ addslashes($lokasi->gedung->nama) }}', lokasi: '{{ addslashes($lokasi->nama) }}', gedung: '{{ addslashes($lokasi->gedung->nama) }}' },
                            @endforeach
HTML;
$content = str_replace($searchOptions1, $replaceOptions1, $content);

// Replace selectedName getter
$searchGetSel = "return sel ? sel.name : 'Pilih Lokasi';";
$replaceGetSel = 'return sel ? sel : null;';
$content = str_replace($searchGetSel, $replaceGetSel, $content);

// Replace Trigger
$searchTrigger = <<<'HTML'
                        <span x-text="selectedName" :class="!selectedId ? 'text-slate-400 font-medium' : ''" class="truncate block"></span>
HTML;
$replaceTrigger = <<<'HTML'
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
$content = str_replace($searchTrigger, $replaceTrigger, $content);

// Replace Option Content
$searchOptionContent = <<<'HTML'
                                <span x-text="option.name"></span>
HTML;
$replaceOptionContent = <<<'HTML'
                                <div class="flex items-center justify-between w-full pr-4">
                                    <span x-text="option.lokasi" class="font-bold"></span>
                                    <span x-text="option.gedung" class="text-[10px] font-bold uppercase tracking-wider py-0.5 px-2 rounded-md transition-colors" :class="selectedId == option.id ? 'bg-[#009B77]/10 text-[#009B77]' : 'bg-slate-100 text-slate-500'"></span>
                                </div>
HTML;
$content = str_replace($searchOptionContent, $replaceOptionContent, $content);

file_put_contents($file, $content);
echo 'Style applied successfully.';
