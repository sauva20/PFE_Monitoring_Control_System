import os
import re

file_path = "c:/laragon/www/APARB.Braun/resources/views/dashboard/index.blade.php"
with open(file_path, "r", encoding="utf-8") as f:
    content = f.read()

replacements = {
    r">Export PDF<": ">{{ __('Export PDF') }}<",
    r"> Segera\n": "> {{ __('Immediate') }}\n",
    r"> Bahaya\n": "> {{ __('Danger') }}\n",
    r"> Kosong\n": "> {{ __('Empty') }}\n",
    r"Terakhir!": "{{ __('Last!') }}",
    r">Bulan Berakhir<": ">{{ __('Month Ended') }}<",
    r">Segera Datang<": ">{{ __('Coming Soon') }}<",
    r"> Perbaikan\n": "> {{ __('Repair') }}\n",
    r"Belum ada aktivitas inspeksi\.": "{{ __('No inspection activity yet.') }}",
    r"Belum ada data grafik": "{{ __('No chart data available') }}",
    r"Belum ada data APAR": "{{ __('No PFE data available') }}",
    r"PROGRES": "{{ __('PROGRESS') }}"
}

for old, new in replacements.items():
    content = re.sub(old, new, content)

with open(file_path, "w", encoding="utf-8") as f:
    f.write(content)

print("Done dashboard.blade.php")
