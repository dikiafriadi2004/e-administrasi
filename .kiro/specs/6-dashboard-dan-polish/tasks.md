# Tasks — Milestone 6: Dashboard & Polish

Prasyarat: Milestone 1–5 selesai.

---

## Fase 1: Dashboard Utama Tiap Role

- [ ] **TASK-1.1** Perbarui `Admin\DashboardController@index`:
  tambahkan query untuk 4 kartu statistik (pengajuan hari ini, menunggu
  verifikasi, surat bulan ini, mahasiswa aktif). Buat view
  `admin/dashboard/index.blade.php` dengan kartu Tailwind.

- [ ] **TASK-1.2** Perbarui `Kaprodi\DashboardController@index`:
  tambahkan query antrian, judul disetujui bulan ini, top 3 dosen
  paling tersedia. Buat view `kaprodi/dashboard/index.blade.php`.

- [ ] **TASK-1.3** Perbarui `Mahasiswa\DashboardController@index`:
  tampilkan status pengajuan judul aktif dan daftar surat aktif milik
  mahasiswa. Perbarui view `mahasiswa/dashboard.blade.php`.

---

## Fase 2: Dashboard Rasio Dosen

- [ ] **TASK-2.1** Tambahkan method `rasio()` di `Admin\DashboardController`
  dan `Kaprodi\DashboardController` yang menggunakan
  `Cache::remember('rasio_dosen', 60, ...)`.

- [ ] **TASK-2.2** Tambahkan model observer `DosenObserver` atau invalidasi
  cache manual di `PengajuanStateService` setiap kali pembimbing/penguji
  ditetapkan: `Cache::forget('rasio_dosen')`.

- [ ] **TASK-2.3** Buat view `admin/dashboard/rasio.blade.php` dan
  `kaprodi/dashboard/rasio.blade.php` dengan tabel rasio dosen sesuai
  design.md (kolom Nama, NIP, Bimbingan, Penguji, Kapasitas, Status).

- [ ] **TASK-2.4** Tambahkan route:
  `GET /admin/dashboard/rasio` dan `GET /kaprodi/dashboard/rasio`.
  Tambahkan link ke halaman ini dari sidebar navigasi.

- [ ] **TASK-2.5** Uji: buat beberapa pengajuan dengan pembimbing berbeda,
  buka halaman rasio → angka harus akurat dan terurut benar.

---

## Fase 3: Arsip Surat (Admin)

- [ ] **TASK-3.1** Buat `Admin\ArsipSuratController`:
  `php artisan make:controller Admin/ArsipSuratController`
  Method `index` dengan filter dan paginasi sesuai design.md.

- [ ] **TASK-3.2** Buat view `admin/arsip/index.blade.php`:
  - Form filter di atas: search NIM/nama, dropdown jenis surat, dropdown
    status, date range dari–sampai, tombol Filter dan Reset
  - Tabel hasil dengan kolom: Mahasiswa, NIM, Jenis Surat, Tanggal, Status,
    Aksi (download docx/pdf/scan)
  - Paginasi di bawah

- [ ] **TASK-3.3** Daftarkan route `GET /admin/arsip` di `routes/admin.php`.

- [ ] **TASK-3.4** Uji: filter by jenis surat → hasil sesuai. Filter by
  nama mahasiswa → hasil sesuai. Paginasi bekerja.

---

## Fase 4: Import Massal Mahasiswa dari Excel

- [ ] **TASK-4.1** Pastikan `maatwebsite/excel` sudah terinstall dari M1.
  Jika belum: `composer require maatwebsite/excel`.

- [ ] **TASK-4.2** Buat `app/Imports/MahasiswaImport.php` sesuai design.md:
  implementasi `ToCollection`, `WithHeadingRow`. Logic per-baris: cek
  duplikat, validasi, buat User + Mahasiswa dalam DB transaction.

- [ ] **TASK-4.3** Buat `Admin\MahasiswaImportController`:
  `php artisan make:controller Admin/MahasiswaImportController`
  Method `create` (tampilkan form + contoh format) dan `store`
  (validasi file, proses import, tampilkan ringkasan hasil).

- [ ] **TASK-4.4** Buat view `admin/mahasiswa/import.blade.php`:
  - Form upload file dengan hint format kolom yang diterima
  - Setelah proses: tampilkan ringkasan (N berhasil, N dilewati, N gagal)
  - Jika ada yang gagal: tampilkan tabel baris yang gagal beserta alasan

- [ ] **TASK-4.5** Daftarkan route:
  `GET  /admin/mahasiswa/import` → `MahasiswaImportController@create`
  `POST /admin/mahasiswa/import` → `MahasiswaImportController@store`

- [ ] **TASK-4.6** Uji: upload file Excel dengan 5 baris valid, 2 duplikat,
  1 email invalid. Verifikasi ringkasan: 5 berhasil, 2 dilewati, 1 gagal
  dengan alasan yang benar. Login dengan akun yang baru diimport → berhasil.

---

## Fase 5: Polish — Flash Message & Error Handling

- [ ] **TASK-5.1** Buat komponen Blade `resources/views/components/flash-message.blade.php`
  untuk success dan error flash sesuai design.md.

- [ ] **TASK-5.2** Tambahkan `<x-flash-message />` di `layouts/app.blade.php`.

- [ ] **TASK-5.3** Audit semua controller — pastikan setiap aksi yang sukses
  memanggil `->with('success', '...')` dan setiap aksi yang gagal
  mengembalikan `->with('error', '...')` atau `->withErrors(...)`.

- [ ] **TASK-5.4** Tambahkan handler exception kustom di `bootstrap/app.php`
  untuk `SuratGenerationException` dan `InvalidStateTransitionException`
  sesuai design.md.

- [ ] **TASK-5.5** Pastikan `APP_DEBUG=false` di `.env.example` dan dokumentasikan
  bahwa production harus pakai `APP_DEBUG=false`.

---

## Fase 6: Polish — UI & Responsiveness

- [ ] **TASK-6.1** Audit semua halaman mahasiswa di layar mobile (375px):
  tabel riwayat harus scrollable horizontal, form tidak overflow.

- [ ] **TASK-6.2** Audit sidebar navigasi: pastikan menu yang aktif
  memiliki highlight (active state Tailwind), dan semua link mengarah
  ke route yang benar.

- [ ] **TASK-6.3** Audit semua tombol aksi — pastikan ada konfirmasi
  JavaScript (`confirm()` atau modal Alpine.js) untuk aksi destructive
  seperti tolak pengajuan.

- [ ] **TASK-6.4** Pastikan semua form memiliki CSRF token (`@csrf`) dan
  method spoofing yang benar untuk PUT/DELETE.

---

## Fase 7: Testing Menyeluruh

- [ ] **TASK-7.1** Jalankan `php artisan route:list` — pastikan tidak ada
  route yang missing controller atau middleware yang salah.

- [ ] **TASK-7.2** Jalankan `vendor/bin/pint --dirty` — perbaiki semua
  formatting issue yang ditemukan.

- [ ] **TASK-7.3** Lakukan smoke test manual dengan 3 akun (mahasiswa, admin,
  kaprodi) dari awal sampai akhir:
  - Mahasiswa: ajukan judul → semua form pengajuan → riwayat → download
  - Admin: verifikasi → generate surat → upload scan → arsip → import Excel
  - Kaprodi: antrian → tetapkan pembimbing → tetapkan penguji → dashboard rasio

- [ ] **TASK-7.4** Cek semua halaman tidak ada `dd()`, `dump()`, atau
  `var_dump()` dengan: `grep -r "dd\|dump\|var_dump" app/ resources/`

- [ ] **TASK-7.5** Uji generate semua 4 jenis surat dengan data dummy
  terpanjang (nama 50 karakter, judul 2 baris) — pastikan layout tidak
  pecah di docx maupun pdf.

- [ ] **TASK-7.6** Uji keamanan: login sebagai mahasiswa, coba akses URL
  admin dan kaprodi langsung → harus 403. Coba akses data mahasiswa
  lain via URL manipulation → harus 403/404.

---

## Checklist Definition of Done (Keseluruhan Aplikasi)

- [ ] Dashboard admin dan kaprodi menampilkan statistik yang akurat.
- [ ] Tabel rasio dosen real-time, ter-cache, dan terurut benar.
- [ ] Arsip surat dengan filter dan paginasi berfungsi.
- [ ] Import Excel 500 baris selesai dalam < 30 detik dengan ringkasan akurat.
- [ ] Flash message tampil konsisten di semua aksi.
- [ ] Tidak ada stack trace tampil ke user di production mode.
- [ ] Semua 4 jenis surat bisa digenerate dengan layout tidak pecah.
- [ ] Tidak ada role yang bisa akses data atau halaman role lain.
- [ ] Semua file PHP melewati Laravel Pint tanpa error.
- [ ] `php artisan route:list` bersih, tidak ada error.
