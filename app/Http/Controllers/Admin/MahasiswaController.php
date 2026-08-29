<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMahasiswaRequest;
use App\Http\Requests\Admin\UpdateMahasiswaRequest;
use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class MahasiswaController extends Controller
{
    public function index(): View
    {
        $perPage = (int) min(max((int) request('perPage', 10), 5), 100);
        $mahasiswas = Mahasiswa::with('user')
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.mahasiswa.index', array_merge(compact('mahasiswas'), ['perPage' => $perPage]));
    }

    public function create(): View
    {
        return view('admin.mahasiswa.create');
    }

    public function store(StoreMahasiswaRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->filled('password') ? $request->password : $request->nim),
                'role' => 'mahasiswa',
                'is_active' => true,
            ]);

            Mahasiswa::create([
                'user_id' => $user->id,
                'nim' => $request->nim,
                'angkatan' => $request->angkatan,
                'alamat' => $request->alamat,
            ]);
        });

        return redirect()->route('admin.mahasiswa.index')
            ->with('success', 'Akun mahasiswa berhasil dibuat.');
    }

    public function edit(Mahasiswa $mahasiswa): View
    {
        $mahasiswa->load('user');

        return view('admin.mahasiswa.edit', compact('mahasiswa'));
    }

    public function update(UpdateMahasiswaRequest $request, Mahasiswa $mahasiswa): RedirectResponse
    {
        DB::transaction(function () use ($request, $mahasiswa) {
            $userUpdates = [
                'name' => $request->name,
                'email' => $request->email,
            ];

            if ($request->filled('password')) {
                $userUpdates['password'] = Hash::make($request->password);
            }

            $mahasiswa->user->update($userUpdates);

            $mahasiswa->update([
                'angkatan' => $request->angkatan,
                'alamat' => $request->alamat,
            ]);
        });

        return redirect()->route('admin.mahasiswa.index')
            ->with('success', 'Data mahasiswa berhasil diperbarui.');
    }

    public function toggleActive(Mahasiswa $mahasiswa): RedirectResponse
    {
        $mahasiswa->user->update([
            'is_active' => ! $mahasiswa->user->is_active,
        ]);

        $status = $mahasiswa->user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('admin.mahasiswa.index')
            ->with('success', "Akun mahasiswa berhasil {$status}.");
    }

    /**
     * Tidak diimplementasi — mahasiswa tidak dihapus, hanya dinonaktifkan.
     */
    public function destroy(Mahasiswa $mahasiswa): RedirectResponse
    {
        return redirect()->route('admin.mahasiswa.index')
            ->with('warning', 'Penghapusan akun mahasiswa tidak diizinkan. Gunakan fitur nonaktifkan.');
    }
}
