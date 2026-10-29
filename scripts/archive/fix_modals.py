import os
import re

app_path = 'resources/views/layouts/app.blade.php'
with open(app_path, 'r', encoding='utf-8') as f:
    app_content = f.read()

app_content = app_content.replace('</body>', '    @stack(\'modals\')\n</body>')

with open(app_path, 'w', encoding='utf-8') as f:
    f.write(app_content)

master_path = 'resources/views/master-data/index.blade.php'
with open(master_path, 'r', encoding='utf-8') as f:
    master_content = f.read()

# We need to wrap the modals at the end of the file
# In master-data/index.blade.php, the modals start around <!-- Modals --> or similar.
# Let's check where the modals are.
