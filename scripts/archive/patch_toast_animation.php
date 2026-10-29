<?php

function fixToastAnimation($file)
{
    if (! file_exists($file)) {
        return;
    }
    $content = file_get_contents($file);

    // Replace the toast initial state to trigger the enter animation
    $oldInit = <<<'HTML'
        <div x-data="{ show: true, type: '{{ session('success') ? 'success' : 'error' }}', message: '{{ addslashes(session('success') ?? session('error')) }}' }" 
             x-init="setTimeout(() => show = false, 4000)"
             x-show="show"
HTML;

    $newInit = <<<'HTML'
        <div x-data="{ show: false, type: '{{ session('success') ? 'success' : 'error' }}', message: '{{ addslashes(session('success') ?? session('error')) }}' }" 
             x-init="setTimeout(() => show = true, 100); setTimeout(() => show = false, 4000)"
             x-show="show"
             style="display: none;"
HTML;

    if (strpos($content, "show: true, type: '{{ session('success')") !== false) {
        $content = str_replace($oldInit, $newInit, $content);
        file_put_contents($file, $content);
        echo 'Patched toast animation in: '.basename($file)."\n";
    }
}

fixToastAnimation(__DIR__.'/resources/views/layouts/app.blade.php');
fixToastAnimation(__DIR__.'/resources/views/index.blade.php');
