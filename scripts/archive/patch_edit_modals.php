<?php

$file = __DIR__.'/resources/views/master-data/index.blade.php';
$content = file_get_contents($file);

$modals = <<<HTML

    <!-- Modal Edit Gedung -->
    <div x-show="showEditGedung" style="display: none;" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true" x-cloak>
        <div x-show="showEditGedung"
             x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/40 transition-opacity" x-cloak></div>
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div x-show="showEditGedung" @click.away="showEditGedung = false"
                     x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-100" x-cloak>
                    <form :action="'/master-data/gedung/' + editGedung.id" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="bg-white px-6 pb-6 pt-6 sm:p-8">
                            <div class="flex items-center justify-between mb-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center">
                                        <i class="ph-bold ph-pencil-simple text-xl"></i>
                                    </div>
                                    <h3 class="text-lg font-bold leading-6 text-slate-800" id="modal-title">Edit Data Gedung</h3>
                                </div>
                                <button type="button" @click="showEditGedung = false" class="text-slate-400 hover:text-slate-500 transition-colors"><i class="ph-bold ph-x text-xl"></i></button>
                            </div>
                            <div class="space-y-5">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Nama Gedung</label>
                                    <input type="text" name="nama" x-model="editGedung.nama" required class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 px-4 text-sm font-medium text-slate-700 focus:bg-white focus:border-amber-500 focus:ring-4 focus:ring-amber-500/15 transition-all outline-none">
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-50 px-6 py-4 sm:flex sm:flex-row-reverse sm:px-8 border-t border-slate-100">
                            <button type="submit" class="inline-flex w-full justify-center rounded-xl bg-amber-500 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-amber-600 sm:ml-3 sm:w-auto transition-colors shadow-amber-500/20">Simpan Perubahan</button>
                            <button type="button" @click="showEditGedung = false" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-5 py-2.5 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit Lokasi -->
    <div x-show="showEditLokasi" style="display: none;" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true" x-cloak>
        <div x-show="showEditLokasi"
             x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/40 transition-opacity" x-cloak></div>
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div x-show="showEditLokasi" @click.away="showEditLokasi = false"
                     x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-100" x-cloak>
                    <form :action="'/master-data/lokasi/' + editLokasi.id" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="bg-white px-6 pb-6 pt-6 sm:p-8">
                            <div class="flex items-center justify-between mb-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center">
                                        <i class="ph-bold ph-pencil-simple text-xl"></i>
                                    </div>
                                    <h3 class="text-lg font-bold leading-6 text-slate-800" id="modal-title">Edit Data Lokasi</h3>
                                </div>
                                <button type="button" @click="showEditLokasi = false" class="text-slate-400 hover:text-slate-500 transition-colors"><i class="ph-bold ph-x text-xl"></i></button>
                            </div>
                            <div class="space-y-5">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Nama Lokasi</label>
                                    <input type="text" name="nama" x-model="editLokasi.nama" required class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 px-4 text-sm font-medium text-slate-700 focus:bg-white focus:border-amber-500 focus:ring-4 focus:ring-amber-500/15 transition-all outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Pilih Gedung</label>
                                    <select name="gedung_id" x-model="editLokasi.gedung_id" required class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 px-4 text-sm font-medium text-slate-700 focus:bg-white focus:border-amber-500 focus:ring-4 focus:ring-amber-500/15 transition-all outline-none appearance-none">
                                        <option value="">Pilih Gedung</option>
                                        @foreach(\App\Models\Gedung::all() as \$ged)
                                            <option value="{{ \$ged->id }}">{{ \$ged->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-50 px-6 py-4 sm:flex sm:flex-row-reverse sm:px-8 border-t border-slate-100">
                            <button type="submit" class="inline-flex w-full justify-center rounded-xl bg-amber-500 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-amber-600 sm:ml-3 sm:w-auto transition-colors shadow-amber-500/20">Simpan Perubahan</button>
                            <button type="button" @click="showEditLokasi = false" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-5 py-2.5 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit Jenis -->
    <div x-show="showEditJenis" style="display: none;" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true" x-cloak>
        <div x-show="showEditJenis"
             x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/40 transition-opacity" x-cloak></div>
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div x-show="showEditJenis" @click.away="showEditJenis = false"
                     x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-100" x-cloak>
                    <form :action="'/master-data/jenis/' + editJenis.id" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="bg-white px-6 pb-6 pt-6 sm:p-8">
                            <div class="flex items-center justify-between mb-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center">
                                        <i class="ph-bold ph-pencil-simple text-xl"></i>
                                    </div>
                                    <h3 class="text-lg font-bold leading-6 text-slate-800" id="modal-title">Edit Jenis APAR</h3>
                                </div>
                                <button type="button" @click="showEditJenis = false" class="text-slate-400 hover:text-slate-500 transition-colors"><i class="ph-bold ph-x text-xl"></i></button>
                            </div>
                            <div class="space-y-5">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Nama Jenis</label>
                                    <input type="text" name="nama" x-model="editJenis.nama" required class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 px-4 text-sm font-medium text-slate-700 focus:bg-white focus:border-amber-500 focus:ring-4 focus:ring-amber-500/15 transition-all outline-none">
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-50 px-6 py-4 sm:flex sm:flex-row-reverse sm:px-8 border-t border-slate-100">
                            <button type="submit" class="inline-flex w-full justify-center rounded-xl bg-amber-500 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-amber-600 sm:ml-3 sm:w-auto transition-colors shadow-amber-500/20">Simpan Perubahan</button>
                            <button type="button" @click="showEditJenis = false" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-5 py-2.5 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit Kapasitas -->
    <div x-show="showEditKapasitas" style="display: none;" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true" x-cloak>
        <div x-show="showEditKapasitas"
             x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/40 transition-opacity" x-cloak></div>
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div x-show="showEditKapasitas" @click.away="showEditKapasitas = false"
                     x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-100" x-cloak>
                    <form :action="'/master-data/kapasitas/' + editKapasitas.id" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="bg-white px-6 pb-6 pt-6 sm:p-8">
                            <div class="flex items-center justify-between mb-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center">
                                        <i class="ph-bold ph-pencil-simple text-xl"></i>
                                    </div>
                                    <h3 class="text-lg font-bold leading-6 text-slate-800" id="modal-title">Edit Kapasitas</h3>
                                </div>
                                <button type="button" @click="showEditKapasitas = false" class="text-slate-400 hover:text-slate-500 transition-colors"><i class="ph-bold ph-x text-xl"></i></button>
                            </div>
                            <div class="space-y-5">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Kapasitas</label>
                                    <input type="text" name="ukuran" x-model="editKapasitas.ukuran" required class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 px-4 text-sm font-medium text-slate-700 focus:bg-white focus:border-amber-500 focus:ring-4 focus:ring-amber-500/15 transition-all outline-none">
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-50 px-6 py-4 sm:flex sm:flex-row-reverse sm:px-8 border-t border-slate-100">
                            <button type="submit" class="inline-flex w-full justify-center rounded-xl bg-amber-500 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-amber-600 sm:ml-3 sm:w-auto transition-colors shadow-amber-500/20">Simpan Perubahan</button>
                            <button type="button" @click="showEditKapasitas = false" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-5 py-2.5 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
HTML;

// Append just before @endsection
$content = str_replace('@endsection', $modals."\n@endsection", $content);
file_put_contents($file, $content);
echo "Modals appended.\n";
