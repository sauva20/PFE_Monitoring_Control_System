import sys

file_path = 'resources/views/inspection-schedule/index.blade.php'

with open(file_path, 'r', encoding='utf-8') as f:
    lines = f.readlines()

start_index = 561 # line 562
end_index = 628   # line 629

replacement = """                            <i class="ph-bold ph-map-pin absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-base transition-colors z-10" :class="open ? 'text-[#009B77]' : ''"></i>
                            <button type="button" @click="open = !open" @click.away="open = false; search = ''" 
                                    class="w-full bg-white border-2 border-slate-200 rounded-xl py-2.5 pl-10 pr-3 text-sm font-medium text-slate-700 focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none cursor-pointer text-left flex items-center justify-between"
                                    :class="open ? 'border-[#009B77] ring-4 ring-[#009B77]/15' : ''">
                                <span x-text="formAreas.length > 0 ? formAreas.length + ' area terpilih' : 'Pilih Area'" :class="formAreas.length === 0 ? 'text-slate-400 font-medium' : 'font-bold text-slate-800'" class="truncate block"></span>
                                <i class="ph-bold ph-caret-down text-slate-400 transition-transform duration-200 flex-shrink-0" :class="open ? 'rotate-180 text-[#009B77]' : ''"></i>
                            </button>
                            
                            <div x-show="open" style="display: none;" class="absolute left-0 z-50 w-full mt-1.5 bg-white rounded-xl shadow-lg border border-slate-100 py-1.5 overflow-hidden origin-top max-h-60 flex flex-col">
                                <div class="px-2 pb-1.5 mb-1.5 border-b border-slate-100 flex-shrink-0">
                                    <div class="relative">
                                        <i class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                        <input type="text" x-model="search" placeholder="Cari gedung atau lokasi..." class="w-full bg-slate-50 border-none rounded-lg py-1.5 pl-9 pr-3 text-xs font-medium text-slate-700 focus:ring-0 placeholder:text-slate-400" @click.stop>
                                    </div>
                                </div>
                                <div class="overflow-y-auto">
                                    <template x-for="option in options" :key="option.id">
                                        <button type="button" x-show="option.nama.toLowerCase().includes(search.toLowerCase())" @click="toggleArea(option.id)" class="w-full text-left px-4 py-2 text-sm transition-colors flex items-center justify-between" :class="formAreas.includes(option.id) ? 'bg-[#009B77]/5' : 'hover:bg-slate-50'">
                                            <div class="flex items-center">
                                                <span class="font-medium" :class="formAreas.includes(option.id) ? 'text-[#009B77] font-bold' : 'text-slate-700'" x-text="option.nama"></span>
                                                <span class="text-[10px] uppercase font-bold tracking-wider px-1.5 py-0.5 rounded ml-2 flex-shrink-0" :class="option.tipe === 'Gedung' ? 'bg-indigo-50 text-indigo-500' : 'bg-orange-50 text-orange-500'" x-text="option.tipe"></span>
                                            </div>
                                            <div class="w-4 h-4 rounded border flex items-center justify-center transition-colors flex-shrink-0 ml-3" :class="formAreas.includes(option.id) ? 'bg-[#009B77] border-[#009B77]' : 'border-slate-300 bg-white'">
                                                <i class="ph-bold ph-check text-white text-[10px]" x-show="formAreas.includes(option.id)"></i>
                                            </div>
                                        </button>
                                    </template>
                                </div>
                            </div>
"""

new_lines = lines[:start_index] + [l + '\\n' for l in replacement.split('\\n')] + lines[end_index:]

with open(file_path, 'w', encoding='utf-8') as f:
    f.writelines(new_lines)

print("Replacement done!")
