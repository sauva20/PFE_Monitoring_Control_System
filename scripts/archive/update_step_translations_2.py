import json
import os

en_file = "c:/laragon/www/APARB.Braun/lang/en.json"

if os.path.exists(en_file):
    with open(en_file, "r", encoding="utf-8") as f:
        data = json.load(f)
    
    data["Ada"] = "Yes"
    data["Tidak Ada"] = "No"
    
    with open(en_file, "w", encoding="utf-8") as f:
        json.dump(data, f, indent=4, ensure_ascii=False)

print("Translations updated successfully.")
