<?php

namespace App\Http\Controllers\Kaprodi;

use App\Http\Controllers\Controller;
use App\Models\PengajuanSurat;
use App\Services\PengajuanStateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UploadScanController extends Controller
{
    public function __construct(
        private readonly PengajuanStateService $stateService
    ) {}

    public function store(Request $request, PengajuanSurat $surat): RedirectResponse
    {
        $request->validate([
            'file_scan' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ], [
            'file_scan.required' => 'File scan wajib diupload.',
            'file_scan.mimes' => 'File scan harus berformat PDF.',
            'file_scan.max' => 'Ukuran file maksimal 10 MB.',
        ]);

        $path = $request->file('file_scan')->storeAs(
            "surat/{$surat->id}",
            'scan_'.now()->format('Ymd_His').'.pdf',
            'private'
        );

        $this->stateService->uploadScan($surat, auth()->user(), $path);

        return back()->with('success', 'Scan surat berhasil diupload. Status diperbarui.');
    }
}
