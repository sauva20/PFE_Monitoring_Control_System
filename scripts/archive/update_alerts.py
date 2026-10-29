import os
import json

# 1. Update app.blade.php
app_blade = "c:/laragon/www/APARB.Braun/resources/views/layouts/app.blade.php"
with open(app_blade, "r", encoding="utf-8") as f:
    app_content = f.read()

app_content = app_content.replace(
    "title: 'Apakah Anda Yakin?'", 
    "title: '{{ __('Are you sure?') }}'"
)
app_content = app_content.replace(
    "text: message || \"Data ini akan dihapus secara permanen!\"", 
    "text: message || \"{{ __('This data will be permanently deleted!') }}\""
)
app_content = app_content.replace(
    "confirmButtonText: 'Ya, Hapus!'", 
    "confirmButtonText: '{{ __('Yes, Delete!') }}'"
)
app_content = app_content.replace(
    "cancelButtonText: 'Batal'", 
    "cancelButtonText: '{{ __('Cancel') }}'"
)

with open(app_blade, "w", encoding="utf-8") as f:
    f.write(app_content)

# 2. Update master-data/index.blade.php
master_blade = "c:/laragon/www/APARB.Braun/resources/views/master-data/index.blade.php"
with open(master_blade, "r", encoding="utf-8") as f:
    master_content = f.read()

master_content = master_content.replace(
    "onsubmit=\"confirmDelete(event, 'Yakin hapus jenis APAR ini?');\"",
    "onsubmit=\"confirmDelete(event, '{{ __('Are you sure you want to delete this PFE type?') }}');\""
)
master_content = master_content.replace(
    "onsubmit=\"confirmDelete(event, 'Yakin hapus kapasitas ini?');\"",
    "onsubmit=\"confirmDelete(event, '{{ __('Are you sure you want to delete this capacity?') }}');\""
)

with open(master_blade, "w", encoding="utf-8") as f:
    f.write(master_content)

# 3. Update dictionaries
id_path = "c:/laragon/www/APARB.Braun/lang/id.json"
en_path = "c:/laragon/www/APARB.Braun/lang/en.json"

new_translations = {
    "Are you sure?": "Apakah Anda Yakin?",
    "This data will be permanently deleted!": "Data ini akan dihapus secara permanen!",
    "Yes, Delete!": "Ya, Hapus!",
    "Cancel": "Batal",
    "Are you sure you want to delete this PFE type?": "Yakin hapus jenis APAR ini?",
    "Are you sure you want to delete this capacity?": "Yakin hapus kapasitas ini?"
}

# Update ID
with open(id_path, "r", encoding="utf-8") as f:
    id_lang = json.load(f)
for k, v in new_translations.items():
    id_lang[k] = v
with open(id_path, "w", encoding="utf-8") as f:
    json.dump(id_lang, f, indent=4, ensure_ascii=False)

# Update EN
with open(en_path, "r", encoding="utf-8") as f:
    en_lang = json.load(f)
for k, v in new_translations.items():
    if k not in en_lang:
        en_lang[k] = k
with open(en_path, "w", encoding="utf-8") as f:
    json.dump(en_lang, f, indent=4, ensure_ascii=False)

print("Alerts updated successfully!")
