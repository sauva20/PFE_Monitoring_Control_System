<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>PFE Monitoring Control System Account Registration</title>
</head>
<body style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8fafc; color: #334155; line-height: 1.6; margin: 0; padding: 0;">
    <div style="max-width: 650px; margin: 40px auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); border: 1px solid #e2e8f0; border-top: 5px solid #009B77;">
        
        <div style="background-color: #009B77; color: #ffffff; padding: 24px; text-align: center;">
            <h1 style="margin: 0; font-size: 22px; font-weight: 700; letter-spacing: 0.5px;">Account Registration</h1>
        </div>
        
        <div style="padding: 32px 24px;">
            <p style="font-weight: 600; font-size: 16px; margin-top: 0;">Hello, {{ $user->name }}</p>
            
            <p>Your account has been registered in the <strong>PFE Monitoring Control System</strong> as <strong>{{ $user->role }}</strong>.</p>
            
            @if($user->jadwal_rutin_tanggal)
            <div style="background-color: #f0fdf4; border: 1px solid #bbf7d0; border-left: 4px solid #009B77; padding: 16px 20px; border-radius: 8px; margin: 20px 0;">
                <p style="margin: 0; font-size: 13px; color: #166534; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Routine Inspection Deadline</p>
                <h3 style="margin: 6px 0 0 0; color: #009B77; font-size: 20px; font-weight: 800;">Date {{ $user->jadwal_rutin_tanggal }}</h3>
                <p style="margin: 6px 0 0 0; font-size: 13px; color: #334155;">Your monthly routine inspection deadline is the <strong>{{ $user->jadwal_rutin_tanggal }}th</strong> of every month.</p>
            </div>
            @endif

            @if($user->role === 'Staff')
                @php
                    $lokasiGroups = $user->lokasis ? $user->lokasis->groupBy('gedung_id') : collect();
                    $assignedAreas = collect();

                    if ($user->gedungs) {
                        foreach ($user->gedungs as $g) {
                            $assignedAreas->put($g->id, [
                                'gedung' => $g,
                                'is_all' => true,
                                'lokasis' => collect(),
                            ]);
                        }
                    }

                    foreach ($lokasiGroups as $gId => $lokList) {
                        if ($assignedAreas->has($gId)) {
                            continue;
                        }
                        $parentGedung = $lokList->first()->gedung ?? \App\Models\Gedung::find($gId);
                        if ($parentGedung) {
                            $assignedAreas->put($gId, [
                                'gedung' => $parentGedung,
                                'is_all' => false,
                                'lokasis' => $lokList,
                            ]);
                        }
                    }
                @endphp
                @if($assignedAreas->isNotEmpty())
                <div style="margin: 20px 0;">
                    <p style="font-weight: 700; color: #1e293b; font-size: 14px; margin-bottom: 10px;">
                        Assigned Responsibility Area (PIC):
                    </p>
                    <div style="border: 1px solid #cbd5e1; border-radius: 8px; overflow: hidden; background-color: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        @foreach($assignedAreas as $item)
                            @php
                                $gedung = $item['gedung'];
                                $isAll = $item['is_all'];
                                $lokasis = $item['lokasis'];
                            @endphp
                            <div style="border-bottom: {{ $loop->last ? 'none' : '1px solid #cbd5e1' }};">
                                <div style="background-color: #f1f5f9; padding: 12px 16px; border-bottom: 2px solid #009B77;">
                                    <table style="width: 100%; border-collapse: collapse;">
                                        <tr>
                                            <td style="vertical-align: middle;">
                                                <strong style="color: #0f172a; font-size: 15px;">
                                                    Building {{ $gedung->nama }}
                                                </strong>
                                            </td>
                                            <td style="vertical-align: middle; text-align: right;">
                                                @if($isAll)
                                                    <span style="font-size: 11px; font-weight: 700; color: #047857; background-color: #d1fae5; border: 1px solid #a7f3d0; padding: 3px 10px; border-radius: 4px; display: inline-block;">
                                                        Entire Building (All Rooms)
                                                    </span>
                                                @else
                                                    <span style="font-size: 11px; font-weight: 700; color: #0369a1; background-color: #e0f2fe; border: 1px solid #bae6fd; padding: 3px 10px; border-radius: 4px; display: inline-block;">
                                                        {{ $lokasis->count() }} Specific Rooms
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>

                                <div style="padding: 16px;">
                                    @if($isAll)
                                        <p style="margin: 0 0 12px 0; font-size: 13px; color: #475569;">
                                            You are assigned to perform routine inspections for all PFE units across all floors in <strong>Building {{ $gedung->nama }}</strong>:
                                        </p>
                                    @endif

                                    @if($lokasis->isNotEmpty())
                                        <table style="width: 100%; border-collapse: collapse; font-size: 13px; border: 1px solid #cbd5e1;">
                                            <thead>
                                                <tr style="background-color: #009B77; color: #ffffff;">
                                                    <th style="padding: 10px 8px; border: 1px solid #008264; text-align: center; width: 36px; font-weight: 700; font-size: 12px; text-transform: uppercase;">No</th>
                                                    <th style="padding: 10px 12px; border: 1px solid #008264; text-align: left; font-weight: 700; font-size: 12px; text-transform: uppercase;">Location / Room Name</th>
                                                    <th style="padding: 10px 10px; border: 1px solid #008264; text-align: center; width: 110px; font-weight: 700; font-size: 12px; text-transform: uppercase;">Room No.</th>
                                                    <th style="padding: 10px 10px; border: 1px solid #008264; text-align: center; width: 80px; font-weight: 700; font-size: 12px; text-transform: uppercase;">Floor</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($lokasis as $idx => $lok)
                                                    <tr style="background-color: {{ $idx % 2 === 0 ? '#ffffff' : '#f8fafc' }};">
                                                        <td style="padding: 9px 8px; border: 1px solid #e2e8f0; text-align: center; color: #64748b; font-weight: 600;">{{ $idx + 1 }}</td>
                                                        <td style="padding: 9px 12px; border: 1px solid #e2e8f0; color: #1e293b; font-weight: 600;">
                                                            {{ $lok->nama }}
                                                        </td>
                                                        <td style="padding: 9px 10px; border: 1px solid #e2e8f0; text-align: center; font-family: monospace; font-weight: 700; color: {{ $lok->nomor_ruangan ? '#009B77' : '#94a3b8' }};">
                                                            {{ $lok->nomor_ruangan ?: '-' }}
                                                        </td>
                                                        <td style="padding: 9px 10px; border: 1px solid #e2e8f0; text-align: center; color: #475569; font-weight: 600;">
                                                            {{ $lok->lantai ? 'Floor '.$lok->lantai : '-' }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            @elseif($user->role === 'EHSS')
                <div style="background-color: #f8fafc; padding: 14px 18px; border-radius: 8px; border: 1px solid #e2e8f0; margin: 20px 0;">
                    <p style="margin: 0; font-size: 14px; color: #334155;"><strong>Area Access:</strong> All Buildings & Facility Areas (EHSS Administrator)</p>
                </div>
            @endif
            
            <p>Here is your <strong>Account Security PIN (4 digits)</strong>. This PIN is required to verify your identity when setting up your password for the first time, and will also serve as your authorization PIN for system operations and PFE field inspections:</p>
            
            <div style="background-color: #fffbeb; padding: 20px; border-radius: 8px; border: 1px solid #fde68a; text-align: center; margin-bottom: 24px;">
                <p style="margin-top: 0; color: #92400e; font-weight: 700; font-size: 13px; letter-spacing: 1px; text-transform: uppercase;">YOUR SECURITY PIN</p>
                @php
                    $displayPin = $pin ?? ($user->pin && !str_starts_with($user->pin, '$2y$') ? $user->pin : null);
                @endphp
                @if($displayPin)
                    <h3 style="letter-spacing: 10px; color: #b45309; font-size: 32px; font-weight: 800; margin: 12px 0;">{{ $displayPin }}</h3>
                @else
                    <p style="font-size: 14px; color: #b45309; font-weight: 700; margin: 10px 0;">Use your personal 4-digit Security PIN</p>
                @endif
                <p style="font-size: 13px; color: #92400e; margin-bottom: 0;"><strong>Important:</strong> Keep this PIN secure. You will need it to verify your account setup below.</p>
            </div>

            <p style="margin-bottom: 24px;">Please click the button below to verify your PIN and set a new password for your account:</p>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $setupUrl }}" style="background-color: #009B77; color: #ffffff; padding: 12px 28px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 14px; display: inline-block;">Set Up Password</a>
            </div>
            
            <div style="margin-top: 24px; padding-top: 20px; border-top: 1px dashed #cbd5e1;">
                <p style="font-size: 13px; color: #64748b; margin-bottom: 5px;">If the button above does not work, you can also copy and paste the following URL into your browser:</p>
                <a href="{{ $setupUrl }}" style="color: #009B77; word-break: break-all; font-size: 13px;">{{ $setupUrl }}</a>
            </div>
        </div>
        
        <div style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 20px 24px; text-align: center; font-size: 13px; color: #64748b;">
            <p style="margin: 0;">This email is sent automatically by the PFE Monitoring Control System — EHSS. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
