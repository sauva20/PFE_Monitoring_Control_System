<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Password Reset - PFE Monitoring Control System</title>
</head>
<body style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8fafc; color: #334155; line-height: 1.6; margin: 0; padding: 0;">
    <div style="max-width: 650px; margin: 40px auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); border: 1px solid #e2e8f0; border-top: 5px solid #009B77;">
        
        <div style="background-color: #009B77; color: #ffffff; padding: 24px; text-align: center;">
            <h1 style="margin: 0; font-size: 22px; font-weight: 700; letter-spacing: 0.5px;">Password Reset</h1>
        </div>
        
        <div style="padding: 32px 24px;">
            <p style="font-weight: 600; font-size: 16px; margin-top: 0;">Hello, {{ $user->name }}</p>
            
            <p>You are receiving this email because we received a password reset request for your account in the <strong>PFE Monitoring Control System</strong>.</p>
            
            <p style="margin-bottom: 24px;">Please click the button below to set a new password:</p>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $resetUrl }}" style="background-color: #009B77; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block;">Reset Password</a>
            </div>
            
            <div style="background-color: #fef2f2; padding: 16px; border-radius: 8px; border-left: 4px solid #ef4444; margin: 20px 0;">
                <p style="color: #b91c1c; font-size: 14px; margin: 0;">This link will expire in <strong>60 minutes</strong>.</p>
            </div>

            <p>If you did not request a password reset, please ignore this email and your account will remain secure.</p>
            
            <div style="margin-top: 24px; padding-top: 20px; border-top: 1px dashed #cbd5e1;">
                <p style="font-size: 13px; color: #64748b; margin-bottom: 5px;">If the button above does not work, you can also copy and paste the following URL into your browser:</p>
                <a href="{{ $resetUrl }}" style="color: #009B77; word-break: break-all; font-size: 13px;">{{ $resetUrl }}</a>
            </div>
        </div>
        
        <div style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 20px 24px; text-align: center; font-size: 13px; color: #64748b;">
            <p style="margin: 0;">This email is sent automatically by the PFE Monitoring Control System — EHSS. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
