# MPG HRIS - Web Management System

Sistem Manajemen Sumber Daya Manusia (HRIS) terintegrasi untuk pengelolaan data pegawai, presensi, lembur, dan perizinan. Dibuat menggunakan framework Laravel dengan fokus pada performa dan kemudahan penggunaan.

## 🚀 Tech Stack

- **Backend**: Laravel 12.x (PHP 8.2+)
- **Frontend**: Blade Templates + Tailwind CSS (v3.x)
- **Database**: MySQL 8.x
- **Icons**: Heroicons (SVG inline)
- **Komponen UI**: Custom Blade Components (Table, Modal, Input, Button, dll.)

## 🛠 Panduan Instalasi

Ikuti langkah-langkah berikut untuk menjalankan proyek di lingkungan lokal:

1. **Clone & Setup Environment**
   ```bash
   cp .env.example .env
   # Sesuaikan DB_DATABASE, DB_USERNAME, DB_PASSWORD di .env
   ```

2. **Instal Dependensi Backend**
   ```bash
   composer install
   ```

3. **Instal Dependensi Frontend**
   ```bash
   npm install
   npm run build # atau npm run dev untuk development
   ```

4. **Generate App Key & Database**
   ```bash
   php artisan key:generate
   php artisan migrate --seed
   ```

5. **Akses Aplikasi**
   Jalankan `php artisan serve` dan buka `http://localhost:8000`.

## 📂 Struktur Proyek & Arsitektur

Proyek ini mengikuti standar **Clean Code** Laravel dengan pemisahan tanggung jawab yang jelas:

- **Form Requests**: Semua validasi input diletakkan di `app/Http/Requests`.
- **Controllers**: Fokus pada pengaturan trafik. Logika query berat dipisahkan ke Eloquent Scopes atau Service.
- **Models**: Menggunakan relasi Eloquent yang jelas dan penamaan kolom `snake_case` Bahasa Indonesia.
- **Resources**: UI dibangun menggunakan komponen Blade di `resources/views/components`.

## 📜 Aturan Pengembangan (Project Rules)

1. **Bahasa**: Gunakan Bahasa Indonesia untuk penjelasan, komentar, dan dokumentasi.
2. **Naming Convention**:
   - Primary Key: `id_nama_tabel` (contoh: `id_divisi`, `id_jabatan`).
   - Tabel Master: `snake_case`.
3. **UI Components**: Gunakan komponen yang sudah ada untuk konsistensi:
   - `<x-table>`: Wrapper tabel standar.
   - `<x-input>` & `<x-select>`: Input form standar.
   - `<x-button>` & `<x-back-button>`: Tombol aksi.
   - `<x-modal>`: Untuk CRUD Data Master sederhana.

## 🔑 Akun Default (Seeder)

- **Super Admin**: `superadmin@mpg.co.id` / `password`
- **Admin**: `admin@mpg.co.id` / `admin123`
- **Pegawai**: `budi@mpg.co.id` / `Mpg123!`

---
Developed by MPG Team.
