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
            
            <p>You have been registered in the <strong>PFE Monitoring Control System</strong> as <strong>{{ $user->role }}</strong>.</p>
            
            @if($user->jadwal_rutin_tanggal)
            <div style="background-color: #f1f5f9; padding: 16px; border-radius: 8px; border-left: 4px solid #009B77; margin: 20px 0;">
                <h3 style="margin-top: 0; color: #009B77; font-size: 16px; margin-bottom: 8px;">Routine Inspection Deadline</h3>
                <p style="margin-bottom: 0;">You have been assigned a routine PFE inspection schedule. Please ensure all your routine inspections are completed <strong>before the {{ $user->jadwal_rutin_tanggal }}th</strong> of every month, as this is your inspection deadline.</p>
            </div>
            @endif
            
            <p>Here is your <strong>Inspection PIN (4 digits)</strong>. This PIN will be used for verification when setting up your password and performing PFE inspections.</p>
            
            <div style="background-color: #fffbeb; padding: 20px; border-radius: 8px; border: 1px solid #fde68a; text-align: center; margin-bottom: 24px;">
                <p style="margin-top: 0; color: #92400e; font-weight: 600; font-size: 14px; letter-spacing: 0.5px;">YOUR INSPECTION PIN</p>
                <h3 style="letter-spacing: 6px; color: #b45309; font-size: 28px; margin: 10px 0;">{{ $user->pin }}</h3>
                <p style="font-size: 13px; color: #92400e; margin-bottom: 0;"><strong>Important:</strong> This PIN can only be used once for the purpose of setting up your password.</p>
            </div>

            <p style="margin-bottom: 24px;">Please click the button below to verify your PIN and set a new password for your account:</p>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $setupUrl }}" style="background-color: #009B77; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block;">Set Password</a>
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
