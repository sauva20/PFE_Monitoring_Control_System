<?php

$file = __DIR__.'/resources/views/master-data/index.blade.php';
$content = file_get_contents($file);

$headerSearch = '<th class="py-4 px-5 text-xs font-bold uppercase tracking-wider">Kapasitas</th>';
$headerReplace = <<<'HTML'
                        <th class="py-4 px-5 text-xs font-bold uppercase tracking-wider">Kapasitas</th>
                        <th class="py-4 px-5 text-xs font-bold uppercase tracking-wider">Kelas Kebakaran</th>
HTML;

$bodySearch = '<td class="py-4 px-5 font-semibold text-slate-600">{{ $apar->kapasitas->ukuran ?? \'-\' }}</td>';
$bodyReplace = <<<HTML
                        <td class="py-4 px-5 font-semibold text-slate-600">{{ $apar->kapasitas->ukuran ?? '-' }}</td>
                        <td class="py-4 px-5">
                            @php
                                \$jenisNamaL = strtolower(\$apar->jenis->nama ?? '');
                                \$kelas = '-';
                                if (strpos(\$jenisNamaL, 'dry chemical') !== false || strpos(\$jenisNamaL, 'powder') !== false) {
                                    \$kelas = 'A-B-C';
                                } elseif (strpos(\$jenisNamaL, 'carbon') !== false || strpos(\$jenisNamaL, 'dioxide') !== false || strpos(\$jenisNamaL, 'co2') !== false) {
                                    \$kelas = 'B-C';
                                }
                            @endphp
                            @if(\$kelas != '-')
                                <span class="text-[10px] font-bold tracking-wider py-1 px-2.5 rounded-lg bg-orange-50 text-orange-600 border border-orange-100">{{ \$kelas }}</span>
                            @else
                                <span class="font-semibold text-slate-400">-</span>
                            @endif
                        </td>
HTML;

$content = str_replace($headerSearch, $headerReplace, $content);
$content = str_replace($bodySearch, $bodyReplace, $content);

file_put_contents($file, $content);
echo 'Patched.';
