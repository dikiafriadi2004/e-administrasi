<?php

namespace App\Imports;

use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MahasiswaImport implements ToCollection, WithHeadingRow
{
    /** @var array<int, string> */
    public array $berhasil = [];

    /** @var array<int, array{baris: int, nim: string, alasan: string}> */
    public array $dilewati = [];

    /** @var array<int, array{baris: int, nim: string, alasan: string}> */
    public array $gagal = [];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $baris = $index + 2; // header = baris 1, data mulai baris 2

            $nim = trim((string) ($row['nim'] ?? ''));
            $nama = trim((string) ($row['nama'] ?? ''));
            $email = trim((string) ($row['email'] ?? ''));
            $angkatan = trim((string) ($row['angkatan'] ?? ''));
            $alamat = trim((string) ($row['alamat'] ?? ''));

            // Cek duplikat NIM atau email
            $nimAda = $nim && Mahasiswa::where('nim', $nim)->exists();
            $emailAda = $email && User::where('email', $email)->exists();

            if ($nimAda || $emailAda) {
                $alasan = $nimAda ? 'NIM sudah terdaftar' : 'Email sudah terdaftar';
                $this->dilewati[] = ['baris' => $baris, 'nim' => $nim ?: '-', 'alasan' => $alasan];

                continue;
            }

            // Validasi data per baris
            $validator = Validator::make([
                'nim' => $nim,
                'nama' => $nama,
                'email' => $email,
                'angkatan' => $angkatan,
            ], [
                'nim' => 'required|string|max:20',
                'nama' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'angkatan' => 'required|digits:4|integer|min:2000|max:'.(date('Y') + 1),
            ]);

            if ($validator->fails()) {
                $this->gagal[] = [
                    'baris' => $baris,
                    'nim' => $nim ?: '-',
                    'alasan' => implode(', ', $validator->errors()->all()),
                ];

                continue;
            }

            // Buat user + mahasiswa dalam transaksi
            DB::transaction(function () use ($nim, $nama, $email, $angkatan, $alamat) {
                $user = User::create([
                    'name' => $nama,
                    'email' => $email,
                    'password' => Hash::make($nim),
                    'role' => 'mahasiswa',
                    'is_active' => true,
                ]);

                Mahasiswa::create([
                    'user_id' => $user->id,
                    'nim' => $nim,
                    'angkatan' => (int) $angkatan,
                    'alamat' => $alamat ?: null,
                ]);
            });

            $this->berhasil[] = $nim;
        }
    }
}
