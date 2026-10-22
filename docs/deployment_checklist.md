#  Deployment Checklist & Pre-Flight Audit

Secara keseluruhan, kode aplikasi **PFE Monitoring Control System** saat ini sudah stabil. Bug kecil, logika realtime, animasi, dan penyesuaian cetak PDF sudah ditangani. 

Namun, agar aplikasi ini berjalan lancar di server produksi (VPS/Hosting), ada **7 Langkah Krusial** yang *wajib* kamu lakukan. Jika ada satu yang terlewat, kemungkinan besar akan muncul pesan error di sistem.

---

## 1. Konfigurasi `.env` 
File `.env` di server produksi harus disesuaikan dengan benar:
- `APP_ENV=production` (Wajib, agar performa maksimal)
- `APP_DEBUG=false` (Wajib! Jangan biarkan true di produksi, nanti celah keamanan terbuka kalau ada error)
- `APP_URL=https://domain-kamu.com` (Wajib! Kalau ini salah, QR Code yang dicetak nggak akan bisa di-scan karena link-nya nyasar ke `localhost`)
- Konfigurasi `DB_*` disesuaikan dengan database server.
- Konfigurasi `MAIL_*` pastikan menggunakan SMTP yang valid (seperti Gmail App Password atau Mailtrap) karena sistem ini bergantung pada pengiriman email (PIN Inspeksi & Pengingat Rutin).

## 2. Install Dependencies (Backend & Frontend)
Setelah kode di-upload ke server, jalankan perintah ini di terminal server:
```bash
# Install PHP dependencies tanpa paket development
composer install --optimize-autoloader --no-dev

# Install Node modules & Build Frontend CSS/JS
npm install
npm run build
```
> [!WARNING]
> Jangan lupa jalankan `npm run build`! Karena kita menggunakan **Vite** dan **Tailwind CSS**. Kalau tidak di-build, tampilan aplikasinya bakal hancur/berantakan di server.

## 3. Setup Database & Key
Jalankan migrasi untuk membuat tabel-tabel di database server:
```bash
php artisan key:generate
php artisan migrate --force
```
*(Opsional: Jika ada data awal / seeder)*
`php artisan db:seed --force`

## 4. Link Storage (Untuk Foto/Gambar)
Karena sistem ini menyimpan foto hasil inspeksi APAR, kita harus menghubungkan folder `storage` ke `public`.
```bash
php artisan storage:link
```
> [!IMPORTANT]
> Pastikan folder `storage` dan `bootstrap/cache` memiliki hak akses tulis (permissions `775` atau `chmod -R 775 storage bootstrap/cache`) dan `chown` disesuaikan dengan user web server (misal `www-data`).

## 5. Caching (Optimasi Kecepatan)
Agar aplikasi ngebut saat diakses banyak user, aktifkan caching Laravel:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
*(Ingat: Jika kamu mengubah `.env` setelah ini, kamu harus menjalankan ulang `php artisan config:clear`)*

## 6. Setup Cron Job (Pengingat Email Otomatis)
Sistem ini punya fitur ngirim email pengingat kedaluwarsa & jadwal inspeksi bulanan otomatis (berdasarkan `routes/console.php`). Agar ini jalan, **kamu wajib memasang Cron Job** di server.
Buka terminal server dan ketik `crontab -e`, lalu tambahkan baris ini di paling bawah:
```bash
* * * * * cd /path-ke-folder-project-kamu && php artisan schedule:run >> /dev/null 2>&1
```
> [!TIP]
> Ganti `/path-ke-folder-project-kamu` dengan *path* asli di server kamu (misal `/var/www/aparb-ehss`). Laravel akan mengecek setiap menit apakah ada jadwal email yang harus dikirim hari itu jam 07:00 dan 08:00.

## 7. Web Server Configuration
Pastikan Nginx atau Apache di-pointing (*Document Root*) langsung ke folder `public`, bukan ke folder *root* project.
Contoh untuk Nginx:
`root /var/www/aparb-ehss/public;`

---

### Potensi Error Saat Deploy:
1. **QR Code tidak bisa di-scan**: Cek kembali nilai `APP_URL` di `.env`.
2. **Email gagal terkirim / nyangkut loading lama**: Pastikan port SMTP server kamu (biasanya 465 atau 587) tidak diblokir oleh *firewall* penyedia server, dan password SMTP benar.
3. **Tampilan hancur**: Kamu lupa menjalankan `npm run build`.
4. **Gambar inspeksi tidak muncul**: Kamu lupa menjalankan `php artisan storage:link` atau *permissions* foldernya salah.
5. **Email pengingat tidak masuk**: Cron Job belum di-setup di server.
