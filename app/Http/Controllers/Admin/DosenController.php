<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDosenRequest;
use App\Http\Requests\Admin\UpdateDosenRequest;
use App\Models\Dosen;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DosenController extends Controller
{
    public function index(): View
    {
        $perPage = (int) min(max((int) request('perPage', 10), 5), 100);
        $dosens = Dosen::orderBy('nama')->paginate($perPage)->withQueryString();

        return view('admin.dosen.index', array_merge(compact('dosens'), ['perPage' => $perPage]));
    }

    public function create(): View
    {
        return view('admin.dosen.create');
    }

    public function store(StoreDosenRequest $request): RedirectResponse
    {
        Dosen::create($request->validated());

        return redirect()->route('admin.dosen.index')
            ->with('success', 'Data dosen berhasil ditambahkan.');
    }

    public function edit(Dosen $dosen): View
    {
        return view('admin.dosen.edit', array_merge(compact('dosen'), ['perPage' => $perPage]));
    }

    public function update(UpdateDosenRequest $request, Dosen $dosen): RedirectResponse
    {
        $dosen->update($request->validated());

        return redirect()->route('admin.dosen.index')
            ->with('success', 'Data dosen berhasil diperbarui.');
    }

    /**
     * Dosen tidak dihapus — bisa ter-assign ke pengajuan historis.
     */
    public function destroy(Dosen $dosen): RedirectResponse
    {
        return redirect()->route('admin.dosen.index')
            ->with('warning', 'Penghapusan data dosen tidak diizinkan.');
    }
}
