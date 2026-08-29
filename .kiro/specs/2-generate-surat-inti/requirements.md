# Requirements — Milestone 2: Generate Surat Inti

## Overview

Inti mesin dokumen aplikasi: template `.docx` per jenis surat disimpan di
server, diisi via PHPWord `TemplateProcessor`, lalu dikonversi ke PDF melalui
LibreOffice headless. Milestone ini membuktikan satu jenis surat jalan
end-to-end sebelum modul pengajuan dibangun di atas-nya.

---

## Functional Requirements

### REQ-GEN-001: Penyimpanan Template Docx
- **WHEN** developer atau Admin mengupload file template `.docx` untuk suatu
  jenis surat,
  **THE SYSTEM SHALL** menyimpannya di direktori server yang terproteksi
  (tidak dapat diakses publik langsung via URL) dan mencatat path-nya di
  tabel `templates_surat`.

- **IF** sudah ada template untuk jenis surat yang sama,
  **THEN** sistem menyimpan versi baru dengan nomor versi yang dinaikkan,
  tanpa menghapus versi lama (untuk audit trail).

### REQ-GEN-002: Generate Dokumen dari Template
- **WHEN** sistem diminta generate dokumen untuk suatu pengajuan,
  **THE SYSTEM SHALL** mengambil template aktif untuk jenis surat tersebut,
  mengganti semua placeholder dengan data dari database, dan menyimpan
  hasilnya sebagai file `.docx` baru yang unik per pengajuan.

- **IF** ada placeholder di template yang tidak memiliki nilai di database,
  **THEN** sistem mengisi dengan string kosong dan mencatat warning di log
  (tidak boleh gagal hanya karena placeholder opsional tidak terisi).

### REQ-GEN-003: Konversi Docx ke PDF
- **WHEN** file `.docx` hasil generate sudah tersimpan,
  **THE SYSTEM SHALL** menjalankan konversi ke PDF menggunakan LibreOffice
  headless di server tanpa menggunakan layanan cloud berbayar.

- **WHEN** konversi berhasil,
  **THE SYSTEM SHALL** menyimpan path file PDF di kolom `file_pdf` pada
  record pengajuan yang bersangkutan.

- **IF** proses LibreOffice gagal (timeout, binary tidak ditemukan, error),
  **THEN** sistem harus mencatat error lengkap di log aplikasi dan
  menandai status generate sebagai gagal — tidak boleh menyimpan path PDF
  yang tidak valid.

### REQ-GEN-004: Penomoran Surat Otomatis
- **WHEN** surat baru digenerate,
  **THE SYSTEM SHALL** membuat nomor surat otomatis dalam format yang sudah
  dikonfigurasi (mis. `001/UN-XX/FAK/PRODI/VIII/2026`), dengan urutan
  nomor yang naik per tahun dan per jenis surat.

- **THE SYSTEM SHALL** memastikan nomor surat unik — tidak boleh ada dua
  surat dengan nomor yang sama dalam satu tahun.

### REQ-GEN-005: Tiga File Terpisah per Pengajuan
- **THE SYSTEM SHALL** menyimpan tiga path file secara independen per
  pengajuan:
  - `file_docx` — hasil generate dari template (belum TTD)
  - `file_pdf`  — hasil konversi dari `file_docx` (belum TTD)
  - `file_scan` — hasil upload scan hardcopy yang sudah TTD (diisi belakangan)

- **THE SYSTEM SHALL NOT** menimpa `file_docx` atau `file_pdf` yang sudah
  ada ketika `file_scan` diupload.

### REQ-GEN-006: Re-generate Dokumen
- **WHEN** Admin meminta re-generate dokumen suatu pengajuan (misalnya setelah
  data dikoreksi),
  **THE SYSTEM SHALL** membuat file `.docx` dan `.pdf` baru, menyimpannya
  dengan nama file baru (tidak menimpa yang lama), dan memperbarui kolom
  `file_docx` dan `file_pdf` di database ke path yang baru.

### REQ-GEN-007: Jenis Surat yang Didukung
Sistem harus mendukung empat jenis surat dengan template masing-masing:
1. **Surat Keterangan Aktif Kuliah** — untuk keperluan beasiswa, instansi, dll.
2. **Surat Pengantar Seminar Proposal** — berisi data judul dan pembimbing.
3. **Surat Undangan / Pengantar Sidang Skripsi** — berisi data judul, pembimbing,
   dan penguji.
4. **Surat Undangan Dosen Penguji** — ditujukan ke dosen penguji yang ditunjuk.

---

## Non-Functional Requirements

- **NFR-GEN-001:** Proses generate + konversi harus selesai dalam waktu maks
  30 detik untuk dokumen normal (1–2 halaman). Jika lebih lama, harus ada
  indikator loading di UI.
- **NFR-GEN-002:** File `.docx` dan `.pdf` hasil generate disimpan di
  `storage/app/private/surat/{pengajuan_id}/` — tidak dapat diakses via URL
  publik langsung; selalu melalui controller yang memverifikasi hak akses.
- **NFR-GEN-003:** Template `.docx` disimpan di
  `storage/app/private/templates/` — hanya dapat diubah oleh Admin lewat
  antarmuka yang tersedia atau langsung di server.
- **NFR-GEN-004:** LibreOffice headless harus sudah terinstall di server
  sebelum milestone ini bisa berjalan. Path binary dikonfigurasi via
  environment variable `LIBREOFFICE_PATH`.
- **NFR-GEN-005:** Nama file generate menggunakan UUID atau hash unik —
  tidak boleh menggunakan nama yang bisa ditebak (mis. nomor surat langsung)
  untuk mencegah enumerasi file.
