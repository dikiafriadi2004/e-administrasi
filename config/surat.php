<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Kode Penomoran Surat
    |--------------------------------------------------------------------------
    | Format: {urutan}/{kode_institusi}/{kode_fakultas}/{kode_prodi}/{bulan_romawi}/{tahun}
    | Contoh: 001/UN-XX/FAK/TI/VIII/2026
    */
    'kode_institusi' => env('SURAT_KODE_INSTITUSI', 'UN-XX'),
    'kode_fakultas' => env('SURAT_KODE_FAKULTAS', 'FAK'),
    'kode_prodi' => env('SURAT_KODE_PRODI', 'TI'),

    /*
    |--------------------------------------------------------------------------
    | Identitas Kepala Prodi (untuk placeholder surat)
    |--------------------------------------------------------------------------
    */
    'nama_kaprodi' => env('SURAT_NAMA_KAPRODI', 'Kepala Program Studi'),
    'nip_kaprodi' => env('SURAT_NIP_KAPRODI', '-'),

    /*
    |--------------------------------------------------------------------------
    | Path LibreOffice untuk konversi docx → pdf
    |--------------------------------------------------------------------------
    | Windows (Herd lokal):
    |   LIBREOFFICE_PATH="C:\Program Files\LibreOffice\program\soffice.exe"
    | Linux (production):
    |   LIBREOFFICE_PATH=/usr/bin/soffice
    */
    'libreoffice_path' => env('LIBREOFFICE_PATH', 'soffice'),

    /*
    |--------------------------------------------------------------------------
    | Timeout konversi (detik)
    |--------------------------------------------------------------------------
    */
    'convert_timeout' => (int) env('SURAT_CONVERT_TIMEOUT', 60),
];
