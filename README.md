# Sistem Administrasi Digital Ilmu Komunikasi — USK

Aplikasi manajemen administrasi akademik Program Studi Ilmu Komunikasi, Universitas Syiah Kuala (USK). Menangani pengajuan judul skripsi, surat aktif kuliah, surat magang, izin penelitian, penjadwalan seminar proposal, dan sidang skripsi secara digital.

---

## Stack Teknologi

| Komponen | Versi |
|---|---|
| PHP | 8.4 |
| Laravel | 12 |
| Database | MySQL 8 |
| Cache / Queue | Redis |
| Containerisasi | Docker + Docker Compose |
| Frontend | Tailwind CSS, Alpine.js, Vite |

---

## Instalasi Development Lokal

```bash
# 1. Clone repository
git clone <repo-url> e-administrasi
cd e-administrasi

# 2. Salin konfigurasi environment
cp .env.example .env

# 3. Sesuaikan koneksi database di .env
#    DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD

# 4. Install dependensi PHP
composer install

# 5. Install dependensi Node
npm install

# 6. Build aset frontend
npm run build

# 7. Jalankan installer — membuat key, migrasi, dan akun admin/kaprodi pertama
php artisan aplikasi:install
```

Perintah `aplikasi:install` akan meminta:
- Nama, email, dan password akun **Admin**
- Nama, email, dan password akun **Kepala Prodi**

Setelah selesai, akses aplikasi di `http://localhost:8000` (atau port dev server Anda).

---

## Deploy Production dengan Docker

```bash
# 1. Salin dan sesuaikan .env untuk production
cp .env.example .env
# Set APP_ENV=production, APP_DEBUG=false, DB_*, REDIS_*, dll.

# 2. Jalankan semua service
docker compose up -d

# 3. Jalankan installer di dalam container
docker compose exec app php artisan aplikasi:install
```

Docker Compose menjalankan: `app` (PHP-FPM), `nginx`, `mysql`, dan `redis`.

---

## Login Pertama Setelah Install

Gunakan kredensial yang dimasukkan saat `aplikasi:install` berjalan.

| URL | Role |
|---|---|
| `/login` | Admin |
| `/login` | Kepala Prodi |

Mahasiswa login menggunakan email (diisi admin) dan password default = NIM.

---

## Pengaturan Sistem (Wajib Diisi)

Setelah login sebagai **Admin**, buka **Pengaturan** di sidebar. Isi semua kolom berikut agar surat yang digenerate tidak kosong:

| Key | Keterangan |
|---|---|
| `nama_prodi` | Nama Program Studi |
| `nama_kaprodi` | Nama & gelar Kepala Program Studi |
| `nip_kaprodi` | NIP Kepala Program Studi |
| `kode_institusi` | Kode institusi (contoh: `UN11.F9`) |
| `kode_prodi` | Kode prodi (contoh: `PK.01.06`) |
| `nama_fakultas` | Nama Fakultas |
| `nama_universitas` | Nama Universitas |
| `alamat_prodi` | Alamat lengkap prodi |
| `kota_prodi` | Kota untuk header tanggal surat |
| `semester_aktif` | Semester aktif (Ganjil / Genap) |
| `tahun_akademik` | Tahun akademik (contoh: `2025/2026`) |

---

## Role dan Akses

| Role | Akses |
|---|---|
| **Admin** | Kelola data mahasiswa & dosen, proses antrian surat, atur jadwal seminar/sidang, upload scan TTD, kelola template surat, pengaturan sistem |
| **Kepala Prodi (Kaprodi)** | Tinjau dan setujui pengajuan judul skripsi, tentukan dosen penguji, setujui jadwal seminar/sidang |
| **Mahasiswa** | Ajukan judul skripsi, ajukan surat (aktif kuliah, magang, izin penelitian, keluar prodi), ajukan seminar & sidang, download surat selesai |

---

## Artisan Commands Custom

```bash
# Instalasi awal aplikasi (migrasi + buat akun admin & kaprodi)
php artisan aplikasi:install

# Buat template surat kosong (contoh: keluar_prodi)
php artisan surat:buat-template {jenis_surat}

# Inject placeholder ke file .docx template
php artisan surat:inject-placeholder {jenis_surat}

# Reset status pengajuan surat ke 'diajukan'
php artisan surat:reset-pengajuan {id}

# Uji generate surat dari template untuk pengajuan tertentu
php artisan surat:test-generate {pengajuan_id}
```

---

## Import Mahasiswa / Dosen dari Excel

1. Login sebagai **Admin**
2. Buka menu **Data Mahasiswa** atau **Data Dosen**
3. Klik tombol **Import Excel**
4. Unduh file contoh (template kolom tersedia di halaman tersebut)
5. Isi data sesuai format, lalu upload

Format file: `.xlsx`. Password default mahasiswa setelah import adalah **NIM** masing-masing.

---

## Kontribusi & Lisensi

Proyek ini dikembangkan khusus untuk Program Studi Ilmu Komunikasi, USK. Tidak untuk distribusi publik.
