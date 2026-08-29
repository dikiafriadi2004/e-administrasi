---
paths:
  - app/Services/SuratGeneratorService.php
  - app/Services/NomorSuratService.php
---

# Services

## Konfigurasi surat dari DB, bukan .env
Semua konfigurasi surat (nama_kaprodi, nip_kaprodi, kota_prodi, libreoffice_path, dll) disimpan di tabel `pengaturan` dan dibaca via `Pengaturan::nilai('key')`. Jangan gunakan config/surat.php atau env SURAT_* — sudah dihapus. LibreOffice path: kosong di DB = auto-fallback ke `soffice` di PATH (standar Linux VPS). Nomor surat diinput manual oleh admin saat generate, tidak di-auto-generate.

## NomorSuratService hanya sebagai preview/helper, bukan auto-generate
Sejak refactoring nomor manual, NomorSuratService::generate() tidak dipanggil saat generate surat. Gunakan preview() untuk saran nomor di form. Kode institusi/fakultas/prodi dibaca dari tabel pengaturan (key: kode_institusi, kode_fakultas, kode_prodi), bukan config/env.
