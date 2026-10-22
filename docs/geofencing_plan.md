# Rencana Eksekusi: Geofencing Radius 7 KM untuk Akses Sistem

Dokumen ini berisi rancangan teknis untuk mengamankan akses ke sistem PFE Monitoring (APAR) agar hanya dapat dibuka ketika pengguna berada dalam radius maksimal 7 KM dari koordinat utama (Pabrik).

## 1. Persiapan Data (Konfigurasi)
Kita akan menyimpan koordinat pusat perusahaan (Latitude & Longitude) dan batas radius ke dalam file `.env` agar mudah diubah tanpa membongkar kode.

**Tambahan di `.env`:**
```env
# Koordinat Pusat Fasilitas / Pabrik EHSS (Contoh: Jakarta/Karawang)
COMPANY_LATITUDE=-6.2088
COMPANY_LONGITUDE=106.8456
# Batas radius dalam satuan kilometer
COMPANY_MAX_RADIUS_KM=7
```

## 2. Pembuatan Middleware (Backend)
Kita akan membuat middleware khusus, misalnya `CheckGeofence.php`. Middleware ini tidak akan langsung memblokir, melainkan mengecek apakah di dalam *session* atau *request* sudah ada informasi lokasi yang valid.

**Konsep Logika (Haversine Formula):**
```php
function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371; // Radius Bumi dalam KM
    // ... Kalkulasi matematika jarak antara dua titik koordinat ...
    return $distanceInKm;
}
```

## 3. Modifikasi Frontend (Halaman Scan / Login)
Kita tidak bisa mendapatkan lokasi tanpa persetujuan (izin) dari perangkat pengguna. Oleh karena itu, sebelum mereka dapat melihat formulir login atau formulir inspeksi, kita akan memicu permintaan lokasi GPS.

**Konsep JavaScript (HTML5 Geolocation API):**
```javascript
if ("geolocation" in navigator) {
    navigator.geolocation.getCurrentPosition(function(position) {
        let lat = position.coords.latitude;
        let lng = position.coords.longitude;
        
        // Kirim (POST) kordinat ini ke server via AJAX/Fetch
        // Jika server merespon "OK (Dalam 7 KM)", tampilkan halaman.
        // Jika server merespon "Ditolak", tampilkan peringatan.
    }, function(error) {
        // Jika user memblokir akses lokasi
        alert("Anda harus mengizinkan akses lokasi untuk membuka sistem ini.");
    });
} else {
    alert("Perangkat Anda tidak mendukung fitur lokasi GPS.");
}
```

## 4. Keamanan Lanjutan (Anti-Spoofing)
- Jika perangkat menolak GPS, akses **wajib** diblokir.
- (Opsional) Menggunakan kombinasi pengecekan GPS *dan* validasi *User-Agent* agar tidak mudah dipalsukan menggunakan *Fake GPS* sembarangan.

## 5. Penyesuaian Alur Sesi & Geofencing Lanjutan
- **Bypass PIN untuk Scan Berkelanjutan**: Pengguna yang sudah login via PIN pada APAR pertama tidak akan di-logout saat membuka halaman scan APAR berikutnya. Dengan ini, mereka tidak perlu memasukkan PIN berulang kali selama sesi masih aktif.
- **Auto-Logout saat Keluar Radius**: Pengecekan lokasi dilakukan pada setiap pemuatan halaman baru (misalnya saat scan APAR berikutnya atau submit form). Jika pada pengecekan tersebut pengguna terdeteksi berada di luar radius 7 KM, maka akses akan diblokir dan sistem akan **otomatis melakukan logout** (menghapus sesi), sehingga pengguna harus login kembali di dalam area pabrik.

## 6. Pengecualian Akses (Route-Based Exception)
Untuk memastikan Admin, Manager, atau PIC yang sedang **Work From Home (WFH)** atau bertugas di luar kota tetap dapat melakukan pemantauan, maka *geofencing* (radius 7 KM) **hanya akan diberlakukan pada proses inspeksi lapangan** (rute `scan.*` dan `inspeksi.*`). 
Sementara itu, akses ke **Dashboard Sistem** (rute `admin.*`, laporan, dll) dan halaman login utama akan dibebaskan dari pembatasan lokasi, sehingga pemantauan dapat dilakukan dari mana saja.

## 7. Bukti Fisik Otentik (Auto-Watermark Foto)
Untuk menutup celah di mana pengguna bisa menggunakan "foto ulang" atau mencurangi batas lokasi pada tahap submit inspeksi, sistem akan mencetak informasi secara permanen langsung ke dalam file foto APAR.
- **Library Pendukung**: Menggunakan package `Intervention Image` di Laravel.
- **Data yang Dicetak**: Tanggal & Jam (Timestamp), Koordinat Lokasi (Latitude & Longitude), serta Nama Petugas.
- **Posisi Watermark**: Akan ditempatkan di **Pojok Kanan Atas** dari foto.
- **Mekanisme**: Watermark akan di-render (*hardcoded*) di sisi *backend* sesaat sebelum file foto disimpan. Karena dilakukan di server, foto tidak dapat dimanipulasi melalui *frontend*.

## Agenda Besok:
1. **Langkah 1**: Menentukan kordinat asli pabrik untuk dimasukkan ke `.env`.
2. **Langkah 2**: Membangun Endpoint API / Logic di Laravel untuk mengecek jarak (dan menyesuaikan `ScanController` agar tidak logout otomatis pada scan berikutnya jika sesi PIN masih valid).
3. **Langkah 3**: Memodifikasi halaman awal (halaman `scan.index` dan `login`) untuk menembakkan JavaScript pembacaan lokasi, serta memastikan trigger auto-logout jika lokasi di luar 7 KM.
4. **Langkah 4**: Uji coba lapangan (menggunakan aplikasi GPS *spoofer* untuk mensimulasikan posisi di dalam dan di luar 7 KM, serta mencoba scan berkelanjutan).
