<?php

$file = __DIR__.'/resources/views/layouts/app.blade.php';
$content = file_get_contents($file);

$toastHTML = <<<'HTML'
    <!-- Global Toast Notification -->
    @if(session('success') || session('error'))
        <div x-data="{ show: true, type: '{{ session('success') ? 'success' : 'error' }}', message: '{{ addslashes(session('success') ?? session('error')) }}' }" 
             x-init="setTimeout(() => show = false, 4000)"
             x-show="show"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 translate-x-8 scale-95"
             x-transition:enter-end="opacity-100 translate-x-0 scale-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 translate-x-0 scale-100"
             x-transition:leave-end="opacity-0 translate-x-8 scale-95"
             class="fixed top-6 right-6 z-[9999] flex items-center gap-3 px-5 py-4 rounded-2xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.2)] border min-w-[300px] max-w-sm backdrop-blur-md"
             :class="type === 'success' ? 'bg-white/95 border-teal-100' : 'bg-white/95 border-red-100'">
             
            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                 :class="type === 'success' ? 'bg-teal-50 text-[#009B77]' : 'bg-red-50 text-red-500'">
                <i class="ph-fill text-xl" :class="type === 'success' ? 'ph-check-circle' : 'ph-warning-circle'"></i>
            </div>
            
            <div class="flex flex-col">
                <span class="text-sm font-bold text-slate-800" x-text="type === 'success' ? 'Berhasil!' : 'Oops, Terjadi Kesalahan!'"></span>
                <span class="text-xs font-medium text-slate-500 mt-0.5" x-text="message"></span>
            </div>
            
            <button @click="show = false" class="ml-auto w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                <i class="ph-bold ph-x text-lg"></i>
            </button>
        </div>
    @endif
HTML;

if (strpos($content, 'Global Toast Notification') === false) {
    // Insert just after <body ...>
    $content = preg_replace('/(<body[^>]*>)/i', "$1\n".$toastHTML, $content, 1);
    file_put_contents($file, $content);
    echo "app.blade.php patched.\n";
} else {
    echo "Already patched app.blade.php.\n";
}

$file2 = __DIR__.'/resources/views/index.blade.php';
$content2 = file_get_contents($file2);

if (strpos($content2, 'Global Toast Notification') === false) {
    // Insert just after <body ...>
    $content2 = preg_replace('/(<body[^>]*>)/i', "$1\n".$toastHTML, $content2, 1);

    // Also inject AlpineJS if it's not present via app.js
    // Since we don't know if app.js has Alpine, let's inject it via CDN conditionally if Alpine isn't defined?
    // Actually, just injecting the CDN script in the head is safer if it's the login page.
    // Let's check if Alpine is in index.blade.php
    if (strpos($content2, 'alpine') === false) {
        $alpineScript = '<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>';
        $content2 = preg_replace('/(<\/head>)/i', '    '.$alpineScript."\n$1", $content2, 1);
    }

    file_put_contents($file2, $content2);
    echo "index.blade.php patched.\n";
} else {
    echo "Already patched index.blade.php.\n";
}
