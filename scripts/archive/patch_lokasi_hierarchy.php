<?php

$file = __DIR__.'/resources/views/master-data/index.blade.php';
$content = file_get_contents($file);

$oldLokasiList = <<<'HTML'
                    <div class="space-y-2">
                        @forelse($lokasis as $lok)
                        <div class="bg-white border border-slate-100 p-3.5 rounded-2xl flex items-center justify-between hover:border-teal-200 hover:shadow-sm transition-all group/item">
                            <div class="flex flex-col">
                                <span class="font-bold text-slate-700 text-sm">{{ $lok->nama }}</span>
                                <span class="text-[10px] font-bold text-slate-400 uppercase">{{ $lok->gedung->nama ?? '-' }}</span>
                            </div>
                            <div class="flex items-center gap-1 opacity-0 group-hover/item:opacity-100 transition-opacity">
                                <button @click="editLokasi = { id: {{ $lok->id }}, nama: '{{ addslashes($lok->nama) }}', gedung_id: '{{ $lok->gedung_id }}' }; showEditLokasi = true" class="w-8 h-8 rounded-lg text-slate-400 hover:text-amber-500 hover:bg-amber-50 flex items-center justify-center transition-colors"><i class="ph-bold ph-pencil-simple text-base"></i></button>
                                <form action="/master-data/lokasi/{{ $lok->id }}" method="POST" class="inline" onsubmit="confirmDelete(event, 'Yakin hapus lokasi ini?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="w-8 h-8 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 flex items-center justify-center transition-colors"><i class="ph-bold ph-trash text-base"></i></button>
                                </form>
                            </div>
                        </div>
                        @empty
                        <div class="flex flex-col items-center justify-center py-8 text-center">
                            <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                                <i class="ph-duotone ph-map-pin text-2xl"></i>
                            </div>
                            <h4 class="font-bold text-slate-700 text-sm">Belum ada data</h4>
                            <p class="text-xs text-slate-500 mt-1 max-w-[200px]">Tambahkan lokasi penempatan APAR pertama Anda</p>
                        </div>
                        @endforelse
                    </div>
HTML;

$newLokasiList = <<<'HTML'
                    <div class="space-y-4">
                        @forelse($gedungs as $gedung)
                            <div class="flex flex-col gap-2">
                                <!-- Gedung Header -->
                                <div class="bg-white border border-indigo-100 p-3 rounded-xl flex items-center justify-between shadow-[0_2px_10px_rgba(0,0,0,0.02)] group/gedung">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                                            <i class="ph-bold ph-buildings text-sm"></i>
                                        </div>
                                        <span class="font-extrabold text-slate-800 text-sm tracking-wide">{{ $gedung->nama }}</span>
                                    </div>
                                    <div class="flex items-center gap-1 opacity-0 group-hover/gedung:opacity-100 transition-opacity">
                                        <button @click="editGedung = { id: {{ $gedung->id }}, nama: '{{ addslashes($gedung->nama) }}' }; showEditGedung = true" class="w-7 h-7 rounded bg-amber-50 text-amber-500 hover:bg-amber-500 hover:text-white flex items-center justify-center transition-colors" title="Edit Gedung"><i class="ph-bold ph-pencil-simple text-sm"></i></button>
                                        <form action="/master-data/gedung/{{ $gedung->id }}" method="POST" class="inline" onsubmit="confirmDelete(event, 'Yakin hapus gedung ini? Semua lokasi di dalamnya juga akan terhapus!');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="w-7 h-7 rounded bg-red-50 text-red-500 hover:bg-red-500 hover:text-white flex items-center justify-center transition-colors" title="Hapus Gedung"><i class="ph-bold ph-trash text-sm"></i></button>
                                        </form>
                                    </div>
                                </div>
                                
                                <!-- Child Lokasi List -->
                                <div class="flex flex-col pl-6 space-y-2 border-l-2 border-slate-200/60 ml-4 relative">
                                    @php
                                        $lokasiByGedung = $lokasis->where('gedung_id', $gedung->id);
                                    @endphp
                                    
                                    @forelse($lokasiByGedung as $lok)
                                    <div class="bg-white/60 border border-slate-100/80 p-2.5 rounded-xl flex items-center justify-between hover:bg-white hover:border-teal-200 hover:shadow-sm transition-all group/item relative">
                                        <!-- Connection line -->
                                        <div class="absolute -left-6 top-1/2 w-4 h-px bg-slate-200/60"></div>
                                        
                                        <div class="flex items-center gap-2">
                                            <i class="ph-bold ph-map-pin text-slate-400 text-sm"></i>
                                            <span class="font-bold text-slate-600 text-sm">{{ $lok->nama }}</span>
                                        </div>
                                        <div class="flex items-center gap-1 opacity-0 group-hover/item:opacity-100 transition-opacity">
                                            <button @click="editLokasi = { id: {{ $lok->id }}, nama: '{{ addslashes($lok->nama) }}', gedung_id: '{{ $lok->gedung_id }}' }; showEditLokasi = true" class="w-7 h-7 rounded-lg text-slate-400 hover:text-amber-500 hover:bg-amber-50 flex items-center justify-center transition-colors"><i class="ph-bold ph-pencil-simple text-sm"></i></button>
                                            <form action="/master-data/lokasi/{{ $lok->id }}" method="POST" class="inline" onsubmit="confirmDelete(event, 'Yakin hapus lokasi ini?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="w-7 h-7 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 flex items-center justify-center transition-colors"><i class="ph-bold ph-trash text-sm"></i></button>
                                            </form>
                                        </div>
                                    </div>
                                    @empty
                                    <div class="text-xs font-semibold text-slate-400 italic py-1 pl-2 relative">
                                        <!-- Connection line -->
                                        <div class="absolute -left-6 top-1/2 w-4 h-px bg-slate-200/60"></div>
                                        Belum ada lokasi
                                    </div>
                                    @endforelse
                                </div>
                            </div>
                        @empty
                            <div class="flex flex-col items-center justify-center py-8 text-center">
                                <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                                    <i class="ph-duotone ph-buildings text-2xl"></i>
                                </div>
                                <h4 class="font-bold text-slate-700 text-sm">Belum ada data</h4>
                                <p class="text-xs text-slate-500 mt-1 max-w-[200px]">Tambahkan gedung dan lokasi pertama Anda</p>
                            </div>
                        @endforelse
                    </div>
HTML;

$content = str_replace($oldLokasiList, $newLokasiList, $content);
file_put_contents($file, $content);
echo "Lokasi list patched.\n";
