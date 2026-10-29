import re

file_path = 'resources/views/master-data/index.blade.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

replacements = {
    r'(?i)Kelola seluruh data APAR dalam sistem': r"{{ __('Manage all PFE data in the system') }}",
    r'(?i)>Cetak Semua QR<': r">{{ __('Print All QR') }}<",
    r'(?i)>Add Data APAR<': r">{{ __('Add PFE Data') }}<",
    r'(?i)>Data APAR<': r">{{ __('PFE Data') }}<",
    r'(?i)>Pengaturan Referensi<': r">{{ __('Reference Settings') }}<",
    r'(?i)CARI ID ATAU LOKASI': r"{{ __('SEARCH ID OR LOCATION') }}",
    r'(?i)Masukkan kata kunci\.\.\.': r"{{ __('Enter keyword...') }}",
    r'(?i)Semua Lokasi': r"{{ __('All Locations') }}",
    r'(?i)Semua Gedung': r"{{ __('All Buildings') }}",
    r'(?i)Pilih Gedung': r"{{ __('Select Building') }}",
    r'(?i)Export ke PDF': r"{{ __('Export to PDF') }}",
    r'(?i)Export ke Excel \(CSV\)': r"{{ __('Export to Excel (CSV)') }}",
    r'(?i)NOMOR APAR': r"{{ __('PFE NUMBER') }}",
    r'(?i)Nomor APAR': r"{{ __('PFE NUMBER') }}",
    r'(?i)\(Huruf ID Otomatis\)': r"{{ __('Auto ID Letters') }}",
    r'(?i)Masukkan nomor \(Misal: 12\)': r"{{ __('Enter number (e.g. 12)') }}",
    r'(?i)Masukkan nomor urut\.\.\.': r"{{ __('Enter sequence number...') }}",
    r'(?i)Pilih \/ Ketik Baru': r"{{ __('Select / Type New') }}",
    r'(?i)Contoh: Corridor': r"{{ __('Example: Corridor') }}",
    r'(?i)TANGGAL KEDALUWARSA': r"{{ __('EXPIRED DATE') }}",
    r'(?i)Pilih Tanggal\.\.\.': r"{{ __('Select Date...') }}",
    r'(?i)>Batal<': r">{{ __('Cancel') }}<",
    r'(?i)>Simpan Data<': r">{{ __('Save Data') }}<",
    r'(?i)Apakah Anda Yakin\?': r"{{ __('Are you sure?') }}",
    r'(?i)Yakin ingin menghapus APAR ini\?': r"{{ __('Are you sure you want to delete this PFE?') }}",
    r'(?i)Ya, Hapus!': r"{{ __('Yes, Delete!') }}",
    r'(?i)>\s*LOKASI\s*<': r">{{ __('LOCATION') }}<",
    r'(?i)>\s*GEDUNG\s*<': r">{{ __('BUILDING') }}<",
    r'(?i)>\s*jenis\s*<': r">{{ __('Type') }}<",
    r'(?i)>\s*Semua jenis\s*<': r">{{ __('All Types') }}<",
    r'(?i)>\s*jenis APAR\s*<': r">{{ __('Type') }} APAR<",
    r'(?i)>\s*Tambah jenis\s*<': r">{{ __('Add Type') }}<",
    r'(?i)>\s*Nama jenis\s*<': r">{{ __('Type Name') }}<",
    r'(?i)Pilih jenis': r"{{ __('Select Type') }}",
    r'(?i)Semua jenis': r"{{ __('All Types') }}",
    r'(?i)>\s*kapasitas\s*<': r">{{ __('Capacity') }}<",
    r'(?i)>\s*Semua kapasitas\s*<': r">{{ __('All Capacities') }}<",
    r'(?i)>\s*kapasitas APAR\s*<': r">{{ __('Capacity') }} APAR<",
    r'(?i)>\s*Tambah kapasitas\s*<': r">{{ __('Add Capacity') }}<",
    r'(?i)>\s*Ukuran Kapasitas \(Kg\)\s*<': r">{{ __('Capacity Size') }} (Kg)<",
    r'(?i)Pilih Kapasitas': r"{{ __('Select Capacity') }}",
    r'(?i)Semua Kapasitas': r"{{ __('All Capacities') }}"
}

for pattern, repl in replacements.items():
    content = re.sub(pattern, repl, content)

content = re.sub(r'(?i)>Menampilkan\s+<', r' >{{ __(\'Showing\') }} <', content)
content = re.sub(r'(?i)>\s*dari\s*<', r'> {{ __(\'of\') }} <', content)
content = re.sub(r'(?i)>\s*data\s*<', r'> {{ __(\'data\') }}<', content)
content = re.sub(r'(?i)Menampilkan \<\w+\>(\{\{.+?\}\})\<\/\w+\>\s*dari\s*\<\w+\>(\{\{.+?\}\})\<\/\w+\>\s*data', r"{{ __('Showing :start-:end of :total data', ['start' => \->firstItem(), 'end' => \->lastItem(), 'total' => \->total()]) }}", content)

# Fix JS issues
content = content.replace("?? '{{ __('All Types') }}'", "?? __('All Types')")
content = content.replace(": '{{ __('All Types') }}'", ": __('All Types')")
content = content.replace("?? '{{ __('All Capacities') }}'", "?? __('All Capacities')")
content = content.replace(": '{{ __('All Capacities') }}'", ": __('All Capacities')")
content = content.replace("?? '{{ __('All Buildings') }}'", "?? __('All Buildings')")
content = content.replace(": '{{ __('All Buildings') }}'", ": __('All Buildings')")
content = content.replace("?? '{{ __('All Locations') }}'", "?? __('All Locations')")
content = content.replace(": '{{ __('All Locations') }}'", ": __('All Locations')")

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

file_qr = 'resources/views/master-data/print-all-qr.blade.php'
with open(file_qr, 'r', encoding='utf-8') as f:
    content_qr = f.read()

replacements_qr = {
    r'(?i)Cetak Stiker QR Code APAR': r"{{ __('Print PFE QR Code Stickers') }}",
    r'(?i)Total: \{\{ \\-\>count\(\) \}\} unit APAR siap dicetak ke kertas label\/stiker': r"{{ __('Total: :count PFE units ready to print on label/sticker paper', ['count' => ->count()]) }}",
    r'(?i)>Kembali<': r">{{ __('Back') }}<",
    r'(?i)>Cetak Sekarang<': r">{{ __('Print Now') }}<",
    r'(?i)Scan QR untuk cek detail & inspeksi': r"{{ __('Scan QR to check details & inspection') }}"
}

for pattern, repl in replacements_qr.items():
    content_qr = re.sub(pattern, repl, content_qr)

with open(file_qr, 'w', encoding='utf-8') as f:
    f.write(content_qr)
