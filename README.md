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

## Placeholder Template Surat

Saat membuat atau mengedit template surat `.docx` di Word, gunakan placeholder berikut. Daftar lengkap juga tersedia di halaman **Admin → Template Surat → Upload**.

### Nomor Surat
| Placeholder | Contoh Nilai | Sumber |
|---|---|---|
| `${nomor_urut}` | `2032` | Diisi admin saat generate |
| `${kode_institusi}` | `UN11.F9` | Pengaturan Sistem |
| `${kode_prodi}` | `PK.01.06` | Pengaturan Sistem |
| `${bulan_surat}` | `10` | Otomatis (angka 2 digit) |
| `${nama_bulan_surat}` | `Oktober` | Otomatis (nama bulan) |
| `${tahun_surat}` | `2025` | Otomatis (4 digit) |
| `${nomor_surat}` | `2032/UN11.F9/PK.01.06/10/2025` | Diisi admin (nomor penuh) |

Contoh penomoran di template Word:
```
${nomor_urut}/${kode_institusi}/${kode_prodi}/${bulan_surat}/${tahun_surat}
→ 2032/UN11.F9/PK.01.06/10/2025
```

### Tanggal & Waktu
| Placeholder | Contoh Nilai | Sumber |
|---|---|---|
| `${tanggal_surat}` | `15 Oktober 2025` | Otomatis |
| `${kota_prodi}` | `Banda Aceh` | Pengaturan Sistem |
| `${tanggal_seminar}` | `Senin, 20 Oktober 2025` | Ditetapkan Admin |
| `${tanggal_sidang}` | `Senin, 20 Oktober 2025` | Ditetapkan Admin |
| `${waktu_sidang}` | `09:00 WIB` | Ditetapkan Admin |
| `${tempat_sidang}` | `Ruang Sidang Lt. 2` | Ditetapkan Admin |
| `${tanggal_mulai}` | `1 Oktober 2025` | Diisi mahasiswa |
| `${tanggal_selesai}` | `31 Oktober 2025` | Diisi mahasiswa |

### Data Mahasiswa
| Placeholder | Contoh Nilai |
|---|---|
| `${nama_mahasiswa}` | `Diki Afriadi` |
| `${nim}` | `1215040001` |
| `${angkatan}` | `2020` |
| `${alamat_mahasiswa}` | `Jl. Darussalam No. 1` |
| `${semester_aktif}` | `Genap` |
| `${tahun_akademik}` | `2025/2026` |

### Identitas Institusi (dari Pengaturan Sistem)
| Placeholder | Contoh Nilai |
|---|---|
| `${nama_prodi}` | `Ilmu Komunikasi` |
| `${nama_fakultas}` | `Fakultas Ilmu Sosial dan Ilmu Politik` |
| `${nama_universitas}` | `Universitas Syiah Kuala` |
| `${alamat_prodi}` | `Darussalam, Banda Aceh 23111` |
| `${telepon_prodi}` | `(0651) 3617196` |
| `${email_prodi}` | `fisip@usk.ac.id` |

### Pejabat Penandatangan
| Placeholder | Contoh Nilai |
|---|---|
| `${nama_kaprodi}` | `Dr. Effendi Hasan, M.A` |
| `${nip_kaprodi}` | `197510012009121005` |
| `${nama_dekan}` | `Prof. Dr. Nama Dekan` |
| `${nip_dekan}` | `196501011990031001` |

### Dosen Pembimbing & Penguji
| Placeholder | Keterangan |
|---|---|
| `${nama_pembimbing}` / `${nama_pembimbing_1}` | Nama dosen pembimbing 1 |
| `${nip_pembimbing}` / `${nip_pembimbing_1}` | NIP dosen pembimbing 1 |
| `${nama_pembimbing_2}` | Nama dosen pembimbing 2 |
| `${nip_pembimbing_2}` | NIP dosen pembimbing 2 |
| `${nama_penguji}` / `${nama_penguji_1}` | Nama dosen penguji 1 |
| `${nip_penguji}` / `${nip_penguji_1}` | NIP dosen penguji 1 |
| `${nama_penguji_2}` | Nama dosen penguji 2 |
| `${nip_penguji_2}` | NIP dosen penguji 2 |

### Magang & Penelitian
| Placeholder | Keterangan |
|---|---|
| `${nama_instansi}` | Nama instansi magang/penelitian |
| `${alamat_instansi}` | Alamat instansi |
| `${keperluan}` | Keperluan surat aktif kuliah |
| `${tujuan_instansi}` | Instansi tujuan surat aktif kuliah |
| `${judul_skripsi}` | Judul skripsi/proposal |
| `${bidang_kajian}` | Bidang kajian skripsi |
| `${judul_penelitian}` | Judul penelitian |
| `${bidang_penelitian}` | Bidang penelitian |

> **Tips:** Ketik placeholder langsung di body dokumen Word tanpa spasi di dalam kurung kurawal. Jika placeholder terpotong oleh autocorrect Word, matikan fitur autocorrect atau ketik ulang dalam satu kesatuan.

---

## Kontribusi & Lisensi

Proyek ini dikembangkan khusus untuk Program Studi Ilmu Komunikasi, USK. Tidak untuk distribusi publik.
