import re

def replace_in_file(file_path, replacements):
    with open(file_path, 'r', encoding='utf-8') as f:
        content = f.read()

    for pattern, repl in replacements.items():
        content = re.sub(pattern, repl, content)

    with open(file_path, 'w', encoding='utf-8') as f:
        f.write(content)

replacements_index = {
    r'(?i)STOK HABIS': r"{{ __('OUT OF STOCK') }}",
    r'(?i)>Tambah Data APAR<': r">{{ __('Add PFE Data') }}<",
    r'(?i)>ID APAR<': r">{{ __('PFE ID') }}<",
    r'(?i)>AKSI<': r">{{ __('ACTIONS') }}<",
    r'(?i)>Data APAR<': r">{{ __('PFE Data') }}<",
    r'(?i)>Pengaturan Referensi<': r">{{ __('Reference Settings') }}<",
}

replace_in_file('resources/views/master-data/index.blade.php', replacements_index)

replacements_pdf = {
    r'(?i)LAPORAN DATA APAR': r"{{ __('PFE DATA REPORT') }}",
    r'(?i)ID APAR': r"{{ __('PFE ID') }}",
    r'(?i)LOKASI': r"{{ __('LOCATION') }}",
    r'(?i)GEDUNG': r"{{ __('BUILDING') }}",
    r'(?i)JENIS': r"{{ __('TYPE') }}",
    r'(?i)KAPASITAS': r"{{ __('CAPACITY') }}",
    r'(?i)KELAS': r"{{ __('FIRE CLASS') }}",
    r'(?i)TGL KEDALUWARSA': r"{{ __('EXPIRED DATE') }}",
    r'(?i)Expired': r"{{ __('Expired') }}",
    r'(?i)STOK HABIS': r"{{ __('OUT OF STOCK') }}"
}
import os
if os.path.exists('resources/views/master-data/pdf.blade.php'):
    replace_in_file('resources/views/master-data/pdf.blade.php', replacements_pdf)

