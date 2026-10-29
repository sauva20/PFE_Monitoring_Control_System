import json
import sys

def update_lang_file(filepath, updates):
    with open(filepath, 'r', encoding='utf-8') as f:
        data = json.load(f)
    
    for k, v in updates.items():
        data[k] = v
        
    with open(filepath, 'w', encoding='utf-8') as f:
        json.dump(data, f, indent=4, ensure_ascii=False)

id_updates = {
    "Last Refill": "Isi Ulang Terakhir",
    "LOCATION NAME": "NAMA LOKASI",
    "LOCATION": "LOKASI"
}

en_updates = {
    "Last Refill": "Last Refill",
    "LOCATION NAME": "LOCATION NAME",
    "LOCATION": "LOCATION"
}

update_lang_file('lang/id.json', id_updates)
update_lang_file('lang/en.json', en_updates)
print("Updated lang files")
