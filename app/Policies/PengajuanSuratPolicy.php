<?php

namespace App\Policies;

use App\Models\PengajuanSurat;
use App\Models\User;

class PengajuanSuratPolicy
{
    /**
     * Mahasiswa hanya bisa lihat miliknya sendiri.
     * Admin & Kaprodi bisa lihat semua.
     */
    public function view(User $user, PengajuanSurat $surat): bool
    {
        if ($user->role === 'mahasiswa') {
            return $user->mahasiswa?->id === $surat->mahasiswa_id;
        }

        return in_array($user->role, ['admin', 'kaprodi']);
    }

    /**
     * Download: aturan sama dengan view.
     */
    public function download(User $user, PengajuanSurat $surat): bool
    {
        return $this->view($user, $surat);
    }

    /**
     * Hanya Admin & Kaprodi yang boleh generate surat.
     */
    public function generate(User $user, PengajuanSurat $surat): bool
    {
        return in_array($user->role, ['admin', 'kaprodi']);
    }

    /**
     * Hanya Admin & Kaprodi yang boleh upload scan.
     */
    public function uploadScan(User $user, PengajuanSurat $surat): bool
    {
        return in_array($user->role, ['admin', 'kaprodi']);
    }
}
