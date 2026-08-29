# Requirements — Milestone 4: Verifikasi Admin & Upload Scan TTD

## Overview

Modul ini menangani sisi Admin dan Kaprodi dalam alur pengajuan: Admin
memverifikasi kelengkapan pengajuan, Kaprodi menerima atau menolaknya,
salah satu dari keduanya mengupload hasil scan surat yang sudah
ditandatangani, dan status pengajuan berubah otomatis mengikuti setiap aksi.

---

## Functional Requirements

### REQ-VER-001: Antrian Pengajuan di Admin
- **WHEN** Admin membuka halaman antrian pengajuan,
  **THE SYSTEM SHALL** menampilkan semua pengajuan (judul maupun surat)
  dengan status `diajukan` yang belum diverifikasi, diurutkan dari yang
  paling lama menunggu.

- **THE SYSTEM SHALL** menampilkan detail pengajuan termasuk nama mahasiswa,
  NIM, jenis pengajuan, tanggal ajukan, dan dokumen pendukung yang bisa
  dipreview/download.

### REQ-VER-002: Verifikasi Kelengkapan oleh Admin
- **WHEN** Admin menandai pengajuan sebagai terverifikasi,
  **THE SYSTEM SHALL** mengubah status pengajuan dari `diajukan` menjadi
  `diverifikasi`, mencatat user yang memverifikasi dan timestamp-nya di
  `status_histories`, dan memindahkan pengajuan ke antrian Kaprodi.

- **WHEN** Admin menolak pengajuan di tahap verifikasi (kelengkapan tidak
  memenuhi syarat),
  **THE SYSTEM SHALL** mengubah status menjadi `ditolak`, mewajibkan Admin
  mengisi catatan alasan penolakan, dan mencatatnya agar terlihat di
  riwayat mahasiswa.

### REQ-VER-003: Antrian Pengajuan di Kaprodi
- **WHEN** Kaprodi membuka halaman antrian,
  **THE SYSTEM SHALL** menampilkan semua pengajuan dengan status
  `diverifikasi` (sudah melewati filter Admin), diurutkan dari yang
  paling lama menunggu.

### REQ-VER-004: Terima / Tolak oleh Kaprodi
- **WHEN** Kaprodi menerima pengajuan surat (bukan judul),
  **THE SYSTEM SHALL** mengubah status menjadi `menunggu_ttd`.

- **WHEN** Kaprodi menerima pengajuan judul skripsi,
  **THE SYSTEM SHALL** mengubah status `pengajuan_judul` menjadi
  `disetujui` — ini membuka akses form seminar proposal bagi mahasiswa.

- **WHEN** Kaprodi menolak pengajuan,
  **THE SYSTEM SHALL** mewajibkan pengisian catatan alasan, mengubah
  status menjadi `ditolak`, dan mencatat di `status_histories`.

- **THE SYSTEM SHALL** menampilkan catatan penolakan di halaman riwayat
  mahasiswa yang bersangkutan.

### REQ-VER-005: Generate Surat oleh Admin/Kaprodi
- **WHEN** pengajuan surat berstatus `menunggu_ttd`,
  **THE SYSTEM SHALL** menampilkan tombol "Generate Surat" di halaman
  detail pengajuan untuk Admin dan Kaprodi.

- **WHEN** Admin atau Kaprodi memicu generate surat,
  **THE SYSTEM SHALL** memanggil `SuratGeneratorService`, menghasilkan
  file `.docx` dan `.pdf`, dan menyimpan path-nya ke record pengajuan.

### REQ-VER-006: Upload Hasil Scan Surat Sudah TTD
- **WHEN** Admin atau Kaprodi mengupload file scan (PDF) dari surat yang
  sudah ditandatangani,
  **THE SYSTEM SHALL** menyimpan file di storage terproteksi, mengisi
  kolom `file_scan` pada record pengajuan, dan otomatis mengubah status
  menjadi `sudah_ditandatangani`.

- **IF** file bukan PDF atau ukurannya melebihi 10 MB,
  **THEN** sistem menolak upload dan menampilkan pesan validasi.

- **THE SYSTEM SHALL NOT** menimpa `file_docx` atau `file_pdf` yang sudah
  ada saat scan diupload.

### REQ-VER-007: Tandai Selesai
- **WHEN** Admin atau Kaprodi menandai pengajuan sebagai selesai (setelah
  scan terupload dan semua proses selesai),
  **THE SYSTEM SHALL** mengubah status menjadi `selesai`.

- Status `selesai` pada `pengajuan_surat` seminar proposal membuka akses
  form pengajuan sidang skripsi bagi mahasiswa bersangkutan.

### REQ-VER-008: Buat Surat Langsung oleh Admin
- **WHEN** Admin membuat surat tanpa pengajuan dari mahasiswa (misal surat
  undangan dosen penguji yang dibuat langsung),
  **THE SYSTEM SHALL** menyediakan form untuk Admin memilih mahasiswa,
  jenis surat, dan mengisi data yang diperlukan, lalu generate surat
  langsung tanpa melalui alur verifikasi.

---

## Non-Functional Requirements

- **NFR-VER-001:** Setiap perubahan status harus dicatat di `status_histories`
  dengan `changed_by` yang benar — tidak ada perubahan status tanpa log.
- **NFR-VER-002:** Tombol aksi (verifikasi, tolak, upload scan) hanya muncul
  untuk role yang berhak dan hanya pada status yang benar — cek status
  divalidasi di controller, bukan hanya di UI.
- **NFR-VER-003:** File scan disimpan di `storage/app/private/surat/{id}/`
  dengan nama file UUID, tidak menimpa file lain.
- **NFR-VER-004:** Admin tidak bisa skip state — tidak bisa langsung upload
  scan pada pengajuan yang masih `diajukan` tanpa lewat verifikasi dulu.
