import json

id_path = "c:/laragon/www/APARB.Braun/lang/id.json"
en_path = "c:/laragon/www/APARB.Braun/lang/en.json"

new_translations_id = {
    "Success!": "Sukses!",
    "Oops, An Error Occurred!": "Ups, Terjadi Kesalahan!"
}

new_translations_en = {
    "Success!": "Success!",
    "Oops, An Error Occurred!": "Oops, An Error Occurred!"
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

print("Toast translations added.")
