import re

file_path = 'resources/views/master-data/pdf.blade.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Fix the broken PHP syntax
content = content.replace("{{ __('LOCATION') }}", "lokasi")
content = content.replace("{{ __('BUILDING') }}", "gedung")
content = content.replace("{{ __('TYPE') }}", "jenis")
content = content.replace("{{ __('CAPACITY') }}", "kapasitas")
content = content.replace("{{ __('FIRE CLASS') }}", "kelas")
content = content.replace("{{ __('Expired') }}", "Expired")
content = content.replace("{{ __('PFE ID') }}", "ID APAR")
content = content.replace("{{ __('PFE DATA REPORT') }}", "LAPORAN DATA APAR")
content = content.replace("{{ __('EXPIRED DATE') }}", "Tgl Kedaluwarsa")
content = content.replace("{{ __('OUT OF STOCK') }}", "STOK HABIS")

# Now selectively apply the translations only where needed
content = content.replace("LAPORAN DATA APAR", "{{ __('PFE DATA REPORT') }}")
content = content.replace(">ID APAR<", ">{{ __('PFE ID') }}<")
content = content.replace(">Lokasi<", ">{{ __('LOCATION') }}<")
content = content.replace(">Gedung<", ">{{ __('BUILDING') }}<")
content = content.replace(">Jenis<", ">{{ __('TYPE') }}<")
content = content.replace(">Kapasitas<", ">{{ __('CAPACITY') }}<")
content = content.replace(">Kelas<", ">{{ __('FIRE CLASS') }}<")
content = content.replace(">Tgl Kedaluwarsa<", ">{{ __('EXPIRED DATE') }}<")

# For Expired badge and STOK HABIS
content = content.replace("Expired (", "{{ __('Expired') }} (")
content = content.replace("STOK HABIS", "{{ __('OUT OF STOCK') }}")

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)
