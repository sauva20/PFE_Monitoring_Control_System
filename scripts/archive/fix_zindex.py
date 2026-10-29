import os
import glob

def fix_z_index(directory):
    for root, dirs, files in os.walk(directory):
        for file in files:
            if file.endswith('.blade.php'):
                filepath = os.path.join(root, file)
                with open(filepath, 'r', encoding='utf-8') as f:
                    content = f.read()
                
                # Replace z-50 with z-[100] for modal wrappers
                if 'class="relative z-50"' in content:
                    content = content.replace('class="relative z-50"', 'class="relative z-[100]"')
                    with open(filepath, 'w', encoding='utf-8') as f:
                        f.write(content)
                    print(f"Fixed {filepath}")

fix_z_index('resources/views')
