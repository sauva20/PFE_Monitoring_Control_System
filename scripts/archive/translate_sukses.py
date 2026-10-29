import os
import re

file_path = "c:/laragon/www/APARB.Braun/resources/views/inspeksi/sukses.blade.php"
with open(file_path, "r", encoding="utf-8") as f:
    content = f.read()

replacements = {
    r">Inspeksi Selesai<": ">{{ __('Inspection Completed') }}<",
    r">Inspeksi Selesai!<": ">{{ __('Inspection Completed!') }}<",
    r"Data inspeksi untuk APAR": "{{ __('Inspection data for PFE') }}",
    r"telah berhasil disimpan\.": "{{ __('has been successfully saved.') }}",
    r"Scan APAR Lain": "{{ __('Scan Another PFE') }}",
    r"Arahkan kamera ke QR Code APAR": "{{ __('Point camera at PFE QR Code') }}",
    r'"Kamera tidak dapat diakses atau diblokir\. Pastikan memberi izin kamera\."': '"{{ __(\'Camera cannot be accessed or is blocked. Make sure to grant camera permissions.\') }}"',
}

for old, new in replacements.items():
    content = re.sub(old, new, content)

with open(file_path, "w", encoding="utf-8") as f:
    f.write(content)

print("Done sukses.blade.php")
