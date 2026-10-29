import os
import re

file_path = "c:/laragon/www/APARB.Braun/resources/views/inspection-schedule/buat.blade.php"
with open(file_path, "r", encoding="utf-8") as f:
    content = f.read()

replacements = {
    r'title="Kembali"': 'title="{{ __(\'Back\') }}"',
    r">Buat Jadwal Inspeksi Baru<": ">{{ __('Create New Inspection Schedule') }}<",
    r"Tentukan area, tanggal, dan petugas untuk inspeksi rutin/khusus": "{{ __('Determine area, date, and officer for routine/special inspection') }}",
    r">Jenis Jadwal<": ">{{ __('Schedule Type') }}<",
    r"'Inspeksi Rutin Bulanan'": "'{{ __('Routine Monthly Inspection') }}'",
    r"'Inspeksi Khusus / Temuan'": "'{{ __('Special Inspection / Findings') }}'",
    r"'Pilih Jenis Jadwal'": "'{{ __('Select Schedule Type') }}'",
    r">Tanggal Inspeksi<": ">{{ __('Inspection Date') }}<",
    r'placeholder="Pilih Tanggal"': 'placeholder="{{ __(\'Select Date\') }}"',
    r">Cakupan Area \(Gedung\)<": ">{{ __('Area Scope (Building)') }}<",
    r"'Semua Area'": "'{{ __('All Areas') }}'",
    r"'Gedung Utama \(Sudah dijadwalkan\)'": "'{{ __('Main Building (Already scheduled)') }}'",
    r"'Gedung Produksi'": "'{{ __('Production Building') }}'",
    r"'Gudang Logistik'": "'{{ __('Logistics Warehouse') }}'",
    r"'Pilih Area Cakupan'": "'{{ __('Select Scope Area') }}'",
    r">Petugas Inspeksi<": ">{{ __('Inspection Officer') }}<",
    r"'Bebas / Siapa Saja'": "'{{ __('Free / Anyone') }}'",
    r"'Pilih Petugas'": "'{{ __('Select Officer') }}'",
    r">Catatan Tambahan \(Opsional\)<": ">{{ __('Additional Notes (Optional)') }}<",
    r'placeholder="Contoh: Fokuskan pada APAR di area dapur dan genset\."': 'placeholder="{{ __(\'Example: Focus on PFE in kitchen and generator area.\') }}"',
    r">\n\s*Batal\n\s*<\/a>": ">\n                    {{ __('Cancel') }}\n                </a>",
    r">\n\s*Simpan Jadwal\n\s*<\/button>": ">\n                    {{ __('Save Schedule') }}\n                </button>"
}

for old, new in replacements.items():
    content = re.sub(old, new, content)

with open(file_path, "w", encoding="utf-8") as f:
    f.write(content)

print("Done buat.blade.php")
