import os
import re

file_path = "c:/laragon/www/APARB.Braun/resources/views/inspection-schedule/index.blade.php"
with open(file_path, "r", encoding="utf-8") as f:
    content = f.read()

replacements = {
    r">Jadwal Inspeksi<": ">{{ __('Inspection Schedule') }}<",
    r"Kelola dan pantau rutinitas pengecekan APAR": "{{ __('Manage and monitor PFE checking routines') }}",
    r">\s*Buat Jadwal\s*<": ">{{ __('Create Schedule') }}<",
    r"Total Inspeksi \{\{ __\('Month'\) \}\}": "{{ __('Total Inspections') }} {{ __('Month') }}",
    r">Selesai<": ">{{ __('Completed') }}<",
    r">Menunggu<": ">{{ __('Waiting') }}<",
    r">Proses<": ">{{ __('Process') }}<",
    r">Terlewat<": ">{{ __('Missed') }}<",
    r"Progress Inspeksi": "{{ __('Inspection Progress') }}",
    r"Belum ada data APAR atau Gedung\.": "{{ __('No PFE or Building data yet.') }}",
    r"Jadwal Terlewat": "{{ __('Missed Schedule') }}",
    r"Jadwal Hari Ini": "{{ __('Today\\'s Schedule') }}",
    r"Daftar Jadwal Mendatang": "{{ __('Upcoming Schedule List') }}",
    r"Belum ada jadwal inspeksi mendatang\.": "{{ __('No upcoming inspection schedule yet.') }}",
    r"Jadwal Selesai": "{{ __('Completed Schedule') }}",
    r"Hasil Inspeksi APAR": "{{ __('PFE Inspection Results') }}",
    r"Selesai pada ": "{{ __('Completed on') }} ",
    r">Akhir<": ">{{ __('Final') }}<",
    r"Diinspeksi Oleh": "{{ __('Inspected By') }}",
    r'title="Bukan PIC Utama Area Ini"': 'title="{{ __(\'Not Main PIC for this Area\') }}"',
    r"Foto Inspeksi": "{{ __('Inspection Photo') }}",
    r">Checklist<": ">{{ __('Checklist') }}<",
    r">Catatan & Petugas<": ">{{ __('Notes & Officer') }}<",
    r"Kendala: ": "{{ __('Constraint:') }} ",
    r"Petugas Pemeriksa": "{{ __('Checking Officer') }}",
    r"Catatan Tambahan:": "{{ __('Additional Notes:') }}",
    r"Tidak ada catatan khusus\.": "{{ __('No specific notes.') }}",
    r">Buat Jadwal Inspeksi<": ">{{ __('Create Inspection Schedule') }}<",
    r"Tentukan area, tanggal, dan petugas inspeksi\.": "{{ __('Determine area, date, and inspection officer.') }}",
    r">Jenis Jadwal<": ">{{ __('Schedule Type') }}<",
    r"'Inspeksi Rutin \{\{ __\('Month'\) \}\}an'": "'{{ __('Routine Inspection') }}'",
    r"'Inspeksi Khusus \/ Temuan'": "'{{ __('Special Inspection / Findings') }}'",
    r"'Pilih Jenis'": "'{{ __('Select Type') }}'",
    r">Tanggal Inspeksi<": ">{{ __('Inspection Date') }}<",
    r'placeholder="Pilih Tanggal"': 'placeholder="{{ __(\'Select Date\') }}"',
    r">Cakupan Area<": ">{{ __('Location Scope') }}<",
    r">Gedung<": ">{{ __('Building') }}<",
    r">Spesifik<": ">{{ __('Specific') }}<",
    r">Pilih Lokasi<": ">{{ __('Select Location') }}<",
    r"' lokasi terpilih'": "' ' + '{{ __('selected locations') }}'",
    r"'Pilih Lokasi'": "'{{ __('Select Location') }}'",
    r'placeholder="Cari lokasi\.\.\."': 'placeholder="{{ __(\'Search location...\') }}"',
    r">Terjadwal<": ">{{ __('Scheduled') }}<",
    r">Petugas Inspeksi<": ">{{ __('Inspection Officer') }}<",
    r"'Pilih Petugas'": "'{{ __('Select Officer') }}'",
    r'placeholder="Cari petugas\.\.\."': 'placeholder="{{ __(\'Search officer...\') }}"',
    r"Tidak ditemukan\.": "{{ __('Not found.') }}",
    r">Catatan Tambahan \(Opsional\)<": ">{{ __('Additional Notes (Optional)') }}<",
    r'placeholder="Fokuskan pada area\.\.\."': 'placeholder="{{ __(\'Focus on area...\') }}"',
    r">\s*Batal\n\s*<\/button>": ">\n                        {{ __('Cancel') }}\n                    </button>",
    r">\s*Simpan Jadwal\n\s*<\/button>": ">\n                        {{ __('Save Schedule') }}\n                    </button>",
    r">Edit Jadwal Inspeksi<": ">{{ __('Edit Inspection Schedule') }}<",
    r"Perbarui area, tanggal, dan petugas inspeksi\.": "{{ __('Update area, date, and inspection officer.') }}",
    r">\s*Simpan Perubahan\n\s*<\/button>": ">\n                        {{ __('Save Changes') }}\n                    </button>",
    r"'Pilih Gedung'": "'{{ __('Select Building') }}'",
    r'placeholder="Cari gedung\.\.\."': 'placeholder="{{ __(\'Search building...\') }}"',
    r"Belum ada gedung\.": "{{ __('No building yet.') }}",
    r"Belum ada lokasi\.": "{{ __('No location yet.') }}",
}

for old, new in replacements.items():
    content = re.sub(old, new, content)

with open(file_path, "w", encoding="utf-8") as f:
    f.write(content)

print("Done inspection-schedule index.blade.php")
