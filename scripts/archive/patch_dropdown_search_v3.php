<?php

$file = __DIR__.'/resources/views/master-data/index.blade.php';
$content = file_get_contents($file);

$old = <<<HTML
                                <div x-data="{ 
                                        open: false, 
                                        options: [
                                            @foreach(\App\Models\Gedung::all() as \$ged)
                                            { id: '{{ \$ged->id }}', name: '{{ addslashes(\$ged->nama) }}' },
                                            @endforeach
                                        ],
                                        get selectedName() {
                                            let sel = this.options.find(o => o.id == editLokasi.gedung_id);
                                            return sel ? sel.name : 'Pilih Gedung';
                                        }
                                    }" 
                                    class="relative">
                                    
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Pilih Gedung</label>
                                    
                                    <!-- Hidden input for form submission -->
                                    <input type="hidden" name="gedung_id" :value="editLokasi.gedung_id" required>
                                    
                                    <!-- Trigger -->
                                    <button type="button" @click="open = !open" @click.away="open = false"
                                            class="w-full bg-slate-50 border-2 rounded-xl py-2.5 px-4 text-sm font-medium text-left transition-all flex items-center justify-between"
                                            :class="open ? 'bg-white border-amber-500 ring-4 ring-amber-500/15 text-slate-800' : 'border-slate-200 text-slate-700 hover:border-slate-300'">
                                        <span x-text="selectedName" :class="editLokasi.gedung_id === '' ? 'text-slate-400' : ''"></span>
                                        <i class="ph-bold ph-caret-down text-slate-400 transition-transform duration-300" :class="open ? 'rotate-180 text-amber-500' : ''"></i>
                                    </button>
                                    
                                    <!-- Dropdown Menu -->
                                    <div x-show="open" x-cloak
                                         x-transition:enter="transition ease-out duration-200"
                                         x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                         x-transition:leave="transition ease-in duration-100"
                                         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                         x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                                         class="absolute z-50 w-full mt-2 bg-white rounded-xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.1)] border border-slate-100 py-2 max-h-60 overflow-y-auto">
                                         
                                        <template x-for="option in options" :key="option.id">
                                            <button type="button" @click="editLokasi.gedung_id = option.id; open = false"
                                                    class="w-full text-left px-4 py-2.5 text-sm font-medium transition-colors flex items-center justify-between"
                                                    :class="editLokasi.gedung_id == option.id ? 'bg-amber-50 text-amber-600' : 'text-slate-600 hover:bg-slate-50'">
                                                <span x-text="option.name"></span>
                                                <i class="ph-bold ph-check text-amber-600" x-show="editLokasi.gedung_id == option.id" x-cloak></i>
                                            </button>
                                        </template>
                                        
                                        <div x-show="options.length === 0" x-cloak class="px-4 py-3 text-sm text-slate-400 text-center italic">
                                            Belum ada data gedung
                                        </div>
                                    </div>
                                </div>
HTML;

$new = <<<HTML
                                <div x-data="{ 
                                        open: false, 
                                        search: '',
                                        options: [
                                            @foreach(\App\Models\Gedung::all() as \$ged)
                                            { id: '{{ \$ged->id }}', name: '{{ addslashes(\$ged->nama) }}' },
                                            @endforeach
                                        ],
                                        get selectedName() {
                                            let sel = this.options.find(o => o.id == editLokasi.gedung_id);
                                            return sel ? sel.name : 'Pilih Gedung';
                                        },
                                        get filteredOptions() {
                                            if (this.search === '') return this.options;
                                            return this.options.filter(o => o.name.toLowerCase().includes(this.search.toLowerCase()));
                                        }
                                    }" 
                                    class="relative">
                                    
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Pilih Gedung</label>
                                    
                                    <!-- Hidden input for form submission -->
                                    <input type="hidden" name="gedung_id" :value="editLokasi.gedung_id" required>
                                    
                                    <!-- Trigger -->
                                    <button type="button" @click="open = !open" @click.away="open = false"
                                            class="w-full bg-slate-50 border-2 rounded-xl py-2.5 px-4 text-sm font-medium text-left transition-all flex items-center justify-between"
                                            :class="open ? 'bg-white border-amber-500 ring-4 ring-amber-500/15 text-slate-800' : 'border-slate-200 text-slate-700 hover:border-slate-300'">
                                        <span x-text="selectedName" :class="editLokasi.gedung_id === '' ? 'text-slate-400' : ''"></span>
                                        <i class="ph-bold ph-caret-down text-slate-400 transition-transform duration-300" :class="open ? 'rotate-180 text-amber-500' : ''"></i>
                                    </button>
                                    
                                    <!-- Dropdown Menu -->
                                    <div x-show="open" x-cloak
                                         x-transition:enter="transition ease-out duration-200"
                                         x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                         x-transition:leave="transition ease-in duration-100"
                                         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                         x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                                         class="absolute z-50 w-full mt-2 bg-white rounded-xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.1)] border border-slate-100 py-2 max-h-60 overflow-y-auto origin-top">
                                         
                                        <div class="sticky top-0 bg-white p-2 border-b border-slate-100 z-10">
                                            <div class="relative">
                                                <i class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                                <input type="text" x-model="search" placeholder="Cari gedung..." class="w-full bg-slate-50 border border-slate-200 rounded-lg py-2 pl-9 pr-3 text-sm font-medium text-slate-700 focus:outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 transition-all" @click.stop @keydown.enter.prevent>
                                            </div>
                                        </div>
                                        
                                        <div class="py-1">
                                            <template x-for="option in filteredOptions" :key="option.id">
                                                <button type="button" @click="editLokasi.gedung_id = option.id; open = false"
                                                        class="w-full text-left px-4 py-2.5 text-sm font-medium transition-colors flex items-center justify-between"
                                                        :class="editLokasi.gedung_id == option.id ? 'bg-amber-50 text-amber-600' : 'text-slate-600 hover:bg-slate-50'">
                                                    <span x-text="option.name"></span>
                                                    <i class="ph-bold ph-check text-amber-600" x-show="editLokasi.gedung_id == option.id" x-cloak></i>
                                                </button>
                                            </template>
                                            
                                            <div x-show="filteredOptions.length === 0" x-cloak class="px-4 py-3 text-sm text-slate-400 text-center italic">
                                                Pencarian tidak ditemukan
                                            </div>
                                        </div>
                                    </div>
                                </div>
HTML;

$content = str_replace($old, $new, $content);
file_put_contents($file, $content);
echo 'Dropdown updated.';
