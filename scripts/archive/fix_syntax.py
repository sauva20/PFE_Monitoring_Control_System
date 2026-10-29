import os

path = 'resources/views/master-data/index.blade.php'
with open(path, 'r', encoding='utf-8') as f:
    content = f.read()

# Remove the backslashes that were accidentally added
content = content.replace(r"__(\'Pilih Gedung terlebih dahulu\')", r"__('Pilih Gedung terlebih dahulu')")

with open(path, 'w', encoding='utf-8') as f:
    f.write(content)
print("Fixed syntax error")
