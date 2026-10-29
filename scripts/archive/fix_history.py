import re
import json
import os

# Update history-partial.blade.php
history_file = 'resources/views/master-data/history-partial.blade.php'
with open(history_file, 'r', encoding='utf-8') as f:
    content = f.read()

replacements = {
    r'(?i)Oleh:': r"{{ __('By:') }}",
    r'(?i)Sistem / Guest': r"{{ __('System / Guest') }}",
    r'(?i)>Data<': r">{{ __('Data') }}<",
    r'(?i)>Before<': r">{{ __('Before') }}<",
    r'(?i)>After<': r">{{ __('After') }}<",
    r'(?i)>Data Masuk \(Baru\)<': r">{{ __('New Data Entered') }}<",
    r'(?i)>Data Dihapus<': r">{{ __('Deleted Data') }}<",
    r"(?i)>Gedung<": r">{{ __('Building') }}<",
    r'(?i)Detail data yang direkam tidak tersedia\.': r"{{ __('Recorded data details are not available.') }}",
    r'(?i)Belum Ada Riwayat': r"{{ __('No History Yet') }}",
    r'(?i)APAR ini belum memiliki catatan aktivitas atau perubahan data\.': r"{{ __('This PFE has no activity records or data changes yet.') }}",
    r"'Jenis APAR'": r"__('PFE Type')",
    r"'Lokasi'": r"__('Location')",
    r"'Kapasitas'": r"__('Capacity')",
    r"'PIC'": r"__('PIC')",
    r"'Tgl Kedaluwarsa'": r"__('Expired Date')",
    r"'ID APAR'": r"__('PFE ID')",
    r"'Qty'": r"__('Qty')",
    r"'Vendor'": r"__('Vendor')",
    r"'Foto'": r"__('Photo')",
    r"'\(File Gambar Diperbarui\)'": r"__('(Image File Updated)')"
}

for pattern, repl in replacements.items():
    content = re.sub(pattern, repl, content)

with open(history_file, 'w', encoding='utf-8') as f:
    f.write(content)

# Update index.blade.php for the modal
index_file = 'resources/views/master-data/index.blade.php'
with open(index_file, 'r', encoding='utf-8') as f:
    index_content = f.read()

index_replacements = {
    r'(?i)>Riwayat Perubahan Data<': r">{{ __('Data Change History') }}<",
    r'(?i)Timeline aktivitas untuk APAR': r"{{ __('Activity timeline for PFE') }}",
    r'(?i)>Tutup<': r">{{ __('Close') }}<"
}
for pattern, repl in index_replacements.items():
    index_content = re.sub(pattern, repl, index_content)

with open(index_file, 'w', encoding='utf-8') as f:
    f.write(index_content)
