import os
import json

blade_file = "c:/laragon/www/APARB.Braun/resources/views/scan/index.blade.php"
id_path = "c:/laragon/www/APARB.Braun/lang/id.json"
en_path = "c:/laragon/www/APARB.Braun/lang/en.json"

with open(blade_file, "r", encoding="utf-8") as f:
    content = f.read()

replacements = {
    "Informasi APAR": "{{ __('PFE Information') }}",
    "Kode APAR": "{{ __('PFE ID') }}",
    ">Detail Informasi<": ">{{ __('Information Details') }}<",
    ">Inspeksi<": ">{{ __('Inspection') }}<",
    ">Lokasi & Gedung<": ">{{ __('Location & Building') }}<",
    ">Jenis / Media<": ">{{ __('Type / Media') }}<",
    ">Kelas Kebakaran<": ">{{ __('Fire Class') }}<",
    "Kelas {{ $kelas }}": "{{ __('Class') }} {{ $kelas }}",
    ">Kapasitas<": ">{{ __('Capacity') }}<",
    ">Kedaluwarsa<": ">{{ __('Expiry Date') }}<",
    ">Inspeksi Terakhir<": ">{{ __('Last Inspection') }}<",
    " oleh ": " {{ __('by') }} ",
    ">Belum pernah diinspeksi<": ">{{ __('Never been inspected') }}<",
    ">Verifikasi Petugas<": ">{{ __('Officer Verification') }}<",
    ">Masukkan 4 digit PIN untuk memulai inspeksi.<": ">{{ __('Enter 4 digit PIN to start inspection.') }}<",
    ">PIN Petugas<": ">{{ __('Officer PIN') }}<",
    ">Verifikasi<": ">{{ __('Verify') }}<"
}

for old, new in replacements.items():
    content = content.replace(old, new)

with open(blade_file, "w", encoding="utf-8") as f:
    f.write(content)

# Update dictionaries
new_translations_id = {
    "PFE Information": "Informasi APAR",
    "Information Details": "Detail Informasi",
    "Inspection": "Inspeksi",
    "Location & Building": "Lokasi & Gedung",
    "Type / Media": "Jenis / Media",
    "Class": "Kelas",
    "by": "oleh",
    "Never been inspected": "Belum pernah diinspeksi",
    "Officer Verification": "Verifikasi Petugas",
    "Enter 4 digit PIN to start inspection.": "Masukkan 4 digit PIN untuk memulai inspeksi.",
    "Officer PIN": "PIN Petugas",
    "Verify": "Verifikasi"
}

new_translations_en = {k: k for k in new_translations_id.keys()}

def update_json(path, trans, is_id):
    with open(path, "r", encoding="utf-8") as f:
        lang = json.load(f)
    for k, v in trans.items():
        if is_id:
            lang[k] = v
        else:
            if k not in lang:
                lang[k] = v
    with open(path, "w", encoding="utf-8") as f:
        json.dump(lang, f, indent=4, ensure_ascii=False)

update_json(id_path, new_translations_id, True)
update_json(en_path, new_translations_en, False)

print("Scan page translated.")
