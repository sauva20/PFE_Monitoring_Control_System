import os
import re

file_path = "c:/laragon/www/APARB.Braun/resources/views/master-data/index.blade.php"
with open(file_path, "r", encoding="utf-8") as f:
    content = f.read()

replacements = {
    r">Data APAR<": ">{{ __('PFE Data') }}<",
    r"Belum ada data APAR\.": "{{ __('No PFE data available.') }}",
    r">Reference Setting<": ">{{ __('Reference Settings') }}<",
    r"Daftar Penempatan": "{{ __('Placement List') }}",
    r">Tambah Gedung<": ">{{ __('Add Building') }}<",
    r">Tambah Lokasi<": ">{{ __('Add Location') }}<",
    r"Edit Data Gedung": "{{ __('Edit Building Data') }}",
    r"Edit Data APAR": "{{ __('Edit PFE Data') }}",
    r"Nama Gedung": "{{ __('Building Name') }}",
    r"Contoh: Gedung A": "{{ __('Example: Building A') }}",
    r"Simpan Perubahan": "{{ __('Save Changes') }}",
    r"Pencarian tidak ditemukan": "{{ __('No results found') }}",
    r"Ketik untuk menambah lokasi baru": "{{ __('Type to add new location') }}",
    r"Yakin hapus gedung ini\? \{\{ \_\_\('All Locations'\) \}\} di dalamnya juga akan terhapus!": "{{ __('Are you sure you want to delete this building? All locations inside it will also be deleted!') }}",
    r"Yakin hapus lokasi ini\?": "{{ __('Are you sure you want to delete this location?') }}",
    r"Masukkan jumlah": "{{ __('Enter quantity') }}",
    r"Cari\.\.\.": "{{ __('Search...') }}",
    r"Pilih Jenis": "{{ __('Select Type') }}",
    r"Jenis APAR": "{{ __('PFE Type') }}",
    r"Pilih Kapasitas": "{{ __('Select Capacity') }}",
    r"Nama Lokasi": "{{ __('Location Name') }}",
    r"Contoh: Selasar Lantai 1": "{{ __('Example: 1st Floor Corridor') }}",
    r"Pilih Gedung": "{{ __('Select Building') }}",
    r"Gedung": "{{ __('Building') }}", # Be careful with this one, maybe too generic
}

for old, new in replacements.items():
    if old != r"Gedung":
        content = re.sub(old, new, content)

with open(file_path, "w", encoding="utf-8") as f:
    f.write(content)

print("Done index.blade.php")
