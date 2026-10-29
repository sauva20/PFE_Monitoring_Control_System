<?php

$file = __DIR__.'/resources/views/master-data/index.blade.php';
$content = file_get_contents($file);

$search1 = '<div x-show="showModalApar" @click.away="showModalApar = false"';
$replace1 = '<div x-show="showModalApar" @click.away="if (!$event.target.closest(\'.flatpickr-calendar\')) showModalApar = false"';

$search2 = '<div x-show="showModalEditApar" @click.away="showModalEditApar = false"';
$replace2 = '<div x-show="showModalEditApar" @click.away="if (!$event.target.closest(\'.flatpickr-calendar\')) showModalEditApar = false"';

$content = str_replace($search1, $replace1, $content);
$content = str_replace($search2, $replace2, $content);

file_put_contents($file, $content);
echo 'Patched.';
