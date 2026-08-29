<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\MahasiswaImport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class MahasiswaImportController extends Controller
{
    public function create(): View
    {
        return view('admin.mahasiswa.import');
    }

    public function store(Request $request): RedirectResponse|View
    {
        $request->validate([
            'file_excel' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
        ], [
            'file_excel.required' => 'File Excel wajib diupload.',
            'file_excel.mimes' => 'Format file harus .xlsx, .xls, atau .csv.',
            'file_excel.max' => 'Ukuran file maksimal 5 MB.',
        ]);

        $import = new MahasiswaImport;

        Excel::import($import, $request->file('file_excel'));

        return redirect()->route('admin.mahasiswa.import.create')
            ->with('import_result', [
                'berhasil' => $import->berhasil,
                'dilewati' => $import->dilewati,
                'gagal' => $import->gagal,
            ]);
    }
}
