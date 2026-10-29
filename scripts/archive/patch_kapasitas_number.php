<?php

$file = __DIR__.'/resources/views/master-data/index.blade.php';
$content = file_get_contents($file);

// Tambah Kapasitas
$tambahSearch = <<<'HTML'
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Kapasitas</label>
                                <input type="text" name="ukuran" x-model="ukuran" @keydown.enter="if(isDuplicate || ukuran.trim() === '') { $event.preventDefault(); showModalKapasitas = false; }" required placeholder="Contoh: 3 Kg" class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 px-4 text-sm font-medium text-slate-700 focus:bg-white focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none">
HTML;
$tambahReplace = <<<'HTML'
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Kapasitas</label>
                                <div class="relative flex items-center">
                                    <input type="number" step="any" name="ukuran" x-model="ukuran" @keydown.enter="if(isDuplicate || ukuran.trim() === '') { $event.preventDefault(); showModalKapasitas = false; }" required placeholder="Contoh: 3" class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 pl-4 pr-12 text-sm font-medium text-slate-700 focus:bg-white focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none">
                                    <span class="absolute right-4 font-bold text-slate-400">Kg</span>
                                </div>
HTML;
$content = str_replace($tambahSearch, $tambahReplace, $content);

// Edit Kapasitas
$editSearch = <<<'HTML'
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Kapasitas</label>
                                    <input type="text" name="ukuran" x-model="editKapasitas.ukuran" required class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 px-4 text-sm font-medium text-slate-700 focus:bg-white focus:border-amber-500 focus:ring-4 focus:ring-amber-500/15 transition-all outline-none">
HTML;
$editReplace = <<<'HTML'
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Kapasitas</label>
                                    <div class="relative flex items-center">
                                        <!-- Remove ' Kg' from the model value for display, but keep the input name as ukuran. 
                                             We use a computed property or just x-effect to strip Kg when modal opens, but easier is just allowing Alpine to display the raw value, 
                                             wait, editKapasitas.ukuran already has " Kg" in it from the backend. 
                                             Let's strip it using x-bind:value and x-on:input, or just let the backend handle the 'Kg' part. 
                                             Actually, if we just set x-model="editKapasitas.ukuran_num" and populate it on click. -->
                                        <input type="number" step="any" name="ukuran" :value="editKapasitas.ukuran.replace(/[^0-9.]/g, '')" required class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 pl-4 pr-12 text-sm font-medium text-slate-700 focus:bg-white focus:border-amber-500 focus:ring-4 focus:ring-amber-500/15 transition-all outline-none">
                                        <span class="absolute right-4 font-bold text-slate-400">Kg</span>
                                    </div>
HTML;
$content = str_replace($editSearch, $editReplace, $content);

file_put_contents($file, $content);

$ctrlFile = __DIR__.'/app/Http/Controllers/MasterDataController.php';
$ctrlContent = file_get_contents($ctrlFile);

$storeSearch = <<<'PHP'
    public function storeKapasitas(Request $request)
    {
        $request->validate([
PHP;
$storeReplace = <<<'PHP'
    public function storeKapasitas(Request $request)
    {
        if ($request->has('ukuran')) {
            $ukuran = trim($request->ukuran);
            if (!preg_match('/(?i)kg$/', $ukuran)) {
                $request->merge(['ukuran' => trim($ukuran) . ' Kg']);
            }
        }
        
        $request->validate([
PHP;
$ctrlContent = str_replace($storeSearch, $storeReplace, $ctrlContent);

$updateSearch = <<<'PHP'
    public function updateKapasitas(Request $request, KapasitasApar $kapasitasApar)
    {
        $request->validate([
PHP;
$updateReplace = <<<'PHP'
    public function updateKapasitas(Request $request, KapasitasApar $kapasitasApar)
    {
        if ($request->has('ukuran')) {
            $ukuran = trim($request->ukuran);
            if (!preg_match('/(?i)kg$/', $ukuran)) {
                $request->merge(['ukuran' => trim($ukuran) . ' Kg']);
            }
        }
        
        $request->validate([
PHP;
$ctrlContent = str_replace($updateSearch, $updateReplace, $ctrlContent);

file_put_contents($ctrlFile, $ctrlContent);

echo 'Patched.';
