# Requirements — Milestone 3: Alur Pengajuan Mahasiswa

## Overview

Modul ini membangun semua form pengajuan dari sisi mahasiswa: pengajuan judul
skripsi, surat aktif kuliah, seminar proposal, dan sidang skripsi. Setiap form
dilengkapi preview live via Livewire. Mahasiswa juga bisa memantau riwayat
status dan mengunduh dokumen miliknya.

---

## Functional Requirements

### REQ-MAH-001: Pengajuan Judul Skripsi
- **WHEN** mahasiswa mengisi form pengajuan judul (judul, bidang kajian,
  ringkasan singkat, upload dokumen pendukung opsional) dan mengirimkan,
  **THE SYSTEM SHALL** membuat record `pengajuan_judul` dengan status
  `diajukan` dan menampilkan konfirmasi berhasil.

- **IF** mahasiswa sudah memiliki pengajuan judul dengan status selain
  `ditolak`,
  **THEN** sistem harus mencegah pengajuan judul baru dan menampilkan
  pesan bahwa pengajuan sebelumnya masih aktif.

- **WHEN** pengajuan judul ditolak oleh Kaprodi,
  **THE SYSTEM SHALL** mengizinkan mahasiswa mengajukan judul baru (bukan
  mengedit yang lama).

### REQ-MAH-002: Pengajuan Surat Aktif Kuliah
- **WHEN** mahasiswa mengisi form surat aktif kuliah (keperluan surat,
  tujuan instansi) dan mengirimkan,
  **THE SYSTEM SHALL** membuat record `pengajuan_surat` dengan
  `jenis_surat = aktif_kuliah` dan status `diajukan`.

- **THE SYSTEM SHALL** mengizinkan mahasiswa mengajukan surat aktif kuliah
  kapan saja, terlepas dari status pengajuan judul.

### REQ-MAH-003: Pengajuan Seminar Proposal
- **WHEN** mahasiswa mencoba mengakses form pengajuan seminar proposal
  sementara pengajuan judulnya belum berstatus `disetujui`,
  **THE SYSTEM SHALL** menampilkan halaman terkunci dengan penjelasan
  bahwa judul harus disetujui dulu.

- **WHEN** pengajuan judul sudah berstatus `disetujui` dan mahasiswa
  mengisi form seminar proposal (tanggal rencana, upload dokumen pendukung),
  **THE SYSTEM SHALL** otomatis mengisi data judul dan dosen pembimbing
  dari pengajuan judul yang disetujui tanpa mahasiswa perlu input ulang.

- **WHEN** mahasiswa mengirimkan form seminar proposal,
  **THE SYSTEM SHALL** membuat record `pengajuan_surat` dengan
  `jenis_surat = seminar_proposal` yang terhubung ke `pengajuan_judul_id`.

### REQ-MAH-004: Pengajuan Sidang Skripsi
- **WHEN** mahasiswa mencoba mengakses form pengajuan sidang skripsi
  sementara seminar proposal belum selesai,
  **THE SYSTEM SHALL** menampilkan halaman terkunci dengan penjelasan
  tahap yang belum terpenuhi.

- **WHEN** seminar proposal sudah berstatus `selesai` dan mahasiswa
  mengisi form sidang skripsi (tanggal rencana, waktu, tempat, checklist
  upload dokumen pendukung sidang),
  **THE SYSTEM SHALL** otomatis mengisi judul dan dosen pembimbing dari
  data sebelumnya.

- **WHEN** mahasiswa mengirimkan form sidang skripsi,
  **THE SYSTEM SHALL** membuat record `pengajuan_surat` dengan
  `jenis_surat = sidang_skripsi`.

### REQ-MAH-005: Preview Live di Form Pengajuan
- **WHILE** mahasiswa mengisi form pengajuan apapun,
  **THE SYSTEM SHALL** menampilkan panel ringkasan data secara real-time
  (menggunakan Livewire) yang mencerminkan nilai field yang sedang diisi.

- Preview adalah ringkasan data (nama, NIM, judul, tanggal, dst) —
  **bukan** replika tampilan kop surat.

- **IF** field wajib belum diisi,
  **THEN** panel preview menampilkan placeholder teks "Belum diisi".

### REQ-MAH-006: Upload Dokumen Pendukung
- **WHEN** mahasiswa mengupload dokumen pendukung dalam form pengajuan,
  **THE SYSTEM SHALL** menerima file PDF atau docx maksimal 10 MB,
  menyimpannya di storage terproteksi, dan menampilkan nama file yang
  terupload.

- **IF** file melebihi batas ukuran atau tipe tidak didukung,
  **THEN** sistem menampilkan pesan error validasi yang jelas sebelum
  form dikirim.

### REQ-MAH-007: Riwayat & Status Pengajuan
- **WHEN** mahasiswa membuka halaman riwayat,
  **THE SYSTEM SHALL** menampilkan semua pengajuan milik mahasiswa tersebut
  (judul maupun surat) dengan status terkini, tanggal pengajuan, dan
  catatan penolakan jika ada.

- **THE SYSTEM SHALL NOT** menampilkan data pengajuan mahasiswa lain,
  bahkan jika ID pengajuan dimanipulasi di URL.

- **WHEN** mahasiswa mengklik detail suatu pengajuan,
  **THE SYSTEM SHALL** menampilkan riwayat lengkap perubahan status
  pengajuan tersebut.

### REQ-MAH-008: Download Dokumen Belum TTD
- **WHEN** mahasiswa membuka halaman riwayat dan status pengajuan surat
  bukan `ditolak`,
  **THE SYSTEM SHALL** menampilkan tombol download untuk file `.docx`
  dan `.pdf` (belum TTD) jika sudah digenerate.

- **THE SYSTEM SHALL** mengizinkan download berulang kali selama status
  belum `ditolak`.

- **WHEN** status pengajuan berubah menjadi `ditolak`,
  **THE SYSTEM SHALL** menyembunyikan tombol download dokumen belum TTD.

### REQ-MAH-009: Download Hasil Scan Surat Sudah TTD
- **WHEN** Admin atau Kaprodi mengupload scan surat yang sudah ditandatangani,
  **THE SYSTEM SHALL** secara otomatis menampilkan tombol download scan
  di halaman riwayat mahasiswa yang bersangkutan.

- **THE SYSTEM SHALL** mengizinkan download scan surat tanpa batas waktu,
  terlepas dari status pengajuan saat itu.

---

## Non-Functional Requirements

- **NFR-MAH-001:** Scoping data mahasiswa diterapkan di query level
  (`where('mahasiswa_id', auth()->user()->mahasiswa->id)`), bukan hanya
  di UI — akses via URL manipulation harus menghasilkan 404 atau 403.
- **NFR-MAH-002:** Livewire component harus debounce input text minimal
  300ms agar tidak terlalu banyak request ke server saat mengetik.
- **NFR-MAH-003:** Dokumen pendukung yang diupload mahasiswa disimpan di
  `storage/app/private/pendukung/{pengajuan_id}/`.
- **NFR-MAH-004:** Nama file upload diubah ke nama yang tidak bisa ditebak
  (UUID) saat disimpan — nama asli disimpan di kolom terpisah untuk
  ditampilkan di UI.
