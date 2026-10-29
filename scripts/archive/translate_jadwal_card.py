import os
import re

file_path = "c:/laragon/www/APARB.Braun/resources/views/inspection-schedule/partials/jadwal-card.blade.php"
with open(file_path, "r", encoding="utf-8") as f:
    content = f.read()

replacements = {
    r'title="Inspeksi Seluruh Gedung"': 'title="{{ __(\'Inspect Entire Building\') }}"',
    r'title="Inspeksi Lokasi Spesifik"': 'title="{{ __(\'Inspect Specific Location\') }}"',
    r"'HARI INI - '": "'{{ __('TODAY') }} - '",
    r"'TERLEWAT - '": "'{{ __('MISSED') }} - '",
    r"'Gedung Terhapus'": "'{{ __('Deleted Building') }}'",
    r"\(Semua Area\)": "{{ __('(All Areas)') }}",
    r"'Lokasi Terhapus'": "'{{ __('Deleted Location') }}'",
    r"Petugas: ": "{{ __('Officer:') }} ",
    r"'Bebas \/ Siapa Saja'": "'{{ __('Free / Anyone') }}'",
    r">\s*TERLEWAT\s*<": ">{{ __('MISSED') }}<",
    r">\s*MENUNGGU\s*<": ">{{ __('WAITING') }}<",
    r">\s*PROSES\s*<": ">{{ __('PROCESS') }}<",
    r">\s*SELESAI\s*<": ">{{ __('COMPLETED') }}<",
    r'title="Edit Jadwal"': 'title="{{ __(\'Edit Schedule\') }}"',
    r"'Hapus Jadwal\?'": "'{{ __('Delete Schedule?') }}'",
    r"'Jadwal yang dihapus tidak dapat dikembalikan!'": "'{{ __('Deleted schedule cannot be restored!') }}'",
    r"'Ya, Hapus!'": "'{{ __('Yes, Delete!') }}'",
    r"'Batal'": "'{{ __('Cancel') }}'",
    r'title="Hapus Jadwal"': 'title="{{ __(\'Delete Schedule\') }}"',
    r"Tidak ada APAR di area ini\.": "{{ __('No PFE in this area.') }}",
    r"> Selesai<": "> {{ __('Completed') }}<",
    r">Menunggu<": ">{{ __('Waiting') }}<"
}

for old, new in replacements.items():
    content = re.sub(old, new, content)

with open(file_path, "w", encoding="utf-8") as f:
    f.write(content)

print("Done jadwal-card.blade.php")
