import json

id_path = "c:/laragon/www/APARB.Braun/lang/id.json"
en_path = "c:/laragon/www/APARB.Braun/lang/en.json"

new_translations_id = {
    "Excel Export Preview": "Pratinjau Ekspor Excel",
    "This is how your data will look when exported.": "Beginilah tampilan data Anda saat diekspor.",
    "Loading preview data...": "Memuat data pratinjau...",
    "No data available for export": "Tidak ada data yang tersedia untuk diekspor",
    "Cancel": "Batal",
    "Download Excel": "Unduh Excel"
}

new_translations_en = {
    "Excel Export Preview": "Excel Export Preview",
    "This is how your data will look when exported.": "This is how your data will look when exported.",
    "Loading preview data...": "Loading preview data...",
    "No data available for export": "No data available for export",
    "Cancel": "Cancel",
    "Download Excel": "Download Excel"
}

def update_json(path, trans):
    with open(path, "r", encoding="utf-8") as f:
        data = json.load(f)
    for k, v in trans.items():
        data[k] = v
    with open(path, "w", encoding="utf-8") as f:
        json.dump(data, f, indent=4, ensure_ascii=False)

update_json(id_path, new_translations_id)
update_json(en_path, new_translations_en)

print("Preview translations added.")
