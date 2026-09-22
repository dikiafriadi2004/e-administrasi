<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengaturan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PengaturanController extends Controller
{
    public function index(): View
    {
        $pengaturan = Pengaturan::orderBy('grup')->orderBy('key')->get()->groupBy('grup');

        return view('admin.pengaturan.index', compact('pengaturan'));
    }

    public function update(Request $request): RedirectResponse
    {
        $allowedKeys = Pengaturan::pluck('key')->toArray();

        $rules = [];
        foreach ($allowedKeys as $key) {
            $rules[$key] = ['nullable', 'string', 'max:1000'];
        }
        // Hanya validasi key yang dikenal — tolak key asing sejak awal.
        $request->validate($rules);

        $data = $request->only($allowedKeys);

        foreach ($data as $key => $value) {
            // Hanya update key yang sudah ada di DB (keamanan — jangan bisa inject key sembarangan)
            if (Pengaturan::where('key', $key)->exists()) {
                Pengaturan::set($key, $value ?: null);
            }
        }

        // Invalidasi semua cache pengaturan
        Pengaturan::flushCache();

        return redirect()->route('admin.pengaturan.index')
            ->with('success', 'Pengaturan berhasil disimpan.');
    }
}
