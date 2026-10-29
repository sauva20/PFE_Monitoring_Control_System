import os
import re

path = 'resources/views/master-data/index.blade.php'
with open(path, 'r', encoding='utf-8') as f:
    content = f.read()

# Replace x-show="open && filteredLokasi.length > 0" for Tambah
content = re.sub(
    r'<div x-show="open && filteredLokasi\.length > 0" x-cloak\s*x-transition:enter="transition ease-out duration-200"\s*x-transition:enter-start="opacity-0 scale-95"',
    r'<div x-show="open && (!gedungId || filtered.length > 0)" x-cloak\n                           x-transition:enter="transition ease-out duration-200"\n                           x-transition:enter-start="opacity-0 scale-95"',
    content,
    count=1
)

# Replace x-show="open && filteredLokasi.length > 0" for Edit
content = re.sub(
    r'<div x-show="open && filteredLokasi\.length > 0" x-cloak\s*x-transition:enter="transition ease-out duration-200"\s*x-transition:enter-start="opacity-0 scale-95"',
    r'<div x-show="open && (!editApar.gedung_id || filtered.length > 0)" x-cloak\n                           x-transition:enter="transition ease-out duration-200"\n                           x-transition:enter-start="opacity-0 scale-95"',
    content,
    count=1
)

# Insert !gedungId warning before first template
content = re.sub(
    r'(<div class="py-1">)\s*<template x-for="lok in filtered" :key="lok\.id">',
    r'\1\n                              <div x-show="!gedungId" class="py-3 px-4 text-center text-sm font-medium text-amber-500">\n                                  <i class="ph-bold ph-warning-circle mr-1"></i>{{ __(\'Pilih Gedung terlebih dahulu\') }}\n                              </div>\n                              <template x-for="lok in filtered" :key="lok.id">',
    content,
    count=1
)

# Insert !editApar.gedung_id warning before second template
content = re.sub(
    r'(<div class="py-1">)\s*<template x-for="lok in filtered" :key="lok\.id">',
    r'\1\n                              <div x-show="!editApar.gedung_id" class="py-3 px-4 text-center text-sm font-medium text-amber-500">\n                                  <i class="ph-bold ph-warning-circle mr-1"></i>{{ __(\'Pilih Gedung terlebih dahulu\') }}\n                              </div>\n                              <template x-for="lok in filtered" :key="lok.id">',
    content,
    count=1
)

# Remove the Type to add new location text
content = re.sub(
    r'<div x-show="filtered\.length === 0" class="py-3 px-4 text-center text-sm font-medium text-slate-500">\s*\{\{ __\(\'Type to add new location\'\)\ \}\}\s*</div>',
    r'',
    content
)

with open(path, 'w', encoding='utf-8') as f:
    f.write(content)
print("Regex replacements applied!")
