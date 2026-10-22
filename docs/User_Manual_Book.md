# USER MANUAL BOOK
**PFE MONITORING CONTROL SYSTEM**

**Version 1.0.0**

---

## DAFTAR ISI
**BAB I PENDAHULUAN**
1.1 Latar Belakang Masalah
1.2 Rumusan Masalah
1.3 Tujuan Pembuatan Sistem
1.4 Deskripsi Umum Sistem
1.5 Deskripsi Umum Dokumen
1.6 Target Pengguna Sistem

**BAB II KEBUTUHAN PERANGKAT**
2.1 Perangkat Lunak (Software)
2.2 Perangkat Keras (Hardware)

**BAB III MENU DAN CARA PENGGUNAAN**
3.1 Struktur Menu
3.2 Penggunaan

**BAB IV TAMPILAN UMUM & CARA PENGGUNAAN**
4.1 Halaman Otentikasi (Login)
4.2 Halaman Utama (Dashboard)
4.3 Master Data
4.4 Jadwal Inspeksi
4.5 Melakukan Inspeksi APAR
4.6 Riwayat Inspeksi

**BAB V GLOSARIUM**

---

## BAB I PENDAHULUAN

### 1.1 Latar Belakang Masalah
Pemantauan dan inspeksi kelayakan Alat Pemadam Api Ringan (APAR) di area perusahaan sering kali menghadapi kendala seperti human error dalam pencatatan manual, serta kesulitan dalam melacak jadwal inspeksi secara akurat. Untuk mengatasi masalah tersebut, dikembangkanlah PFE Monitoring Control System guna mendigitalisasi proses inspeksi APAR di Departemen EHSS (Environment, Health, Safety & Security).

### 1.2 Rumusan Masalah
Bagaimana merancang sistem yang dapat mengelola data APAR, menjadwalkan inspeksi, dan memastikan petugas lapangan dapat melakukan pengecekan secara akurat baik menggunakan perangkat genggam maupun pencatatan fisik (kertas).

### 1.3 Tujuan Pembuatan Sistem
Sistem ini bertujuan untuk:
1. Mendigitalisasi pencatatan dan status ketersediaan APAR secara real-time.
2. Mengotomatisasi notifikasi peringatan jika ada APAR yang mendekati masa expired.
3. Memudahkan proses inspeksi lapangan melalui pemindaian QR code.

### 1.4 Deskripsi Umum Sistem
PFE Monitoring Control System adalah platform digital terintegrasi yang berfungsi untuk mengelola dan memantau status kelayakan APAR. Sistem ini menyediakan fitur mulai dari manajemen data inventaris APAR, pembuatan jadwal inspeksi otomatis, pemindaian QR code untuk inspeksi instan di lapangan, hingga pelaporan dan rekapitulasi riwayat inspeksi.

### 1.5 Deskripsi Umum Dokumen
Dokumen ini merupakan panduan operasional (User Manual) yang disusun untuk memberikan instruksi teknis kepada pengguna sistem.

### 1.6 Target Pengguna Sistem
1. **Administrator (Head of EHSS)**: Memiliki hak akses penuh untuk mengelola master data (Gedung, Jenis APAR), mengelola akun pengguna, dan melihat seluruh riwayat inspeksi.
2. **Operator Lapangan (EHSS / Staff)**: Bertugas melakukan eksekusi inspeksi APAR di lapangan menggunakan smartphone atau form fisik.

---

## BAB II KEBUTUHAN PERANGKAT

### 2.1 Perangkat Lunak (Software)
a. **Sistem Operasi**: Berjalan dengan baik di Windows, macOS, Android, maupun iOS.
b. **Browser**: Google Chrome, Mozilla Firefox, Safari, atau Microsoft Edge versi terbaru.
c. **Email Client**: Untuk menerima notifikasi jadwal dan pembuatan akun baru.

### 2.2 Perangkat Keras (Hardware)
a. **PC/Laptop**: Digunakan oleh Administrator untuk mengelola master data dan mengekspor laporan.
b. **Smartphone Berkamera**: Digunakan oleh petugas lapangan untuk melakukan pemindaian (scan) QR code APAR dan mengambil foto bukti inspeksi.
c. **Printer (Opsional)**: Untuk mencetak stiker QR code APAR dan form kertas checklist inspeksi.

---

## BAB III MENU DAN CARA PENGGUNAAN

### 3.1 Struktur Menu
1. **Halaman Login**
2. **Dashboard**: Statistik APAR dan Peringatan Expired.
3. **Master Data** (Khusus Admin): Mengelola Gedung, Lokasi, Jenis APAR, Kapasitas, dan Data APAR.
4. **Jadwal Inspeksi**: Kalender jadwal dan fitur cetak checklist (PDF).
5. **Riwayat Inspeksi**: Laporan dan detail history.
6. **Display Dashboard / Report**: Tampilan *fullscreen* interaktif untuk diputar di layar TV pabrik.

### 3.2 Penggunaan
Sistem mensyaratkan pengguna untuk memiliki akun yang didaftarkan oleh Administrator. Setiap pengguna baru akan menerima email untuk membuat kata sandi secara mandiri sebelum dapat mengakses menu-menu di atas.

---

## BAB IV TAMPILAN UMUM & CARA PENGGUNAAN

### 4.1 Halaman Otentikasi (Login & Setup Password)

**Tampilan Antarmuka Otentikasi**
Halaman ini adalah gerbang masuk ke aplikasi yang menampilkan form pengisian Email dan Password.

1. Buka email Anda, cari pesan dengan subjek **Welcome to PFE Monitoring**, lalu klik tombol "Set Your Password".
2. Atur kata sandi Anda.
3. Buka sistem dan masuk menggunakan **Email** dan **Kata Sandi** yang baru dibuat.

### 4.2 Halaman Utama (Dashboard)

**Tampilan Antarmuka Dashboard**
Menampilkan ringkasan status kelayakan APAR secara keseluruhan.

1. **Statistik APAR**: Kotak ringkasan yang menampilkan total APAR dan kondisinya (Baik, Perbaikan, Isi Ulang, Rusak).
2. **Peringatan Kedaluwarsa**: Sistem akan memunculkan notifikasi merah di dasbor untuk APAR yang masa expired-nya sudah dekat.

### 4.3 Master Data

Halaman ini berfungsi sebagai pusat manajemen inventaris APAR (hanya untuk Admin).

1. Klik menu **Master Data**.
2. Anda dapat menambahkan **Gedung** baru atau **Lokasi** spesifik.
3. Untuk mendaftarkan APAR, buka tab **Data APAR** lalu klik Tambah.
4. Anda dapat mengunduh dan mencetak QR Code menggunakan tombol **Print QR Code**.

### 4.4 Jadwal Inspeksi

Fitur untuk mengatur penugasan pengecekan APAR bulanan atau rutin.

1. Klik menu **Jadwal Inspeksi**.
2. Tekan **Create Schedule** untuk menugaskan petugas mengecek gedung tertentu. Sistem akan mengirim notifikasi email ke petugas bersangkutan.
3. **Export Checklist (Cetak Form Kertas)**: Jika petugas tidak diperbolehkan membawa HP ke area pabrik, Anda dapat mengeklik ikon **Printer**, pilih nama gedung, dan sistem akan mengunduh file PDF berisi tabel daftar pertanyaan inspeksi khusus gedung tersebut.

### 4.5 Melakukan Inspeksi APAR (Alur Lapangan)

1. **Via Pemindai QR (Scan)**:
   - Buka kamera smartphone Anda.
   - Pindai stiker QR pada tabung APAR. Anda akan langsung diarahkan ke form pengisian.
2. **Via Sistem**:
   - Jika petugas baru selesai menggunakan form checklist kertas, mereka dapat menekan ikon **Mata (Detail)** pada menu Jadwal Inspeksi, lalu memilih APAR yang ingin diinput datanya.
3. **Pengisian Form**:
   - Jawab 10 pertanyaan kondisi fisik terlebih dahulu.
   - Lanjutkan dengan 15 pertanyaan standar kelayakan.
   - **WAJIB** melampirkan **Foto Bukti** (foto alat fisik jika scan QR langsung, atau foto form kertas jika menginput dari kantor).

### 4.6 Riwayat Inspeksi & Pelaporan

Halaman arsip digital untuk seluruh kegiatan inspeksi yang telah dilakukan.

1. Buka menu **Riwayat Inspeksi**.
2. Laporan menampilkan tanggal, nama pemeriksa, dan status kelayakan akhir APAR.
3. Tekan tombol **Mata (Detail)** untuk melihat rincian jawaban inspeksi dan foto yang dilampirkan.
4. Gunakan tombol **Export PDF / Excel** di pojok kanan atas untuk mengunduh laporan ke perangkat komputer Anda.

    ### 4.7 Display Dashboard (Mode Presentasi / TV)

    Fitur khusus berupa layar interaktif *fullscreen* (layar penuh) yang cocok untuk ditampilkan di monitor besar, TV lobi pabrik, atau ruang *monitoring*.

    1. Anda dapat mengaksesnya dengan mengeklik menu/ikon **Display Report** atau mengakses langsung *link* `/display-report`.
    2. Layar akan menampilkan daftar APAR yang perlu diinspeksi (berdasarkan jadwal) lengkap dengan animasi teks berjalan (marquee) di bagian bawah.
    3. Tampilan ini dirancang untuk memantau aktivitas tanpa perlu berinteraksi (*view-only*).

---

## BAB V GLOSARIUM

| Istilah | Keterangan |
| --- | --- |
| **APAR / PFE** | Alat Pemadam Api Ringan (*Portable Fire Extinguisher*). |
| **PIC** | *Person In Charge*, petugas yang bertanggung jawab melakukan inspeksi. |
| **QR Code** | Kode matriks dua dimensi yang berisi identitas digital setiap unit APAR. |
| **Dashboard** | Halaman utama yang berisi ringkasan data statistik sistem. |
| **Master Data** | Kumpulan data utama (Gedung, Jenis, Kapasitas) yang menjadi referensi sistem. |
| **Expired** | Tanggal kedaluwarsa isi tabung APAR yang membutuhkan *refill* (isi ulang). |
| **Checklist** | Daftar periksa yang digunakan oleh PIC sebagai panduan inspeksi fisik alat. |
