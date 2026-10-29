import os
import re

base_dir = "c:/laragon/www/APARB.Braun/resources/views/"

for root, dirs, files in os.walk(base_dir):
    for file in files:
        if file.endswith(".blade.php"):
            file_path = os.path.join(root, file)
            with open(file_path, "r", encoding="utf-8") as f:
                content = f.read()
            
            # Find all {{ ... }} blocks
            # We want to catch {{ ... {{ ... }} ... }}
            # Instead of a complex regex, we can just look for "{{ __('" inside "{{ "
            
            # We will use regex to find instances where {{ ... '{{ __('...') }}' ... }} exists and replace it.
            
            # Fix reports/index.blade.php Line 74
            content = content.replace(
                r"'{{ request('gedung_id') ? addslashes($gedungs->firstWhere('id', request('gedung_id'))->nama ?? '{{ __('All Buildings') }}') : '{{ __('All Buildings') }}' }}'",
                r"'{{ request('gedung_id') ? addslashes($gedungs->firstWhere('id', request('gedung_id'))->nama ?? __('All Buildings')) : __('All Buildings') }}'"
            )
            
            # Fix reports/index.blade.php Line 99
            content = content.replace(
                r"'{{ $status == 'sudah' ? '{{ __('Inspected') }}' : ($status == 'belum' ? '{{ __('Uninspected') }}' : '{{ __('All Statuses') }}') }}'",
                r"'{{ $status == 'sudah' ? __('Inspected') : ($status == 'belum' ? __('Uninspected') : __('All Statuses')) }}'"
            )
            
            with open(file_path, "w", encoding="utf-8") as f:
                f.write(content)
print("Done fixing nested blade")
