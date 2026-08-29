<?php

namespace App\Exceptions;

use Exception;

class SuratGenerationException extends Exception
{
    public static function templateTidakDitemukan(string $jenisSurat): self
    {
        return new self("Template aktif untuk jenis surat '{$jenisSurat}' tidak ditemukan.");
    }

    public static function konversiPdfGagal(string $detail = ''): self
    {
        $pesan = 'Konversi PDF gagal. Pastikan LibreOffice terinstall dan path-nya benar.';
        if ($detail) {
            $pesan .= " Detail: {$detail}";
        }

        return new self($pesan);
    }

    public static function filePdfTidakDitemukan(string $path): self
    {
        return new self("File PDF tidak ditemukan setelah konversi: {$path}");
    }
}
