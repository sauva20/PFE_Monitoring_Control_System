import os
import json

blade_file = "c:/laragon/www/APARB.Braun/resources/views/master-data/index.blade.php"
id_path = "c:/laragon/www/APARB.Braun/lang/id.json"
en_path = "c:/laragon/www/APARB.Braun/lang/en.json"

with open(blade_file, "r", encoding="utf-8") as f:
    content = f.read()

replacements = {
    # Lokasi
    ">Tambah Data Lokasi<": ">{{ __('Add Location Data') }}<",
    "placeholder=\"Contoh: Ruang Server Lt 3\"": "placeholder=\"{{ __('Example: Server Room 3rd Floor') }}\"",
    "Lokasi dengan nama ini sudah ada di gedung yang dipilih.": "{{ __('Location with this name already exists in the selected building.') }}",
    "Belum ada data gedung": "{{ __('No building data available') }}",
    ">Edit Data Lokasi<": ">{{ __('Edit Location Data') }}<",
    "Ubah Nama Lokasi": "{{ __('Change Location Name') }}",
    
    # Gedung
    ">Tambah Data Gedung<": ">{{ __('Add Building Data') }}<",
    "placeholder=\"Contoh: Gedung BUR\"": "placeholder=\"{{ __('Example: BUR Building') }}\"",
    "Nama gedung ini sudah terdaftar.": "{{ __('This building name is already registered.') }}",
    ">Edit Data Gedung<": ">{{ __('Edit Building Data') }}<",
    "Ubah Nama Gedung": "{{ __('Change Building Name') }}",
    
    # Jenis
    "Tambah {{ __('PFE Type') }}": "{{ __('Add PFE Type') }}",
    "placeholder=\"Contoh: ABC Powder\"": "placeholder=\"{{ __('Example: ABC Powder') }}\"",
    "{{ __('PFE Type') }} ini sudah terdaftar.": "{{ __('This PFE Type is already registered.') }}",
    "Edit {{ __('PFE Type') }}": "{{ __('Edit PFE Type') }}",
    "Ubah Nama Type": "{{ __('Change Type Name') }}",
    
    # Kapasitas
    ">Edit Kapasitas<": ">{{ __('Edit Capacity') }}<",
    "placeholder=\"Contoh: 3\"": "placeholder=\"{{ __('Example: 3') }}\"",
    "Kapasitas APAR ini sudah terdaftar.": "{{ __('This capacity is already registered.') }}",
    "Ubah Kapasitas": "{{ __('Change Capacity') }}",
    
    # General Button
    ">Simpan<": ">{{ __('Save') }}<",
}

for old, new in replacements.items():
    content = content.replace(old, new)

with open(blade_file, "w", encoding="utf-8") as f:
    f.write(content)

# Update dictionaries
new_translations = {
    "Add Location Data": "Tambah Data Lokasi",
    "Example: Server Room 3rd Floor": "Contoh: Ruang Server Lt 3",
    "Location with this name already exists in the selected building.": "Lokasi dengan nama ini sudah ada di gedung yang dipilih.",
    "No building data available": "Belum ada data gedung",
    "Edit Location Data": "Edit Data Lokasi",
    "Change Location Name": "Ubah Nama Lokasi",
    
    "Add Building Data": "Tambah Data Gedung",
    "Example: BUR Building": "Contoh: Gedung BUR",
    "This building name is already registered.": "Nama gedung ini sudah terdaftar.",
    "Edit Building Data": "Edit Data Gedung",
    "Change Building Name": "Ubah Nama Gedung",
    
    "Add PFE Type": "Tambah Type APAR",
    "Example: ABC Powder": "Contoh: ABC Powder",
    "This PFE Type is already registered.": "Type APAR ini sudah terdaftar.",
    "Edit PFE Type": "Edit Type APAR",
    "Change Type Name": "Ubah Nama Type",
    
    "Edit Capacity": "Edit Kapasitas",
    "Example: 3": "Contoh: 3",
    "This capacity is already registered.": "Kapasitas APAR ini sudah terdaftar.",
    "Change Capacity": "Ubah Kapasitas",
    
    "Save": "Simpan",
}

def update_lang(path, trans_dict, is_id=False):
    with open(path, "r", encoding="utf-8") as f:
        lang = json.load(f)
    for k, v in trans_dict.items():
        if is_id:
            lang[k] = v
        else:
            if k not in lang:
                lang[k] = k
    with open(path, "w", encoding="utf-8") as f:
        json.dump(lang, f, indent=4, ensure_ascii=False)

update_lang(id_path, new_translations, True)
update_lang(en_path, new_translations, False)

print("Master data modals updated.")
