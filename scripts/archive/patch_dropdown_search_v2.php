<?php

function patchDropdown($file)
{
    if (! file_exists($file)) {
        return;
    }
    $content = file_get_contents($file);

    // 1. Patch x-data
    $content = preg_replace_callback(
        '/x-data="\{\s*open: false,\s*selectedId: (.*?),\s*options: \[(.*?)\],\s*get selectedName\(\) \{(.*?)\}\s*\}"/s',
        function ($matches) {
            $selectedId = $matches[1];
            $options = $matches[2];
            $selectedNameBody = $matches[3];

            $newXData = "x-data=\"{\n                            open: false,\n                            search: '',\n                            selectedId: $selectedId,\n                            options: [$options],\n                            get selectedName() {{$selectedNameBody}},\n                            get filteredOptions() {\n                                if (this.search === '') return this.options;\n                                return this.options.filter(o => o.name.toLowerCase().includes(this.search.toLowerCase()));\n                            }\n                        }\"";

            return $newXData;
        },
        $content
    );

    // 2. Patch template loop
    $searchInputHTML = <<<'HTML'
<div class="sticky top-0 bg-white p-2 border-b border-slate-100 z-10">
                                <div class="relative">
                                    <i class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                    <input type="text" x-model="search" placeholder="Cari..." class="w-full bg-slate-50 border border-slate-200 rounded-lg py-2 pl-9 pr-3 text-sm font-medium text-slate-700 focus:outline-none focus:border-[#009B77] focus:ring-2 focus:ring-[#009B77]/20 transition-all" @click.stop @keydown.enter.prevent>
                                </div>
                            </div>
                            <div class="py-1">
                                <template x-for="option in filteredOptions"
HTML;

    // First, restore template to "option in options" in case it was half-patched, but we know edit.blade.php is unpatched.
    // Replace <template x-for="option in options"
    $content = preg_replace('/<template x-for="option in options"/s', $searchInputHTML, $content);

    // 3. Add no results footer
    // Since we know the structure is: <i class="ph-bold ph-check ...></i></button></template></div>
    $content = preg_replace_callback(
        '/<\/button>\s*<\/template>\s*<\/div>/',
        function ($matches) {
            return "</button>\n                                </template>\n                                <div x-show=\"filteredOptions.length === 0\" class=\"py-3 px-4 text-center text-sm font-medium text-slate-500\">\n                                    Pencarian tidak ditemukan\n                                </div>\n                            </div>\n                        </div>";
        },
        $content
    );

    file_put_contents($file, $content);
    echo 'Patched '.basename($file)."\n";
}

patchDropdown(__DIR__.'/resources/views/master-data/edit.blade.php');
patchDropdown(__DIR__.'/resources/views/master-data/tambah.blade.php');
