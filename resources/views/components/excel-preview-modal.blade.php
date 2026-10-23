<div x-data="{ 
    showPreviewModal: false, 
    isLoading: false, 
    previewData: { headers: [], rows: [] }, 
    downloadUrl: '', 
    
    openPreview(previewUrl, downloadLink) {
        this.showPreviewModal = true;
        this.isLoading = true;
        this.downloadUrl = downloadLink;
        
        fetch(previewUrl)
            .then(res => res.json())
            .then(data => {
                this.previewData = data;
                this.isLoading = false;
            })
            .catch(err => {
                console.error(err);
                this.isLoading = false;
                alert('Gagal memuat preview data / Failed to load preview data');
            });
    }
}" 
@open-excel-preview.window="openPreview($event.detail.previewUrl, $event.detail.downloadUrl)" 
class="relative z-[100]" aria-labelledby="modal-title" role="dialog" aria-modal="true" x-cloak>

    <!-- Background overlay -->
    <div x-show="showPreviewModal" class="fixed inset-0 bg-slate-900/40" x-cloak></div>
  
    <div x-show="showPreviewModal" class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <!-- Modal panel -->
            <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl sm:my-8 sm:w-full sm:max-w-5xl border border-slate-100 flex flex-col max-h-[85vh]" x-cloak>
                
                <!-- Header -->
                <div class="bg-white px-6 py-5 border-b border-slate-100 flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <i class="ph-bold ph-file-csv text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold leading-6 text-slate-800" id="modal-title">{{ __('Excel Export Preview') }}</h3>
                            <p class="text-sm text-slate-500 mt-0.5">{{ __('This is how your data will look when exported.') }}</p>
                        </div>
                    </div>
                    <button @click="showPreviewModal = false" class="text-slate-400 bg-slate-50 p-2 rounded-xl">
                        <i class="ph-bold ph-x text-xl"></i>
                    </button>
                </div>
                
                <!-- Body (Table) -->
                <div class="p-6 bg-slate-50 overflow-auto flex-1">
                    <div x-show="isLoading" class="flex flex-col items-center justify-center py-12 text-slate-400">
                        <p class="text-sm font-medium">{{ __('Loading preview data...') }}</p>
                    </div>

                    <div x-show="!isLoading" class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left whitespace-nowrap">
                                <thead class="text-xs text-emerald-600 uppercase bg-emerald-50 border-b border-emerald-100">
                                    <tr class="divide-x divide-emerald-200/50">
                                        <template x-for="(header, index) in previewData.headers" :key="index">
                                            <th scope="col" class="px-4 py-3 font-bold tracking-wider" x-text="header"></th>
                                        </template>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(row, rowIndex) in previewData.rows" :key="rowIndex">
                                        <tr class="border-b border-slate-100 last:border-0 divide-x divide-slate-100">
                                            <template x-for="(cell, cellIndex) in row" :key="cellIndex">
                                                <td class="px-4 py-2.5 text-slate-700" x-text="cell"></td>
                                            </template>
                                        </tr>
                                    </template>
                                    <tr x-show="previewData.rows.length === 0" x-cloak>
                                        <td :colspan="previewData.headers.length" class="px-4 py-8 text-center text-slate-400">
                                            {{ __('No data available for export') }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="bg-white px-6 py-4 border-t border-slate-100 flex items-center justify-end gap-3 shrink-0">
                    <button type="button" @click="showPreviewModal = false" class="px-5 py-2.5 text-sm font-bold text-slate-700 bg-white border-2 border-slate-200 rounded-xl">
                        {{ __('Cancel') }}
                    </button>
                    <a :href="downloadUrl" target="_blank" rel="noopener noreferrer" @click="showPreviewModal = false" class="px-5 py-2.5 text-sm font-bold text-white bg-emerald-600 rounded-xl flex items-center gap-2">
                        <i class="ph-bold ph-download-simple"></i>
                        {{ __('Download Excel') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
