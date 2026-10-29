import os
import re

file_path = "c:/laragon/www/APARB.Braun/resources/views/inspeksi/mulai.blade.php"
with open(file_path, "r", encoding="utf-8") as f:
    content = f.read()

replacements = {
    r">Mulai Inspeksi - ": ">{{ __('Start Inspection') }} - ",
    r'title="Kembali"': 'title="{{ __(\'Back\') }}"',
    r">Formulir Inspeksi APAR<": ">{{ __('PFE Inspection Form') }}<",
    r"Catat hasil pengecekan fisik dan fungsi komponen APAR": "{{ __('Record physical and functional check results of PFE components') }}",
    r">Jenis<": ">{{ __('Type') }}<",
    r">Kapasitas<": ">{{ __('Capacity') }}<",
    r"Terdapat beberapa kesalahan:": "{{ __('There are some errors:') }}",
    r"Checklist Pemeriksaan": "{{ __('Inspection Checklist') }}",
    r"Jawab setiap poin dengan kondisi aktual APAR di lapangan\.": "{{ __('Answer each point according to actual condition in the field.') }}",
    r"> Ya\n": "> {{ __('Yes') }}\n",
    r"> Tidak\n": "> {{ __('No') }}\n",
    r'placeholder="Tulis keterangan\.\.\."': 'placeholder="{{ __(\'Write notes...\') }}"',
    r"Kesimpulan & Dokumentasi": "{{ __('Conclusion & Documentation') }}",
    r"Status Keseluruhan": "{{ __('Overall Status') }}",
    r"'Layak Pakai \(Good Condition\)'": "'{{ __('Good Condition') }}'",
    r"'Perlu Perbaikan \(Needs Repair\)'": "'{{ __('Needs Repair') }}'",
    r"'Perlu Isi Ulang \(Needs Refill\)'": "'{{ __('Needs Refill') }}'",
    r"'Rusak Total \/ Afkir'": "'{{ __('Broken / Service') }}'",
    r"Foto Kondisi": "{{ __('Condition Photo') }}",
    r"Ambil Foto": "{{ __('Take Photo') }}",
    r"AMBIL FOTO SECARA LANDSCAPE": "{{ __('TAKE PHOTO IN LANDSCAPE') }}",
    r"Ganti Foto": "{{ __('Change Photo') }}",
    r"Catatan Tambahan \(Opsional\)": "{{ __('Additional Notes (Optional)') }}",
    r'placeholder="Tuliskan catatan observasi lainnya secara umum\.\.\."': 'placeholder="{{ __(\'Write other general observation notes...\') }}"',
    r">\n\s*Batal\n": ">\n                    {{ __('Cancel') }}\n",
    r"Selesaikan Inspeksi": "{{ __('Complete Inspection') }}",
    r"Update Data APAR": "{{ __('Update PFE Data') }}",
    r"Nomor APAR": "{{ __('PFE Number') }}",
    r"\(Otomatis\)": "{{ __('(Automatic)') }}",
    r'placeholder="Masukkan nomor urut\.\.\."': 'placeholder="{{ __(\'Enter sequence number...\') }}"',
    r">Gedung<": ">{{ __('Building') }}<",
    r"'Pilih Gedung'": "'{{ __('Select Building') }}'",
    r'placeholder="Cari\.\.\."': 'placeholder="{{ __(\'Search...\') }}"',
    r"Lokasi <span": "{{ __('Location') }} <span",
    r"\(Ketik\)": "{{ __('(Type)') }}",
    r'placeholder="Contoh: Corridor"': 'placeholder="{{ __(\'Example: Corridor\') }}"',
    r">Jenis APAR<": ">{{ __('PFE Type') }}<",
    r"'Pilih Jenis'": "'{{ __('Select Type') }}'",
    r"'Pilih Kapasitas'": "'{{ __('Select Capacity') }}'",
    r">Vendor<": ">{{ __('Vendor') }}<",
    r'placeholder="Nama vendor \(Opsional\)"': 'placeholder="{{ __(\'Vendor name (Optional)\') }}"',
    r">Tanggal Kedaluwarsa<": ">{{ __('Expiration Date') }}<",
    r"Simpan Perubahan": "{{ __('Save Changes') }}"
}

for old, new in replacements.items():
    content = re.sub(old, new, content)

with open(file_path, "w", encoding="utf-8") as f:
    f.write(content)

print("Done mulai.blade.php")
