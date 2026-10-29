<?php

$file = __DIR__.'/resources/views/master-data/index.blade.php';
$content = file_get_contents($file);

function replaceInBlock($content, $startMarker, $endMarker, $field)
{
    $editModalStart = strpos($content, '<!-- Modal Edit APAR -->');

    $startPos = strpos($content, $startMarker, $editModalStart);
    if ($startPos === false) {
        return $content;
    }
    $endPos = strpos($content, $endMarker, $startPos);
    if ($endPos === false) {
        return $content;
    }

    $block = substr($content, $startPos, $endPos - $startPos);

    // Remove selectedId: editApar.*,
    $block = preg_replace('/selectedId:\s*editApar\.[a-z_]+,/', '', $block);

    // Replace this.selectedId with editApar.$field
    $block = str_replace('this.selectedId', "editApar.$field", $block);

    // Replace selectedId with editApar.$field
    $block = str_replace('selectedId', "editApar.$field", $block);

    return substr_replace($content, $block, $startPos, $endPos - $startPos);
}

// 1. Revert the global str_replace I did before (if it wasn't run yet, it's fine, but wait, the script hasn't been run yet! Good.)
// Wait, the previous script had:
// $content = str_replace($searchLokasi, $replaceLokasi, $content);
// That was perfectly safe because $searchLokasi was exact.
// But the global str_replace:
// $content = str_replace('<input type="hidden" name="lokasi_id" :value="selectedId" required>', '<input type="hidden" name="lokasi_id" :value="editApar.lokasi_id" required>', $content);
// That also might have hit Tambah APAR!
// Good thing I didn't run it!

$content = replaceInBlock($content, '<!-- Lokasi Penempatan -->', '<!-- Jenis APAR -->', 'lokasi_id');
$content = replaceInBlock($content, '<!-- Jenis APAR -->', '<!-- Kapasitas -->', 'jenis_id');
$content = replaceInBlock($content, '<!-- Kapasitas -->', '<!-- Vendor Refill -->', 'kapasitas_id');

file_put_contents($file, $content);
echo 'Patched.';
