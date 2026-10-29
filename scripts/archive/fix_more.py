import re

file_path = 'resources/views/master-data/index.blade.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

replacements = {
    r'(?i)>Master Data APAR<': r">{{ __('PFE Master Data') }}<",
    r'(?i)>Export Data<': r">{{ __('Export Data') }}<"
}

for pattern, repl in replacements.items():
    content = re.sub(pattern, repl, content)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

