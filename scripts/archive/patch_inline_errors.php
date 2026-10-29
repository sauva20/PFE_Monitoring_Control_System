<?php

$file = __DIR__.'/resources/views/master-data/index.blade.php';
$content = file_get_contents($file);

$replacements = [
    // 1. x-data initialization
    [
        'search' => "showModalApar: false, showModalEditApar: false, editApar: { id:'', kode:'', lokasi_id:'', jenis_id:'', kapasitas_id:'', vendor:'', tgl_kedaluwarsa:'' },  showModalLokasi: false, showModalGedung: false, showModalJenis: false, showModalKapasitas: false, showEditGedung: false, editGedung: { id:'', nama:'' }, showEditLokasi: false, editLokasi: { id:'', nama:'', gedung_id:'' }, showEditJenis: false, editJenis: { id:'', nama:'' }, showEditKapasitas: false, editKapasitas: { id:'', ukuran:'' }",
        'replace' => <<<'HTML'
showModalApar: {{ old('form_type') == 'tambah_apar' && $errors->any() ? 'true' : 'false' }}, 
showModalEditApar: {{ old('form_type') == 'edit_apar' && $errors->any() ? 'true' : 'false' }}, 
editApar: { id:'{{ old('form_type') == 'edit_apar' ? old('id') : '' }}', kode:'{{ old('form_type') == 'edit_apar' ? old('kode') : '' }}', lokasi_id:'{{ old('form_type') == 'edit_apar' ? old('lokasi_id') : '' }}', jenis_id:'{{ old('form_type') == 'edit_apar' ? old('jenis_id') : '' }}', kapasitas_id:'{{ old('form_type') == 'edit_apar' ? old('kapasitas_id') : '' }}', vendor:'{{ old('form_type') == 'edit_apar' ? old('vendor') : '' }}', tgl_kedaluwarsa:'{{ old('form_type') == 'edit_apar' ? old('tgl_kedaluwarsa') : '' }}' },  
showModalLokasi: {{ old('form_type') == 'tambah_lokasi' && $errors->any() ? 'true' : 'false' }}, 
showModalGedung: {{ old('form_type') == 'tambah_gedung' && $errors->any() ? 'true' : 'false' }}, 
showModalJenis: {{ old('form_type') == 'tambah_jenis' && $errors->any() ? 'true' : 'false' }}, 
showModalKapasitas: {{ old('form_type') == 'tambah_kapasitas' && $errors->any() ? 'true' : 'false' }}, 
showEditGedung: {{ old('form_type') == 'edit_gedung' && $errors->any() ? 'true' : 'false' }}, 
editGedung: { id:'{{ old('form_type') == 'edit_gedung' ? old('id') : '' }}', nama:'{{ old('form_type') == 'edit_gedung' ? old('nama') : '' }}' }, 
showEditLokasi: {{ old('form_type') == 'edit_lokasi' && $errors->any() ? 'true' : 'false' }}, 
editLokasi: { id:'{{ old('form_type') == 'edit_lokasi' ? old('id') : '' }}', nama:'{{ old('form_type') == 'edit_lokasi' ? old('nama') : '' }}', gedung_id:'{{ old('form_type') == 'edit_lokasi' ? old('gedung_id') : '' }}' }, 
showEditJenis: {{ old('form_type') == 'edit_jenis' && $errors->any() ? 'true' : 'false' }}, 
editJenis: { id:'{{ old('form_type') == 'edit_jenis' ? old('id') : '' }}', nama:'{{ old('form_type') == 'edit_jenis' ? old('nama') : '' }}' }, 
showEditKapasitas: {{ old('form_type') == 'edit_kapasitas' && $errors->any() ? 'true' : 'false' }}, 
editKapasitas: { id:'{{ old('form_type') == 'edit_kapasitas' ? old('id') : '' }}', ukuran:'{{ old('form_type') == 'edit_kapasitas' ? old('ukuran') : '' }}' }
HTML
    ],

    // Modal Tambah Gedung
    [
        'search' => '<form action="/master-data/gedung" method="POST">',
        'replace' => '<form action="/master-data/gedung" method="POST">'."\n".'<input type="hidden" name="form_type" value="tambah_gedung">',
    ],
    [
        'search' => '<input type="text" name="nama" required class="w-full',
        'replace' => '<input type="text" name="nama" value="{{ old(\'form_type\') == \'tambah_gedung\' ? old(\'nama\') : \'\' }}" required class="w-full',
    ],
    [
        'search' => '<!-- error_tambah_gedung -->', // Wait, I will just append after the input
        'replace' => '',
    ],
];

foreach ($replacements as $rep) {
    if ($rep['replace'] !== '') {
        $content = str_replace($rep['search'], $rep['replace'], $content);
    }
}

// Automatically append @error after inputs
function injectHiddenAndError($content, $formActionMatch, $formType, $inputName, $xModel = false, $isEdit = false)
{
    // Inject form_type hidden input
    if (strpos($content, '<input type="hidden" name="form_type" value="'.$formType.'">') === false) {
        $content = str_replace(
            $formActionMatch,
            $formActionMatch."\n                        <input type=\"hidden\" name=\"form_type\" value=\"$formType\">\n".($isEdit ? "                        <input type=\"hidden\" name=\"id\" :value=\"$isEdit\">\n" : ''),
            $content
        );
    }

    // Inject @error after the input
    if (! $xModel) {
        // Normal input
        $pattern = '/(<input[^>]+name="'.$inputName.'"[^>]*>)/i';
    } else {
        // Alpine input
        $pattern = '/(<input[^>]+x-model="'.$xModel.'"[^>]*>)/i';
    }

    // We only want to replace in the context of the specific form, but it's hard with regex.
    // Since names are mostly unique per form or we can just append it manually.

    return $content;
}

$content = injectHiddenAndError($content, '<form action="/master-data/gedung" method="POST">', 'tambah_gedung', 'nama');
$content = injectHiddenAndError($content, '<form action="/master-data/lokasi" method="POST">', 'tambah_lokasi', 'nama');
$content = injectHiddenAndError($content, '<form action="/master-data/jenis" method="POST">', 'tambah_jenis', 'nama');
$content = injectHiddenAndError($content, '<form action="/master-data/kapasitas" method="POST">', 'tambah_kapasitas', 'ukuran');
$content = injectHiddenAndError($content, '<form action="/master-data/apar" method="POST">', 'tambah_apar', 'kode');

$content = injectHiddenAndError($content, '<form :action="\'/master-data/gedung/\' + editGedung.id" method="POST">', 'edit_gedung', 'nama', 'editGedung.nama', 'editGedung.id');
$content = injectHiddenAndError($content, '<form :action="\'/master-data/lokasi/\' + editLokasi.id" method="POST">', 'edit_lokasi', 'nama', 'editLokasi.nama', 'editLokasi.id');
$content = injectHiddenAndError($content, '<form :action="\'/master-data/jenis/\' + editJenis.id" method="POST">', 'edit_jenis', 'nama', 'editJenis.nama', 'editJenis.id');
$content = injectHiddenAndError($content, '<form :action="\'/master-data/kapasitas/\' + editKapasitas.id" method="POST">', 'edit_kapasitas', 'ukuran', 'editKapasitas.ukuran', 'editKapasitas.id');
$content = injectHiddenAndError($content, '<form :action="\'/master-data/\' + editApar.id" method="POST">', 'edit_apar', 'kode', 'editApar.kode', 'editApar.id');

// Replace all inputs to show error message
$searchReplaceErrors = [
    // Tambah Gedung
    [
        'search' => '<input type="text" name="nama" required class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 px-4 text-sm font-medium text-slate-700 focus:bg-white focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none">',
        'replace' => '<input type="text" name="nama" value="{{ old(\'form_type\') == \'tambah_gedung\' ? old(\'nama\') : \'\' }}" required class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 px-4 text-sm font-medium text-slate-700 focus:bg-white focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none">
                                    @if(old(\'form_type\') == \'tambah_gedung\') @error(\'nama\') <p class="text-xs text-red-500 font-medium mt-2"><i class="ph-bold ph-warning-circle mr-1"></i>{{ $message }}</p> @enderror @endif',
    ],
    // Tambah Lokasi
    [
        'search' => '<input type="text" name="nama" required class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 px-4 text-sm font-medium text-slate-700 focus:bg-white focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none">',
        'replace' => '<input type="text" name="nama" value="{{ old(\'form_type\') == \'tambah_lokasi\' ? old(\'nama\') : \'\' }}" required class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 px-4 text-sm font-medium text-slate-700 focus:bg-white focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none">
                                    @if(old(\'form_type\') == \'tambah_lokasi\') @error(\'nama\') <p class="text-xs text-red-500 font-medium mt-2"><i class="ph-bold ph-warning-circle mr-1"></i>{{ $message }}</p> @enderror @endif',
    ],
    // Tambah Jenis
    [
        'search' => '<input type="text" name="nama" required class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 px-4 text-sm font-medium text-slate-700 focus:bg-white focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none">',
        'replace' => '<input type="text" name="nama" value="{{ old(\'form_type\') == \'tambah_jenis\' ? old(\'nama\') : \'\' }}" required class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 px-4 text-sm font-medium text-slate-700 focus:bg-white focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none">
                                    @if(old(\'form_type\') == \'tambah_jenis\') @error(\'nama\') <p class="text-xs text-red-500 font-medium mt-2"><i class="ph-bold ph-warning-circle mr-1"></i>{{ $message }}</p> @enderror @endif',
    ],
    // Tambah Kapasitas
    [
        'search' => '<input type="text" name="ukuran" required class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 px-4 text-sm font-medium text-slate-700 focus:bg-white focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none">',
        'replace' => '<input type="text" name="ukuran" value="{{ old(\'form_type\') == \'tambah_kapasitas\' ? old(\'ukuran\') : \'\' }}" required class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 px-4 text-sm font-medium text-slate-700 focus:bg-white focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none">
                                    @if(old(\'form_type\') == \'tambah_kapasitas\') @error(\'ukuran\') <p class="text-xs text-red-500 font-medium mt-2"><i class="ph-bold ph-warning-circle mr-1"></i>{{ $message }}</p> @enderror @endif',
    ],

    // Edit Gedung
    [
        'search' => '<input type="text" name="nama" x-model="editGedung.nama" required class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 px-4 text-sm font-medium text-slate-700 focus:bg-white focus:border-amber-500 focus:ring-4 focus:ring-amber-500/15 transition-all outline-none">',
        'replace' => '<input type="text" name="nama" x-model="editGedung.nama" required class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 px-4 text-sm font-medium text-slate-700 focus:bg-white focus:border-amber-500 focus:ring-4 focus:ring-amber-500/15 transition-all outline-none">
                                    @if(old(\'form_type\') == \'edit_gedung\') @error(\'nama\') <p class="text-xs text-red-500 font-medium mt-2"><i class="ph-bold ph-warning-circle mr-1"></i>{{ $message }}</p> @enderror @endif',
    ],
    // Edit Lokasi
    [
        'search' => '<input type="text" name="nama" x-model="editLokasi.nama" required class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 px-4 text-sm font-medium text-slate-700 focus:bg-white focus:border-amber-500 focus:ring-4 focus:ring-amber-500/15 transition-all outline-none">',
        'replace' => '<input type="text" name="nama" x-model="editLokasi.nama" required class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 px-4 text-sm font-medium text-slate-700 focus:bg-white focus:border-amber-500 focus:ring-4 focus:ring-amber-500/15 transition-all outline-none">
                                    @if(old(\'form_type\') == \'edit_lokasi\') @error(\'nama\') <p class="text-xs text-red-500 font-medium mt-2"><i class="ph-bold ph-warning-circle mr-1"></i>{{ $message }}</p> @enderror @endif',
    ],
    // Edit Jenis
    [
        'search' => '<input type="text" name="nama" x-model="editJenis.nama" required class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 px-4 text-sm font-medium text-slate-700 focus:bg-white focus:border-amber-500 focus:ring-4 focus:ring-amber-500/15 transition-all outline-none">',
        'replace' => '<input type="text" name="nama" x-model="editJenis.nama" required class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 px-4 text-sm font-medium text-slate-700 focus:bg-white focus:border-amber-500 focus:ring-4 focus:ring-amber-500/15 transition-all outline-none">
                                    @if(old(\'form_type\') == \'edit_jenis\') @error(\'nama\') <p class="text-xs text-red-500 font-medium mt-2"><i class="ph-bold ph-warning-circle mr-1"></i>{{ $message }}</p> @enderror @endif',
    ],
    // Edit Kapasitas
    [
        'search' => '<input type="text" name="ukuran" x-model="editKapasitas.ukuran" required class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 px-4 text-sm font-medium text-slate-700 focus:bg-white focus:border-amber-500 focus:ring-4 focus:ring-amber-500/15 transition-all outline-none">',
        'replace' => '<input type="text" name="ukuran" x-model="editKapasitas.ukuran" required class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 px-4 text-sm font-medium text-slate-700 focus:bg-white focus:border-amber-500 focus:ring-4 focus:ring-amber-500/15 transition-all outline-none">
                                    @if(old(\'form_type\') == \'edit_kapasitas\') @error(\'ukuran\') <p class="text-xs text-red-500 font-medium mt-2"><i class="ph-bold ph-warning-circle mr-1"></i>{{ $message }}</p> @enderror @endif',
    ],
];

foreach ($searchReplaceErrors as $sr) {
    // We only replace the first occurrence for "Tambah", but since they are all identical...
    // Wait, the "Tambah" inputs don't have x-model, the "Edit" inputs do! So they are strictly unique!
    $content = str_replace($sr['search'], $sr['replace'], $content);
}

file_put_contents($file, $content);
echo 'Inline errors and form_type patched.';
