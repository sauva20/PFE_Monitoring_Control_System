import os

# 1. users/index.blade.php
users_path = 'resources/views/users/index.blade.php'
with open(users_path, 'r', encoding='utf-8') as f:
    content = f.read()

content = content.replace(
    'class="fixed inset-0 transition-opacity bg-slate-900/40" @click="showModalUser = false"',
    'class="fixed inset-0 transition-opacity bg-slate-900/40"'
)
content = content.replace(
    'class="fixed inset-0 transition-opacity bg-slate-900/40" @click="showEditUser = false"',
    'class="fixed inset-0 transition-opacity bg-slate-900/40"'
)

with open(users_path, 'w', encoding='utf-8') as f:
    f.write(content)

# 2. master-data/index.blade.php
master_path = 'resources/views/master-data/index.blade.php'
with open(master_path, 'r', encoding='utf-8') as f:
    content = f.read()

to_remove = [
    ' @click.away="showModalLokasi = false"',
    ' @click.away="showModalGedung = false"',
    ' @click.away="showModalJenis = false"',
    ' @click.away="showModalKapasitas = false"',
    ' @click.away="if (!$event.target.closest(\'.flatpickr-calendar\')) showModalApar = false"',
    ' @click.away="if (!$event.target.closest(\'.flatpickr-calendar\')) showModalEditApar = false"',
    ' @click.away="showEditGedung = false"',
    ' @click.away="showEditLokasi = false"',
    ' @click.away="showEditJenis = false"',
    ' @click.away="showEditKapasitas = false"',
    ' @click.away="showQrModal = false"'
]

for item in to_remove:
    content = content.replace(item, '')

with open(master_path, 'w', encoding='utf-8') as f:
    f.write(content)

# 3. layouts/app.blade.php
app_path = 'resources/views/layouts/app.blade.php'
with open(app_path, 'r', encoding='utf-8') as f:
    content = f.read()

content = content.replace(
    '<div x-show="open" @click.away="open = false" class="bg-white rounded-2xl shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto flex flex-col custom-scrollbar border border-slate-100">',
    '<div x-show="open" class="bg-white rounded-2xl shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto flex flex-col custom-scrollbar border border-slate-100">'
)

content = content.replace(
    '<div x-show="open" @click.away="open = false" class="bg-white rounded-3xl shadow-xl w-full max-w-sm flex flex-col border border-slate-100 overflow-hidden">',
    '<div x-show="open" class="bg-white rounded-3xl shadow-xl w-full max-w-sm flex flex-col border border-slate-100 overflow-hidden">'
)

with open(app_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Done replacing.")
