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
        $data = $request->except(['_token', '_method']);

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
