<?php

$file = __DIR__.'/resources/views/master-data/index.blade.php';
$content = file_get_contents($file);

$search = "{ id: '{{ \$lokasi->id }}', name: '{{ addslashes(\$lokasi->nama) }} - {{ addslashes(\$lokasi->gedung->nama) }}' }";
$replace = "{ id: '{{ \$lokasi->id }}', name: '{{ addslashes(\$lokasi->nama) }} • {{ addslashes(\$lokasi->gedung->nama) }}' }";

$newContent = str_replace($search, $replace, $content);

if ($newContent !== $content) {
    file_put_contents($file, $newContent);
    echo 'Replaced hyphen with bullet.';
} else {
    echo 'No changes made.';
}
