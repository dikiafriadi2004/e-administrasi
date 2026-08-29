# Tasks — Milestone 4: Verifikasi Admin & Upload Scan TTD

Prasyarat: Milestone 1, 2, 3 selesai.

---

## Fase 1: Service State Machine

- [ ] **TASK-1.1** Buat exception class:
  `php artisan make:exception InvalidStateTransitionException`

- [ ] **TASK-1.2** Buat `PengajuanStateService`:
  `php artisan make:class Services/PengajuanStateService`
  Implementasi semua method transisi status (verifikasi, tolak,
  setujuiJudul, terimaSurat, uploadScan, selesaikan) beserta
  helper `recordHistory` yang selalu mencatat ke `status_histories`.
  Setiap method harus validasi status awal yang benar sebelum transisi.

- [ ] **TASK-1.3** Bind `PengajuanStateService` di `AppServiceProvider`
  agar bisa di-inject via constructor (atau biarkan Laravel resolve
  otomatis via type-hint jika tidak ada interface).

---

## Fase 2: Controller & Routes Admin

- [ ] **TASK-2.1** Buat `Admin\AntrianPengajuanController`:
  `php artisan make:controller Admin/AntrianPengajuanController`
  Method: `index`, `showJudul`, `showSurat`, `verifikasi`, `tolak`,
  `selesaikan`. Inject `PengajuanStateService`.

- [ ] **TASK-2.2** Buat `Admin\BuatSuratLangsungController`:
  `php artisan make:controller Admin/BuatSuratLangsungController`
  Method: `index`, `create`, `store`. Store langsung buat pengajuan
  dengan status `menunggu_ttd` dan trigger generate.

- [ ] **TASK-2.3** Perbarui `Admin\GenerateSuratController` (dari M2):
  tambahkan method `uploadScan` dan `selesaikan`.

- [ ] **TASK-2.4** Daftarkan semua route admin antrian dan buat-surat
  di `routes/admin.php` sesuai design.md.

---

## Fase 3: Views Admin

- [ ] **TASK-3.1** Buat `admin/antrian/index.blade.php`:
  - Tabel dengan kolom: Mahasiswa, NIM, Jenis Pengajuan, Tanggal, Status, Aksi
  - Filter tab: "Semua Pengajuan Baru" (status diajukan)
  - Setiap baris ada link ke halaman detail

- [ ] **TASK-3.2** Buat `admin/antrian/show-judul.blade.php`:
  - Tampilkan semua data pengajuan judul
  - Link download dokumen pendukung jika ada
  - Tombol "Verifikasi" dan "Tolak" (tolak membuka form catatan alasan)
  - Timeline riwayat status di bawah

- [ ] **TASK-3.3** Buat `admin/antrian/show-surat.blade.php`:
  - Tampilkan semua data pengajuan surat
  - Tombol aksi sesuai status saat ini:
    - status `diajukan`: Verifikasi / Tolak
    - status `menunggu_ttd`: Generate Surat / Upload Scan
    - status `sudah_ditandatangani`: Tandai Selesai

- [ ] **TASK-3.4** Buat `admin/buat-surat/create.blade.php`:
  - Dropdown pilih mahasiswa (cari by NIM atau nama)
  - Select jenis surat
  - Fields dinamis sesuai jenis surat yang dipilih (gunakan Alpine.js
    atau Livewire untuk show/hide field)

---

## Fase 4: Controller & Routes Kaprodi

- [ ] **TASK-4.1** Buat `Kaprodi\AntrianPengajuanController`:
  `php artisan make:controller Kaprodi/AntrianPengajuanController`
  Method: `index`, `showJudul`, `showSurat`, `setujui`, `tolak`,
  `terima`, `selesaikan`.

- [ ] **TASK-4.2** Buat `Kaprodi\UploadScanController`:
  `php artisan make:controller Kaprodi/UploadScanController`
  Method: `store` — validasi file PDF max 10MB, simpan ke storage,
  panggil `stateService->uploadScan()`.

- [ ] **TASK-4.3** Daftarkan semua route kaprodi di `routes/kaprodi.php`
  sesuai design.md.

---

## Fase 5: Views Kaprodi

- [ ] **TASK-5.1** Buat `kaprodi/antrian/index.blade.php`:
  - Tabel pengajuan status `diverifikasi` (sudah lewat filter admin)
  - Tampilkan badge warna berbeda untuk jenis pengajuan (judul vs surat)

- [ ] **TASK-5.2** Buat `kaprodi/antrian/show-judul.blade.php`:
  - Detail pengajuan judul
  - Tombol "Setujui Judul" dan "Tolak" dengan form catatan alasan
  - (Penentuan pembimbing akan ditambah di Milestone 5)

- [ ] **TASK-5.3** Buat `kaprodi/antrian/show-surat.blade.php`:
  - Detail pengajuan surat
  - Tombol aksi sesuai status: Terima / Tolak / Upload Scan / Tandai Selesai

---

## Fase 6: Uji Alur End-to-End

- [ ] **TASK-6.1** Uji alur lengkap Surat Aktif Kuliah:
  1. Mahasiswa ajukan → status `diajukan`
  2. Admin verifikasi → status `diverifikasi`
  3. Kaprodi terima → status `menunggu_ttd`
  4. Admin generate surat → file docx & pdf terbuat
  5. Admin upload scan → status `sudah_ditandatangani`, scan tersimpan
  6. Admin tandai selesai → status `selesai`
  7. Mahasiswa buka riwayat → tombol download scan muncul, bisa didownload

- [ ] **TASK-6.2** Uji penolakan: Admin tolak tanpa catatan → validasi error.
  Admin tolak dengan catatan → status ditolak, catatan tampil di riwayat
  mahasiswa.

- [ ] **TASK-6.3** Uji guard state: coba upload scan pada pengajuan yang
  masih `diajukan` (manipulasi POST langsung) → harus dapat 422.

- [ ] **TASK-6.4** Uji buat surat langsung oleh Admin: pilih mahasiswa,
  pilih jenis surat, isi data, submit → surat tergenerate langsung, tampil
  di riwayat mahasiswa.

- [ ] **TASK-6.5** Verifikasi setiap perubahan status tercatat di
  `status_histories` dengan user yang benar.

---

## Checklist Definition of Done

- [ ] Alur Surat Aktif Kuliah berjalan penuh dari diajukan hingga selesai.
- [ ] Setiap transisi status tercatat di status_histories.
- [ ] Penolakan wajib mengisi catatan alasan — catatan tampil di riwayat mahasiswa.
- [ ] Guard state: aksi tidak valid pada status yang salah menghasilkan 422.
- [ ] Upload scan hanya menerima PDF max 10MB.
- [ ] Admin bisa buat surat langsung tanpa pengajuan mahasiswa.
- [ ] Scan yang terupload langsung tersedia di halaman download mahasiswa.
