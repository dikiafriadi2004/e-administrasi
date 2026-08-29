<?php

namespace App\Http\Controllers;

use App\Models\BerkasPengajuan;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

trait BerkasDownloadTrait
{
    /**
     * Download berkas syarat yang diupload mahasiswa.
     * Subclass harus override authorizeDownloadBerkas() untuk scope akses.
     */
    public function downloadBerkas(BerkasPengajuan $berkas): StreamedResponse
    {
        $this->authorizeDownloadBerkas($berkas);

        abort_unless(Storage::disk('private')->exists($berkas->path_file), 404, 'File tidak ditemukan.');

        return Storage::disk('private')->download($berkas->path_file, $berkas->nama_asli);
    }

    /** Override di subclass untuk validasi akses */
    protected function authorizeDownloadBerkas(BerkasPengajuan $berkas): void
    {
        // Default: allow all authenticated users (middleware di route sudah handle role)
    }
}
