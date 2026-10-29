import os

path = 'resources/views/master-data/index.blade.php'
with open(path, 'r', encoding='utf-8') as f:
    content = f.read()

# Fix for Tambah APAR
target1 = '''<div x-show="open && filteredLokasi.length > 0" x-cloak
                           x-transition:enter="transition ease-out duration-200"
                           x-transition:enter-start="opacity-0 scale-95"
                           x-transition:enter-end="opacity-100 scale-100"
                           x-transition:leave="transition ease-in duration-100"
                           x-transition:leave-start="opacity-100 scale-100"
                           x-transition:leave-end="opacity-0 scale-95"
                           class="absolute left-0 z-50 w-full mt-2 bg-white rounded-xl shadow-[0_10px_40px_rgba(0,0,0,0.1)] border border-slate-100 py-2 max-h-60 overflow-y-auto origin-top">
                          <div class="py-1">
                              <template x-for="lok in filtered" :key="lok.id">
                              <button type="button" @click="lokasiName = lok.nama; open = false" class="w-full text-left px-4 py-2.5 text-sm font-medium transition-colors flex items-center justify-between" :class="lokasiName == lok.nama ? 'text-[#009B77] bg-[#009B77]/5' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'">
                                  <span x-text="lok.nama" class="font-bold"></span>
                                  <i class="ph-bold ph-check text-[#009B77]" x-show="lokasiName == lok.nama" x-cloak></i>
                              </button>
                              </template>
                              <div x-show="filtered.length === 0" class="py-3 px-4 text-center text-sm font-medium text-slate-500">
                                  {{ __('Type to add new location') }}
                              </div>
                          </div>
                      </div>'''

replacement1 = '''<div x-show="open" x-cloak
                           x-transition:enter="transition ease-out duration-200"
                           x-transition:enter-start="opacity-0 scale-95"
                           x-transition:enter-end="opacity-100 scale-100"
                           x-transition:leave="transition ease-in duration-100"
                           x-transition:leave-start="opacity-100 scale-100"
                           x-transition:leave-end="opacity-0 scale-95"
                           class="absolute left-0 z-50 w-full mt-2 bg-white rounded-xl shadow-[0_10px_40px_rgba(0,0,0,0.1)] border border-slate-100 py-2 max-h-60 overflow-y-auto origin-top">
                          <div class="py-1">
                              <div x-show="!gedungId" class="py-3 px-4 text-center text-sm font-medium text-amber-500 flex items-center justify-center">
                                  <i class="ph-bold ph-warning-circle mr-1.5 text-base"></i>{{ __('Pilih Gedung terlebih dahulu') }}
                              </div>
                              <template x-for="lok in filtered" :key="lok.id">
                              <button type="button" @click="lokasiName = lok.nama; open = false" class="w-full text-left px-4 py-2.5 text-sm font-medium transition-colors flex items-center justify-between" :class="lokasiName == lok.nama ? 'text-[#009B77] bg-[#009B77]/5' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'">
                                  <span x-text="lok.nama" class="font-bold"></span>
                                  <i class="ph-bold ph-check text-[#009B77]" x-show="lokasiName == lok.nama" x-cloak></i>
                              </button>
                              </template>
                              <div x-show="gedungId && filtered.length === 0" class="py-3 px-4 text-center text-sm font-medium text-slate-500">
                                  {{ __('Ketik untuk menambah lokasi baru') }}
                              </div>
                          </div>
                      </div>'''

# Fix for Edit APAR
target2 = '''<div x-show="open && filteredLokasi.length > 0" x-cloak
                           x-transition:enter="transition ease-out duration-200"
                           x-transition:enter-start="opacity-0 scale-95"
                           x-transition:enter-end="opacity-100 scale-100"
                           x-transition:leave="transition ease-in duration-100"
                           x-transition:leave-start="opacity-100 scale-100"
                           x-transition:leave-end="opacity-0 scale-95"
                           class="absolute left-0 z-50 w-full mt-2 bg-white rounded-xl shadow-[0_10px_40px_rgba(0,0,0,0.1)] border border-slate-100 py-2 max-h-60 overflow-y-auto origin-top">
                          <div class="py-1">
                              <template x-for="lok in filtered" :key="lok.id">
                              <button type="button" @click="editApar.lokasi = lok.nama; open = false" class="w-full text-left px-4 py-2.5 text-sm font-medium transition-colors flex items-center justify-between" :class="editApar.lokasi == lok.nama ? 'text-amber-500 bg-amber-500/5' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'">
                                  <span x-text="lok.nama" class="font-bold"></span>
                                  <i class="ph-bold ph-check text-amber-500" x-show="editApar.lokasi == lok.nama" x-cloak></i>
                              </button>
                              </template>
                              <div x-show="filtered.length === 0" class="py-3 px-4 text-center text-sm font-medium text-slate-500">
                                  {{ __('Type to add new location') }}
                              </div>
                          </div>
                      </div>'''

replacement2 = '''<div x-show="open" x-cloak
                           x-transition:enter="transition ease-out duration-200"
                           x-transition:enter-start="opacity-0 scale-95"
                           x-transition:enter-end="opacity-100 scale-100"
                           x-transition:leave="transition ease-in duration-100"
                           x-transition:leave-start="opacity-100 scale-100"
                           x-transition:leave-end="opacity-0 scale-95"
                           class="absolute left-0 z-50 w-full mt-2 bg-white rounded-xl shadow-[0_10px_40px_rgba(0,0,0,0.1)] border border-slate-100 py-2 max-h-60 overflow-y-auto origin-top">
                          <div class="py-1">
                              <div x-show="!editApar.gedung_id" class="py-3 px-4 text-center text-sm font-medium text-amber-500 flex items-center justify-center">
                                  <i class="ph-bold ph-warning-circle mr-1.5 text-base"></i>{{ __('Pilih Gedung terlebih dahulu') }}
                              </div>
                              <template x-for="lok in filtered" :key="lok.id">
                              <button type="button" @click="editApar.lokasi = lok.nama; open = false" class="w-full text-left px-4 py-2.5 text-sm font-medium transition-colors flex items-center justify-between" :class="editApar.lokasi == lok.nama ? 'text-amber-500 bg-amber-500/5' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'">
                                  <span x-text="lok.nama" class="font-bold"></span>
                                  <i class="ph-bold ph-check text-amber-500" x-show="editApar.lokasi == lok.nama" x-cloak></i>
                              </button>
                              </template>
                              <div x-show="editApar.gedung_id && filtered.length === 0" class="py-3 px-4 text-center text-sm font-medium text-slate-500">
                                  {{ __('Ketik untuk menambah lokasi baru') }}
                              </div>
                          </div>
                      </div>'''


import re
content = content.replace(target1, replacement1)
content = content.replace(target2, replacement2)

# If standard replace didn't work, maybe trailing spaces?
# Let's do a fallback replace using regex for the general structure:
if target1 not in content:
    print("Warning: Tambah Target 1 not found by exact match. Trying regex...")
    pattern1 = re.compile(r'<div x-show="open && filteredLokasi\.length > 0" x-cloak.*?<div class="py-1">.*?<template x-for="lok in filtered" :key="lok\.id">.*?</template>.*?<div x-show="filtered\.length === 0" class="py-3 px-4 text-center text-sm font-medium text-slate-500">\s*\{\{ __\(\'Type to add new location\'\)\ \}\}\s*</div>\s*</div>\s*</div>', re.DOTALL)
    content = pattern1.sub(replacement1, content)
    
if target2 not in content:
    print("Warning: Edit Target 2 not found by exact match. Trying regex...")
    # wait, target2 and target1 are identical except for editApar, but they can be distinguished.
    # regex for edit
    pattern2 = re.compile(r'<div x-show="open && filteredLokasi\.length > 0" x-cloak.*?<div class="py-1">.*?<template x-for="lok in filtered" :key="lok\.id">.*?@click="editApar\.lokasi.*?</template>.*?<div x-show="filtered\.length === 0" class="py-3 px-4 text-center text-sm font-medium text-slate-500">\s*\{\{ __\(\'Type to add new location\'\)\ \}\}\s*</div>\s*</div>\s*</div>', re.DOTALL)
    content = pattern2.sub(replacement2, content)

with open(path, 'w', encoding='utf-8') as f:
    f.write(content)
print("Finished!")
