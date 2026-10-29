import os
import re

file_path = "c:/laragon/www/APARB.Braun/resources/views/layouts/app.blade.php"
with open(file_path, "r", encoding="utf-8") as f:
    content = f.read()

replacements = {
    r"'Berhasil!'": "'{{ __('Success!') }}'",
    r"'Oops, Terjadi Kesalahan!'": "'{{ __('Oops, An Error Occurred!') }}'",
    r"Lihat profil Anda": "{{ __('View your profile') }}",
    r">Ganti Sandi<": ">{{ __('Change Password') }}<",
    r"Perbarui kata sandi": "{{ __('Update password') }}",
    r">Ganti PIN<": ">{{ __('Change PIN') }}<",
    r"Cari ID APAR, lokasi, atau status\.\.\.": "{{ __('Search PFE ID, location, or status...') }}",
    r"Tidak ada notifikasi penting\.": "{{ __('No important notifications.') }}",
    r"Tanggung Jawab Utama Gedung": "{{ __('Main Building Responsibility') }}",
    r"Memiliki akses pantau penuh ke semua area\.": "{{ __('Has full monitoring access to all areas.') }}",
    r"Anda belum ditugaskan sebagai PIC untuk gedung manapun\.": "{{ __('You have not been assigned as a PIC for any building.') }}",
    r"Setiap tanggal": "{{ __('Every') }}",
    r"setiap bulannya\.": "{{ __('of the month.') }}",
    r"Keamanan Akun": "{{ __('Account Security') }}",
    r"Logout dari Sistem\?": "{{ __('Logout from System?') }}",
    r"Apakah Anda yakin ingin Logout dari aplikasi PFE Monitoring Control System\?": "{{ __('Are you sure you want to logout from the PFE Monitoring Control System application?') }}",
    r">Ya, Logout<": ">{{ __('Yes, Logout') }}<",
    r">Batal<": ">{{ __('Cancel') }}<",
}

for old, new in replacements.items():
    content = re.sub(old, new, content)

with open(file_path, "w", encoding="utf-8") as f:
    f.write(content)

print("Done app.blade.php")
