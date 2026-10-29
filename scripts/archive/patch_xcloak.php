<?php

$file = __DIR__.'/resources/views/master-data/index.blade.php';
$content = file_get_contents($file);

// Add x-cloak to any element with x-show that doesn't already have it
$content = preg_replace('/(<[^>]*?x-show="[^"]+"[^>]*?)(?<!x-cloak)>/', '$1 x-cloak>', $content);

file_put_contents($file, $content);
echo "Added x-cloak.\n";
