<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class ProfilController extends Controller
{
    /**
     * Halaman profil pengguna — tersedia untuk semua role.
     * Menampilkan info akun dan form ganti password.
     */
    public function show(): View
    {
        return view('profil.show', [
            'user' => auth()->user(),
        ]);
    }
}
