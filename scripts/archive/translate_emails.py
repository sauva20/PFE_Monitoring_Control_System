import os

files = {
    'new-user.blade.php': [
        ('Pendaftaran Akun PFE Monitoring Control System', 'PFE Monitoring Control System Account Registration'),
        ('Halo, {{ $user->name }}', 'Hello, {{ $user->name }}'),
        ('Anda telah didaftarkan ke dalam', 'You have been registered in the'),
        ('sebagai', 'as'),
        ('Berikut adalah <strong>PIN Inspeksi (6 digit)</strong> Anda. PIN ini akan digunakan sebagai verifikasi saat mengatur kata sandi dan juga saat melakukan inspeksi APAR.', 'Here is your <strong>Inspection PIN (6 digits)</strong>. This PIN will be used for verification when setting up your password and performing PFE inspections.'),
        ('<strong>Penting:</strong> PIN ini hanya dapat digunakan satu kali untuk keperluan *setup* password (kata sandi) Anda.', '<strong>Important:</strong> This PIN can only be used once for the purpose of setting up your password.'),
        ('Silakan klik tombol di bawah ini untuk memverifikasi PIN Anda dan membuat kata sandi baru untuk akun Anda:', 'Please click the button below to verify your PIN and set a new password for your account:'),
        ('Buat Kata Sandi', 'Set Password'),
        ('Jika tombol di atas tidak berfungsi, Anda juga dapat menyalin dan menempelkan URL berikut ke browser Anda:<br>', 'If the button above does not work, you can also copy and paste the following URL into your browser:<br>'),
        ('Terima kasih,<br>Tim Administrator PFE Monitoring Control System', 'Thank you,<br>PFE Monitoring Control System Administrator Team')
    ],
    'reset-password.blade.php': [
        ('Reset Kata Sandi - PFE Monitoring Control System', 'Password Reset - PFE Monitoring Control System'),
        ('Halo, {{ $user->name }}', 'Hello, {{ $user->name }}'),
        ('Anda menerima email ini karena kami menerima permintaan reset kata sandi untuk akun Anda di <strong>PFE Monitoring Control System</strong>.', 'You are receiving this email because we received a password reset request for your account in the <strong>PFE Monitoring Control System</strong>.'),
        ('Silakan klik tombol di bawah ini untuk membuat kata sandi baru:', 'Please click the button below to set a new password:'),
        ('Reset Kata Sandi', 'Reset Password'),
        ('Tautan ini akan kedaluwarsa dalam <strong>60 menit</strong>.', 'This link will expire in <strong>60 minutes</strong>.'),
        ('Jika Anda tidak pernah meminta reset kata sandi, abaikan email ini dan akun Anda akan tetap aman.', 'If you did not request a password reset, please ignore this email and your account will remain secure.'),
        ('Jika tombol di atas tidak berfungsi, Anda juga dapat menyalin dan menempelkan URL berikut ke browser Anda:<br>', 'If the button above does not work, you can also copy and paste the following URL into your browser:<br>'),
        ('Terima kasih,<br>Tim Administrator PFE Monitoring Control System', 'Thank you,<br>PFE Monitoring Control System Administrator Team')
    ],
    'inspection_assigned.blade.php': [
        ('Jadwal Inspeksi APAR', 'PFE Inspection Schedule'),
        ('Halo, {{ $user->name }}', 'Hello, {{ $user->name }}'),
        ('Anda telah ditugaskan untuk melakukan inspeksi APAR pada jadwal berikut:', 'You have been assigned to perform a PFE inspection on the following schedule:'),
        ('<strong>Tanggal:</strong>', '<strong>Date:</strong>'),
        ('<strong>Jenis Jadwal:</strong>', '<strong>Schedule Type:</strong>'),
        ('Berikut adalah daftar APAR yang harus diinspeksi:', 'Here is the list of PFEs to be inspected:'),
        ('>No<', '>No<'),
        ('>Kode APAR<', '>PFE ID<'),
        ('>Gedung<', '>Building<'),
        ('>Lokasi<', '>Location<'),
        ('Tidak ada data APAR di area tersebut.', 'No PFE data in this area.'),
        ('Untuk memulai inspeksi, silakan datangi lokasi APAR dan scan QR Code yang terdapat pada APAR tersebut.', 'To start the inspection, please go to the PFE location and scan the QR Code attached to the PFE.'),
        ('<strong>PIN INSPEKSI ANDA:</strong>', '<strong>YOUR INSPECTION PIN:</strong>'),
        ('Gunakan PIN ini untuk verifikasi saat Anda menekan tombol \'Inspeksi\' setelah melakukan scan QR Code APAR.', 'Use this PIN for verification when clicking the \'Inspect\' button after scanning the PFE QR Code.'),
        ('Notes :<br>', 'Notes:<br>'),
        ('Terima kasih,<br>', 'Thank you,<br>')
    ],
    'apar-expiry.blade.php': [
        ('Peringatan Kedaluwarsa APAR</title>', 'PFE Expiry Warning</title>'),
        ('Peringatan Kedaluwarsa APAR (H-30)', 'PFE Expiry Warning (30 Days)'),
        ('Ada APAR yang akan kedaluwarsa pada tanggal {{ $targetDate }}', 'There are PFEs that will expire on {{ $targetDate }}'),
        ('Halo{{ $recipientName ? \' \' . $recipientName : \'\' }},', 'Hello{{ $recipientName ? \' \' . $recipientName : \'\' }},'),
        ('Email ini adalah pengingat otomatis bahwa ada <strong>{{ $apars->count() }} Alat Pemadam Api Ringan (APAR)</strong> di area tanggung jawab Anda yang akan segera habis masa berlakunya dalam waktu 30 hari.', 'This email is an automated reminder that there are <strong>{{ $apars->count() }} Portable Fire Extinguishers (PFE)</strong> in your responsible area that will expire in 30 days.'),
        ('>ID APAR<', '>PFE ID<'),
        ('>Gedung<', '>Building<'),
        ('>Titik Lokasi<', '>Location Point<'),
        ('>Jenis<', '>Type<'),
        ('Mohon segera lakukan koordinasi untuk pengecekan, pengisian ulang (refill), atau penggantian APAR tersebut sebelum tanggal kedaluwarsa demi menjaga standar keselamatan dan keamanan.', 'Please coordinate immediately for checking, refilling, or replacing these PFEs before the expiry date to maintain safety and security standards.'),
        ('Lihat Data APAR di Sistem', 'View PFE Data in System'),
        ('Email ini dikirim secara otomatis oleh PFE Monitoring Control System (EHSS). Mohon tidak membalas email ini.', 'This email is sent automatically by the PFE Monitoring Control System (EHSS). Please do not reply to this email.')
    ],
    'inspection-reminder.blade.php': [
        ('Pengingat Inspeksi APAR</title>', 'PFE Inspection Reminder</title>'),
        ('Pengingat Inspeksi APAR</h1>', 'PFE Inspection Reminder</h1>'),
        ('Halo, <strong>{{ $user->name }}</strong>', 'Hello, <strong>{{ $user->name }}</strong>'),
        ('Ini adalah pengingat otomatis dari sistem APAR Monitoring EHSS.', 'This is an automated reminder from the EHSS PFE Monitoring Control System.'),
        ('Anda memiliki jadwal <strong>{{ $jenis }}</strong> yang akan jatuh pada tanggal:', 'You have a <strong>{{ $jenis }}</strong> schedule that will fall on:'),
        ('Mohon persiapkan waktu Anda untuk melakukan inspeksi rutin pada seluruh gedung yang menjadi tanggung jawab Anda:', 'Please prepare your time to perform routine inspections on all buildings under your responsibility:'),
        ('Gedung {{ $gedung->nama }}', 'Building {{ $gedung->nama }}'),
        ('<strong>PIN Inspeksi Anda:</strong>', '<strong>Your Inspection PIN:</strong>'),
        ('Gunakan PIN ini untuk memverifikasi inspeksi di aplikasi.', 'Use this PIN to verify inspections in the application.'),
        ('Anda dapat login ke sistem untuk melihat detail lebih lanjut.', 'You can log in to the system to view further details.'),
        ('Buka Sistem', 'Open System'),
        ('Email ini dikirim secara otomatis oleh PFE Monitoring Control System EHSS.<br>', 'This email is sent automatically by the PFE Monitoring Control System EHSS.<br>'),
        ('Mohon tidak membalas email ini.', 'Please do not reply to this email.')
    ]
}

base_path = "c:/laragon/www/APARB.Braun/resources/views/emails/"

for filename, translations in files.items():
    filepath = os.path.join(base_path, filename)
    if os.path.exists(filepath):
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
        
        for search, replace in translations:
            content = content.replace(search, replace)
            
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(content)
        print(f"Translated {filename}")

