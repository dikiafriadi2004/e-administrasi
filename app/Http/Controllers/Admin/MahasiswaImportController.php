<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\MahasiswaImport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MahasiswaImportController extends Controller
{
    public function create(): View
    {
        return view('admin.mahasiswa.import');
    }

    /** Download template Excel kosong untuk diisi data mahasiswa. */
    public function template(): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Mahasiswa');

        // Header baris 1
        $headers = ['nim', 'nama', 'email', 'angkatan', 'alamat'];
        foreach ($headers as $col => $header) {
            $cell = chr(65 + $col).'1';
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->getFont()->setBold(true);
            $sheet->getColumnDimensionByColumn($col + 1)->setWidth(
                match ($header) {
                    'nama', 'email', 'alamat' => 30,
                    'nim' => 20,
                    default => 15,
                }
            );
        }

        // Baris contoh
        $contoh = [
            ['12345001', 'Nama Mahasiswa', 'mahasiswa@email.com', date('Y') - 3, 'Jl. Contoh No. 1'],
            ['12345002', 'Mahasiswa Dua', 'mahasiswa2@email.com', date('Y') - 2, ''],
        ];
        foreach ($contoh as $r => $row) {
            foreach ($row as $c => $val) {
                $sheet->setCellValue(chr(65 + $c).($r + 2), $val);
            }
        }

        // Warnai baris header
        $sheet->getStyle('A1:E1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E2E8F0');

        // Keterangan di bawah
        $sheet->setCellValue('A'.($r + 4), 'Catatan:');
        $sheet->setCellValue('A'.($r + 5), '- nim      : NIM mahasiswa (wajib, unik, maks 20 karakter)');
        $sheet->setCellValue('A'.($r + 6), '- nama     : Nama lengkap (wajib)');
        $sheet->setCellValue('A'.($r + 7), '- email    : Email aktif (wajib, unik)');
        $sheet->setCellValue('A'.($r + 8), '- angkatan : Tahun angkatan 4 digit (wajib, contoh: 2023)');
        $sheet->setCellValue('A'.($r + 9), '- alamat   : Alamat lengkap (opsional)');
        $sheet->setCellValue('A'.($r + 10), '- Password default: NIM mahasiswa (wajib diganti setelah login)');

        $writer = new Xlsx($spreadsheet);
        $filename = 'template_import_mahasiswa.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
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
