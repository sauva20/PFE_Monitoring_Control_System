import sys

file_path = 'resources/views/master-data/index.blade.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

content = content.replace('class="relative z-50"', 'class="relative z-[100]"')

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)
