import json
import os

en_file = "c:/laragon/www/APARB.Braun/lang/en.json"
id_file = "c:/laragon/www/APARB.Braun/lang/id.json"

translations_id = {
    ":day's Statistics": "Statistik :day"
}

translations_en = {
    ":day's Statistics": ":day's Statistics"
}

for file_path, new_trans in [(id_file, translations_id), (en_file, translations_en)]:
    if os.path.exists(file_path):
        with open(file_path, "r", encoding="utf-8") as f:
            data = json.load(f)
        
        for k, v in new_trans.items():
            data[k] = v
        
        with open(file_path, "w", encoding="utf-8") as f:
            json.dump(data, f, indent=4, ensure_ascii=False)

print("Day translations updated successfully.")
