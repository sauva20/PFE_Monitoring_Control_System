import os
import re

base_dir = "c:/laragon/www/APARB.Braun/resources/views/"

files = {
    "index.blade.php": {
        r"Berhasil!": "{{ __('Success!') }}",
        r"Oops, Terjadi Kesalahan!": "{{ __('Oops, an error occurred!') }}",
        r"\{\{ __\('SISTEM MONITORING APAR'\) \}\}": "{{ __('PFE MONITORING SYSTEM') }}",
        r"\{\{ __\('Masukkan email Anda'\) \}\}": "{{ __('Enter your email') }}",
        r"\{\{ __\('Lupa Password\?'\) \}\}": "{{ __('Forgot Password?') }}",
        r"\{\{ __\('Masuk'\) \}\}": "{{ __('Login') }}"
    },
    "auth/setup-success.blade.php": {
        r"Berhasil - ": "{{ __('Success') }} - ",
        r"SETUP BERHASIL": "{{ __('SETUP SUCCESSFUL') }}",
        r"Kata sandi akun Anda telah berhasil dibuat dan disimpan dengan aman\.": "{{ __('Your account password has been successfully created and safely stored.') }}",
        r">\s*Masuk ke Sistem Sekarang\s*<": ">{{ __('Login to System Now') }}<"
    },
    "auth/forgot-password.blade.php": {
        r"Lupa Kata Sandi - ": "{{ __('Forgot Password') }} - ",
        r"Kembali ke Login": "{{ __('Back to Login') }}",
        r"RESET PASSWORD": "{{ __('RESET PASSWORD') }}",
        r"Masukkan email Anda dan kami akan mengirimkan tautan untuk mengatur ulang kata sandi\.": "{{ __('Enter your email and we will send a link to reset your password.') }}",
        r"Email Terkirim": "{{ __('Email Sent') }}",
        r"Email Terdaftar": "{{ __('Registered Email') }}",
        r'placeholder="Contoh: user@bbraun\.com"': 'placeholder="{{ __(\'Example: user@ehss.id\') }}"',
        r">\s*Kirim Link Reset\s*<": ">{{ __('Send Reset Link') }}<"
    },
    "auth/reset-password.blade.php": {
        r"Reset Kata Sandi - ": "{{ __('Reset Password') }} - ",
        r"Halo, <strong>": "{{ __('Hello,') }} <strong>",
        r"<\/strong>\.<br>Silakan buat kata sandi baru untuk akun Anda\.": "</strong>.<br>{{ __('Please create a new password for your account.') }}",
        r"Email Akun": "{{ __('Account Email') }}",
        r"Password Baru": "{{ __('New Password') }}",
        r'placeholder="Minimal 8 karakter"': 'placeholder="{{ __(\'Minimum 8 characters\') }}"',
        r"Ulangi Password Baru": "{{ __('Repeat New Password') }}",
        r'placeholder="Ulangi password di atas"': 'placeholder="{{ __(\'Repeat the password above\') }}"',
        r">\s*Simpan Password\s*<": ">{{ __('Save Password') }}<"
    },
    "auth/setup-password.blade.php": {
        r"Halo, <strong>": "{{ __('Hello,') }} <strong>",
        r"<\/strong>\.<br>Silakan buat kata sandi untuk akun Anda\.": "</strong>.<br>{{ __('Please create a password for your account.') }}",
        r"PIN Inspeksi \(6 Digit\)": "{{ __('Inspection PIN (6 Digits)') }}",
        r'placeholder="Masukkan PIN dari Email"': 'placeholder="{{ __(\'Enter PIN from Email\') }}"',
        r">\s*Verifikasi PIN\s*<": ">{{ __('Verify PIN') }}<",
        r"PIN Terverifikasi": "{{ __('PIN Verified') }}",
        r"Silakan buat kata sandi baru untuk akun Anda\.": "{{ __('Please create a new password for your account.') }}",
        r"Password Baru": "{{ __('New Password') }}",
        r'placeholder="Minimal 8 karakter"': 'placeholder="{{ __(\'Minimum 8 characters\') }}"',
        r"Ulangi Password Baru": "{{ __('Repeat New Password') }}",
        r'placeholder="Ulangi password di atas"': 'placeholder="{{ __(\'Repeat the password above\') }}"',
        r">\s*Simpan Password\s*<": ">{{ __('Save Password') }}<"
    }
}

for file_rel_path, replacements in files.items():
    file_path = os.path.join(base_dir, file_rel_path)
    if os.path.exists(file_path):
        with open(file_path, "r", encoding="utf-8") as f:
            content = f.read()

        for old, new in replacements.items():
            content = re.sub(old, new, content)

        with open(file_path, "w", encoding="utf-8") as f:
            f.write(content)
        print(f"Done {file_rel_path}")
    else:
        print(f"File not found: {file_rel_path}")
