import os
import re

file_path = "c:/laragon/www/APARB.Braun/resources/views/reports/pdf.blade.php"
with open(file_path, "r", encoding="utf-8") as f:
    content = f.read()

replacements = {
    r">Laporan Inspeksi APAR<": ">{{ __('PFE Inspection Report') }}<",
    r"Laporan Inspeksi APAR Bulan": "{{ __('PFE Inspection Report') }} {{ __('Month') }}",
    r"'Semua Gedung'": "'{{ __('All Buildings') }}'",
    r"'Sudah Diinspeksi'": "'{{ __('Inspected') }}'",
    r"'Belum Diinspeksi'": "'{{ __('Uninspected') }}'",
    r">Gedung<": ">{{ __('Building') }}<",
    r">Lokasi<": ">{{ __('Location') }}<",
    r">Jenis<": ">{{ __('Type') }}<",
    r">Kapasitas<": ">{{ __('Capacity') }}<",
    r">Status<": ">{{ __('Status') }}<",
    r">Tgl Inspeksi<": ">{{ __('Inspection Date') }}<",
    r">Inspektor<": ">{{ __('Inspector') }}<",
    r">Kondisi<": ">{{ __('Condition') }}<",
    r">Catatan Tambahan<": ">{{ __('Additional Notes') }}<",
    r">Sudah<": ">{{ __('Inspected') }}<",
    r">Belum<": ">{{ __('Uninspected') }}<",
    r">LAYAK<": ">{{ __('PASS') }}<",
    r">PERBAIKAN<": ">{{ __('REPAIR') }}<",
    r"Tidak ada data untuk periode dan filter ini\.": "{{ __('No data for this period and filter.') }}"
}

for old, new in replacements.items():
    content = re.sub(old, new, content)

with open(file_path, "w", encoding="utf-8") as f:
    f.write(content)

print("Done pdf.blade.php")
