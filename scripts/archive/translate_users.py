import os
import re

base_dir = "c:/laragon/www/APARB.Braun/resources/views/users/"

files = {
    "index.blade.php": {
        r"Belum ditugaskan": "{{ __('Unassigned') }}",
        r"Rutin: Tgl ": "{{ __('Routine: Date') }} ",
        r"User ini akan dihapus secara permanen!": "{{ __('This user will be permanently deleted!') }}",
        r"Belum ada data User\.": "{{ __('No User data yet.') }}",
        r"contoh@bbraun\.com": "example@ehss.id",
        r'title="Edit"': 'title="{{ __(\'Edit\') }}"',
        r'title="Hapus"': 'title="{{ __(\'Delete\') }}"'
    },
    "pdf.blade.php": {
        r">Data User<": ">{{ __('User Data') }}<",
        r"Laporan Data User PFE Monitoring Control System": "{{ __('PFE Monitoring Control System User Data Report') }}",
        r">Nama<": ">{{ __('Name') }}<",
        r">Gedung<": ">{{ __('Building') }}<",
        r">Jadwal Inspeksi<": ">{{ __('Inspection Schedule') }}<",
        r"'Tanggal '": "'{{ __('Date') }} '",
        r"Belum ada data User\.": "{{ __('No User data yet.') }}"
    }
}

for file_rel_path, replacements in files.items():
    file_path = os.path.join(base_dir, file_rel_path)
    if os.path.exists(file_path):
        with open(file_path, "r", encoding="utf-8") as f:
            content = f.read()

        for old, new in replacements.items():
            content = re.sub(old, new, content)

        with open(file_path, "w", encoding="utf-8") as f:
            f.write(content)
        print(f"Done {file_rel_path}")
    else:
        print(f"File not found: {file_rel_path}")
