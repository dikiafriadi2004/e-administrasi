<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\SuratGenerationException;
use App\Http\Controllers\Controller;
use App\Models\PengajuanSurat;
use App\Services\NomorSuratService;
use App\Services\PengajuanStateService;
use App\Services\SuratGeneratorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GenerateSuratController extends Controller
{
    public function __construct(
        private readonly SuratGeneratorService $generator,
        private readonly PengajuanStateService $stateService,
        private readonly NomorSuratService $nomorService,
    ) {}

    /**
     * Generate DOCX untuk satu pengajuan surat.
     * Nomor surat diinput manual oleh admin.
     * Tidak ada konversi PDF — admin cetak dari Word, minta TTD Kaprodi, lalu scan + upload.
     */
    public function generate(Request $request, PengajuanSurat $surat): RedirectResponse
    {
        Gate::authorize('generate', $surat);

        $request->validate([
            'nomor_urutan' => ['required', 'string', 'max:20'],
        ], [
            'nomor_urutan.required' => 'Nomor urutan surat wajib diisi sebelum generate.',
        ]);

        // Simpan nomor_urut saja ke database — suffix akan jadi placeholder terpisah di template
        $surat->update(['nomor_surat' => trim($request->nomor_urutan)]);
        $surat->refresh();

        try {
            $this->generator->generate($surat);

            // Ubah status diajukan → menunggu_ttd
            if ($surat->status === 'diajukan') {
                $this->stateService->generateSurat($surat, auth()->user());
            }

            return back()->with('success', 'Surat berhasil dibuat (DOCX). Unduh, cetak, dan minta tanda tangan Kaprodi, kemudian upload scan di bawah.');
        } catch (SuratGenerationException $e) {
            Log::error('Generate surat gagal', [
                'pengajuan_id' => $surat->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Download file surat — hanya docx dan scan (tidak ada pdf).
     */
    public function download(PengajuanSurat $surat, string $tipe): StreamedResponse
    {
        Gate::authorize('download', $surat);

        $path = match ($tipe) {
            'docx' => $surat->file_docx,
            'pdf' => $surat->file_pdf,
            'scan' => $surat->file_scan,
            default => null,
        };

        abort_if(! $path, 404, 'File tidak tersedia atau belum digenerate.');
        abort_unless(Storage::disk('private')->exists($path), 404, 'File tidak tersedia.');

        // Format: {nim}_{nama_depan}_{jenis_surat}.ext — contoh: 1215_Herman_seminar_proposal.docx
        $surat->loadMissing('mahasiswa.user');
        $namaFile = PengajuanSurat::namaFileDownload($surat, $tipe);

        return Storage::disk('private')->download($path, $namaFile);
    }

    /**
     * Upload hasil scan surat yang sudah ditandatangani Kaprodi.
     */
    public function uploadScan(Request $request, PengajuanSurat $surat): RedirectResponse
    {
        Gate::authorize('uploadScan', $surat);

        $request->validate([
            'file_scan' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ], [
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

        $this->stateService->uploadScan($surat, auth()->user(), $path);

        return back()->with('success', 'Scan surat berhasil diupload. Mahasiswa kini dapat mengunduh surat.');
    }
}
