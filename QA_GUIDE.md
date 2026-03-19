# 🛡️ QA & System Testing Guide - MPG HRIS

Dokumen ini disusun untuk membantu tim Quality Assurance (QA) memahami alur bisnis, aturan validasi, dan skenario pengujian untuk ekosistem MPG HRIS (Web & Mobile).

---

## 👥 1. Matriks Peran & Izin (RBAC)

Sistem ini menggunakan Role-Based Access Control yang ketat. Berikut adalah daftar akun uji coba default:

| Role | Email | Password | Cakupan Akses |
| :--- | :--- | :--- | :--- |
| **Super Admin** | `superadmin@mpg.co.id` | `password` | Seluruh data & seluruh kantor (Global). |
| **Admin** | `admin@mpg.co.id` | `admin123` | Manajemen data master & operasional (Global). |
| **Manajer** | (Sesuai User) | `Mpg123!` | Approval Izin & Koreksi Presensi (**Hanya Kantor Sendiri**). |
| **Supervisor** | (Sesuai User) | `Mpg123!` | Management Jadwal Pegawai (**Hanya Kantor Sendiri**). |
| **Pegawai** | `budi@mpg.co.id` | `Mpg123!` | Presensi & Pengajuan Izin via Mobile. |

---

## 🏢 2. Isolasi Data Per Kantor (Feature Highlight)

Fitur ini memastikan Manajer dan Supervisor **tidak bisa** melihat atau memproses data dari kantor lain.

**Skenario Test QA:**
1. Login sebagai Manajer Kantor A.
2. Pastikan di Dashboard/Laporan tidak muncul nama pegawai dari Kantor B.
3. Coba akses ID Izin/Presensi dari Kantor B via URL langsung (ID di URL).
4. **Expected Result**: Muncul error 403 (Forbidden) atau redirect kembali dengan pesan error.

---

## 📱 3. Skenario Pengujian Mobile (Pegawai)

### A. Presensi Real-time
1. **Validasi Radius**: Gunakan Mock Location. Coba absen di luar radius (misal > 100m dari titik kantor).
   - **Expected**: Status presensi menjadi "Pending" (menunggu validasi admin) dan muncul peringatan "Di Luar Radius".
2. **Face Recognition**: 
   - Absen dengan wajah yang sudah terdaftar -> **Lolos**.
   - Absen dengan wajah orang lain/foto -> **Gagal/Pending verifikasi**.
3. **Shift Kerja**: Absen sesuai jam shift. Jika lewat toleransi 15 menit, status otomatis "Terlambat".

### B. Pengajuan Izin/Cuti
1. **Aturan Cuti**: Coba ajukan cuti kurang dari 7 hari dari tanggal mulai (H-7).
   - **Expected**: Muncul error "Pengajuan Cuti minimal H-7".
2. **Signature**: Pastikan tanda tangan tersimpan dengan jelas dan muncul di PDF/Web Panel.

---

## 💻 4. Skenario Pengujian Web (Manajer/Admin)

### A. Alur Persetujuan Izin (Workflow)
1. **Flow**: Pegawai (Mobile) -> Manajer (Approve) -> HRD/Admin (Approve).
2. **Dampak Data**: Setelah disetujui HRD (Final), cek tabel Presensi pegawai tersebut. 
   - **Expected**: Data presensi otomatis terisi sesuai jenis izin (Cuti/Sakit) pada rentang tanggal tersebut.

### B. Penjadwalan (Scheduling)
1. **Bulk Create**: Buat jadwal untuk 10 pegawai sekaligus untuk 1 bulan.
2. **Conflict Detect**: Buat jadwal pada tanggal di mana pegawai sudah mengajukan Cuti.
   - **Expected**: Muncul peringatan konflik penggunaan poin/cuti.

---

## 🛠️ 5. Prasyarat Teknis QA
- **Web**: Laravel 12, MySQL 8, npm installed.
- **Mobile**: Flutter SDK, Dio, Google ML Kit (Face Detection).
- **Environment**: Pastikan `BASE_URL` di mobile `.env` sudah mengarah ke IP/Domain server Laravel.

---
*Dibuat oleh Tim Development MPG*
