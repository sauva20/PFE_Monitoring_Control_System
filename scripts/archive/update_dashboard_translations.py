import json
import os

en_file = "c:/laragon/www/APARB.Braun/lang/en.json"
id_file = "c:/laragon/www/APARB.Braun/lang/id.json"

translations_id = {
    "Today's Statistics": "Statistik Hari Ini"
}

translations_en = {
    "Today's Statistics": "Today's Statistics"
}

for file_path, new_trans in [(id_file, translations_id), (en_file, translations_en)]:
    if os.path.exists(file_path):
        with open(file_path, "r", encoding="utf-8") as f:
            data = json.load(f)
        
        for k, v in new_trans.items():
            if k not in data or data[k] == k:
                data[k] = v
        
        with open(file_path, "w", encoding="utf-8") as f:
            json.dump(data, f, indent=4, ensure_ascii=False)

print("Dashboard translations updated successfully.")
