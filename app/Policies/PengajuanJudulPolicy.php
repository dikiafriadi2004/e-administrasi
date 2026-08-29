<?php

namespace App\Policies;

use App\Models\PengajuanJudul;
use App\Models\User;

class PengajuanJudulPolicy
{
    public function view(User $user, PengajuanJudul $judul): bool
    {
        if ($user->role === 'mahasiswa') {
            return $user->mahasiswa?->id === $judul->mahasiswa_id;
        }

        return in_array($user->role, ['admin', 'kaprodi']);
    }
}
