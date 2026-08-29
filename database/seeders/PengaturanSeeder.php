<?php

namespace Database\Seeders;

use App\Models\Pengaturan;
use Illuminate\Database\Seeder;

class PengaturanSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            // Identitas Institusi
            ['key' => 'nama_universitas', 'label' => 'Nama Universitas',                       'grup' => 'institusi', 'value' => ''],
            ['key' => 'nama_fakultas',    'label' => 'Nama Fakultas',                          'grup' => 'institusi', 'value' => ''],
            ['key' => 'nama_prodi',       'label' => 'Nama Program Studi',                     'grup' => 'institusi', 'value' => ''],
            ['key' => 'alamat_prodi',     'label' => 'Alamat Lengkap Prodi',                   'grup' => 'institusi', 'value' => ''],
            ['key' => 'telepon_prodi',    'label' => 'Telepon / Fax',                          'grup' => 'institusi', 'value' => ''],
            ['key' => 'email_prodi',      'label' => 'Email Prodi',                            'grup' => 'institusi', 'value' => ''],
            ['key' => 'kota_prodi',       'label' => 'Kota (untuk tanggal surat)',             'grup' => 'institusi', 'value' => ''],

            // Kepala Program Studi
            ['key' => 'nama_kaprodi',     'label' => 'Nama Lengkap & Gelar Kaprodi',          'grup' => 'kaprodi',   'value' => ''],
            ['key' => 'nip_kaprodi',      'label' => 'NIP Kaprodi',                           'grup' => 'kaprodi',   'value' => ''],

            // Dekan
            ['key' => 'nama_dekan',       'label' => 'Nama Lengkap & Gelar Dekan',            'grup' => 'kaprodi',   'value' => ''],
            ['key' => 'nip_dekan',        'label' => 'NIP Dekan',                             'grup' => 'kaprodi',   'value' => ''],

            // Kalender Akademik
            ['key' => 'semester_aktif',   'label' => 'Semester Aktif (Ganjil / Genap)',       'grup' => 'akademik',  'value' => 'Ganjil'],
            ['key' => 'tahun_akademik',   'label' => 'Tahun Akademik (contoh: 2025/2026)',    'grup' => 'akademik',  'value' => '2025/2026'],

            // Penomoran Surat
            ['key' => 'kode_institusi',   'label' => 'Kode Institusi (contoh: UN11.F9)',      'grup' => 'penomoran', 'value' => ''],
            ['key' => 'kode_prodi',       'label' => 'Kode Prodi (contoh: PK.01.06)',         'grup' => 'penomoran', 'value' => ''],

            // Sistem
            ['key' => 'libreoffice_path', 'label' => 'Path LibreOffice (kosong = auto)',      'grup' => 'sistem',    'value' => ''],
        ];

        foreach ($defaults as $item) {
            Pengaturan::firstOrCreate(
                ['key' => $item['key']],
                ['value' => $item['value'], 'label' => $item['label'], 'grup' => $item['grup']]
            );
        }
    }
}
