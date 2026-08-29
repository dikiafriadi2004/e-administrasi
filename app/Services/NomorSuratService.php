<?php

namespace App\Services;

use App\Models\Pengaturan;

/**
 * NomorSuratService — komponen-komponen nomor surat.
 *
 * Nomor surat TIDAK lagi digabung menjadi satu string di server.
 * Setiap bagian menjadi placeholder terpisah di template Word:
 *
 *   ${nomor_urut}     → angka urutan diinput admin (misal: 2032)
 *   ${kode_institusi} → dari Pengaturan (misal: UN11.F9)
 *   ${kode_prodi}     → dari Pengaturan (misal: PK.01.06)
 *   ${bulan_surat}    → bulan 2 digit otomatis (misal: 08)
 *   ${tahun_surat}    → tahun 4 digit otomatis (misal: 2026)
 *
 * Di template Word ditulis:
 *   ${nomor_urut}/${kode_institusi}/${kode_prodi}/${bulan_surat}/${tahun_surat}
 *
 * Hasilnya: 2032/UN11.F9/PK.01.06/08/2026
 */
class NomorSuratService
{
    /**
     * Kembalikan semua komponen nomor surat sebagai array placeholder.
     * Dipakai oleh SuratGeneratorService::buildPlaceholders().
     *
     * @param  string  $nomorUrut  Angka urutan yang diinput admin
     * @return array<string, string>
     */
    public function getComponents(string $nomorUrut): array
    {
        return [
            'nomor_urut' => $nomorUrut,
            'kode_institusi' => Pengaturan::nilai('kode_institusi', ''),
            'kode_prodi' => Pengaturan::nilai('kode_prodi', ''),
            'bulan_surat' => now()->format('m'),   // 01 – 12
            'tahun_surat' => now()->format('Y'),   // 2026
        ];
    }

    /**
     * Preview nomor lengkap sebagai string (hanya untuk tampilan di form admin).
     * Tidak dipakai oleh TemplateProcessor — hanya untuk UI.
     *
     * @param  string  $nomorUrut  Angka urutan (boleh kosong untuk placeholder)
     */
    public function previewNomor(string $nomorUrut = '...'): string
    {
        $components = $this->getComponents($nomorUrut);

        return implode('/', [
            $components['nomor_urut'],
            $components['kode_institusi'] ?: 'KODE-INST',
            $components['kode_prodi'] ?: 'KODE-PRODI',
            $components['bulan_surat'],
            $components['tahun_surat'],
        ]);
    }

    /**
     * Suffix untuk ditampilkan di form admin (bagian setelah nomor urut).
     * Contoh: "/UN11.F9/PK.01.06/08/2026"
     */
    public function getSuffix(): string
    {
        $components = $this->getComponents('');

        return sprintf(
            '/%s/%s/%s/%s',
            $components['kode_institusi'] ?: 'KODE-INST',
            $components['kode_prodi'] ?: 'KODE-PRODI',
            $components['bulan_surat'],
            $components['tahun_surat'],
        );
    }
}
