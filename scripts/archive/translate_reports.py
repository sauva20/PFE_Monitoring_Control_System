import os
import re

file_path = "c:/laragon/www/APARB.Braun/resources/views/reports/index.blade.php"
with open(file_path, "r", encoding="utf-8") as f:
    content = f.read()

replacements = {
    r">Laporan Inspeksi APAR<": ">{{ __('PFE Inspection Report') }}<",
    r"Rekapitulasi data inspeksi bulanan": "{{ __('Recapitulation of monthly inspection data') }}",
    r">\n\s*Export PDF\n": ">\n                {{ __('Export PDF') }}\n",
    r">\n\s*Export Excel\n": ">\n                {{ __('Export Excel') }}\n",
    r">Bulan<": ">{{ __('Month') }}<",
    r">Tahun<": ">{{ __('Year') }}<",
    r">Gedung<": ">{{ __('Building') }}<",
    r"'Semua Gedung'": "'{{ __('All Buildings') }}'",
    r">Semua Gedung<": ">{{ __('All Buildings') }}<",
    r">Status<": ">{{ __('Status') }}<",
    r"'Sudah Diinspeksi'": "'{{ __('Inspected') }}'",
    r">Sudah Diinspeksi<": ">{{ __('Inspected') }}<",
    r"'Belum Diinspeksi'": "'{{ __('Uninspected') }}'",
    r">Belum Diinspeksi<": ">{{ __('Uninspected') }}<",
    r"'Semua Status'": "'{{ __('All Statuses') }}'",
    r">Semua Status<": ">{{ __('All Statuses') }}<",
    r">Jenis<": ">{{ __('Type') }}<",
    r">Kapasitas<": ">{{ __('Capacity') }}<",
    r">Lokasi<": ">{{ __('Location') }}<",
    r">Status & Tgl Inspeksi<": ">{{ __('Status & Inspection Date') }}<",
    r">Kondisi<": ">{{ __('Condition') }}<",
    r">\s*Sudah\n\s*<\/span>": "\n                                        {{ __('Inspected') }}\n                                    </span>",
    r">\s*Belum\n\s*<\/span>": "\n                                        {{ __('Uninspected') }}\n                                    </span>",
    r">\s*Layak\n\s*<\/span>": "\n                                            {{ __('Pass') }}\n                                        </span>",
    r">\s*Perbaikan\n\s*<\/span>": "\n                                            {{ __('Repair') }}\n                                        </span>",
    r"Tidak ada data untuk periode ini\.": "{{ __('No data for this period.') }}"
}

for old, new in replacements.items():
    content = re.sub(old, new, content)

with open(file_path, "w", encoding="utf-8") as f:
    f.write(content)

print("Done reports index.blade.php")
