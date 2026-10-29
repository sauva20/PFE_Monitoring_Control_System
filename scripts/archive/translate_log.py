import os
import re

file_path = "c:/laragon/www/APARB.Braun/resources/views/activity-log/index.blade.php"
with open(file_path, "r", encoding="utf-8") as f:
    content = f.read()

replacements = {
    r"Riwayat tindakan pengguna dalam sistem": "{{ __('History of user actions in the system') }}",
    r">\n\s*Export PDF\n": ">\n                {{ __('Export PDF') }}\n",
    r">Waktu<": ">{{ __('Time') }}<",
    r">Pengguna<": ">{{ __('User') }}<",
    r">Aksi<": ">{{ __('Action') }}<",
    r">Modul<": ">{{ __('Module') }}<",
    r">Keterangan<": ">{{ __('Description') }}<",
    r"'Sistem \/ Guest'": "'{{ __('System / Guest') }}'",
    r"Belum ada riwayat aktivitas\.": "{{ __('No activity history yet.') }}"
}

for old, new in replacements.items():
    content = re.sub(old, new, content)

with open(file_path, "w", encoding="utf-8") as f:
    f.write(content)

print("Done activity-log index.blade.php")
