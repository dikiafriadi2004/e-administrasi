# Tasks — Milestone 1: Autentikasi & Manajemen Akun

Urutan sekuensial — selesaikan satu per satu sebelum lanjut ke berikutnya.

---

## Fase 1: Database & Model Dasar

- [ ] **TASK-1.1** Jalankan `php artisan make:migration add_role_and_is_active_to_users_table`
  dan tambahkan kolom `role ENUM('mahasiswa','admin','kaprodi')` dan
  `is_active BOOLEAN DEFAULT true` ke tabel `users`.

- [ ] **TASK-1.2** Jalankan `php artisan make:migration create_mahasiswas_table`
  dan buat tabel `mahasiswas` (id, user_id FK, nim UNIQUE, angkatan).

- [ ] **TASK-1.3** Jalankan `php artisan make:migration create_dosens_table`
  dan buat tabel `dosens` (id, nama, nip UNIQUE, kapasitas_maksimal nullable).

- [ ] **TASK-1.4** Perbarui model `User`: tambahkan kolom `role` dan `is_active`
  ke `$fillable`, tambahkan cast `is_active` ke boolean, tambahkan relasi
  `hasOne(Mahasiswa::class)`.

- [ ] **TASK-1.5** Buat model `Mahasiswa` dengan `php artisan make:model Mahasiswa`
  — isi `$fillable`, relasi `belongsTo(User::class)`, factory untuk testing.

- [ ] **TASK-1.6** Buat model `Dosen` dengan `php artisan make:model Dosen`
  — isi `$fillable`, factory untuk testing.

- [ ] **TASK-1.7** Jalankan `php artisan migrate` dan verifikasi semua tabel terbuat.

---

## Fase 2: Install Laravel Breeze & Konfigurasi Auth

- [ ] **TASK-2.1** Install Laravel Breeze: `composer require laravel/breeze --dev`
  lalu `php artisan breeze:install blade --no-interaction`. Jalankan
  `npm run build` untuk mengkompilasi asset.

- [ ] **TASK-2.2** Install library import Excel:
  `composer require maatwebsite/excel`.

- [ ] **TASK-2.3** Buat tiga middleware kustom:
  `php artisan make:middleware EnsureMahasiswa`,
  `php artisan make:middleware EnsureAdmin`,
  `php artisan make:middleware EnsureKaprodi`.
  Isi logika pengecekan role dan `is_active` di masing-masing.

- [ ] **TASK-2.4** Daftarkan alias middleware di `bootstrap/app.php`
  (`mahasiswa`, `admin`, `kaprodi`).

- [ ] **TASK-2.5** Definisikan tiga Gate (`is-mahasiswa`, `is-admin`, `is-kaprodi`)
  di `AppServiceProvider::boot()`.

---

## Fase 3: Login & Redirect per Role

- [ ] **TASK-3.1** Modifikasi `AuthenticatedSessionController` (bawaan Breeze):
  ubah redirect after login agar mengarah ke dashboard sesuai `$user->role`.

- [ ] **TASK-3.2** Tambahkan pengecekan `is_active` di
  `LoginRequest::authenticate()` — lempar ValidationException jika akun
  tidak aktif.

- [ ] **TASK-3.3** Buat placeholder dashboard untuk tiap role:
  - `php artisan make:controller Admin/DashboardController`
  - `php artisan make:controller Kaprodi/DashboardController`
  - `php artisan make:controller Mahasiswa/DashboardController`
  Masing-masing dengan method `index()` yang return view sederhana.

- [ ] **TASK-3.4** Buat file route terpisah: `routes/admin.php`,
  `routes/kaprodi.php`, `routes/mahasiswa.php`. Daftarkan route dashboard
  di masing-masing.

- [ ] **TASK-3.5** Perbarui `routes/web.php`: tambahkan route groups dengan
  middleware yang benar untuk ketiga role, serta redirect root.

- [ ] **TASK-3.6** Uji manual: login dengan akun tiap role, pastikan redirect
  benar. Login dengan akun nonaktif, pastikan error muncul. Akses URL role
  lain langsung, pastikan 403.

---

## Fase 4: Layout & Navigasi Dasar

- [ ] **TASK-4.1** Buat `resources/views/layouts/app.blade.php` dengan sidebar
  yang memuat menu sesuai role (`auth()->user()->role`). Gunakan TailwindCSS.

- [ ] **TASK-4.2** Perbarui view dashboard tiap role untuk extend layout `app`.

---

## Fase 5: CRUD Mahasiswa oleh Admin

- [ ] **TASK-5.1** Buat `Admin\MahasiswaController` dengan method:
  `index`, `create`, `store`, `edit`, `update`, `toggleActive`.
  (`php artisan make:controller Admin/MahasiswaController --resource`)

- [ ] **TASK-5.2** Buat Form Request:
  `php artisan make:request Admin/StoreMahasiswaRequest` dan
  `php artisan make:request Admin/UpdateMahasiswaRequest`.
  Isi aturan validasi (NIM unik, email unik, angkatan valid).

- [ ] **TASK-5.3** Buat views:
  - `resources/views/admin/mahasiswa/index.blade.php` (tabel + tombol tambah/edit/nonaktifkan)
  - `resources/views/admin/mahasiswa/create.blade.php` (form tambah)
  - `resources/views/admin/mahasiswa/edit.blade.php` (form edit)

- [ ] **TASK-5.4** Daftarkan route resource mahasiswa di `routes/admin.php`
  dan route tambahan untuk `toggleActive`.

- [ ] **TASK-5.5** Uji: tambah mahasiswa baru, edit data, nonaktifkan, aktifkan
  kembali. Coba tambah dengan NIM duplikat — pastikan validasi muncul.

---

## Fase 6: Import Excel Mahasiswa

- [ ] **TASK-6.1** Buat class import: `php artisan make:import MahasiswaImport`
  (atau buat manual di `app/Imports/MahasiswaImport.php`). Implementasi
  logika per-baris: cek duplikat, validasi, buat User+Mahasiswa.

- [ ] **TASK-6.2** Buat `Admin\MahasiswaImportController` dengan method
  `create` (tampilkan form upload) dan `store` (proses file).

- [ ] **TASK-6.3** Buat view `resources/views/admin/mahasiswa/import.blade.php`
  dengan form upload file dan tampilan ringkasan hasil.

- [ ] **TASK-6.4** Daftarkan route import di `routes/admin.php`.

- [ ] **TASK-6.5** Uji: upload file Excel valid dengan beberapa baris termasuk
  duplikat dan baris tidak valid. Verifikasi ringkasan hasil sesuai.

---

## Fase 7: CRUD Dosen oleh Admin

- [ ] **TASK-7.1** Buat `Admin\DosenController` dengan method resource standar
  (tanpa delete).

- [ ] **TASK-7.2** Buat Form Request `Admin\StoreDosenRequest` dan
  `Admin\UpdateDosenRequest` dengan validasi NIP unik.

- [ ] **TASK-7.3** Buat views `admin/dosen/index.blade.php`,
  `create.blade.php`, `edit.blade.php`.

- [ ] **TASK-7.4** Daftarkan route dosen di `routes/admin.php`.

- [ ] **TASK-7.5** Uji: tambah dosen baru, edit, coba tambah NIP duplikat.

---

## Fase 8: Seeder Akun Awal

- [ ] **TASK-8.1** Perbarui `DatabaseSeeder` untuk membuat:
  - 1 akun Admin (email: `admin@prodi.ac.id`, password: `password`)
  - 1 akun Kaprodi (email: `kaprodi@prodi.ac.id`, password: `password`)
  Gunakan factory atau `User::create()` langsung.

- [ ] **TASK-8.2** Jalankan `php artisan db:seed` dan verifikasi akun terbuat
  dengan login langsung.

---

## Checklist Definition of Done

- [ ] Login dengan 3 role berbeda berhasil dan redirect ke halaman yang benar.
- [ ] Akun nonaktif tidak bisa login.
- [ ] Mahasiswa tidak bisa mengakses `/admin/*` dan `/kaprodi/*` via URL langsung.
- [ ] Admin bisa tambah/edit/nonaktifkan akun mahasiswa.
- [ ] Import Excel dengan duplikat dan data tidak valid menghasilkan ringkasan
      yang akurat.
- [ ] CRUD dosen berfungsi dengan validasi NIP unik.
- [ ] Seeder menghasilkan akun admin dan kaprodi yang bisa login.
