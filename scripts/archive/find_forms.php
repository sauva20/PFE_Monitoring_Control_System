<?php

$lines = file(__DIR__.'/resources/views/master-data/index.blade.php');
foreach ($lines as $i => $line) {
    if (strpos($line, '<form action="/master-data/') !== false) {
        echo ($i + 1).': '.trim($line)."\n";
    }
}
