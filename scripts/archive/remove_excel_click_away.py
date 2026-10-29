import os

path = 'resources/views/components/excel-preview-modal.blade.php'
if os.path.exists(path):
    with open(path, 'r', encoding='utf-8') as f:
        content = f.read()
    
    content = content.replace(' @click.away="showPreviewModal = false"', '')
    
    with open(path, 'w', encoding='utf-8') as f:
        f.write(content)
