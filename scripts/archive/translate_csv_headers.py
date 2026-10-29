import os
import json

files_to_modify = {
    "c:/laragon/www/APARB.Braun/app/Http/Controllers/UserController.php": {
        "old": "$columns = ['No', 'User ID', 'Nama', 'Email', 'Gedung', 'Role', 'Jadwal Inspeksi'];",
        "new": "$columns = [__('No'), __('User ID'), __('Name'), __('Email'), __('Building'), __('Role'), __('Inspection Schedule')];"
    },
    "c:/laragon/www/APARB.Braun/app/Http/Controllers/ReportController.php": {
        "old": "$columns = ['No', 'ID APAR', 'Gedung', 'Lokasi', 'Jenis', 'Kapasitas', 'Status Inspeksi', 'Tanggal Inspeksi', 'Inspektor', 'Kondisi', 'Catatan Tambahan'];",
        "new": "$columns = [__('No'), __('PFE ID'), __('Building'), __('Location'), __('Type'), __('Capacity'), __('Inspection Status'), __('Inspection Date'), __('Inspector'), __('Condition'), __('Additional Notes')];"
    },
    "c:/laragon/www/APARB.Braun/app/Http/Controllers/MasterDataController.php": {
        "old": "$columns = ['No', 'ID APAR', 'Lokasi', 'Gedung', 'Jenis', 'Kapasitas', 'Kelas Kebakaran', 'Tgl Kedaluwarsa', 'Qty', 'Vendor', 'PIC', 'Terakhir Inspeksi'];",
        "new": "$columns = [__('No'), __('PFE ID'), __('Location'), __('Building'), __('Type'), __('Capacity'), __('Fire Class'), __('Expiry Date'), __('Qty'), __('Vendor'), __('PIC'), __('Last Inspection')];"
    },
    "c:/laragon/www/APARB.Braun/app/Http/Controllers/ActivityLogController.php": {
        "old": "$columns = ['Waktu', 'Pengguna', 'Aksi', 'Modul', 'Keterangan'];",
        "new": "$columns = [__('Time'), __('User'), __('Action'), __('Module'), __('Description')];"
    }
}

for file_path, replace_info in files_to_modify.items():
    if os.path.exists(file_path):
        with open(file_path, "r", encoding="utf-8") as f:
            content = f.read()
        
        content = content.replace(replace_info["old"], replace_info["new"])
        
        with open(file_path, "w", encoding="utf-8") as f:
            f.write(content)
        print(f"Updated {file_path}")

# Update dictionaries
new_translations = {
    "No": "No",
    "User ID": "User ID",
    "Name": "Nama",
    "Email": "Email",
    "Building": "Gedung",
    "Role": "Role",
    "Inspection Schedule": "Jadwal Inspeksi",
    "PFE ID": "ID APAR",
    "Location": "Lokasi",
    "Type": "Jenis",
    "Capacity": "Kapasitas",
    "Inspection Status": "Status Inspeksi",
    "Inspection Date": "Tanggal Inspeksi",
    "Inspector": "Inspektor",
    "Condition": "Kondisi",
    "Additional Notes": "Catatan Tambahan",
    "Fire Class": "Kelas Kebakaran",
    "Expiry Date": "Tgl Kedaluwarsa",
    "Qty": "Qty",
    "Vendor": "Vendor",
    "PIC": "PIC",
    "Last Inspection": "Terakhir Inspeksi",
    "Time": "Waktu",
    "User": "Pengguna",
    "Action": "Aksi",
    "Module": "Modul",
    "Description": "Keterangan"
}

id_path = "c:/laragon/www/APARB.Braun/lang/id.json"
en_path = "c:/laragon/www/APARB.Braun/lang/en.json"

def update_json(path, is_id=False):
    with open(path, "r", encoding="utf-8") as f:
        data = json.load(f)
    for k, v in new_translations.items():
        if is_id:
            data[k] = v
        else:
            if k not in data:
                data[k] = k
    with open(path, "w", encoding="utf-8") as f:
        json.dump(data, f, indent=4, ensure_ascii=False)

update_json(id_path, True)
update_json(en_path, False)

print("Dictionaries updated.")
