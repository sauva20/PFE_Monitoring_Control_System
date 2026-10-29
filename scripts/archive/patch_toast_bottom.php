<?php

// 1. Move toast to bottom right in app.blade.php
$fileApp = __DIR__.'/resources/views/layouts/app.blade.php';
$contentApp = file_get_contents($fileApp);

$contentApp = str_replace('fixed top-6 right-6', 'fixed bottom-6 right-6', $contentApp);
// Also adjust animations from translate-x-8 to translate-y-8 so it slides up from bottom
$contentApp = str_replace('translate-x-8', 'translate-y-8', $contentApp);

file_put_contents($fileApp, $contentApp);

// 2. Move toast to bottom right in index.blade.php (login)
$fileIndex = __DIR__.'/resources/views/index.blade.php';
$contentIndex = file_get_contents($fileIndex);

$contentIndex = str_replace('fixed top-6 right-6', 'fixed bottom-6 right-6', $contentIndex);
$contentIndex = str_replace('translate-x-8', 'translate-y-8', $contentIndex);

file_put_contents($fileIndex, $contentIndex);

// 3. Add success flash message to AuthController
$fileAuth = __DIR__.'/app/Http/Controllers/AuthController.php';
$contentAuth = file_get_contents($fileAuth);

$contentAuth = str_replace(
    "return redirect()->intended('/dashboard');",
    "return redirect()->intended('/dashboard')->with('success', 'Login berhasil! Selamat datang.');",
    $contentAuth
);

file_put_contents($fileAuth, $contentAuth);

echo "Patched toast positions and AuthController.\n";
