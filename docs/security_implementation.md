# 🛡️ Dokumentasi Implementasi Keamanan Sistem (Security Implementation)
**PFE Monitoring Control System — EHSS Department — Environment, Health, Safety & Security**

Dokumen ini menjelaskan seluruh arsitektur, lapisan pertahanan, mekanisme teknis, dan cara kerja fitur keamanan yang diimplementasikan pada aplikasi **PFE Monitoring Control System**.

---

## Daftar Isi
1. [Ringkasan Arsitektur Keamanan (Defense-in-Depth)](#1-ringkasan-arsitektur-keamanan-defense-in-depth)
2. [Proteksi Brute-Force & Flooding pada Login](#2-proteksi-brute-force--flooding-pada-login)
3. [Keamanan Verifikasi PIN (Inspeksi Lapangan & Viewer Display)](#3-keamanan-verifikasi-pin-inspeksi-lapangan--viewer-display)
4. [Proteksi Bot (Honeypot & Google reCAPTCHA)](#4-proteksi-bot-honeypot--google-recaptcha)
5. [Isolasi Hak Akses & Sesi (Privilege & Session Separation)](#5-isolasi-hak-akses--sesi-privilege--session-separation)
6. [Kriptografi & URL Bertanda Tangan (Signed URLs)](#6-kriptografi--url-bertanda-tangan-signed-urls)
7. [Integritas Data Lapangan (Anti-Fraud & Geofencing)](#7-integritas-data-lapangan-anti-fraud--geofencing)
8. [Audit Trail & Activity Logging](#8-audit-trail--activity-logging)
9. [Matriks Parameter Keamanan](#9-matriks-parameter-keamanan)

---

## 1. Ringkasan Arsitektur Keamanan (Defense-in-Depth)

Aplikasi menerapkan konsep **Pertahanan Berlapis (*Defense-in-Depth*)**, di mana setiap celah potensial dilindungi oleh lebih dari satu mekanisme keamanan:

```mermaid
flowchart TD
    A[Request Pengguna / Bot] --> B[Layer 1: Route Throttling / HTTP 429]
    B --> C[Layer 2: Honeypot & reCAPTCHA Validation]
    C --> D[Layer 3: Global IP Rate Limiter - Anti Password Spraying]
    D --> E[Layer 4: Account-Specific Rate Limiter - Anti Credential Stuffing]
    E --> F[Layer 5: Bcrypt Hashing & Credential Verification]
    F --> G[Layer 6: Session Regeneration & Privilege Isolation Middleware]
    G --> H[Layer 7: Stealth 404 & Cloaking Internal Endpoints]
```

---

## 2. Proteksi Brute-Force & Flooding pada Login

Pada halaman login utama ([AuthController.php](file:///c:/laragon/www/APAR PFE EHSS/app/Http/Controllers/AuthController.php)), diterapkan proteksi 3 lapis untuk mengantisipasi berbagai jenis serangan siber:

### A. Lapis Kecepatan Request (Route Throttling)
* **Lokasi**: [routes/web.php](file:///c:/laragon/www/APAR PFE EHSS/routes/web.php#L34) (`middleware('throttle:20,1')`)
* **Cara Kerja**:
  Membatasi maksimal **20 request per menit per IP** ke endpoint `POST /login`.
* **Tujuan**:
  Mencegah bot otomatis yang mengirimkan ratusan request per detik. Jika batas terlampaui, web server langsung mengembalikan status `HTTP 429 Too Many Requests` tanpa menjalankan proses database atau perhitungan hashing kata sandi (`Bcrypt`) yang berat di CPU.

### B. Lapis Akun Spesifik (Anti-Brute Force Akun Tunggal)
* **Kunci Throttle**: `email + IP Address`
* **Ambang Batas**: Maksimal **5 kali percobaan gagal**.
* **Durasi Blokir**: **10 menit** (600 detik).
* **Pesan Error**: *"Akun Anda diblokir sementara karena terlalu banyak percobaan masuk."*
* **Cara Kerja**:
  Jika penyerang menargetkan satu akun tertentu (misal: `admin@ehss.id`) dan gagal 5 kali berturut-turut, maka kombinasi akun tersebut dari IP pengakses akan dibekukan selama 10 menit.

### C. Lapis Global IP (Anti-Password Spraying)
* **Kunci Throttle**: `login-ip:{ip_address}`
* **Ambang Batas**: Maksimal **10 kali percobaan gagal total** (akumulatif dari semua akun).
* **Durasi Blokir**: **10 menit** (600 detik).
* **Pesan Error**: *"Akses masuk diblokir sementara karena terlalu banyak percobaan gagal dari perangkat ini."*
* **Cara Kerja**:
  Mendeteksi serangan *Password Spraying* (bot yang mencoba satu kata sandi umum ke puluhan akun berbeda agar tidak terkena limit akun 5x). Begitu total kegagalan dari IP tersebut mencapai 10 kali, seluruh IP perangkat langsung diblokir total untuk semua akun.

### D. Pencegahan Kebocoran Informasi (Generic Error Handling)
Sistem **tidak menampilkan hitungan mundur** (*countdown* detik/menit sisa blokir) pada pesan error. Hal ini mengikuti standar OWASP agar penyerang tidak dapat mengetahui waktu pasti pembukaan blokir untuk menjadwalkan serangan berikutnya.

---

## 3. Keamanan Verifikasi PIN (Inspeksi Lapangan & Viewer Display)

Petugas yang melakukan inspeksi di lapangan memindai QR Code APAR dan memverifikasi identitas menggunakan **4 Digit PIN** ([ScanController.php](file:///c:/laragon/www/APAR PFE EHSS/app/Http/Controllers/ScanController.php)).

### A. Kunci Throttle Global per Perangkat
* **Kunci Throttle**: `verify-pin:{ip_address}`
* **Ambang Batas**: Maksimal **5 kali percobaan salah**.
* **Durasi Blokir**: **10 menit** (600 detik).
* **Pesan Error**: *"Akses verifikasi PIN diblokir sementara karena terlalu banyak percobaan salah."*
* **Mengapa Berbasis IP Global (Bukan per APAR ID)?**
  Jika kunci menyertakan ID APAR, penyerang yang salah 5x di APAR A bisa pindah memindai APAR B untuk mencoba 5x lagi. Dengan kunci berbasis IP, pemblokiran 10 menit berlaku serentak di seluruh APAR.

### B. Hashing PIN
PIN disimpan di database dalam bentuk terenkripsi satu arah menggunakan **Bcrypt** (`Hash::make()`), bukan teks polos (*plaintext*).

### C. Keamanan Akses Portal Viewer Display (Kiosk Report)
Fitur portal Display Report diperuntukkan bagi tamu dan stakeholder eksternal yang diundang secara resmi oleh EHSS untuk memantau status kesiapan APAR secara transparan tanpa memberikan akun login internal ([GuestDisplayController.php](file:///c:/laragon/www/APAR PFE EHSS/app/Http/Controllers/GuestDisplayController.php)).

1. **Proteksi Anti-Brute Force PIN Berbasis IP Perangkat**:
   * **Lokasi**: [GuestDisplayController.php](file:///c:/laragon/www/APAR PFE EHSS/app/Http/Controllers/GuestDisplayController.php)
   * **Kunci Throttle**: `viewer-verify-pin:{ip_address}`
   * **Ambang Batas**: Maksimal **3 kali percobaan salah**.
   * **Durasi Blokir**: **30 menit** (1.800 detik) di backend melalui `RateLimiter::hit($throttleKey, 1800)`.

2. **Pencegahan Kebocoran Informasi & Anti-Timing Attack (OWASP Generic Notice)**:
   * **Judul Banner**: *"Perangkat Diblokir"*
   * **Pesan Error**: *"Akses verifikasi PIN diblokir karena telah mencapai batas maksimal percobaan salah dari perangkat ini."*
   * **Eliminasi Durasi & Status Sementara**: Sistem sengaja **tidak mencantumkan hitungan mundur (*countdown*)**, durasi menit/detik, kata *"sementara"*, maupun anjuran *"coba kembali nanti"*.
   * **Tujuan**: Mencegah penyerang mengetahui batas waktu *cooldown* blokir untuk merancang serangan brute-force berkala secara terjadwal.

3. **Umpan Balik Sisa Kesempatan Dinamis**:
   * Sebelum IP terblokir (percobaan salah ke-1 dan ke-2), sistem menginformasikan sisa kesempatan mencoba secara transparan (*"PIN yang Anda masukkan salah. Sisa kesempatan mencoba: X kali."*) agar pengakses berhati-hati.

4. **Auto-Lockdown UI di Sisi Frontend**:
   * Begitu IP terdeteksi diblokir oleh backend, antarmuka verifikasi secara otomatis mengunci seluruh input 4-digit PIN (`disabled`) dan tombol konfirmasi (`disabled` dengan cursor `not-allowed`).

5. **Validasi Format & Regex Numerik Murni**:
   * Input PIN divalidasi ketat pada backend: `required|string|size:4|regex:/^[0-9]{4}$/` untuk menolak injeksi karakter khusus, payload non-numerik, atau panjang tidak sesuai sebelum diproses oleh database.

6. **Route Throttling**:
   * Endpoint verifikasi `POST /display-report/access/{token}/verify` diproteksi middleware `throttle:15,1` (maksimal 15 request per menit) pada [routes/web.php](file:///c:/laragon/www/APAR PFE EHSS/routes/web.php#L52) untuk menangkal spam bot otomatis dan serangan flooding.

7. **Pencegahan Reconnaissance & Form Login Cloaking (Stealth 404)**:
   * Endpoint internal `/display-report` yang dapat dibuka dari profil pengguna internal menerapkan mekanisme **Stealth 404** ([DashboardController.php](file:///c:/laragon/www/APAR PFE EHSS/app/Http/Controllers/DashboardController.php#L182-L188)).
   * Jika dibuka tanpa sesi kredensial aktif (`! auth()->check()`), sistem **tidak melakukan redirect ke form `/login`**, melainkan langsung mengeksekusi `abort(404)` (HTTP 404 Not Found).
   * **Manfaat**: Pihak luar, penyerang, atau perayap bot yang menyalin URL `/display-report` tidak dapat menemukan keberadaan halaman login web utama aplikasi ataupun struktur otentikasi internal.

---

## 4. Proteksi Bot (Honeypot & Google reCAPTCHA)

Untuk membedakan pengakses manusia dengan bot / skrip otomatis:

### A. Honeypot Trap
* Pada form login terdapat field input tersembunyi: `<input name="_contact_number" class="hidden">`.
* User tidak melihat dan tidak akan mengisi field ini.
* Bot perayap (*crawler/spammer*) otomatis mengisi semua input form yang ditemukan.
* Jika field `_contact_number` terisi nilai apa pun, request langsung digagalkan seketika.

### B. Google reCAPTCHA v2
* Divalidasi melalui rule [Recaptcha.php](file:///c:/laragon/www/APARB.Braun/app/Rules/Recaptcha.php) dengan memanggil Google Siteverify API.
* Mencegah pengiriman form otomatis tanpa interaksi manusia.

---

## 5. Isolasi Hak Akses & Sesi (Privilege & Session Separation)

Aplikasi memisahkan secara ketat antara sesi **Inspeksi Lapangan (PIN)** dan sesi **Manajemen Utama (Web Login)**.

### A. Middleware `RestrictPinLogin`
* **Lokasi**: [RestrictPinLogin.php](file:///c:/laragon/www/APARB.Braun/app/Http/Middleware/RestrictPinLogin.php)
* **Cara Kerja**:
  Petugas yang masuk melalui verifikasi PIN saat memindai QR Code hanya diberikan izin mengakses modul inspeksi (`/inspeksi/*`). Jika mereka mencoba mengakses dashboard admin, master data, manajemen pengguna, atau laporan:
  1. Sesi pengguna langsung dimusnahkan (`Auth::logout()`, `session()->invalidate()`).
  2. CSRF Token di-regenerasi ulang (`session()->regenerateToken()`).
  3. Pengguna dilempar keluar dengan peringatan keamanan.

### B. Auto-Session Logout Saat Scan Ulang
* Pada [ScanController::index](file:///c:/laragon/www/APARB.Braun/app/Http/Controllers/ScanController.php#L22):
  Setiap kali halaman scan dibuka dari luar sistem (bukan dari dashboard), sesi inspeksi sebelumnya otomatis di-*logout* dan dibersihkan. Hal ini mencegah petugas lain atau orang asing menggunakan sesi yang tertinggal di peramban.

### C. Middleware `PreventBackHistory`
* **Lokasi**: [PreventBackHistory.php](file:///c:/laragon/www/APARB.Braun/app/Http/Middleware/PreventBackHistory.php)
* Menambahkan header HTTP:
  ```http
  Cache-Control: nocache, no-store, max-age=0, must-revalidate
  Pragma: no-cache
  Expires: Sat, 01 Jan 1990 00:00:00 GMT
  ```
* **Fungsi**: Mematikan fitur *back-forward cache* di browser. Setelah pengguna logout, menekan tombol "Back" di browser tidak akan menampilkan kembali formulir atau data sensitif yang tersimpan di cache.

### D. Isolasi Sesi Portal Viewer Display (Kiosk Portal)
* **Pemisahan Kredensial**: Sesi viewer publik tidak pernah menyentuh tabel pengguna (`users`) atau sesi autentikasi Laravel utama (`auth()->user()`). Sesi disimpan dengan kunci unik terisolasi: `guest_display_auth_{token}`.
* **Prinsip Least Privilege**: Pengakses viewer hanya memiliki izin *read-only* terhadap data agregat inspeksi APAR pada endpoint `/display-report/view/{token}`. Segala upaya memanggil rute administratif, master data, atau mutasi data ditolak.
* **Proactive Invalidation (Kedaluwarsa & Nonaktif)**: Sistem memvalidasi tanggal aktif `valid_until` dan status `is_active` secara *real-time* di setiap pemanggilan halaman. Begitu masa aktif habis atau dinonaktifkan oleh Admin EHSS, sesi langsung dihanguskan dan diarahkan ke halaman pemberitahuan kedaluwarsa/nonaktif.
* **Sesi Logout Bersih**: Fitur logout viewer (`POST /display-report/logout/{token}`) segera menghapus kunci sesi dari server (`session()->forget(...)`) dan mengarahkan kembali ke verifikasi PIN tanpa menyisakan cache laporan.
* **Kriptografi Token Akses**: Token URL viewer di-generate menggunakan `Str::random(40)` (240-bit entropy acak) yang secara matematis mustahil ditebak melalui *dictionary attack* atau *sequential scanning*.

### E. Batas Waktu Sesi Otomatis Akibat Inaktivitas (Session Inactivity Lifetime)
* **Konfigurasi**: `SESSION_LIFETIME=60` (60 menit) pada [.env](file:///c:/laragon/www/APARB.Braun/.env#L36) dan [config/session.php](file:///c:/laragon/www/APARB.Braun/config/session.php#L35).
* **Penyimpanan Driver**: Menggunakan database session (`SESSION_DRIVER=database`). Setiap aksi/request pengguna memperbarui timestamp kolom `last_activity` pada tabel `sessions`.
* **Mekanisme Auto-Expire**: Jika pengguna tidak melakukan aktivitas/request apa pun selama **60 menit**, sesi di server secara otomatis dianggap kedaluwarsa (*expired*). Saat pengguna mencoba berinteraksi atau mengklik menu berikutnya, middleware `Authenticate` langsung membatalkan akses dan mengarahkan kembali ke halaman login, mencegah pembajakan sesi (*session hijacking*) saat komputer ditinggalkan.

---

## 6. Kriptografi & URL Bertanda Tangan (Signed URLs)

Untuk fitur yang melibatkan tautan via email (Setup Akun & Lupa Password):
* Menggunakan fitur **Laravel Signed URLs** (`middleware('signed')`).
* URL memiliki parameter signature berbasis `HMAC-SHA256` dengan kunci aplikasi (`APP_KEY`).
* Memiliki masa kedaluwarsa waktu (*expiration timestamp*).
* Parameter URL yang dimanipulasi sekecil apa pun akan menghasilkan error `403 Invalid Signature`.

---

## 7. Integritas Data Lapangan (Anti-Fraud & Geofencing)

Untuk memastikan bahwa inspeksi benar-benar dilakukan secara fisik di lokasi APAR:

1. **Geofencing GPS Real-Time**:
   Sistem membaca koordinat GPS perangkat petugas dan memvalidasi radius jarak fisik terhadap titik koordinat APAR/gedung.
2. **Watermarking Foto Otomatis**:
   Foto bukti fisik yang diambil saat inspeksi langsung dibubuhi *watermark* permanen di server yang memuat:
   * Kode APAR
   * Nama Petugas
   * Tanggal & Jam (Presisi Detik)
   * Titik Koordinat GPS
3. **Anti-Inspect Script**:
   Pada tampilan mobile/inspeksi, script pelindung mematikan klik kanan (*context menu*) dan *shortcut* keyboard developer tools (`F12`, `Ctrl+Shift+I`, `Ctrl+U`) untuk mencegah manipulasi form secara kasual.

---

## 8. Audit Trail & Activity Logging

* Menggunakan pustaka **Spatie Activitylog** pada model-model inti (`User`, `Apar`, `Inspeksi`).
* Setiap operasi penting (Create, Update, Delete) tercatat ke tabel database:
  * ID dan Nama pengguna yang melakukan aksi
  * Atribut data sebelum dan sesudah perubahan (*dirty attributes*)
  * Alamat IP dan waktu kejadian

---

## 9. Matriks Parameter Keamanan

| Modul / Komponen | Kunci Throttle | Batas Percobaan | Durasi Blokir | Tindakan / Respon |
| :--- | :--- | :--- | :--- | :--- |
| **Login (Per Akun)** | `email` + `IP` | 5 kali gagal | 10 Menit (600s) | Kunci akun sementara untuk IP terkait |
| **Login (Global IP)** | `login-ip:{ip}` | 10 kali gagal | 10 Menit (600s) | Blokir seluruh akses login dari IP terkait |
| **Login (HTTP Traffic)** | IP Address | 20 request/menit | 1 Menit (Rolling) | HTTP 429 Too Many Requests |
| **Honeypot Form** | Field `_contact_number` | 1 kali terdeteksi | Instan | Ditolak seketika (anggap Bot) |
| **PIN Verifikasi APAR** | `verify-pin:{ip}` | 5 kali gagal | 10 Menit (600s) | Blokir input PIN di semua APAR |
| **PIN Verifikasi Viewer Display** | `viewer-verify-pin:{ip}` | 3 kali gagal | 30 Menit (1.800s - Dirahasiakan) | Kunci form PIN, generic notice tanpa kata "sementara" / countdown |
| **Viewer PIN (HTTP Traffic)** | IP Address | 15 request/menit | 1 Menit (Rolling) | HTTP 429 Too Many Requests |
| **Display Kiosk Internal (`/display-report`)** | Sesi Login Web (`auth`) | Tanpa sesi login aktif | Instan | HTTP 404 Not Found (Stealth 404 / Cloaking form login) |
| **Token Akses Viewer** | `access_token` (40 char cryptographically secure) | Validitas tanggal & `is_active` | Sesuai tanggal `valid_until` | Akses ditutup, sesi dimusnahkan jika expired/nonaktif/logout |
| **Sesi Login Web (Inactivity)** | Database Session (`sessions.last_activity`) | Tidak ada interaksi selama 60 menit | 60 Menit (Inaktif) | Sesi server hangus, redirect ke login saat request berikutnya |
| **Sesi PIN Inspeksi** | `session('is_pin_login')` | Akses rute non-inspeksi | Instan | Sesi dimusnahkan, force logout |
| **Reset / Setup Password** | Signed URL (HMAC) | 1 signature valid | Waktu terbatas | HTTP 403 Forbidden jika dimanipulasi |

---
*Dokumen ini diperbarui secara berkala mengikuti arsitektur keamanan aktif pada proyek APAR PFE EHSS.*
