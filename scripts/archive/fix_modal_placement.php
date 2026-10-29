<?php

$file = __DIR__.'/resources/views/master-data/index.blade.php';
$content = file_get_contents($file);

$pos = strpos($content, '    <!-- Modal Edit Gedung -->');
if ($pos !== false) {
    $endPos = strpos($content, '@endsection', $pos);
    $modals = substr($content, $pos, $endPos - $pos);

    $content = str_replace($modals, '', $content);

    // The main wrapper </div> is likely just before the now-empty space before @endsection.
    // Let's find the last </div> before @endsection.
    $beforeEndsection = substr($content, 0, strpos($content, '@endsection'));
    $lastDivPos = strrpos($beforeEndsection, '</div>');

    if ($lastDivPos !== false) {
        $content = substr_replace($content, "\n".$modals."\n", $lastDivPos, 0);
        file_put_contents($file, $content);
        echo 'Fixed modal placement.';
    } else {
        echo 'Could not find closing div.';
    }
} else {
    echo 'Could not find modals.';
}
