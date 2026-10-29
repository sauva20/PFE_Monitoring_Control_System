<?php

$file = __DIR__.'/resources/views/master-data/index.blade.php';
$content = file_get_contents($file);

// 1. Tambah Lokasi Input
$lokasiInputSearch = <<<'HTML'
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Nama Lokasi</label>
                                <input type="text" name="nama" placeholder="Contoh: Ruang Server Lt 3" class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 px-4 text-sm font-medium text-slate-700 focus:bg-white focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none">
                            </div>
HTML;
$lokasiInputReplace = <<<'HTML'
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Nama Lokasi</label>
                                <input type="text" name="nama" x-model="nama" required placeholder="Contoh: Ruang Server Lt 3" class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 px-4 text-sm font-medium text-slate-700 focus:bg-white focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none">
                                <p x-show="isDuplicate" x-cloak class="text-xs text-red-500 font-medium mt-2"><i class="ph-bold ph-warning-circle mr-1"></i>Lokasi dengan nama ini sudah ada di gedung yang dipilih.</p>
                            </div>
HTML;
$content = str_replace($lokasiInputSearch, $lokasiInputReplace, $content);

// 2. Tambah Lokasi Button
$lokasiBtnSearch = <<<'HTML'
                    <div class="bg-slate-50 px-6 py-4 sm:flex sm:flex-row-reverse sm:px-8 border-t border-slate-100">
                        <button type="submit" class="inline-flex w-full justify-center rounded-xl bg-teal-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-teal-700 sm:ml-3 sm:w-auto transition-colors shadow-teal-600/20">Simpan</button>
                        <button type="button" @click="showModalLokasi = false" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-5 py-2.5 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors">Batal</button>
                    </div>
HTML;
$lokasiBtnReplace = <<<'HTML'
                    <div class="bg-slate-50 px-6 py-4 sm:flex sm:flex-row-reverse sm:px-8 border-t border-slate-100">
                        <button type="submit" :disabled="isDuplicate || nama.trim() === '' || gedung_id === ''" :class="isDuplicate || nama.trim() === '' || gedung_id === '' ? 'opacity-50 cursor-not-allowed' : 'hover:bg-teal-700'" class="inline-flex w-full justify-center rounded-xl bg-teal-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm sm:ml-3 sm:w-auto transition-colors shadow-teal-600/20">Simpan</button>
                        <button type="button" @click="showModalLokasi = false" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-5 py-2.5 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors">Batal</button>
                    </div>
HTML;
$content = str_replace($lokasiBtnSearch, $lokasiBtnReplace, $content);

// 3. Tambah Jenis Input
$jenisInputSearch = <<<'HTML'
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Nama Jenis</label>
                                <input type="text" name="nama" required placeholder="Contoh: ABC Powder" class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 px-4 text-sm font-medium text-slate-700 focus:bg-white focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none">
                            </div>
HTML;
$jenisInputReplace = <<<'HTML'
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Nama Jenis</label>
                                <input type="text" name="nama" x-model="nama" required placeholder="Contoh: ABC Powder" class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 px-4 text-sm font-medium text-slate-700 focus:bg-white focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none">
                                <p x-show="isDuplicate" x-cloak class="text-xs text-red-500 font-medium mt-2"><i class="ph-bold ph-warning-circle mr-1"></i>Jenis APAR ini sudah terdaftar.</p>
                            </div>
HTML;
$content = str_replace($jenisInputSearch, $jenisInputReplace, $content);

// 4. Tambah Jenis Button
$jenisBtnSearch = <<<'HTML'
                    <div class="bg-slate-50 px-6 py-4 sm:flex sm:flex-row-reverse sm:px-8 border-t border-slate-100">
                        <button type="submit" class="inline-flex w-full justify-center rounded-xl bg-amber-500 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-amber-600 sm:ml-3 sm:w-auto transition-colors shadow-amber-500/20">Simpan</button>
                        <button type="button" @click="showModalJenis = false" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-5 py-2.5 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors">Batal</button>
                    </div>
HTML;
$jenisBtnReplace = <<<'HTML'
                    <div class="bg-slate-50 px-6 py-4 sm:flex sm:flex-row-reverse sm:px-8 border-t border-slate-100">
                        <button type="submit" :disabled="isDuplicate || nama.trim() === ''" :class="isDuplicate || nama.trim() === '' ? 'opacity-50 cursor-not-allowed' : 'hover:bg-amber-600'" class="inline-flex w-full justify-center rounded-xl bg-amber-500 px-5 py-2.5 text-sm font-bold text-white shadow-sm sm:ml-3 sm:w-auto transition-colors shadow-amber-500/20">Simpan</button>
                        <button type="button" @click="showModalJenis = false" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-5 py-2.5 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors">Batal</button>
                    </div>
HTML;
$content = str_replace($jenisBtnSearch, $jenisBtnReplace, $content);

// 5. Tambah Kapasitas Input
$kapasitasInputSearch = <<<'HTML'
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Kapasitas</label>
                                <input type="text" name="ukuran" required placeholder="Contoh: 3 Kg" class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 px-4 text-sm font-medium text-slate-700 focus:bg-white focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none">
                            </div>
HTML;
$kapasitasInputReplace = <<<'HTML'
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Kapasitas</label>
                                <input type="text" name="ukuran" x-model="ukuran" required placeholder="Contoh: 3 Kg" class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 px-4 text-sm font-medium text-slate-700 focus:bg-white focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none">
                                <p x-show="isDuplicate" x-cloak class="text-xs text-red-500 font-medium mt-2"><i class="ph-bold ph-warning-circle mr-1"></i>Kapasitas APAR ini sudah terdaftar.</p>
                            </div>
HTML;
$content = str_replace($kapasitasInputSearch, $kapasitasInputReplace, $content);

// 6. Tambah Kapasitas Button
$kapasitasBtnSearch = <<<'HTML'
                    <div class="bg-slate-50 px-6 py-4 sm:flex sm:flex-row-reverse sm:px-8 border-t border-slate-100">
                        <button type="submit" class="inline-flex w-full justify-center rounded-xl bg-purple-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-purple-700 sm:ml-3 sm:w-auto transition-colors shadow-purple-600/20">Simpan</button>
                        <button type="button" @click="showModalKapasitas = false" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-5 py-2.5 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors">Batal</button>
                    </div>
HTML;
$kapasitasBtnReplace = <<<'HTML'
                    <div class="bg-slate-50 px-6 py-4 sm:flex sm:flex-row-reverse sm:px-8 border-t border-slate-100">
                        <button type="submit" :disabled="isDuplicate || ukuran.trim() === ''" :class="isDuplicate || ukuran.trim() === '' ? 'opacity-50 cursor-not-allowed' : 'hover:bg-purple-700'" class="inline-flex w-full justify-center rounded-xl bg-purple-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm sm:ml-3 sm:w-auto transition-colors shadow-purple-600/20">Simpan</button>
                        <button type="button" @click="showModalKapasitas = false" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-5 py-2.5 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors">Batal</button>
                    </div>
HTML;
$content = str_replace($kapasitasBtnSearch, $kapasitasBtnReplace, $content);

file_put_contents($file, $content);
echo 'All 3 forms patched.';
