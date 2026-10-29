<?php

$file = __DIR__.'/resources/views/master-data/index.blade.php';
$content = file_get_contents($file);

// Replace the filter section
$filterSearch = <<<'HTML'
    <!-- Filter & Search Section -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-5 space-y-4">
        <!-- Row 1: Search Input (Full Width) -->
        <div>
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Cari ID atau Lokasi</label>
            <div class="relative">
                <i class="ph-bold ph-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-[#009B77] text-base"></i>
                <input type="text" placeholder="Masukkan kata kunci..."
                       class="w-full bg-white border-2 border-[#009B77] rounded-xl py-2.5 pl-10 pr-4 text-sm text-slate-700 focus:outline-none focus:ring-4 focus:ring-[#009B77]/15 transition-all placeholder:text-slate-400 font-medium">
            </div>
        </div>

        <!-- Row 2: Filters (2 Columns) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Filter Lokasi -->
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Lokasi</label>
                <div x-data="{ open: false, selected: 'Semua Lokasi', options: ['Semua Lokasi', 'Open Office', 'Archive Room', 'Preparation Area'] }" class="relative">
                    <button @click="open = !open" @click.away="open = false" class="w-full flex items-center justify-between py-2.5 px-3.5 bg-white border-2 border-slate-200 rounded-xl text-sm font-semibold text-slate-700 focus:outline-none focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all cursor-pointer hover:border-slate-300">
                        <span x-text="selected" class="truncate"></span>
                        <i class="ph-bold ph-caret-down text-slate-400 transition-transform duration-200 flex-shrink-0" :class="open ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         style="display: none;" 
                         class="absolute left-0 z-50 w-full mt-2 bg-white rounded-xl shadow-[0_10px_40px_rgba(0,0,0,0.1)] border border-slate-100 py-2 overflow-hidden origin-top" x-cloak>
                        <template x-for="option in options">
                            <button @click="selected = option; open = false" class="w-full text-left px-4 py-2.5 text-sm font-medium transition-colors flex items-center justify-between" :class="selected === option ? 'text-[#009B77] bg-[#009B77]/5' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'">
                                <span x-text="option"></span>
                                <i class="ph-bold ph-check text-[#009B77]" x-show="selected === option" x-cloak></i>
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Filter Jenis -->
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Jenis</label>
                <div x-data="{ open: false, selected: 'Semua Jenis', options: ['Semua Jenis', 'ABC Powder', 'CO2', 'Foam'] }" class="relative">
                    <button @click="open = !open" @click.away="open = false" class="w-full flex items-center justify-between py-2.5 px-3.5 bg-white border-2 border-slate-200 rounded-xl text-sm font-semibold text-slate-700 focus:outline-none focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all cursor-pointer hover:border-slate-300">
                        <span x-text="selected" class="truncate"></span>
                        <i class="ph-bold ph-caret-down text-slate-400 transition-transform duration-200 flex-shrink-0" :class="open ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         style="display: none;" 
                         class="absolute left-0 z-50 w-full mt-2 bg-white rounded-xl shadow-[0_10px_40px_rgba(0,0,0,0.1)] border border-slate-100 py-2 overflow-hidden origin-top" x-cloak>
                        <template x-for="option in options">
                            <button @click="selected = option; open = false" class="w-full text-left px-4 py-2.5 text-sm font-medium transition-colors flex items-center justify-between" :class="selected === option ? 'text-[#009B77] bg-[#009B77]/5' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'">
                                <span x-text="option"></span>
                                <i class="ph-bold ph-check text-[#009B77]" x-show="selected === option" x-cloak></i>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
HTML;
$filterReplace = <<<'HTML'
    <!-- Filter & Search Section -->
    <form method="GET" action="/master-data" class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-5 space-y-4">
        <!-- Row 1: Search Input (Full Width) -->
        <div>
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Cari ID atau Lokasi</label>
            <div class="relative">
                <i class="ph-bold ph-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-[#009B77] text-base"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Masukkan kata kunci..."
                       class="w-full bg-white border-2 border-[#009B77] rounded-xl py-2.5 pl-10 pr-4 text-sm text-slate-700 focus:outline-none focus:ring-4 focus:ring-[#009B77]/15 transition-all placeholder:text-slate-400 font-medium"
                       onchange="this.form.submit()">
            </div>
        </div>

        <!-- Row 2: Filters (2 Columns) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Filter Lokasi -->
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Lokasi</label>
                <div x-data="{ open: false, selected: '{{ request('lokasi_id') ? addslashes($lokasis->firstWhere('id', request('lokasi_id'))->nama ?? 'Semua Lokasi') : 'Semua Lokasi' }}' }" class="relative">
                    <input type="hidden" name="lokasi_id" value="{{ request('lokasi_id') }}" x-ref="lokasi_input">
                    <button type="button" @click="open = !open" @click.away="open = false" class="w-full flex items-center justify-between py-2.5 px-3.5 bg-white border-2 border-slate-200 rounded-xl text-sm font-semibold text-slate-700 focus:outline-none focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all cursor-pointer hover:border-slate-300">
                        <span x-text="selected" class="truncate"></span>
                        <i class="ph-bold ph-caret-down text-slate-400 transition-transform duration-200 flex-shrink-0" :class="open ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         style="display: none;" 
                         class="absolute left-0 z-50 w-full mt-2 bg-white rounded-xl shadow-[0_10px_40px_rgba(0,0,0,0.1)] border border-slate-100 py-2 max-h-60 overflow-y-auto origin-top" x-cloak>
                         
                        <button type="button" @click="selected = 'Semua Lokasi'; open = false; $refs.lokasi_input.value = ''; $refs.lokasi_input.form.submit();" class="w-full text-left px-4 py-2.5 text-sm font-medium transition-colors flex items-center justify-between" :class="selected === 'Semua Lokasi' ? 'text-[#009B77] bg-[#009B77]/5' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'">
                            <span>Semua Lokasi</span>
                            <i class="ph-bold ph-check text-[#009B77]" x-show="selected === 'Semua Lokasi'" x-cloak></i>
                        </button>
                        
                        @foreach($lokasis as $lok)
                        <button type="button" @click="selected = '{{ addslashes($lok->nama) }}'; open = false; $refs.lokasi_input.value = '{{ $lok->id }}'; $refs.lokasi_input.form.submit();" class="w-full text-left px-4 py-2.5 text-sm font-medium transition-colors flex items-center justify-between" :class="selected === '{{ addslashes($lok->nama) }}' ? 'text-[#009B77] bg-[#009B77]/5' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'">
                            <span>{{ $lok->nama }} ({{ $lok->gedung->nama ?? '' }})</span>
                            <i class="ph-bold ph-check text-[#009B77]" x-show="selected === '{{ addslashes($lok->nama) }}'" x-cloak></i>
                        </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Filter Jenis -->
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Jenis</label>
                <div x-data="{ open: false, selected: '{{ request('jenis_id') ? addslashes($jenisApars->firstWhere('id', request('jenis_id'))->nama ?? 'Semua Jenis') : 'Semua Jenis' }}' }" class="relative">
                    <input type="hidden" name="jenis_id" value="{{ request('jenis_id') }}" x-ref="jenis_input">
                    <button type="button" @click="open = !open" @click.away="open = false" class="w-full flex items-center justify-between py-2.5 px-3.5 bg-white border-2 border-slate-200 rounded-xl text-sm font-semibold text-slate-700 focus:outline-none focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all cursor-pointer hover:border-slate-300">
                        <span x-text="selected" class="truncate"></span>
                        <i class="ph-bold ph-caret-down text-slate-400 transition-transform duration-200 flex-shrink-0" :class="open ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         style="display: none;" 
                         class="absolute left-0 z-50 w-full mt-2 bg-white rounded-xl shadow-[0_10px_40px_rgba(0,0,0,0.1)] border border-slate-100 py-2 max-h-60 overflow-y-auto origin-top" x-cloak>
                         
                        <button type="button" @click="selected = 'Semua Jenis'; open = false; $refs.jenis_input.value = ''; $refs.jenis_input.form.submit();" class="w-full text-left px-4 py-2.5 text-sm font-medium transition-colors flex items-center justify-between" :class="selected === 'Semua Jenis' ? 'text-[#009B77] bg-[#009B77]/5' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'">
                            <span>Semua Jenis</span>
                            <i class="ph-bold ph-check text-[#009B77]" x-show="selected === 'Semua Jenis'" x-cloak></i>
                        </button>
                        
                        @foreach($jenisApars as $jen)
                        <button type="button" @click="selected = '{{ addslashes($jen->nama) }}'; open = false; $refs.jenis_input.value = '{{ $jen->id }}'; $refs.jenis_input.form.submit();" class="w-full text-left px-4 py-2.5 text-sm font-medium transition-colors flex items-center justify-between" :class="selected === '{{ addslashes($jen->nama) }}' ? 'text-[#009B77] bg-[#009B77]/5' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'">
                            <span>{{ $jen->nama }}</span>
                            <i class="ph-bold ph-check text-[#009B77]" x-show="selected === '{{ addslashes($jen->nama) }}'" x-cloak></i>
                        </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </form>
HTML;

$content = str_replace($filterSearch, $filterReplace, $content);

// Replace the pagination section
$paginationSearch = <<<'HTML'
        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-sm font-semibold text-slate-500">Menampilkan <span class="font-bold text-slate-800">1-10</span> dari <span class="font-bold text-slate-800">45</span> data</p>
            <div class="flex items-center gap-1.5">
                <button class="w-9 h-9 rounded-lg border border-slate-200 flex items-center justify-center text-slate-400 hover:border-slate-300 hover:text-slate-600 transition-colors" disabled>
                    <i class="ph-bold ph-caret-left text-sm"></i>
                </button>
                <button class="w-9 h-9 rounded-lg bg-[#009B77] text-white font-bold text-sm flex items-center justify-center shadow-sm">1</button>
                <button class="w-9 h-9 rounded-lg border border-slate-200 text-slate-600 font-semibold text-sm flex items-center justify-center hover:border-[#009B77] hover:text-[#009B77] transition-colors">2</button>
                <button class="w-9 h-9 rounded-lg border border-slate-200 text-slate-600 font-semibold text-sm flex items-center justify-center hover:border-[#009B77] hover:text-[#009B77] transition-colors">3</button>
                <span class="w-9 h-9 flex items-center justify-center text-slate-400 font-bold text-sm">...</span>
                <button class="w-9 h-9 rounded-lg border border-slate-200 text-slate-600 font-semibold text-sm flex items-center justify-center hover:border-[#009B77] hover:text-[#009B77] transition-colors">9</button>
                <button class="w-9 h-9 rounded-lg border border-slate-200 flex items-center justify-center text-slate-600 hover:border-[#009B77] hover:text-[#009B77] transition-colors">
                    <i class="ph-bold ph-caret-right text-sm"></i>
                </button>
            </div>
        </div>
HTML;

$paginationReplace = <<<'HTML'
        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-slate-100">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-sm font-semibold text-slate-500">Menampilkan <span class="font-bold text-slate-800">{{ $apars->firstItem() ?? 0 }}-{{ $apars->lastItem() ?? 0 }}</span> dari <span class="font-bold text-slate-800">{{ $apars->total() }}</span> data</p>
                <div class="w-full sm:w-auto">
                    {{ $apars->links('pagination::tailwind') }}
                </div>
            </div>
        </div>
HTML;

$content = str_replace($paginationSearch, $paginationReplace, $content);

file_put_contents($file, $content);
echo "Frontend filtering and pagination patched.\n";

$controllerFile = __DIR__.'/app/Http/Controllers/MasterDataController.php';
$controllerContent = file_get_contents($controllerFile);

$controllerSearch = <<<'PHP'
    public function index()
    {
        $gedungs = Gedung::all();
        $lokasis = Lokasi::with('gedung')->get();
        $jenisApars = JenisApar::all();
        $kapasitasApars = KapasitasApar::all();
        $apars = Apar::with(['lokasi.gedung', 'jenis', 'kapasitas'])->get();

        return view('master-data.index', compact('gedungs', 'lokasis', 'jenisApars', 'kapasitasApars', 'apars'));
    }
PHP;

$controllerReplace = <<<'PHP'
    public function index(Request $request)
    {
        $gedungs = Gedung::all();
        $lokasis = Lokasi::with('gedung')->get();
        $jenisApars = JenisApar::all();
        $kapasitasApars = KapasitasApar::all();
        
        $query = Apar::with(['lokasi.gedung', 'jenis', 'kapasitas']);
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('kode', 'like', "%{$search}%")
                  ->orWhereHas('lokasi', function($q2) use ($search) {
                      $q2->where('nama', 'like', "%{$search}%")
                         ->orWhereHas('gedung', function($q3) use ($search) {
                             $q3->where('nama', 'like', "%{$search}%");
                         });
                  });
            });
        }
        
        if ($request->filled('lokasi_id')) {
            $query->where('lokasi_id', $request->lokasi_id);
        }
        
        if ($request->filled('jenis_id')) {
            $query->where('jenis_id', $request->jenis_id);
        }
        
        $apars = $query->latest()->paginate(10)->withQueryString();

        return view('master-data.index', compact('gedungs', 'lokasis', 'jenisApars', 'kapasitasApars', 'apars'));
    }
PHP;

$controllerContent = str_replace($controllerSearch, $controllerReplace, $controllerContent);
file_put_contents($controllerFile, $controllerContent);
echo "Backend logic patched.\n";
