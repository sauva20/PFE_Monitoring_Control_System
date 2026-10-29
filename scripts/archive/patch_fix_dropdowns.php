<?php

$file = __DIR__.'/resources/views/master-data/index.blade.php';
$lines = explode("\n", file_get_contents($file));

$targetLines = [964, 1036, 1264, 1336, 1520]; // Adjusting for the exact start of the div

foreach ($targetLines as $l) {
    // Check if the div actually exists at these approximate lines
    // The previous findstr found option.lokasi at 966, 1038, 1266, 1338, 1522.
    // The div starts 1 line before.
    $startIdx = -1;
    for ($i = $l - 5; $i <= $l + 5; $i++) {
        if (strpos($lines[$i] ?? '', '<div class="flex items-center justify-between w-full pr-4">') !== false) {
            $startIdx = $i;
            break;
        }
    }

    if ($startIdx !== -1) {
        $lines[$startIdx] = '                                    <span x-text="option.name" class="font-bold"></span>';
        $lines[$startIdx + 1] = ''; // option.lokasi
        $lines[$startIdx + 2] = ''; // option.gedung
        $lines[$startIdx + 3] = ''; // </div>
    }
}

file_put_contents($file, implode("\n", $lines));
echo 'Patched.';
