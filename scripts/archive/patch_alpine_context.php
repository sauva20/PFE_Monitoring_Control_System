<?php

$file = __DIR__.'/resources/views/master-data/index.blade.php';
$content = file_get_contents($file);

$content = str_replace(
    'let sel = this.options.find(o => o.id == editApar.lokasi_id);',
    'let sel = this.options.find(o => o.id == this.editApar.lokasi_id);',
    $content
);

$content = str_replace(
    'let sel = this.options.find(o => o.id == editApar.jenis_id);',
    'let sel = this.options.find(o => o.id == this.editApar.jenis_id);',
    $content
);

$content = str_replace(
    'let sel = this.options.find(o => o.id == editApar.kapasitas_id);',
    'let sel = this.options.find(o => o.id == this.editApar.kapasitas_id);',
    $content
);

file_put_contents($file, $content);
echo 'Patched.';
