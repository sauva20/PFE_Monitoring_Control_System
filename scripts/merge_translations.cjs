const fs = require('fs');
const path = require('path');

const enPath = path.join(__dirname, '../lang/en.json');
const idPath = path.join(__dirname, '../lang/id.json');

const en = JSON.parse(fs.readFileSync(enPath, 'utf8'));
const id = JSON.parse(fs.readFileSync(idPath, 'utf8'));

const extraTranslations = {
  // Key: [English, Indonesian]
  'Apar': ['PFE', 'APAR'],
  'APAR': ['PFE', 'APAR'],
  'Inspeksi': ['Inspection', 'Inspeksi'],
  'Jadwal Inspeksi': ['Inspection Schedule', 'Jadwal Inspeksi'],
  'APAR telah di-created': ['PFE has been created', 'APAR telah dibuat'],
  'APAR telah di-updated': ['PFE has been updated', 'APAR telah diperbarui'],
  'APAR telah di-deleted': ['PFE has been deleted', 'APAR telah dihapus'],
  'Inspeksi telah di-created': ['Inspection has been created', 'Inspeksi telah dibuat'],
  'Inspeksi telah di-updated': ['Inspection has been updated', 'Inspeksi telah diperbarui'],
  'Inspeksi telah di-deleted': ['Inspection has been deleted', 'Inspeksi telah dihapus'],
  'Jadwal Inspeksi telah di-created': ['Inspection Schedule has been created', 'Jadwal Inspeksi telah dibuat'],
  'Jadwal Inspeksi telah di-updated': ['Inspection Schedule has been updated', 'Jadwal Inspeksi telah diperbarui'],
  'Jadwal Inspeksi telah di-deleted': ['Inspection Schedule has been deleted', 'Jadwal Inspeksi telah dihapus'],
  'User telah di-created': ['User has been created', 'Pengguna telah dibuat'],
  'User telah di-updated': ['User has been updated', 'Pengguna telah diperbarui'],
  'User telah di-deleted': ['User has been deleted', 'Pengguna telah dihapus'],
  'PERATURAN MENTRI TENAGA KERJA DAN TRANSMIGRASI No : PER.04/MEN/1980 TENTANG SYARAT-SYARAT PEMASANGAN DAN PEMELIHARAAN ALAT PEMADAM API RINGAN': [
    'REGULATION OF THE MINISTER OF MANPOWER AND TRANSMIGRATION No : PER.04/MEN/1980 CONCERNING REQUIREMENTS FOR INSTALLATION AND MAINTENANCE OF LIGHT FIRE EXTINGUISHERS',
    'PERATURAN MENTERI TENAGA KERJA DAN TRANSMIGRASI No : PER.04/MEN/1980 TENTANG SYARAT-SYARAT PEMASANGAN DAN PEMELIHARAAN ALAT PEMADAM API RINGAN'
  ],
  'APAR Keseluruhan': ['Overall PFE', 'APAR Keseluruhan'],
  'APAR Expired': ['Expired PFE', 'APAR Kedaluwarsa'],
  'Perlu Perbaikan': ['Needs Repair', 'Perlu Perbaikan'],
  'Perlu Isi Ulang': ['Needs Refill', 'Perlu Isi Ulang'],
  'Rusak / Servis': ['Damaged / Service', 'Rusak / Servis'],
  'Pilih Gedung terlebih dahulu': ['Please select a building first', 'Pilih Gedung terlebih dahulu'],
  'Damaged': ['Damaged', 'Rusak'],
  'EXPIRING SOON': ['EXPIRING SOON', 'SEGERA KEDALUWARSA'],
  'TOTAL PFE': ['TOTAL PFE', 'TOTAL APAR'],
  'Days': ['Days', 'Hari'],
  'Recent Inspections': ['Recent Inspections', 'Inspeksi Terbaru'],
  'Inspection Progress for': ['Inspection Progress for', 'Kemajuan Inspeksi untuk'],
  'PFE Type Distribution': ['PFE Type Distribution', 'Distribusi Jenis APAR'],
  'View the last 10 inspections performed': ['View the last 10 inspections performed', 'Lihat 10 inspeksi terakhir yang dilakukan'],
  'Settings': ['Settings', 'Pengaturan'],
  'GOOD CONDITION': ['GOOD CONDITION', 'KONDISI BAIK'],
  'View All': ['View All', 'Lihat Semua'],
  'Inspection Reports': ['Inspection Reports', 'Laporan Inspeksi'],
  'NOT INSPECTED': ['NOT INSPECTED', 'BELUM DIINSPEKSI'],
  'Based on media / agent': ['Based on media / agent', 'Berdasarkan media / bahan'],
  'BROKEN / SERVICE': ['BROKEN / SERVICE', 'RUSAK / SERVIS'],
  'MONTHLY INSPECTION DEADLINE': ['MONTHLY INSPECTION DEADLINE', 'BATAS WAKTU INSPEKSI BULANAN'],
  'Inspection Trend Year': ['Inspection Trend Year', 'Tren Inspeksi Tahunan'],
  'EMPTY PFE': ['EMPTY PFE', 'APAR KOSONG'],
  'Monitor monthly inspection task completion': ['Monitor monthly inspection task completion', 'Pantau penyelesaian tugas inspeksi bulanan'],
  'My Profile': ['My Profile', 'Profil Saya'],
  'Logout': ['Logout', 'Keluar'],
  'Uninspected List': ['Uninspected List', 'Daftar Belum Diinspeksi'],
  'PFE & Location': ['PFE & Location', 'APAR & Lokasi'],
  'Already Expired!': ['Already Expired!', 'Sudah Kedaluwarsa!'],
  'Pass': ['Pass', 'Lolos'],
  'Expiring Today!': ['Expiring Today!', 'Kedaluwarsa Hari Ini!'],
  'Inspected List': ['Inspected List', 'Daftar Sudah Diinspeksi'],
  'Status': ['Status', 'Status'],
  'Already assigned to': ['Already assigned to', 'Sudah ditugaskan ke'],
  'STOCK': ['STOCK', 'STOK'],
  'COMPLIANT': ['COMPLIANT', 'SESUAI'],
  'NON-COMPLIANT': ['NON-COMPLIANT', 'TIDAK SESUAI'],
  'NEEDS REFILL': ['NEEDS REFILL', 'PERLU ISI ULANG'],
  'Compliant': ['Compliant', 'Sesuai'],
  'Non-Compliant': ['Non-Compliant', 'Tidak Sesuai'],
  'Contoh: 6 jadi 006, 6A jadi 006A (huruf di digit ke-4)': [
    'Example: 6 formatted to 006, 6A to 006A (letter in 4th digit)',
    'Contoh: 6 jadi 006, 6A jadi 006A (huruf di digit ke-4)'
  ],
  'Nomor ini sudah dipakai di lantai & gedung ini!': [
    'This number is already used on this floor & building!',
    'Nomor ini sudah dipakai di lantai & gedung ini!'
  ],
  'Nomor urut ruangan (:seq) sudah digunakan di lantai ini.': [
    'Room sequence number (:seq) is already in use on this floor.',
    'Nomor urut ruangan (:seq) sudah digunakan di lantai ini.'
  ],
  'Nomor ruangan (:code) sudah terdaftar di lantai dan gedung yang dipilih.': [
    'Room number (:code) is already registered on this floor and building.',
    'Nomor ruangan (:code) sudah terdaftar di lantai dan gedung yang dipilih.'
  ]
};

// 1. Inject extra translations
for (const [key, [enVal, idVal]] of Object.entries(extraTranslations)) {
  en[key] = en[key] || enVal;
  id[key] = id[key] || idVal;
}

// 2. Cross-pollinate any keys existing in one but not the other
for (const key of Object.keys(en)) {
  if (!(key in id)) {
    id[key] = en[key];
  }
}
for (const key of Object.keys(id)) {
  if (!(key in en)) {
    en[key] = id[key];
  }
}

// 3. Sort keys alphabetically
function sortObject(obj) {
  const sorted = {};
  Object.keys(obj).sort((a, b) => a.localeCompare(b, undefined, { sensitivity: 'base' })).forEach(k => {
    sorted[k] = obj[k];
  });
  return sorted;
}

const sortedEn = sortObject(en);
const sortedId = sortObject(id);

fs.writeFileSync(enPath, JSON.stringify(sortedEn, null, 2) + '\n', 'utf8');
fs.writeFileSync(idPath, JSON.stringify(sortedId, null, 2) + '\n', 'utf8');

console.log('Successfully merged and synchronized translations!');
console.log('EN total keys:', Object.keys(sortedEn).length);
console.log('ID total keys:', Object.keys(sortedId).length);
