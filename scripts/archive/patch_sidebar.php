<?php

$file = __DIR__.'/resources/views/layouts/app.blade.php';
$content = file_get_contents($file);

// Add overflow-x-hidden to sidebar container just in case
$content = str_replace(
    'class="bg-white flex-col hidden md:flex border-r border-slate-200/60 z-20 transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] relative flex-shrink-0 shadow-[4px_0_24px_rgba(0,0,0,0.01)] will-change-[width]"',
    'class="bg-white flex-col hidden md:flex border-r border-slate-200/60 z-20 transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] relative flex-shrink-0 shadow-[4px_0_24px_rgba(0,0,0,0.01)] will-change-[width] overflow-x-hidden"',
    $content
);

// Fix Profile Area
$profileOld = <<<'HTML'
        <div class="p-4 border-b border-slate-50 flex items-center gap-3 transition-all duration-300 overflow-hidden" :class="sidebarOpen ? 'justify-start' : 'justify-center'">
            <div class="w-10 h-10 rounded-full bg-slate-100 border border-slate-200 flex-shrink-0 flex items-center justify-center">
                <span class="text-sm font-bold text-[#009B77]">SS</span>
            </div>
            <div class="flex-1 whitespace-nowrap opacity-100 transition-opacity duration-300" :class="sidebarOpen ? 'opacity-100 block' : 'opacity-0 hidden'">
HTML;
$profileNew = <<<'HTML'
        <div class="p-4 border-b border-slate-50 flex items-center transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] overflow-hidden" :class="sidebarOpen ? 'justify-start' : 'justify-center'">
            <div class="w-10 h-10 rounded-full bg-slate-100 border border-slate-200 flex-shrink-0 flex items-center justify-center">
                <span class="text-sm font-bold text-[#009B77]">SS</span>
            </div>
            <div class="whitespace-nowrap transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] overflow-hidden" :class="sidebarOpen ? 'opacity-100 w-32 ml-3' : 'opacity-0 w-0 ml-0'">
HTML;
$content = str_replace($profileOld, $profileNew, $content);

// Fix Nav Items
// Nav items currently have `flex items-center gap-3 p-3 ...` and `span` with `:class="sidebarOpen ? 'opacity-100 block' : 'opacity-0 hidden'"`

// I will use regex to replace the a tags correctly.
// 1. Remove gap-3 and add overflow-hidden to all nav items' classes
$content = preg_replace('/class="flex items-center gap-3 p-3([^"]+)"/', 'class="flex items-center p-3 overflow-hidden$1"', $content);

// 2. Replace span classes
$content = preg_replace('/<span class="text-sm whitespace-nowrap transition-opacity duration-300" :class="sidebarOpen \? \'opacity-100 block\' : \'opacity-0 hidden\'">/', '<span class="text-sm whitespace-nowrap transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] overflow-hidden" :class="sidebarOpen ? \'opacity-100 w-32 ml-3\' : \'opacity-0 w-0 ml-0\'">', $content);

file_put_contents($file, $content);
echo "Sidebar patched.\n";
