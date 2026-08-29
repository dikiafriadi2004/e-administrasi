# Tasks — Milestone 3: Alur Pengajuan Mahasiswa

Prasyarat: Milestone 1 & 2 selesai.

---

## Fase 1: Setup Livewire & Database Tambahan

- [ ] **TASK-1.1** Install Livewire: `composer require livewire/livewire`.
  Tambahkan `@livewireStyles` di `<head>` dan `@livewireScripts` sebelum
  `</body>` pada `layouts/app.blade.php`.

- [ ] **TASK-1.2** Buat migration untuk menambah kolom `file_pendukung` dan
  `nama_file_pendukung` ke `pengajuan_judul` dan `pengajuan_surat`:
  `php artisan make:migration add_file_pendukung_to_pengajuan_tables`

- [ ] **TASK-1.3** Buat migration tabel `status_histories`:
  `php artisan make:migration create_status_histories_table`
  Kolom: id, model_type, model_id, status_lama (nullable), status_baru,
  catatan (nullable), changed_by (FK users), created_at.

- [ ] **TASK-1.4** Jalankan `php artisan migrate`.

- [ ] **TASK-1.5** Perbarui model `PengajuanJudul` dan `PengajuanSurat`:
  tambahkan kolom baru ke `$fillable`. Buat model `StatusHistory` dengan
  relasi polymorphic (`morphTo`).

- [ ] **TASK-1.6** Buat `PengajuanJudulPolicy` dan `PengajuanSuratPolicy`
  (jika belum ada dari M2) dengan method `view` dan `download` yang
  scope ke mahasiswa pemilik.
  `php artisan make:policy PengajuanJudulPolicy --model=PengajuanJudul`

---

## Fase 2: Livewire Component — Pengajuan Judul Skripsi

- [ ] **TASK-2.1** Buat Livewire component:
  `php artisan make:livewire Mahasiswa/PengajuanJudulForm`

- [ ] **TASK-2.2** Implementasi `PengajuanJudulForm`:
  - Public properties: `judul`, `bidangKajian`, `ringkasan`, `filePendukung`
  - Gunakan trait `WithFileUploads`
  - Rules validasi: judul wajib max 500, bidangKajian wajib, ringkasan
    wajib min 50, filePendukung nullable pdf/doc/docx max 10MB
  - Method `submit()`: cek tidak ada pengajuan aktif, simpan ke DB,
    catat `StatusHistory`, redirect ke riwayat

- [ ] **TASK-2.3** Buat view `livewire/mahasiswa/pengajuan-judul-form.blade.php`:
  - Layout 2 kolom: kiri = form fields, kanan = panel preview reaktif
  - Preview menampilkan: Nama, NIM, Judul (atau "Belum diisi"), Bidang Kajian,
    Ringkasan (truncated 100 karakter)
  - Tombol submit dengan loading state (`wire:loading`)

- [ ] **TASK-2.4** Buat controller `Mahasiswa\PengajuanJudulController` dengan
  method `create` (tampilkan form, cek tidak ada pengajuan aktif) dan
  `show` (detail pengajuan dengan policy check).
  `php artisan make:controller Mahasiswa/PengajuanJudulController`

- [ ] **TASK-2.5** Buat view `mahasiswa/pengajuan/judul/create.blade.php`
  yang embed `<livewire:mahasiswa.pengajuan-judul-form />`.

- [ ] **TASK-2.6** Daftarkan route di `routes/mahasiswa.php`:
  `GET  /mahasiswa/pengajuan/judul/create` → `PengajuanJudulController@create`
  `GET  /mahasiswa/pengajuan/judul/{id}`   → `PengajuanJudulController@show`

- [ ] **TASK-2.7** Uji: login sebagai mahasiswa, isi form judul, submit.
  Verifikasi record terbuat di DB, status history tercatat, preview
  reaktif saat mengetik.

---

## Fase 3: Livewire Component — Surat Aktif Kuliah

- [ ] **TASK-3.1** Buat `php artisan make:livewire Mahasiswa/PengajuanAktifKuliahForm`

- [ ] **TASK-3.2** Implementasi component:
  - Properties: `keperluan`, `tujuanInstansi`
  - Rules: keduanya wajib, max 255
  - Submit: simpan `pengajuan_surat` dengan `jenis_surat = aktif_kuliah`,
    `data_form = ['keperluan' => ..., 'tujuan_instansi' => ...]`

- [ ] **TASK-3.3** Buat view component dengan panel preview: Nama, NIM,
  Keperluan, Tujuan Instansi.

- [ ] **TASK-3.4** Buat controller `Mahasiswa\PengajuanSuratController` (satu
  controller untuk semua jenis surat, dibedakan via parameter `jenis`).
  Method: `createAktifKuliah`, `storeSurat`, `show`, `download`.

- [ ] **TASK-3.5** Daftarkan route dan buat view wrapper.

- [ ] **TASK-3.6** Uji: ajukan surat aktif kuliah, verifikasi record DB benar.

---

## Fase 4: Livewire Component — Seminar Proposal

- [ ] **TASK-4.1** Buat `php artisan make:livewire Mahasiswa/PengajuanSeminarForm`

- [ ] **TASK-4.2** Implementasi component:
  - Inject data judul & pembimbing dari `pengajuan_judul` yang disetujui
    (read-only, tidak bisa diubah mahasiswa)
  - Properties yang bisa diisi: `tanggalRencana`, `filePendukung`
  - Validasi: tanggal wajib dan harus di masa depan, file optional max 10MB

- [ ] **TASK-4.3** Buat controller `Mahasiswa\PengajuanSuratController@createSeminar`:
  - Cek guard: `pengajuan_judul.status === 'disetujui'`
  - Jika tidak memenuhi: return view `terkunci` dengan pesan yang sesuai
  - Jika memenuhi: pass data judul ke view/component

- [ ] **TASK-4.4** Buat view `mahasiswa/pengajuan/terkunci.blade.php` yang
  generik dengan variabel `$pesan` dan link ke halaman riwayat.

- [ ] **TASK-4.5** Buat view component seminar dengan preview: Judul (auto),
  Pembimbing (auto), Tanggal Rencana.

- [ ] **TASK-4.6** Uji dua skenario: (a) akses saat judul belum disetujui →
  halaman terkunci muncul; (b) akses setelah judul disetujui (update
  status manual via tinker) → form muncul dengan data auto-terisi.

---

## Fase 5: Livewire Component — Sidang Skripsi

- [ ] **TASK-5.1** Buat `php artisan make:livewire Mahasiswa/PengajuanSidangForm`

- [ ] **TASK-5.2** Implementasi component:
  - Properties: `tanggalRencana`, `waktuRencana`, `tempat`, `filePendukung`
  - Guard di controller: seminar proposal harus berstatus `selesai`
  - Data auto-terisi: judul, pembimbing dari pengajuan_judul

- [ ] **TASK-5.3** Buat view dengan checklist dokumen pendukung yang wajib
  diupload (bisa list statis sesuai ketentuan prodi, mis. draft skripsi,
  lembar persetujuan, dll).

- [ ] **TASK-5.4** Tambahkan route dan integrasikan ke controller.

- [ ] **TASK-5.5** Uji guard: akses saat seminar belum selesai → terkunci.

---

## Fase 6: Halaman Riwayat & Download

- [ ] **TASK-6.1** Buat `Mahasiswa\RiwayatController@index`:
  - Load semua `pengajuan_judul` milik mahasiswa ini (scoped by mahasiswa_id)
  - Load semua `pengajuan_surat` milik mahasiswa ini (scoped)
  - Eager load relasi yang dibutuhkan

- [ ] **TASK-6.2** Buat view `mahasiswa/riwayat/index.blade.php`:
  - Section 1: tabel riwayat judul dengan status badge berwarna
  - Section 2: tabel riwayat surat dengan kolom jenis, tanggal, status,
    dan tombol aksi (download docx, pdf, scan — kondisional sesuai rules)

- [ ] **TASK-6.3** Implementasi method `download` di `PengajuanSuratController`:
  - Authorize via policy `download`
  - Tipe parameter: `docx`, `pdf`, `scan`
  - Stream file via `Storage::disk('private')->download($path)`
  - Abort 404 jika file tidak ada

- [ ] **TASK-6.4** Daftarkan route download:
  `GET /mahasiswa/surat/{surat}/download/{tipe}` → `PengajuanSuratController@download`

- [ ] **TASK-6.5** Uji scoping: login sebagai mahasiswa A, manipulasi URL
  dengan ID pengajuan mahasiswa B → harus dapat 403 atau 404.

- [ ] **TASK-6.6** Uji download: generate surat via admin (dari M2), buka
  riwayat mahasiswa, download docx dan pdf, buka file — konten benar.

---

## Checklist Definition of Done

- [ ] Semua 4 form pengajuan bisa disubmit dan membuat record di DB.
- [ ] Preview Livewire reaktif di semua form.
- [ ] Guard seminar dan sidang bekerja di level controller (bukan hanya UI).
- [ ] Mahasiswa tidak bisa akses data mahasiswa lain via URL manipulation.
- [ ] Halaman riwayat menampilkan semua pengajuan milik sendiri dengan status benar.
- [ ] Download docx/pdf hanya muncul saat status bukan ditolak.
- [ ] Download scan selalu tersedia begitu file diupload.
- [ ] File pendukung mahasiswa tersimpan di storage private dengan UUID.
