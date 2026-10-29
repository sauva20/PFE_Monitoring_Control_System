import os

views = {
    "c:/laragon/www/APARB.Braun/resources/views/master-data/index.blade.php": {
        "export_route": "master-data.export-excel"
    },
    "c:/laragon/www/APARB.Braun/resources/views/users/index.blade.php": {
        "export_route": "users.export-excel"
    },
    "c:/laragon/www/APARB.Braun/resources/views/reports/index.blade.php": {
        "export_route": "reports.export-excel"
    },
    "c:/laragon/www/APARB.Braun/resources/views/activity-log/index.blade.php": {
        "export_route": "activity-log.export-excel"
    }
}

for view_path, info in views.items():
    if not os.path.exists(view_path):
        continue
        
    with open(view_path, "r", encoding="utf-8") as f:
        content = f.read()
        
    export_route = info["export_route"]
    preview_route = export_route + "-preview"
    
    # Old link pattern:
    # href="{{ route('master-data.export-excel', request()->all()) }}"
    # We replace it with:
    # href="#" @click.prevent="$dispatch('open-excel-preview', { previewUrl: '{{ route('master-data.export-excel-preview', request()->all()) }}', downloadUrl: '{{ route('master-data.export-excel', request()->all()) }}' })"
    
    # Find the exact href string
    search_str = f"href=\"{{{{ route('{export_route}', request()->all()) }}}}\""
    replace_str = f"href=\"#\" @click.prevent=\"$dispatch('open-excel-preview', {{ previewUrl: '{{{{ route('{preview_route}', request()->all()) }}}}', downloadUrl: '{{{{ route('{export_route}', request()->all()) }}}}' }})\""
    
    if search_str in content:
        content = content.replace(search_str, replace_str)
        
        # Add the component at the end of the file before @endsection or at the very end
        if "<x-excel-preview-modal />" not in content:
            if "@endsection" in content:
                content = content.replace("@endsection", "    <x-excel-preview-modal />\n@endsection", 1)
            else:
                content += "\n<x-excel-preview-modal />"
        
        with open(view_path, "w", encoding="utf-8") as f:
            f.write(content)
        print(f"Updated {view_path}")
    else:
        print(f"Could not find exact href in {view_path}")
