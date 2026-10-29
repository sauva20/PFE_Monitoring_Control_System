<?php

$file = __DIR__.'/resources/views/vendor/pagination/tailwind.blade.php';
$content = file_get_contents($file);

// Remove all dark mode classes
$content = preg_replace('/dark:[a-zA-Z0-9\-:]+/', '', $content);

// Change border colors from gray-300 to slate-200
$content = str_replace('border-gray-300', 'border-slate-200', $content);

// Change active page styles to emerald/green
$content = str_replace(
    'text-gray-700 bg-gray-200',
    'text-[#009B77] bg-emerald-50 border-emerald-500 font-bold',
    $content
);

// Fix focus rings
$content = preg_replace('/focus:ring ring-gray-300 focus:border-blue-300/', 'focus:ring-2 focus:ring-[#009B77] focus:border-[#009B77]', $content);

// Change default text colors
$content = str_replace('text-gray-700', 'text-slate-600', $content);
$content = str_replace('text-gray-500', 'text-slate-400', $content);

file_put_contents($file, $content);
echo 'Patched.';
