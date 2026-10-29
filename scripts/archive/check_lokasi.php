<?php

$content = file_get_contents(__DIR__.'/resources/views/master-data/index.blade.php');
$lines = explode("\n", $content);
foreach ($lines as $i => $line) {
    if (strpos($line, 'option.lokasi') !== false) {
        echo ($i + 1).': '.trim($line)."\n";
    }
}
