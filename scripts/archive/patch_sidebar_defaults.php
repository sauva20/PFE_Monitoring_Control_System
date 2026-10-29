<?php

$file = __DIR__.'/resources/views/layouts/app.blade.php';
$content = file_get_contents($file);

// 1. Aside
$content = str_replace(
    'class="bg-white flex-col hidden md:flex border-r border-slate-200/60 z-20 transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] relative flex-shrink-0 shadow-[4px_0_24px_rgba(0,0,0,0.01)] will-change-[width] overflow-x-hidden"',
    'class="w-[80px] bg-white flex-col hidden md:flex border-r border-slate-200/60 z-20 transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] relative flex-shrink-0 shadow-[4px_0_24px_rgba(0,0,0,0.01)] will-change-[width] overflow-x-hidden"',
    $content
);

// 2. Logo Container
$content = str_replace(
    'class="h-[72px] flex items-center border-b border-slate-100 transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] w-full overflow-hidden"',
    'class="px-0 justify-center h-[72px] flex items-center border-b border-slate-100 transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] w-full overflow-hidden"',
    $content
);

// 3. Logo Wrapper
$content = str_replace(
    'class="relative h-6 transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] overflow-hidden flex-shrink-0"',
    'class="w-[22px] relative h-6 transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] overflow-hidden flex-shrink-0"',
    $content
);

// 4. Profile Container
$content = str_replace(
    'class="p-4 border-b border-slate-50 flex items-center transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] overflow-hidden"',
    'class="justify-center p-4 border-b border-slate-50 flex items-center transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] overflow-hidden"',
    $content
);

// 5. Profile Text
$content = str_replace(
    'class="whitespace-nowrap transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] overflow-hidden"',
    'class="opacity-0 w-0 ml-0 whitespace-nowrap transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] overflow-hidden"',
    $content
);

// 6. Nav <a>
$content = preg_replace(
    '/class="flex items-center p-3 overflow-hidden rounded-xl font-semibold transition-all/',
    'class="justify-center flex items-center p-3 overflow-hidden rounded-xl font-semibold transition-all',
    $content
);
$content = preg_replace(
    '/class="flex items-center p-3 overflow-hidden rounded-xl text-slate-500 hover:bg-slate-50 hover:text-slate-800 font-medium transition-colors"/',
    'class="justify-center flex items-center p-3 overflow-hidden rounded-xl text-slate-500 hover:bg-slate-50 hover:text-slate-800 font-medium transition-colors"',
    $content
);

// 7. Nav <span>
$content = str_replace(
    'class="text-sm whitespace-nowrap transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] overflow-hidden"',
    'class="opacity-0 w-0 ml-0 text-sm whitespace-nowrap transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] overflow-hidden"',
    $content
);

file_put_contents($file, $content);
echo "Sidebar defaults patched.\n";
