<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\DosenImport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DosenImportController extends Controller
{
    public function create(): View
    {
        return view('admin.dosen.import');
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

        $import = new DosenImport;
        Excel::import($import, $request->file('file_excel'));

        return redirect()->route('admin.dosen.import.create')
            ->with('import_result', [
                'berhasil' => $import->berhasil,
                'dilewati' => $import->dilewati,
                'gagal' => $import->gagal,
            ]);
    }

    /** Download template Excel kosong untuk diisi data dosen. */
    public function template(): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Dosen');

        // Header
        $headers = ['nip', 'nama', 'kapasitas_maksimal'];
        foreach ($headers as $col => $header) {
            $cell = chr(65 + $col).'1';
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->getFont()->setBold(true);
            $sheet->getColumnDimensionByColumn($col + 1)->setWidth(
                match ($header) {
                    'nama' => 35,
                    'nip' => 25,
                    default => 20,
                }
            );
        }

        // Baris contoh
        $contoh = [
            ['198001012005011001', 'Dr. Nama Dosen, M.Kom.', 5],
            ['197505152003121002', 'Nama Dosen Dua, S.T., M.T.', ''],
        ];
        foreach ($contoh as $r => $row) {
            foreach ($row as $c => $val) {
                $sheet->setCellValue(chr(65 + $c).($r + 2), $val);
            }
        }

        // Warnai header
        $sheet->getStyle('A1:C1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E2E8F0');

        // Keterangan
        $sheet->setCellValue('A5', 'Catatan:');
        $sheet->setCellValue('A6', '- nip                : NIP dosen (wajib, unik, maks 30 karakter)');
        $sheet->setCellValue('A7', '- nama               : Nama lengkap beserta gelar (wajib)');
        $sheet->setCellValue('A8', '- kapasitas_maksimal : Batas maks bimbingan (opsional, kosongkan jika tidak dibatasi)');

        $writer = new Xlsx($spreadsheet);
        $filename = 'template_import_dosen.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
