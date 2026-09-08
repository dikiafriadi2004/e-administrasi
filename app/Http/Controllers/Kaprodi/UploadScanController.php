<?php

namespace App\Http\Controllers\Kaprodi;

use App\Exceptions\InvalidStateTransitionException;
use App\Http\Controllers\Controller;
use App\Models\PengajuanSurat;
use App\Services\PengajuanStateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

        $surat->loadMissing('mahasiswa');
        $nim = $surat->mahasiswa?->nim ?? 'unknown';
        $path = $request->file('file_scan')->storeAs(
            "surat/{$nim}/{$surat->jenis_surat}",
            'scan_'.now()->format('Ymd_His').'.pdf',
            'private'
        );

        try {
            $this->stateService->uploadScan($surat, auth()->user(), $path);
        } catch (InvalidStateTransitionException $e) {
            // Hapus file yang sudah terupload karena state transition gagal
            Storage::disk('private')->delete($path);

            return back()->with('error', 'Gagal upload scan: '.$e->getMessage());
        }

        return back()->with('success', 'Scan surat berhasil diupload. Status diperbarui.');
    }
}
