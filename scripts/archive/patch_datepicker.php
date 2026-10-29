<?php

// 1. Add Flatpickr init to app.blade.php
$fileApp = __DIR__.'/resources/views/layouts/app.blade.php';
$contentApp = file_get_contents($fileApp);

$flatpickrInit = <<<'HTML'
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            flatpickr(".datepicker", {
                dateFormat: "Y-m-d",
                locale: "id",
                allowInput: true
            });
        });
    </script>
HTML;

if (strpos($contentApp, 'flatpickr(".datepicker"') === false) {
    $contentApp = str_replace('</body>', $flatpickrInit."\n</body>", $contentApp);
    file_put_contents($fileApp, $contentApp);
    echo "app.blade.php patched with Flatpickr init.\n";
}

// 2. Change type="date" to type="text" and add .datepicker class in tambah.blade.php and edit.blade.php
function patchDateInput($file)
{
    if (! file_exists($file)) {
        return;
    }
    $content = file_get_contents($file);

    // Find the date input
    if (preg_match('/<input[^>]+name="tgl_kedaluwarsa"[^>]*>/i', $content, $matches)) {
        $input = $matches[0];

        // Change type="date" to type="text"
        $newInput = str_replace('type="date"', 'type="text" placeholder="Pilih Tanggal..."', $input);

        // Add datepicker class to class attribute
        if (preg_match('/class="([^"]+)"/i', $newInput, $classMatches)) {
            $oldClass = $classMatches[1];
            if (strpos($oldClass, 'datepicker') === false) {
                // Also remove appearance-none just in case, and add datepicker
                $newClass = trim(str_replace('appearance-none', '', $oldClass)).' datepicker';
                $newInput = str_replace('class="'.$oldClass.'"', 'class="'.$newClass.'"', $newInput);
            }
        }

        $content = str_replace($input, $newInput, $content);
        file_put_contents($file, $content);
        echo basename($file)." patched.\n";
    }
}

patchDateInput(__DIR__.'/resources/views/master-data/tambah.blade.php');
patchDateInput(__DIR__.'/resources/views/master-data/edit.blade.php');
