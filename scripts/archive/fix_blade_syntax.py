import os

files_to_fix = {
    "c:/laragon/www/APARB.Braun/resources/views/reports/pdf.blade.php": [
        (
            r"if (!empty($gedungName) && $gedungName !== '{{ __('All Buildings') }}') $activeFilters[] = $gedungName;",
            r"if (!empty($gedungName) && $gedungName !== __('All Buildings')) $activeFilters[] = $gedungName;"
        ),
        (
            r"if ($status === 'sudah') $activeFilters[] = '{{ __('Inspected') }}';",
            r"if ($status === 'sudah') $activeFilters[] = __('Inspected');"
        ),
        (
            r"elseif ($status === 'belum') $activeFilters[] = '{{ __('Uninspected') }}';",
            r"elseif ($status === 'belum') $activeFilters[] = __('Uninspected');"
        ),
        (
            r"@if($row['status'] == '{{ __('Inspected') }}')",
            r"@if($row['status'] == __('Inspected'))"
        )
    ],
    "c:/laragon/www/APARB.Braun/resources/views/reports/index.blade.php": [
        (
            r"@if($row['status'] == '{{ __('Inspected') }}')",
            r"@if($row['status'] == __('Inspected'))"
        )
    ],
    "c:/laragon/www/APARB.Braun/resources/views/inspection-schedule/partials/jadwal-card.blade.php": [
        (
            r"$dateText = '{{ __('TODAY') }} - ' . $dateText;",
            r"$dateText = __('TODAY') . ' - ' . $dateText;"
        ),
        (
            r"$dateText = '{{ __('MISSED') }} - ' . $dateText;",
            r"$dateText = __('MISSED') . ' - ' . $dateText;"
        ),
        (
            r"{{ $jadwal->gedung->nama ?? '{{ __('Deleted Building') }}' }}",
            r"{{ $jadwal->gedung->nama ?? __('Deleted Building') }}"
        ),
        (
            r"{{ $jadwal->lokasi->nama ?? '{{ __('Deleted Location') }}' }}",
            r"{{ $jadwal->lokasi->nama ?? __('Deleted Location') }}"
        ),
        (
            r"{{ $jadwal->user ? $jadwal->user->name : '{{ __('Free / Anyone') }}' }}",
            r"{{ $jadwal->user ? $jadwal->user->name : __('Free / Anyone') }}"
        )
    ],
    "c:/laragon/www/APARB.Braun/resources/views/inspection-schedule/index.blade.php": [
        (
            r"'{{ __('Status') }}' => $j->{{ __('Status') }},",
            r"'status' => $j->status,"
        ),
        (
            r"'{{ __('Status') }}' => $inspeksi->{{ __('Status') }},",
            r"'status' => $inspeksi->status,"
        )
    ],
    "c:/laragon/www/APARB.Braun/resources/views/activity-log/pdf.blade.php": [
        (
            r"{{ $log->causer ? $log->causer->name : '{{ __('System') }}' }}",
            r"{{ $log->causer ? $log->causer->name : __('System') }}"
        )
    ],
    "c:/laragon/www/APARB.Braun/resources/views/activity-log/index.blade.php": [
        (
            r"{{ $log->causer ? $log->causer->name : '{{ __('System') }}' }}",
            r"{{ $log->causer ? $log->causer->name : __('System') }}"
        )
    ]
}

for file_path, replacements in files_to_fix.items():
    if os.path.exists(file_path):
        with open(file_path, "r", encoding="utf-8") as f:
            content = f.read()
        
        for old_str, new_str in replacements:
            content = content.replace(old_str, new_str)
            
        with open(file_path, "w", encoding="utf-8") as f:
            f.write(content)
        print(f"Fixed {file_path}")
    else:
        print(f"File not found: {file_path}")
