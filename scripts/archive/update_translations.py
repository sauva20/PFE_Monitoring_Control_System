import json
import os

id_path = "c:/laragon/www/APARB.Braun/lang/id.json"
en_path = "c:/laragon/www/APARB.Braun/lang/en.json"

with open(id_path, "r", encoding="utf-8") as f:
    id_lang = json.load(f)

with open(en_path, "r", encoding="utf-8") as f:
    en_lang = json.load(f)

translations_to_add = {
    "Physical Inspection": {"id": "Pemeriksaan Fisik", "en": "Physical Inspection"},
    "Function Check": {"id": "Pemeriksaan Fungsi", "en": "Function Check"},
    "Present": {"id": "Ada", "en": "Present"},
    "Not Present": {"id": "Tidak Ada", "en": "Not Present"},
    "Yes": {"id": "Ya", "en": "Yes"},
    "No": {"id": "Tidak", "en": "No"},
    "Notes:": {"id": "Keterangan:", "en": "Notes:"}
}

for k, v in translations_to_add.items():
    id_lang[k] = v["id"]
    en_lang[k] = v["en"]

with open(id_path, "w", encoding="utf-8") as f:
    json.dump(id_lang, f, indent=4, ensure_ascii=False)

with open(en_path, "w", encoding="utf-8") as f:
    json.dump(en_lang, f, indent=4, ensure_ascii=False)

print("Translations updated successfully.")
