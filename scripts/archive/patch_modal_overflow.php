<?php

$file = __DIR__.'/resources/views/master-data/index.blade.php';
$content = file_get_contents($file);

$search = 'relative transform overflow-hidden rounded-3xl bg-white';
$replace = 'relative transform overflow-visible rounded-3xl bg-white';

$newContent = str_replace($search, $replace, $content);

if ($newContent !== $content) {
    file_put_contents($file, $newContent);
    echo 'Replaced overflow-hidden with overflow-visible in modals.';
} else {
    echo 'No changes made. Check the search string.';
}
