<?php

$url = 'https://raw.githubusercontent.com/phosphor-icons/core/main/raw/duotone/fire-extinguisher-duotone.svg';
$svg = file_get_contents($url);
if ($svg) {
    file_put_contents('duotone.svg', $svg);
    echo 'Saved to duotone.svg';
} else {
    echo 'FAILED';
}
