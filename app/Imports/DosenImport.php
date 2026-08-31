<?php

namespace App\Imports;

use App\Models\Dosen;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DosenImport implements ToCollection, WithHeadingRow
{
    /** @var array<int, string> */
    public array $berhasil = [];

    /** @var array<int, array{baris: int, nip: string, alasan: string}> */
    public array $dilewati = [];

    /** @var array<int, array{baris: int, nip: string, alasan: string}> */
    public array $gagal = [];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $baris = $index + 2;

            $nip = trim((string) ($row['nip'] ?? ''));
            $nama = trim((string) ($row['nama'] ?? ''));
            $kapasitasMaksimal = trim((string) ($row['kapasitas_maksimal'] ?? ''));

            // Cek duplikat NIP
            if ($nip && Dosen::where('nip', $nip)->exists()) {
                $this->dilewati[] = ['baris' => $baris, 'nip' => $nip, 'alasan' => 'NIP sudah terdaftar'];

                continue;
            }

            $validator = Validator::make([
                'nip' => $nip,
                'nama' => $nama,
            ], [
                'nip' => 'required|string|max:30',
                'nama' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                $this->gagal[] = [
                    'baris' => $baris,
                    'nip' => $nip ?: '-',
                    'alasan' => implode(', ', $validator->errors()->all()),
                ];

                continue;
            }

            Dosen::create([
                'nip' => $nip,
                'nama' => $nama,
                'kapasitas_maksimal' => $kapasitasMaksimal !== '' ? (int) $kapasitasMaksimal : null,
            ]);

            $this->berhasil[] = $nip;
        }
    }
}
