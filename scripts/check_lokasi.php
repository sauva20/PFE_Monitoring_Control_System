<?php
// Check lokasi data for BU-T (T.*.NC.*)
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$locs = \App\Models\Lokasi::where('nomor_ruangan', 'like', 'T.%')->get();
foreach ($locs as $l) {
    echo $l->id . ' | ' . $l->nama . ' | ' . $l->nomor_ruangan . ' | lantai:' . $l->lantai . PHP_EOL;
}
