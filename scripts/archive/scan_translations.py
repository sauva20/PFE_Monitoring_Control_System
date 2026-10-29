import os
import re
import json

base_dir = "c:/laragon/www/APARB.Braun/resources/views/"
lang_id_path = "c:/laragon/www/APARB.Braun/lang/id.json"
lang_en_path = "c:/laragon/www/APARB.Braun/lang/en.json"

with open(lang_id_path, "r", encoding="utf-8") as f:
    id_lang = json.load(f)
with open(lang_en_path, "r", encoding="utf-8") as f:
    en_lang = json.load(f)

pattern = re.compile(r"__\(\s*(['\"])(.*?)\1\s*\)")

keys_found = set()

for root, dirs, files in os.walk(base_dir):
    for file in files:
        if file.endswith(".blade.php"):
            with open(os.path.join(root, file), "r", encoding="utf-8") as f:
                content = f.read()
                matches = pattern.findall(content)
                for match in matches:
                    keys_found.add(match[1])

# Generate a dictionary for missing keys in ID
missing_id = {}
for key in keys_found:
    if key not in id_lang:
        missing_id[key] = key  # Default translation is the English key itself for now

with open("c:/laragon/www/APARB.Braun/missing_id.json", "w", encoding="utf-8") as f:
    json.dump(missing_id, f, indent=4, ensure_ascii=False)

# Same for EN
missing_en = {}
for key in keys_found:
    if key not in en_lang:
        missing_en[key] = key

with open("c:/laragon/www/APARB.Braun/missing_en.json", "w", encoding="utf-8") as f:
    json.dump(missing_en, f, indent=4, ensure_ascii=False)

print(f"Done. Missing ID: {len(missing_id)}, Missing EN: {len(missing_en)}")
