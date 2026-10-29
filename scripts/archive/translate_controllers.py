import os
import re
import json
import glob

# 1. Update Controllers
controller_dir = "c:/laragon/www/APARB.Braun/app/Http/Controllers"
php_files = glob.glob(os.path.join(controller_dir, "**/*.php"), recursive=True)

strings_found = []

def replace_with_trans(match):
    key = match.group(1)
    msg = match.group(2)
    # Avoid replacing if it's already wrapped, though regex '([^']+)' won't match __('')
    strings_found.append(msg)
    return f"with('{key}', __('{msg}'))"

for file_path in php_files:
    with open(file_path, "r", encoding="utf-8") as f:
        content = f.read()
    
    # Matches with('success', 'Something here')
    # and with('error', 'Something here')
    new_content = re.sub(r"with\('(success|error)', '([^']+)'\)", replace_with_trans, content)
    
    if content != new_content:
        with open(file_path, "w", encoding="utf-8") as f:
            f.write(new_content)
        print(f"Updated {file_path}")

# 2. Add to Dictionaries
id_path = "c:/laragon/www/APARB.Braun/lang/id.json"
en_path = "c:/laragon/www/APARB.Braun/lang/en.json"

translations = {
    "User berhasil ditambahkan! Email setup telah dikirim.": "User added successfully! Setup email has been sent.",
    "Data user berhasil diperbarui!": "User data updated successfully!",
    "User berhasil dihapus!": "User deleted successfully!",
    "Kata sandi berhasil diubah!": "Password changed successfully!",
    "PIN berhasil diubah!": "PIN changed successfully!",
    
    "Data gedung berhasil ditambahkan!": "Building data added successfully!",
    "Data lokasi berhasil ditambahkan!": "Location data added successfully!",
    "Jenis APAR berhasil ditambahkan!": "PFE type added successfully!",
    "Kapasitas APAR berhasil ditambahkan!": "PFE capacity added successfully!",
    "Data APAR berhasil ditambahkan!": "PFE data added successfully!",
    
    "Data gedung berhasil dihapus!": "Building data deleted successfully!",
    "Data lokasi berhasil dihapus!": "Location data deleted successfully!",
    "Jenis APAR berhasil dihapus!": "PFE type deleted successfully!",
    "Kapasitas APAR berhasil dihapus!": "PFE capacity deleted successfully!",
    "Data APAR berhasil dihapus!": "PFE data deleted successfully!",
    
    "Data gedung berhasil diperbarui!": "Building data updated successfully!",
    "Data lokasi berhasil diperbarui!": "Location data updated successfully!",
    "Jenis APAR berhasil diperbarui!": "PFE type updated successfully!",
    "Kapasitas APAR berhasil diperbarui!": "PFE capacity updated successfully!",
    "Data APAR berhasil diperbarui!": "PFE data updated successfully!",
    
    "Jadwal inspeksi berhasil dibuat dan notifikasi email telah dikirim!": "Inspection schedule created successfully and email notification sent!",
    "Jadwal inspeksi berhasil diperbarui!": "Inspection schedule updated successfully!",
    "Jadwal inspeksi berhasil dihapus!": "Inspection schedule deleted successfully!",
    
    "Kata sandi berhasil direset! Silakan masuk.": "Password reset successfully! Please log in.",
    "Login berhasil! Selamat datang.": "Login successful! Welcome.",
    "PIN tidak valid atau tidak ditemukan.": "Invalid or not found PIN."
}

def update_json(path, is_id=False):
    with open(path, "r", encoding="utf-8") as f:
        data = json.load(f)
    
    for indo_str in strings_found:
        if is_id:
            data[indo_str] = indo_str
        else:
            if indo_str in translations:
                data[indo_str] = translations[indo_str]
            else:
                data[indo_str] = indo_str # Fallback
                
    with open(path, "w", encoding="utf-8") as f:
        json.dump(data, f, indent=4, ensure_ascii=False)

update_json(id_path, True)
update_json(en_path, False)

print("Translation dictionaries updated successfully!")
