<?php

function replaceClasses($file)
{
    if (! file_exists($file)) {
        return;
    }
    $content = file_get_contents($file);

    // Target specific class combinations used in inputs and dropdowns
    $replacements = [
        // Dropdown triggers and inputs
        'text-sm font-bold text-slate-800' => 'text-sm font-medium text-slate-700',
        'text-sm font-semibold text-slate-800' => 'text-sm font-medium text-slate-700',

        // Dropdown options
        'text-sm font-bold transition-colors' => 'text-sm font-medium transition-colors',
        'text-sm font-semibold transition-colors' => 'text-sm font-medium transition-colors',

        // Dropdown triggers specific to index.blade.php
        'text-sm font-semibold text-left' => 'text-sm font-medium text-left',
        'text-sm font-bold text-left' => 'text-sm font-medium text-left',

        // Placeholders (often font-medium or font-semibold)
        'placeholder:font-medium' => 'placeholder:font-normal',
        'placeholder:font-semibold' => 'placeholder:font-normal',
        'placeholder:font-bold' => 'placeholder:font-normal',
    ];

    foreach ($replacements as $old => $new) {
        $content = str_replace($old, $new, $content);
    }

    // Also, some inputs in index.blade.php might have 'font-semibold text-slate-800' without 'text-sm' adjacent.
    // Let's use preg_replace for inputs specifically to be safe and thorough.
    $content = preg_replace_callback('/<input[^>]+class="([^"]+)"[^>]*>/i', function ($matches) {
        $classes = $matches[1];
        $classes = str_replace(['font-bold', 'font-semibold'], 'font-medium', $classes);
        $classes = str_replace('text-slate-800', 'text-slate-700', $classes);

        return str_replace($matches[1], $classes, $matches[0]);
    }, $content);

    file_put_contents($file, $content);
}

$files = [
    __DIR__.'/resources/views/master-data/index.blade.php',
    __DIR__.'/resources/views/master-data/tambah.blade.php',
    __DIR__.'/resources/views/master-data/edit.blade.php',
];

foreach ($files as $file) {
    replaceClasses($file);
    echo 'Patched: '.basename($file)."\n";
}
