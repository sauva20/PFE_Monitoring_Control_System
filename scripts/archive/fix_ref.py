import os
filepath = 'resources/views/master-data/index.blade.php'
with open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

content = content.replace('>                 Reference Setting', '>                 {{ __(\'Reference Settings\') }}')

with open(filepath, 'w', encoding='utf-8') as f:
    f.write(content)
